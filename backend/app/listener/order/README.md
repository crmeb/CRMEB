# 订单事件监听器目录

## 概述

本目录包含订单生命周期各阶段的事件监听器，实现了事件驱动的业务解耦，便于二次开发扩展。

## 监听器列表

| 文件名 | 事件名称 | 触发时机 | 主要职责 |
|--------|---------|---------|---------|
| OrderCancelListener.php | OrderCancelListener | 订单取消时 | 处理订单取消后的业务（库存回滚、积分返还等） |
| OrderCommentListener.php | OrderCommentListener | 用户评价时 | 处理评价后的业务（通知管理员、缓存抽奖次数等） |
| OrderCompleteListener.php | OrderCompleteListener | 订单完成时 | 处理订单完成后的业务（发放奖励、更新统计等） |
| OrderCreateAfterListener.php | OrderCreateAfterListener | 订单创建后 | 处理订单创建后的业务 |
| OrderDeleteListener.php | OrderDeleteListener | 订单删除时 | 处理订单删除后的业务 |
| OrderDeliveryListener.php | OrderDeliveryListener | 订单发货时 | 处理订单发货后的业务（包含到期自动收货） |
| OrderPaySuccessListener.php | OrderPaySuccessListener | 订单支付成功时 | 处理支付成功后的业务（状态记录、优惠券赠送等） |
| OrderRefundCancelAfterListener.php | OrderRefundCancelAfterListener | 退款取消后 | 处理退款取消后的业务 |
| OrderRefundCreateAfterListener.php | OrderRefundCreateAfterListener | 退款创建后 | 处理退款创建后的业务 |
| OrderShippingListener.php | OrderShippingListener | 订单物流更新时 | 处理物流更新后的业务 |
| OrderTakeListener.php | OrderTakeListener | 用户确认收货时 | 处理确认收货后的业务 |

## 事件触发流程

```
订单创建 → OrderCreateAfterListener
    ↓
订单支付成功 → OrderPaySuccessListener
    ↓
订单发货 → OrderDeliveryListener / OrderShippingListener
    ↓
用户收货 → OrderTakeListener
    ↓
用户评价 → OrderCommentListener
    ↓
订单完成 → OrderCompleteListener
    ↓
订单取消 → OrderCancelListener
    ↓
订单删除 → OrderDeleteListener
```

## 二开扩展

所有监听器均提供扩展点，可在 `handle` 方法中添加自定义业务逻辑：

```php
public function handle($event): void
{
    // 获取事件参数
    [$order, $uid] = $event;
    
    // 添加自定义逻辑
    // ...
}
```

## 注册方式

所有监听器需在 `app/event.php` 中注册：

```php
'listen' => [
    'OrderCancelListener' => [\app\listener\order\OrderCancelListener::class],
    // ... 其他监听器
]
```

## 触发方式

在业务代码中使用 `event()` 函数触发：

```php
event('OrderCancel', [$order, $uid]);
```

---

**版本**: v8.0  
**最后更新**: 2026-06-02
