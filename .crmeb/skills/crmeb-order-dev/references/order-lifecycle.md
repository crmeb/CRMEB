# 订单生命周期总览（状态机 · 推进机制 · 事件地图）

> 本文是 `crmeb-order-dev` 技能子文档，总览见上级 [../SKILL.md](../SKILL.md)。中台统一状态码定义、Adapter 契约等强制条款见 [订单中台规范](../../../rules/订单中台规范.md)，本文只讲流程与代码定位。

## 一、生命周期全景

以实物商品（normal）为例，扩展状态最全；其他类型的扩展状态各异，但中台状态收敛规则相同：

```
                    ┌─────────────── 取消（用户/超时）──────────────┐
                    ▼                                              │
 创建 ──► 待支付 ──► 支付成功 ──► 待发货 ──► 待收货 ──► 待评价 ──► 已完成
 pending_    processing     processing   processing  pending_   completed
 payment     (pay_status=1)  (pending_   (pending_    review
                            shipped)     receive)
                    │                          │
                    │      需安装商品：待安装(pending_installation)
                    │                          │
                    └────────► 售后中(after_sale) ──► 已退款(refunded)
                              （任意已支付节点可进入；审核拒绝则恢复原状态）
```

- **扩展表状态**（如 `pt_normal_order.status`：`pending_shipped`/`pending_receive`…）是各产品类型自己的状态机，由模块监听器推进。
- **中台状态码**（`eb_order_center.status_code`，8 种通用值）是聚合展示用的统一状态，由 Adapter 从扩展表状态**推导**（`resolveStatusCode`）或推进时**同步**（`advance` → `syncStatus`）。码表见 [订单中台规范](../../../rules/订单中台规范.md)。
- **业务状态 badge**（`business_status`，如「拼团中」「待安装」）挂在 `extra_info`，不改变 `status_code`。

## 二、状态推进的唯一路径：advance()

所有履约动作（支付、发货、收货、评价、超时取消）最终都收敛到 [BaseOrderTypeAdapter::advance()](../../../../backend/crmeb/trade/order/type/BaseOrderTypeAdapter.php)：

```php
// 调用方式：{Type}OrderAdapter::instance()->advance($orderNo, $extStatus, $extra)
public function advance(string $orderNo, string $extStatus, array $extra = []): void
// 1. updateExtStatus()        写扩展表 status 字段（+extra）
// 2. onEnter 回调             状态节点的进入钩子（状态树中声明）
// 3. syncCenterAfterAdvance() 按状态树 center_status 映射，
//    经 OrderCenterSyncInterface::syncStatus() 同步中台
```

要点：

- 状态树 `defineStatusTree()` 是唯一数据源（`OrderFlowNode` DSL：`centerStatus/label/userButtons/on_enter`），未定义的状态节点直接抛异常。
- `canAdvanceTo()` 提供幂等检查；依赖副作用（返佣、通知）的节点建议在 `on_enter` 或独立监听器中做，保持 advance 本身可重放。
- 中台同步走 [OrderCenterSyncInterface](../../../../backend/crmeb/trade/order/type/OrderCenterSyncInterface.php)（绑定到 `OrderCenterServices`），实现 crmeb 核心层不反向依赖 app 层的依赖反转。

## 三、订单事件地图

事件调用一律用字符串 key（不带 `Listener` 后缀），注册分布与全量清单见 [事件规范](../../../rules/事件规范.md)。下表按生命周期阶段给出**触发位置 → 典型监听职责**：

| 阶段 | 事件 | 触发位置（app 层） | 模块监听器典型职责 |
| --- | --- | --- | --- |
| 创建后 | `OrderCreateAfter` | [CenterOrderController.php](../../../../backend/app/api/controller/v1/order/CenterOrderController.php)（create 末尾） | 下单通知；调度超时取消；分销快照 |
| 支付成功 | `OrderPaySuccess` | [OrderSuccessServices.php](../../../../backend/app/services/order/OrderSuccessServices.php)（`paySuccess` 内，先 `syncStatus('processing')` 再触发） | 推进扩展表待发货/自动履约；返佣；拼团推进；支付通知 |
| 支付成功（旁路） | `OrderPayLottery`、`OrderPaySuccessCustomerNotice`、`OutPush` | 同上及 [app/jobs/OrderJob.php](../../../../backend/app/jobs/OrderJob.php) | 抽奖次数、客服/模板消息、外部推送 |
| 发货 | `OrderDelivery` | [OrderDeliveryServices.php](../../../../backend/app/services/order/OrderDeliveryServices.php) | 推进待收货；发货通知；微信发货管理 |
| 收货 | `OrderTake` | [OrderTakeServices.php](../../../../backend/app/services/order/OrderTakeServices.php)（`takeOrder`） | 推进待评价/待安装；收货奖励（返佣/积分/经验在服务层统一处理） |
| 评价 | `OrderComment` | 评价服务（`StoreProductReplyServices`） | 标记已评价；全部明细评价完则推进已完成 |
| 取消 | `OrderCancel` | [OrderCenterUserServices.php](../../../../backend/app/services/order/OrderCenterUserServices.php)（`cancelOrder`，事务内触发） | 推进 canceled；释放库存 |
| 删除 | `OrderDelete` | `OrderCenterUserServices` | 扩展表逻辑删除 |
| 改价 | `OrderPriceChange` | 改价服务 | 同步扩展表实付金额 |
| 售后 | `OrderRefundSuccess` | [RefundEffectServices.php](../../../../backend/app/services/order/RefundEffectServices.php) | 更新扩展表与中台退款状态；返还积分 |
| 列表/详情扩展 | `OrderCenterListExtend` / `OrderCenterDetailExtend` | `OrderCenterServices`、`OrderCenterUserServices` | 注入扩展字段/渲染 section |
| 业务状态解析 | `OrderCenterBusinessStatusResolve` | `OrderAdapter::resolveBusinessStatus` 内 | 拼团等覆盖业务状态 badge |

