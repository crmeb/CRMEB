# 阶段二：履约流转（发货 · 收货 · 评价 · 完成 · 取消）

> 本文是 `crmeb-order-dev` 技能子文档，总览见上级 [../SKILL.md](../SKILL.md)。以实物商品（normal）为主干讲解，其他产品类型的差异在各模块同名监听器中体现。状态推进机制统一见 [订单生命周期](order-lifecycle.md)。

## 一、发货

```
管理端操作
  modules/product/normal/controller/admin/OrderController.php::shipment / batchShipment
    └─► app/services/order/OrderDeliveryServices.php::delivery / doDelivery
          ├─ 校验订单状态、运单信息；写物流信息
          └─ event('OrderDelivery', [...])
               ├─ OrderDeliveryListener      推进扩展表 pending_receive（advance）
               └─ OrderDeliveryNoticeListener 发货通知（含微信发货管理同步）
```

- 电子核销类（卡密）没有物流发货，履约动作在**支付成功时**由各自 `OrderPaidListener` 完成（出票/发卡），核销走 `OrderWriteOffServices`。
- 虚拟/卡密类履约依赖 `Handler::afterPay()` 自动完成，无人工发货环节。

## 二、收货

```
用户端确认收货
  CenterOrderController::take
    └─► app/services/order/OrderTakeServices.php::takeOrder
          ├─ 订单锁 + 收货奖励幂等标记（take_rewards_given）
          ├─ 统一收货奖励：返佣 / 赠积分 / 经验（OrderLifecycleServices）
          └─ event('OrderTake', [...])
               ├─ OrderTakeListener   无需安装 → pending_review（advance）
               │                      需安装 → pending_installation（创建履约记录）
               └─ OrderTakeNoticeListener 收货通知
```

- **待安装分支**：`pt_normal_order.has_installation=1` 的订单收货后进入「待安装」，安装完成后继续推进到待评价；「待安装」通过 `resolveBusinessStatus` 以 badge 展示，中台仍是 `processing`。
- **自动收货**：`modules/product/normal/crontab/NormalTakeDeliveryTask` 按 `system_delivery_time` 天数到期自动确认收货，复用 `takeOrder`。该任务依赖 `eb_system_crontab` 种子配置，**未在模块 App.php 注册**。

## 三、评价与完成

```
用户评价
  CenterOrderController::comment
    └─► StoreProductReplyServices（写评价）
          └─ event('OrderComment', [...])
               └─ OrderCommentListener 标记该明细已评价
                    └─ OrderCenterServices::checkOrderOver()
                         全部明细评价完 → completeOrderStatus()
                         → {Type}OrderAdapter::advance(completed)
未评价超时
  OrderCenterServices::autoComment() 自动好评 → 同样推进 completed
```

- 订单完成的判定权在中台 `checkOrderOver()`：逐明细检查评价状态，全完成才推进，避免部分评价即完成。
- `OrderComplete` 事件监听器已注册但触发点**待接线**（现状见 [订单生命周期·事件地图](order-lifecycle.md#三订单事件地图)），依赖它的后置逻辑暂挂在评价完成链路或 `advance` 的 `on_enter` 中。

## 四、取消（双路径）

| | 用户主动取消 | 超时自动取消 |
| --- | --- | --- |
| 入口 | `OrderCenterUserServices::cancelOrder`（仅待支付可取消） | [app/jobs/UnpaidOrderCancelJob.php](../../../../backend/app/jobs/UnpaidOrderCancelJob.php) + normal [NormalOrderCancelTask](../../../../backend/modules/product/normal/crontab/NormalOrderCancelTask.php)（扫描超时未付） |
| 关闭支付单 | `PayRecordServices::closeBeforeCancel()` 防止取消后支付回调进来 | 同左 |
| 状态推进 | 事务内行锁 → `status_code=canceled` → 事务内 `event('OrderCancel')` → 监听器 advance 扩展表 | `adapter->advance(EXT_STATUS_CANCELED)` 直调（不经事件）+ `syncStatus` |
| 库存与资源回滚 | 监听器内 `OrderLifecycleServices::handleCancel()`（返还积分/优惠券、回滚库存、写日志） | 同一 `handleCancel()` |

要点：

- 两条路径**必须共享同一份回滚逻辑**（`OrderLifecycleServices::handleCancel`），新增回滚项只改这一处，避免双写漂移。
- 超时取消任务与延迟取消队列（`OrderCreateAfter` 时投递的 `UnpaidOrderCancelJob`）共用订单锁与超时核对，Job 发现未到期会重新延迟投递。
- 已支付订单没有「取消」，走售后退款，见 [售后与退款](order-aftersale.md)。

## 五、其他流转操作

| 操作 | 入口 | 事件/机制 |
| --- | --- | --- |
| 改价 | 管理端改价服务 | `event('OrderPriceChange')` → 同步扩展表实付金额 + 通知 |
| 删除订单 | 用户端 | `OrderCenterUserServices` → `event('OrderDelete')` → 扩展表逻辑删除 |
| 核销 | `OrderWriteOffServices` | 核销码验证，核销类履约终点 |
| 订单日志 | `OrderLogDao` | 下单/支付/发货/收货/取消等节点写入，排查首选 |

## 六、新订单类型接入清单

- [ ] 监听 `OrderDelivery`/`OrderTake`/`OrderComment`（仅实物类；纯虚拟/核销类不需要）
- [ ] 取消：监听 `OrderCancel`（用户路径）+ 确认超时取消 Job 对你的 source_type 生效（扫描任务按扩展表状态 ∩ 中台超时筛选）
- [ ] 回滚：`incStock` 与取消释放逻辑接入 `OrderLifecycleServices::handleCancel()` 或模块监听器
- [ ] 通知：各节点通知用独立 `XxxNoticeListener`（命名即分级，获得异常隔离）
- [ ] 定时任务：自动收货/自动取消依赖 `eb_system_crontab` 种子，模块自建任务则注册 `CrontabTaskRegistry`

## 七、验证清单

- [ ] 发货 → 收货 → 评价 → 完成：中台与扩展表状态逐步推进且一致
- [ ] 需安装订单：收货后进入待安装 badge，安装完成后到待评价
- [ ] 部分明细评价时不推进 completed；全部评价完推进；超时自动好评生效
- [ ] 用户取消：仅待支付可取消；支付单关闭；库存/优惠券/积分回滚
- [ ] 超时取消：到期订单被取消且回滚；未到期订单不误杀
- [ ] 取消后收到原订单支付回调：被 `closeBeforeCancel` 拦截，不产生脏数据
