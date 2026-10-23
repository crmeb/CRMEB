# product/virtual/ — 网盘产品类型

## 功能概述

网盘产品类型，适用于网盘资源售卖、支付后自动发放网盘链接与提取码等场景（如电子书、课程、会员码等）。用户下单支付成功后，订单详情自动展示网盘链接与提取码。

## 核心文件

```
virtual/
├── controller/
│   ├── admin/
│   │   └── ProductController.php          网盘商品管理
│   └── user/
│       └── OrderController.php            用户端网盘订单
├── dao/
│   ├── VirtualOrderDao.php                网盘订单 DAO
│   ├── VirtualProductDao.php              网盘商品 DAO
│   └── VirtualProductSkuDao.php           网盘商品 SKU DAO
├── handler/
│   ├── VirtualOrderAdapter.php            网盘订单适配器
│   └── VirtualAfterSaleHandler.php        网盘售后规则处理器
├── listener/
│   ├── OrderPaidListener.php              支付成功自动发货
│   ├── OrderCancelListener.php            订单取消
│   ├── OrderCommentListener.php           订单评价
│   ├── OrderDeleteListener.php            订单删除
│   ├── OrderPriceChangeListener.php       改价
│   ├── OrderRefundCreateAfterListener.php 申请退款后
│   ├── OrderRefundSuccessListener.php     退款成功
│   ├── OrderRefundRefuseListener.php      拒绝退款
│   ├── OrderRefundCancelAfterListener.php 取消退款后
│   └── OrderCenterDetailExtendListener.php 订单详情注入网盘链接与提取码
├── model/
│   ├── VirtualProduct.php                 网盘商品模型（eb_pt_virtual）
│   ├── VirtualProductSku.php              网盘 SKU 模型（eb_pt_virtual_sku）
│   └── VirtualOrder.php                   网盘订单模型（eb_pt_virtual_order）
├── pipeline/
│   └── VirtualPipeline.php                网盘订单管线
├── route/
│   ├── admin.php                          后台路由
│   └── user.php                           用户端路由
├── service/
│   ├── VirtualProductServices.php         网盘商品服务
│   └── VirtualOrderServices.php           网盘订单服务
├── App.php
├── Handler.php                            产品类型主处理器
├── Installer.php
├── event.php
└── plugin.json
```

## 关键事件

| 事件名 | 说明 |
|--------|------|
| `OrderPaySuccess` | 支付成功自动发货（发放网盘资源） |
| `OrderCancel` | 订单取消，检查资源是否已发放 |
| `OrderRefundCreateAfter` | 申请退款后检查资源状态 |
| `OrderRefundSuccess` | 退款成功撤销资源访问 |
| `OrderRefundRefuse` | 拒绝退款恢复资源访问 |
| `OrderRefundCancelAfter` | 取消退款后恢复资源访问 |
| `OrderComment` | 订单评价 |
| `OrderDelete` | 订单删除 |
| `OrderPriceChange` | 改价 |
| `OrderCenterDetailExtend` | 订单详情注入网盘链接与提取码（履约信息） |

## 售后规则

- 支持售后期限：24小时
- 已发放后不支持售后
- 仅支持退款类型

## 用户端加购策略

网盘商品不支持加入购物车，只能直接购买：

- `Handler::getCartStrategy()` 返回 `cart_mode=direct`、`multi_item=false`，中台据此下发 `cart_button=0`，列表与详情只展示「立即购买」。
- 商品详情页点击「立即购买」跳单品直购页 `/pages/center/order/create/index?product_id=&sku_unique=&product_type=virtual&num=`，由 `CenterOrderController::confirm/create` 的 direct 链路下单，不写购物车（多规格首次点击先弹规格窗确认规格）。
- `StoreCartServices::setCart()` 对 `cart_mode=direct` 的商品在 `new=false` 时抛「该商品不支持加入购物车，请直接购买」，防止其他入口绕过 UI 加购。

## 数据表

- `eb_pt_virtual` — 网盘商品扩展表
- `eb_pt_virtual_sku` — 网盘商品 SKU 表
- `eb_pt_virtual_order` — 网盘商品订单扩展表

## 插件信息

```json
{
    "key": "product_virtual",
    "name": "网盘产品",
    "type": "product_type"
}
```