> **已知待接线事件（现状说明）**：`OrderComplete`、`OrderRefundCreateAfter`、`OrderRefundRefuse`、`OrderRefundCancelAfter` 的模块监听器已在各 `event.php` 注册，但 app 层服务触发点尚未接线（[app/command/EventCheck.php](../../../../backend/app/command/EventCheck.php) 有标注）。依赖这些事件的新代码需先确认触发链路已存在，或改用已接线事件。

## 四、SafeEvent 分级对订单监听器的要求

框架通过 `SafeEvent` 对监听器异常分级隔离（规范见 [事件规范](../../../rules/事件规范.md) 2.4 节），订单开发需遵守：

- **通知类监听器命名必须以 `NoticeListener` 结尾**、展示扩展类以 `ExtendListener` 结尾——这是框架识别旁路职责并隔离异常的唯一依据。订单族监听器已按此拆分（如 `OrderPaidListener` 业务推进 + `OrderPaySuccessNoticeListener` 通知）。
- **关键监听器**（状态推进、库存、资金）异常会上抛中断主流程以保数据一致性；其内部对可降级失败自行 try/catch 并记日志，但禁止吞掉事务类异常。

## 五、新订单类型接入时的监听器清单

新增产品类型/订单类型时，参照 normal 的 [event.php](../../../../backend/modules/product/normal/event.php) 按需挂载：

| 监听器 | 必需性 | 职责 |
| --- | --- | --- |
| `OrderPaidListener` | 必须 | 校验 `product_type` 防误触 → 幂等推进扩展表（自动履约型在此调 `Handler::afterPay()`） |
| `OrderPaySuccessNoticeListener` | 建议 | 支付成功通知（命名带 Notice 后缀获得异常隔离） |
| `OrderCancelListener` | 必须 | 推进 canceled、补 cancel_time、释放库存 |
| `OrderRefundSuccessListener` | 有售后必须 | 更新扩展表/中台退款状态（如卡密回收） |
| `OrderCenterDetailExtendListener` | 建议 | 详情页注入业态数据与渲染 section |
| `OrderCenterListExtendListener` | 按需 | 列表注入扩展字段 |
| `OrderTakeListener / OrderDeliveryListener / OrderCommentListener` | 实物类必须 | 收货/发货/评价推进（纯虚拟类通常无这些节点） |

监听器内的通用防御写法：先查中台记录校验 `product_type`/`source_type` 防误触发，再用扩展表当前状态做幂等短路，最后 `advance()`。示例参考 `modules/product/cardkey/listener/OrderPaidListener.php`（卡密模块未随当前版本提供，路径为规划参考）。

## 六、状态异常排查指引

| 现象 | 排查顺序 |
| --- | --- |
| 中台与扩展表状态不一致 | 详情接口会经 Adapter 实时重新推导；不一致通常是推进时漏调 `advance()` 直接改了扩展表，或 `defineStatusTree` 未定义该节点 |
| 支付后状态没推进 | 1) `OrderSuccessServices::paySuccess` 是否触发 `OrderPaySuccess`；2) 模块监听器是否校验了 `product_type` 导致被过滤；3) 监听器幂等短路条件是否过宽 |
| 订单卡在待支付但钱已扣 | 查 `PayRecordServices::complete()` 幂等回调与 `paySuccess` 链路日志（支付回调可能异步重试） |
| 取消后库存没回滚 | 用户取消走 `OrderCancel` 事件、超时取消走 `UnpaidOrderCancelJob` 直调适配器，两条路径最终都应经过 `OrderLifecycleServices::handleCancel()`，确认监听器/Job 是否漏接 |
| 详情无进度条/按钮 | Adapter 的 `getProgress()`/状态树按钮是否实现；中台组装是否调用 `getProgressFromHandler` |

## 七、参考文件

- 中台契约与状态码：[订单中台规范](../../../rules/订单中台规范.md)
- 状态推进基类：[crmeb/trade/order/type/BaseOrderTypeAdapter.php](../../../../backend/crmeb/trade/order/type/BaseOrderTypeAdapter.php)
- 分阶段详解：[创建与支付](order-create-pay.md)、[履约流转](order-flow.md)、[售后与退款](order-aftersale.md)
