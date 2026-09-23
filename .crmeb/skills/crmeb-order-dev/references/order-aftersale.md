# 阶段三：售后与退款

> 本文是 `crmeb-order-dev` 技能子文档，总览见上级 [../SKILL.md](../SKILL.md)。核心服务 [AfterSaleServices.php](../../../../backend/app/services/order/AfterSaleServices.php)，规则契约在 [crmeb/trade/order/aftersale/](../../../../backend/crmeb/trade/order/aftersale/)。

## 一、售后规则：双维度注册与合并

售后能否退、能退多少，由 **product_type × activity_type** 两个维度的规则合并决定：

```
AfterSaleRuleRegistry（注册）
  register('product_type',  'normal',  NormalAfterSaleHandler::class)   ← 产品类型维度（必须）
  register('activity_type', 'integral', IntegralAfterSaleHandler::class) ← 交易模式维度（可选）

AfterSaleRuleResolver::resolve()（合并，app/services/order/rule/）
  ├─ 一票否决：任一维度拒绝 → 不可售后
  ├─ available_types：两维声明取交集（refund_only / return_refund / exchange / repair / resend）
  ├─ deadline：售后期限取更晚（如 normal 7 天 ∩ virtual 24 小时）
  └─ max_refund_num：可退数量取更小
```

关键约束（详见 [订单中台规范](../../../rules/订单中台规范.md)）：

- **未按 product_type 注册售后规则的订单类型（如积分订单）默认拒绝售后**——这是安全兜底，新产品类型必须显式注册。
- 活动维度未注册时放行，由产品类型规则独自约束。

## 二、AfterSaleHandlerInterface 契约

接口：[AfterSaleHandlerInterface.php](../../../../backend/crmeb/trade/order/aftersale/AfterSaleHandlerInterface.php)，基类 [BaseAfterSaleHandler.php](../../../../backend/crmeb/trade/order/aftersale/BaseAfterSaleHandler.php)（空实现，按需覆盖）。

| 方法组 | 方法 | 用途 |
| --- | --- | --- |
| 规则声明 | `getRule()` | 可售后类型、售后期限、最大可退数量等 |
| 生命周期回调 | `afterApply / afterAudit / afterReturn / afterReceive / afterRefund / afterResend / afterRepair / afterComplete / afterCancel` | 售后单各节点后置处理（如标记扩展表退款中） |
| 退款方案 | `buildRefundPlan / beforeRefundSubmit / applyRefundEffects` | 定制退款金额构成与资金副作用；**副作用必须经 `RefundEffectContextInterface::once()` 登记**保证幂等 |
| 表单与动作 | `getFormConfig / getUserActions` | 申请表单、用户侧可用动作 |

参考实现：实物 [NormalAfterSaleHandler](../../../../backend/modules/product/normal/handler/NormalAfterSaleHandler.php)（标准 7 天）、卡密 [CardkeyAfterSaleHandler](../../../../backend/modules/product/cardkey/handler/CardkeyAfterSaleHandler.php)（1 小时 + 退款回收卡密）。

## 三、售后单流程

```
申请
  CenterOrderController::applyRefund
    └─► AfterSaleServices::apply
          ├─ 事务锁中台订单 → checkOrderEligibility（订单状态、可售后期）
          ├─ ruleResolver->resolve() 规则校验（双维度合并结果）
          ├─ items 可退数量/金额/积分上限校验 → 保存售后单（状态 pending_audit）
          └─ lifecycle->afterApply()（订单进入 after_sale 状态）

审核（管理端）
  AfterSaleServices::audit
    ├─ 拒绝 → restoreOrderStatus 恢复订单原状态 + afterAudit
    └─ 通过 → return_refund/exchange/repair → pending_return（待退货）
              refund_only/resend → audit_passed（直接进入退款/补发）
```

## 四、退款执行与幂等

```
AfterSaleServices::refund
  └─► RefundOrderServices::request → process     // 渠道退款（原路退回）
        └─► RefundEffectServices::deliverSuccess // 资金事务外幂等投递
              └─ event('OrderRefundSuccess', [$order, refund_no, refund_price...])
                   ├─ 模块 OrderRefundSuccessListener：更新扩展表/中台退款状态
                   │  （卡密回收、积分返还等）
                   └─ 通知监听器发送退款成功通知
```

幂等设计要点：

- `PaymentDispatch::replay` 保证 `OrderRefundSuccess` 事件重放安全；触发失败转待办 `recoverSuccessEvents` 补偿。
- Handler 中的资金/资源副作用一律经 `RefundEffectContextInterface::once()` 登记去重，禁止直接裸写。
- 中台 `refund_status`（0 无 / 1 申请中 / 2 已退）由 Adapter `resolveRefundStatus` 推导，退款完成时 `syncStatus` 同步。

> **现状说明**：`OrderRefundCreateAfter / OrderRefundRefuse / OrderRefundCancelAfter` 的模块监听器已注册但服务层触发点待接线（见 [订单生命周期·事件地图](order-lifecycle.md#三订单事件地图)），相关后置逻辑目前挂在 `AfterSaleServices` 内部回调（`lifecycle->afterApply/afterAudit`）。

## 五、自定义售后 Handler 开发步骤

1. 在产品类型模块创建 `handler/{Type}AfterSaleHandler` 继承 `BaseAfterSaleHandler`。
2. `getRule()`：声明可售后类型与期限（如票务「开演前可退」需在规则中实现时间窗判断）。
3. 按需覆盖生命周期回调：申请后标记扩展表、退款成功后回收资源（卡密/票号）。
4. 在模块 `App.php::boot()` 注册（**product_type 维度**，key 与 `Handler::getTypeKey()` 一致）：

```php
AfterSaleRuleRegistry::register('product_type', '{type}', {Type}AfterSaleHandler::class);
```

5. 前端售后表单差异经 `getFormConfig()` 输出。

## 六、验证清单

- [ ] 未注册售后规则的订单类型申请售后被拒绝
- [ ] 双维度规则合并正确：拒绝优先、类型交集、期限取晚、数量取小
- [ ] 超出可退数量/金额/售后期限的申请被拦截
- [ ] 审核拒绝后订单恢复原状态（不残留 after_sale）
- [ ] 退款成功：渠道退款到账、扩展表与中台 `refund_status` 更新、资源回收（卡密/票）执行
- [ ] 退款事件重复投递：`once()` 登记的资源副作用不重复执行
- [ ] 部分退款：剩余可退数量/金额正确递减
