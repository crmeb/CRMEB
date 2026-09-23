# product/normal/ — 实物商品产品类型

## 功能概述

实物商品（通用）产品类型，是最核心的产品履约形态。支持完整的商品管理（库存/SKU/规格）、物流发货、订单生命周期管理和售后流程。

> 运费模板（ShippingTemplates）已迁移至 `app/` 层作为系统级功能，详见 `app/services/shipping/`、`app/adminapi/controller/freight/`、`app/adminapi/route/freight.php`。此处不再包含运费模板相关文件。

## 核心文件

```
normal/
├── controller/
│   ├── admin/
│   │   ├── ProductController.php              实物商品管理
│   │   ├── OrderController.php                实物订单管理
│   │   └── DeliveryServiceController.php      客服/发货管理
│   └── user/
│       ├── ProductController.php              用户端商品展示
│       ├── OrderController.php                用户端实物订单
│       └── CheckoutController.php             用户端下单确认
├── crontab/
│   ├── NormalOrderCancelTask.php              未支付订单自动取消
│   └── NormalTakeDeliveryTask.php             自动确认收货定时任务
├── dao/
│   ├── NormalOrderDao.php                     实物订单 DAO
│   ├── NormalProductDao.php                   实物商品 DAO
│   ├── NormalProductSkuDao.php                实物 SKU DAO
│   └── DeliveryServiceDao.php                 客服服务 DAO
├── handler/
│   ├── NormalAfterSaleHandler.php             实物售后规则处理器
│   └── NormalOrderAdapter.php                 实物订单适配器
├── listener/
│   ├── OrderPaidListener.php                  支付成功
│   ├── OrderCancelListener.php                订单取消
│   ├── OrderDeliveryListener.php              订单发货
│   ├── OrderTakeListener.php                  确认收货
│   ├── OrderCompleteListener.php              订单完成
│   ├── OrderCommentListener.php               订单评价
│   ├── OrderDeleteListener.php                订单删除
│   ├── OrderPriceChangeListener.php           改价
│   ├── OrderRefundCreateAfterListener.php     申请退款后
│   ├── OrderRefundSuccessListener.php         退款成功
│   ├── OrderRefundRefuseListener.php          拒绝退款
│   ├── OrderRefundCancelAfterListener.php     取消退款后
├── model/
│   ├── NormalProductModel.php                 实物商品模型（eb_pt_normal）
│   ├── NormalProductSkuModel.php              实物 SKU 模型（eb_pt_normal_sku）
│   ├── NormalOrderModel.php                   实物订单模型（eb_pt_normal_order）
│   └── DeliveryService.php
├── route/
│   ├── admin.php                              后台路由
│   ├── user.php                               用户端路由
│   └── manage.php                             移动管理端路由
├── service/
│   ├── ProductServices.php                    商品查询服务
│   ├── ProductSaveServices.php                商品保存服务
│   ├── OrderServices.php                      订单服务
│   ├── OrderCreateServices.php                订单创建服务
│   ├── CheckoutServices.php                   结算页服务
│   ├── RefundServices.php                     退款服务
│   ├── CrontabServices.php                    定时任务服务
│   └── DeliveryServiceServices.php            客服/发货服务
├── App.php
├── Handler.php                                产品类型主处理器
├── Installer.php
├── event.php
└── plugin.json
```

## 关键事件

| 事件名 | 说明 |
|--------|------|
| `OrderPaySuccess` | 支付成功 |
| `OrderCancel` | 订单取消 |
| `OrderDelivery` | 订单发货 |
| `OrderTake` | 确认收货 |
| `OrderComplete` | 订单完成 |
| `OrderComment` | 订单评价 |
| `OrderDelete` | 订单删除 |
| `OrderPriceChange` | 改价 |
| `OrderRefundCreateAfter` | 申请退款后 |
| `OrderRefundSuccess` | 退款成功 |
| `OrderRefundRefuse` | 拒绝退款 |
| `OrderRefundCancelAfter` | 取消退款后 |
| 运费模板系列 | 运费模板相关事件 |

## 售后规则

- 支持售后类型：退款、退货退款、换货、维修
- 售后期限：7天
- 需要物流配送

## 数据表

- `eb_pt_normal` — 实物商品扩展表
- `eb_pt_normal_sku` — 实物商品 SKU 表
- `eb_pt_normal_order` — 实物商品订单扩展表
- `eb_shipping_templates` — 运费模板表

## 插件信息

```json
{
    "key": "product_normal",
    "name": "实物商品",
    "type": "product"
}
```
