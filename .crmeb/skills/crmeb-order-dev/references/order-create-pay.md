# 阶段一：订单创建与支付

> 本文是 `crmeb-order-dev` 技能子文档，总览见上级 [../SKILL.md](../SKILL.md)。主控制器 [CenterOrderController.php](../../../../backend/app/api/controller/v1/order/CenterOrderController.php)，中台写入 [OrderCenterServices.php](../../../../backend/app/services/order/OrderCenterServices.php)。

## 一、confirm：下单确认页

入口：`GET /api/center/order/confirm` → `CenterOrderController::confirm()`。职责是**算价 + 缓存快照 + 签发一次性凭证**，不写任何订单表。

### 1.1 直购流程

1. 读取 `product_id / sku_unique / product_type / activity_type / activity_id / num` 请求参数。
2. 交易模式校验：`TradeModeRegistry->get($activityType)` 取 Handler；非 normal 且 `activity_id > 0` 时调用 `beforeCreateOrder()` + `validateOrder()`（活动资格、限购）。
3. 产品类型取数：`ProductTypeRegistry->get($productType)` 的 `getSkuInfo()` 等构建购物车信息。
4. 缓存快照：`OrderCenterUserServices::cacheOrderInfo()` 缓存订单与价格数据，签发一次性 `orderKey`，有效期 600 秒。
5. 组装确认单：用户信息/VIP 等级、可用积分（冻结口径）、可用优惠券、Handler `getDetailExtend()`（自定义表单）、交易模式附加信息（允许支付方式、免费支付标识）。

### 1.2 购物车合单流程

带 `cartId` 时走 `cartConfirm()`：读购物车明细 → 运费/送达校验 → **强校验同单产品类型一致** → `OrderComputedServices::getOrderPriceGroup()` 分组计价 → 同样 `cacheOrderInfo()`（`order_mode='cart'`）。

> 促销计价扩展点：`OrderComputedServices::applyPromotion` 中触发 `OrderPromotionCompute` 事件（价格链优惠券段之前，活动单不触发），营销插件按需挂载写回优惠。

## 二、create：提交订单

入口：`POST /api/center/order/create` → `CenterOrderController::create()`。核心原则：**订单主体以 confirm 缓存为准，禁止请求参数替换；写库在一个事务内原子完成**。

### 2.1 防重三件套

1. 强制校验一次性 `key`（confirm 签发），用后即焚（`consumeOrderCache`）。
2. `acquireOrderCacheLock()` 订单缓存锁防重复提交。
3. 数据库层 `saveOrUpdate(['order_no' => ...])` 按 order_no 幂等。

### 2.2 事务内步骤序列（顺序严格）

```php
Db::startTrans();
  1. 非活动单：$handler->decStock()          // 产品类型锁库存，失败抛出回滚
  2. 积分抵扣落 UserBill 流水
  3. useCoupon() 消费优惠券
  4. $handler->createOrderExtend()           // 写产品扩展订单表，返回 extendId
  5. OrderCenterServices::createRecord()     // 写 eb_order_center，source_id=extendId，
                                             //    status_code='pending_payment'
Db::commit();
```

事务外后置：

6. 活动单 `$tradeModeHandler->afterOrderCreate()`（如拼团建团记录）。
7. `event('OrderCreateAfter')` → 下单通知、超时取消调度（延迟队列）、分销快照。
8. 清除已下单购物车、写订单状态日志（`OrderLogDao`）。

异常补偿：事务内失败自动回滚；事务外库存扣减失败逆序 `incStock()` 补偿。

### 2.3 关键细节

| 事项 | 说明 |
| --- | --- |
| 订单号 | `OrderCenterServices::getNewOrderId('cp')` → `SnowflakeIdGenerator::generate()`，雪花 ID + 业务前缀，全局唯一 |
| source_type | `$handler->getOrderSourceType()`（如 `normal_order`），决定中台详情去哪张源表取数 |
| 活动单支付方式 | 交易模式 `getAllowedPayTypes()` 强制；`isFreePay()` 时 `payType='free'` |
| 最终核价 | `OrderComputedServices::computedOrder()`（优惠券+积分+运费）；自提运费归零 |
| 普通单重算 | create 时对普通单按当前库存/运费实时重建购物车重算（`shouldRepriceNormal`），不信任确认页缓存 |
| createRecord 字段 | `order_no/source_type/source_id/uid/order_category/product_type/activity_type/activity_id/status_code/pay_status/refund_status/total_price/pay_price/extra_info/custom_form/...`，见 `OrderCenterServices::createRecord()` |
| 自定义表单 | 结构化后存 `eb_order_center.custom_form`（写入时即结构化），方案详见 [创建产品类型·附二](../../crmeb-dev/references/03-系统商品说明/product-type.md) |

## 三、paySuccess：支付成功链路

核心服务：[OrderSuccessServices.php](../../../../backend/app/services/order/OrderSuccessServices.php)::`paySuccess($orderInfo, $paytype, $other)`，支付回调（同步/异步）与 0 元支付共同汇聚于此：

```
paySuccess
 ├─ PayRecordServices::complete()          // 支付单幂等闭环（校验 pay_status/终态）
 ├─ OrderCenterServices::syncStatus(       // 中台：processing + pay_status=1 + 交易号
 │        'processing', [pay_status, pay_time, extra_info.trade_no...])
 ├─ event('ActivityDataProxy', ...)        // 拼团单经代理事件建团（app 不直接引 modules）
 ├─ event('OrderPayLottery', ...)          // 抽奖旁路
 ├─ event('OrderPaySuccess', [$orderInfo]) // ★ 各产品类型/分销监听的主事件
 └─ event('OutPush', ...)                  // 外部推送
```

- **`OrderPaySuccess` 的 `$orderInfo` 是中台订单数组**（`order_id` 字段即 order_no）。各模块监听器先校验 `product_type`/`source_type` 防误触，再幂等推进自己的扩展表（自动履约型在此调 `Handler::afterPay()`；实物推进 `pending_shipped`）。
- 0 元/积分兑换支付走 `zeroYuanPayment()` 同样汇入本链路；积分扣减由积分交易模式 Handler 的 `onOrderPay` 完成。
- 异步通知场景的客服/模板消息由 [app/jobs/OrderJob.php](../../../../backend/app/jobs/OrderJob.php) 触发 `OrderPaySuccessCustomerNotice`，与主链路解耦。

## 四、扩展点小结：新类型在本阶段要做什么

| 角色 | 必做 | 位置 |
| --- | --- | --- |
| 产品类型 | `getSkuInfo` / `decStock` / `createOrderExtend` / `afterOrder`；自动履约型实现 `afterPay` + 监听 `OrderPaySuccess` | 模块 Handler + listener |
| 交易模式 | `beforeCreateOrder` / `validateOrder` / `getActivityPrice` / `afterOrderCreate`；特殊支付覆盖支付四钩子 | 模块 ActivityHandler |
| 旁路需求（统计/通知/营销） | 监听 `OrderCreateAfter` / `OrderPaySuccess`（通知类命名以 `NoticeListener` 结尾） | 模块 event.php |

## 五、验证清单

- [ ] confirm 不落库；orderKey 过期（>600s）后 create 被拒
- [ ] 重复提交 create：同 key 第二次被锁/被焚拒绝，无重复订单
- [ ] 下单事务：库存不足时整体回滚（扩展表、中台表、优惠券、积分流水均无残留）
- [ ] 下单后 `eb_order_center` 的 `source_type/source_id/product_type/activity_type` 正确
- [ ] 活动单：限购校验生效；`afterOrderCreate` 后置数据（团记录等）落库
- [ ] 支付成功：中台 `processing` + `pay_status=1`；扩展表推进；返佣/积分流水正确
- [ ] 0 元支付：`payType='free'` 闭环，`paySuccess` 正常触发
- [ ] 支付回调重复通知：`PayRecordServices::complete()` 幂等，不重复推进
