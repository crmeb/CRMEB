# 用户端创建订单实现

产品中台统一提供订单确认和下单接口，各产品类型通过 `{Type}ProductTypeHandler`（继承 `BaseProductTypeHandler`、实现 `ProductTypeHandlerInterface`，位于 `modules/product/{type}/Handler.php`）提供 SKU、库存、运费、自定义表单等数据，并通过 `{ProductType}OrderAdapter`（继承 `BaseOrderTypeAdapter`）定义订单创建后的状态。

## 一、页面入口

新中台下单页：
- 页面文件：`frontend/uni-app/src/pages/center/order/create/index.vue`
- 引擎文件：`frontend/uni-app/src/dynamic-pages/engines/OrderCreateEngine.vue`
- 接口文件：`frontend/uni-app/src/api/order-center.js`

旧下单页：
- `frontend/uni-app/src/pages/goods/order_confirm/index.vue`

## 二、后端接口

### 2.1 订单确认（数据展示）

```
GET /api/center/order/confirm
```

控制器：`backend/app/api/controller/v1/order/CenterOrderController.php::confirm()`，服务层 `OrderCenterServices`

请求参数：

| 参数 | 类型 | 说明 |
|------|------|------|
| product_id | int | 商品ID |
| sku_unique | string | SKU 唯一码 |
| product_type | string | 产品类型 |
| num | int | 购买数量 |

响应关键字段：

```json
{
  "status": 200,
  "data": {
    "order_key": "xxx",
    "product_type": "ticket",
    "activity_type": "normal",
    "activity_id": 0,
    "features": {
      "express_shipping": true,
      "store_pickup": true,
      "door_service": true,
      "door_service_postage": 30.00,
      "remark": true,
      "invoice": true,
      "integral": true,
      "coupon": true,
      "custom_form": true,
      "params_list": true,
      "visitor_form": true
    },
    "cartInfo": {...},
    "total_price": "199.00",
    "pay_price": "199.00",
    "pay_postage": "0.00",
    "priceGroup": {
      "totalPrice": "199.00",
      "payPostage": "0.00",
      "postageDiscount": 0,
      "levelPrice": 0,
      "memberPrice": 0,
      "couponPrice": 0,
      "deductionPrice": 0,
      "giftPrice": 0
    },
    "userInfo": {...},
    "usable_integral": 100,
    "integral_open": true,
    "integral_ratio": "1",
    "coupon_list": [],
    "custom_form": [],
    "params_list": [],
    "activity_info": {}
  }
}
```

`features` 字段说明（由后端 [`confirm()`](../../../../../backend/app/api/controller/v1/order/CenterOrderController.php#L280-L292) 动态组装，共 11 个）：

| features 字段 | 类型 | 说明 |
|------|------|------|
| `express_shipping` | bool | 是否支持快递配送（从商品中心表读取） |
| `store_pickup` | bool | 是否支持到店自提（从商品中心表读取） |
| `door_service` | bool | 是否支持上门服务（从商品中心表读取） |
| `door_service_postage` | number | 上门服务费用（元，从商品 `freight_config.door_service.postage` 读取） |
| `remark` | bool | 买家留言输入框（对应订单表 `mark` 字段） |
| `invoice` | bool | 发票功能（由全局配置 `invoice_func_status` 控制） |
| `integral` | bool | 积分抵扣（活动订单关闭） |
| `coupon` | bool | 优惠券（活动订单关闭） |
| `custom_form` | bool | 自定义表单（由产品类型 Handler 决定） |
| `params_list` | bool | 商品参数展示（由产品类型 Handler 决定） |
| `visitor_form` | bool | 游客信息表单（门票专属，由产品类型 Handler 的 `visitor_form_config` 决定） |

### 2.2 下单提交

```
POST /api/center/order/create
```

请求参数：

| 参数 | 类型 | 说明 |
|------|------|------|
| order_key | string | 确认页返回的缓存 key |
| address_id | int | 收货地址ID（非虚拟商品） |
| shipping_type | int | 配送方式：1快递 2自提（非虚拟商品） |
| mark | string | 买家留言 |
| coupon_id | int | 优惠券ID |
| use_integral | int | 是否使用积分 |
| custom_form | object | 自定义表单数据 |
| invoice_id | int | 发票ID |
| store_id | int | 门店ID（到店自提时必填） |

## 三、数据存储分布

下单创建时，数据被写入**两个表**：

### 3.1 中台表 `eb_order_center`（所有订单统一管理）

```sql
-- 订单标识
order_no       VARCHAR(32)   -- 订单号（全局唯一）
source_type    VARCHAR(32)   -- 来源类型标识（normal_order/integral_order/...）
source_id      INT           -- 扩展表ID
uid            INT           -- 用户ID

-- 分类维度（索引列，不做 JSON 双存）
order_category VARCHAR(32)   -- 订单大类：goods/points/member/recharge
product_type   VARCHAR(32)   -- 产品类型：normal/virtual/cardkey/hotel/ticket
activity_type  VARCHAR(32)   -- 活动类型：seckill/combination/bargain/advance（非活动空字符串）

-- 统一状态体系
status_code    VARCHAR(32)   -- pending_payment/processing/completed/canceled/refunding/refunded
pay_status     TINYINT       -- 0未支付 1已支付 2部分退款 3全额退款
refund_status  TINYINT       -- 0无退款 1申请中 2已退款

-- 金额体系（均为实体列，高频独立字段，避免从 JSON 提取）
total_price    DECIMAL(10,2) -- 订单总金额
pay_price      DECIMAL(10,2) -- 实际支付金额
pay_postage    DECIMAL(10,2) -- 运费
refund_price   DECIMAL(10,2) -- 已退款金额
pay_type       VARCHAR(32)   -- 支付方式
coupon_price   DECIMAL(10,2) -- 优惠券抵扣金额
deduction_price DECIMAL(10,2) -- 积分抵扣金额
use_integral_num INT         -- 使用的积分数
member_discount DECIMAL(10,2) -- 等级会员优惠金额
vip_discount   DECIMAL(10,2) -- 付费会员优惠金额
gift_price     DECIMAL(10,2) -- 礼品费用

-- 订单概要（列表渲染用，冗余免 join）
total_num      INT           -- 商品总数
title          VARCHAR(255)  -- 订单标题摘要
image          VARCHAR(500)  -- 缩略图
subtitle       VARCHAR(255)  -- 副标题

-- 扩展元数据（仅存扩展层动态产生的字段，金额字段已升级为实体列）
extra_info     JSON          -- 扩展信息 JSON（存放收货地址/配送信息/商品快照/业务状态等）

-- 管理字段
remark         VARCHAR(500)  -- 管理员备注（后台填写）
detail_route   VARCHAR(255)  -- 详情页跳转路由
is_del         TINYINT       -- 软删除

-- 时间线
add_time       INT           -- 下单时间
pay_time       INT           -- 支付时间
delivery_time  INT           -- 发货/履约时间
take_time      INT           -- 收货/完成时间
refund_time    INT           -- 退款时间
cancel_time    INT           -- 取消时间

-- 配送/核销实体列（高频独立字段，避免从 JSON 中提取）
store_id       INT           -- 门店ID（到店自提时使用，不自提为0）
shipping_type  TINYINT       -- 配送方式：1=快递 2=自提
verify_code    VARCHAR(32)   -- 核销码（到店自提/需核销场景）

-- 扩展实体列（高频独立字段，避免从 JSON 中提取）
mark           TEXT          -- 买家留言（用户下单时填写）
custom_form    JSON          -- 自定义表单数据（结构化 [{title, label, value}]）
invoice        JSON          -- 发票信息（用户选择的抬头）
```

**中台表存储原则**：
- 实体列：存**所有订单类型通用**的字段（金额含优惠抵扣、时间、状态、概要、配送核销）
- JSON：存**仅商品订单有或扩展层动态产生**的字段（收货地址、配送信息、商品快照、业务状态等）

### 3.2 扩展表（产品类型专属数据）

每个产品类型有自己的扩展秩序表，以实物商品为例 `eb_pt_normal_order`：

```sql
-- 订单标识
order_no       VARCHAR(32)   -- 关联 eb_order_center.order_no

-- 商品关联
order_item_id  INT           -- 关联订单项ID（多商品下单时标识该扩展记录对应的订单项）
product_id     INT           -- 商品ID
sku_id         INT           -- SKU ID
uid            INT           -- 用户ID

-- 金额
pay_price      DECIMAL(10,2) -- 实际支付金额
total_price    DECIMAL(10,2) -- 订单总金额

-- 扩展状态
status         VARCHAR(32)   -- 扩展订单状态（pending_payment/processing/completed/...）
paid           TINYINT       -- 是否支付
is_cancel      TINYINT       -- 是否取消
refund_status  TINYINT       -- 退款状态
refund_price   DECIMAL(12,2) -- 已退款金额

-- 配送信息
delivery_type  VARCHAR(32)   -- 配送方式（delivery/pickup）
delivery_name  VARCHAR(64)   -- 快递公司名
delivery_id    VARCHAR(64)   -- 快递单号
user_address   VARCHAR(200)  -- 用户地址
real_name      VARCHAR(32)   -- 收货人姓名
user_phone     VARCHAR(20)   -- 收货人手机号

-- 活动
activity_type  VARCHAR(32)   -- 活动类型

-- 安装服务
has_installation TINYINT     -- 是否需要安装服务

-- 时间线
add_time       INT           -- 创建时间
update_time    INT           -- 更新时间
```

### 3.3 数据归属决策表（代码实际写入映射）

| 数据项 | 存哪里 | 代码映射 |
|--------|--------|---------|
| `order_no` | **中台表** + **扩展表** | `createRecord()` + `createOrderExtend()` |
| `product_type` | **中台表** (实体列) | `$insert['product_type']` |
| `activity_type` | **中台表** + **扩展表** | `$insert['activity_type']` + 扩展表 |
| `status_code` | **中台表** (实体列) | `$insert['status_code']` |
| `total_price` `pay_price` | **中台表** + **扩展表** | 两表各存一份 |
| `pay_postage` | **中台表** (实体列) | `$insert['pay_postage']` |
| `pay_type` | **中台表** (实体列) | `$insert['pay_type']` |
| `title` `image` `total_num` | **中台表** (列表冗余) | 从购物车首项提取 |
| `mark` (买家留言) | **中台表** (实体列) | `$insert['mark']` |
| `custom_form` | **中台表** (JSON列) | `$insert['custom_form']` |
| `invoice` | **中台表** (JSON列) | `$insert['invoice']` |
| `store_id` | **中台表** (实体列) | `$insert['store_id']` |
| `shipping_type` | **中台表** (实体列) | `$insert['shipping_type']` |
| `verify_code` | **中台表** (实体列) | `$insert['verify_code']` |

| 数据项 | 存哪里 | 代码映射 |
|--------|--------|---------|
| `coupon_price` | **中台表** (实体列) | `$insert['coupon_price']` |
| `deduction_price` | **中台表** (实体列) | `$insert['deduction_price']` |
| `use_integral_num` | **中台表** (实体列) | `$insert['use_integral_num']` |
| `member_discount` | **中台表** (实体列) | `$insert['member_discount']` |
| `vip_discount` | **中台表** (实体列) | `$insert['vip_discount']` |
| `gift_price` | **中台表** (实体列) | `$insert['gift_price']` |
| `contact` (收货地址) | **中台表** `extra_info.contact` | JSON 内 |
| `sku_info` (规格快照) | **中台表** `extra_info.sku_info` | JSON 内 |
| `items` (商品快照) | **中台表** `extra_info.items` | JSON 内 |
| `product_id` | **中台表** `extra_info.product_id` | JSON 内 |
| `business_status` | **中台表** `extra_info.business_status` | JSON 内 |

> **金额字段存储原则**：`coupon_price`/`deduction_price`/`use_integral_num`/`member_discount`/`vip_discount`/`gift_price` 均为 `eb_order_center` **实体列**（高频独立字段，避免从 JSON 提取），写入位置见 `OrderCenterServices::createRecord()` 的 `$insert` 数组。详情读取时实体列值为 0/0.00 才从 `extra_info` 回退（`buildGroupedData()` 的 `$priceFields` 循环）。

| 数据项 | 存哪里 | 代码映射 |
|--------|--------|---------|
| `product_id` | **扩展表** (实体列) | `$result['product_id']` |
| `uid` | **扩展表** (实体列) | `$result['uid']` |
| `delivery_type` | **扩展表** `delivery_type` | `$result['delivery_type']` |
| `delivery_name` `delivery_id` | **扩展表** (发货后) | `$result['delivery_name']` |
| `user_address` | **扩展表** (实体列) | 旧系统遗留，当前优先用 `extra_info.contact` |
| `real_name` `user_phone` | **扩展表** (实体列) | 旧系统遗留 |
| `has_installation` | **扩展表** (实体列) | `$result['has_installation']` |
| `ext_status` (状态推进) | **扩展表** `status` | Handler 维护 |

> **注意**：`extra_info.contact` 存的是结构化地址（省/市/区/详情），扩展表 `user_address`/`real_name`/`user_phone` 存的是旧版平面字段。**当前版本以 `extra_info.contact` 为准**，`loadSourceOrderInfo()` 优先从中台表 `extra_info.contact` 读取，扩展表字段仅作 `contact` 的回退。`shipping_type`/`verify_code`/`store_id` 直接从 `eb_order_center` 表实体列读取，无需回退。

### 3.4 createRecord() 实际写入字段

```php
// OrderCenterServices::createRecord() 实际写入的字段
$insert = [
    // 实体列
    'order_no', 'source_type', 'source_id', 'uid',
    'order_category', 'product_type', 'activity_type', 'activity_id',
    'status_code', 'pay_status', 'refund_status',
    'total_price', 'pay_price', 'pay_postage', 'refund_price', 'pay_type',
    'title', 'image', 'total_num', 'subtitle',
    'store_id', 'shipping_type', 'verify_code',  // 配送/核销实体列
    'mark',
    'detail_route', 'add_time',

    // JSON 列
    'custom_form',
    'invoice',
    'extra_info',   // 内含所有优惠/配送/快照
];
```

### 3.5 extra_info JSON 结构（两侧路径一致）

```php
'extra_info' => [
    'business_status' => [
        'code'   => 'pending_payment',
        'text'   => '待支付',
        'detail' => '',
        'extend' => new \stdClass(),
    ],
    'coupon_price'          => '0.00',
    'member_level_discount' => '0.00',
    'vip_discount'          => '0.00',
    'deduction_price'       => '0.00',
    'use_integral_num'      => 0,
    'contact' => [
        'name'    => '张三',
        'phone'   => '13800138000',
        'address' => [
            'province'    => '广东省',
            'city'        => '深圳市',
            'district'    => '南山区',
            'detail'      => '科技园xxx号',
            'postal_code' => '',
        ],
    ],
    'store_info' => [                      // 仅自提时有值（API 下单时写入）
        'store_id'        => 1,
        'store_name'      => '深圳旗舰店',
        'store_phone'     => '0755-xxx',
        'address'         => '广东省深圳市南山区',
        'detailed_address' => '南山区xxx路xx号',
    ],
    'sku_info' => [...],                   // 规格快照
    'items' => [...],                      // 商品明细快照
    'product_id' => 123,                   // 商品ID（详情页跳转用）
    'activity_info' => [],                 // 活动扩展信息
]
```

> **说明**：`shipping_type`/`store_id`/`verify_code` 已写入 `eb_order_center` 实体列，`extra_info` 中不重复存储。自提订单的门店详情（店名、电话、地址）仅存于 `extra_info.store_info` 供详情页展示用。`extra_info.delivery` 节点仅由管理端发货流程（`OrderCenterUserServices::confirmOrder()`）写入，下单流程不写入。

## 四、两套下单链路

### 4.1 API 控制器路径（推荐）

`CenterOrderController::create()` → 直接调用 `OrderCenterUserServices::createRecord()`

**传入参数**：
```php
$centerServices->createRecord([
    'order_no'       => $orderNo,
    'source_type'    => $sourceType,
    'source_id'      => $extendId,
    'uid'            => $uid,
    'order_category' => 'goods',
    'product_type'   => $productType,
    'store_id'       => $storeId,
    'activity_type'  => $activityType,
    'activity_id'    => $activityId,
    'status_code'    => 'pending_payment',
    'pay_status'     => 0,
    'refund_status'  => 0,
    'total_price'    => $originalTotalPrice,
    'pay_price'      => $finalPayPrice,
    'pay_postage'    => $computedResult['pay_postage'] ?? 0,
    'pay_type'       => $payType,
    'title'          => $cartInfo[0]['productInfo']['store_name'] ?? '',
    'image'          => $cartInfo[0]['productInfo']['image'] ?? '',
    'total_num'      => $cartInfo[0]['cart_num'] ?? 1,
    'mark'           => $formData['mark'] ?? '',
    'custom_form'    => $formData['custom_form'] ?? [],
    'invoice'        => $invoiceInfo,
    'extra_info'     => [ ... ],    // 见 3.5 JSON 结构
    'detail_route'   => '/pages/center-order/detail/index?order_no=' . $orderNo,
]);
```

### 4.2 模块级路径

`OrderCreateServices::createOrder()` → 先写旧系统主订单 → 再写扩展表 → 再写中台表

```
1. 调 AppOrderCreateServices::createOrder() → 旧主订单
2. Handler::createOrderExtend()             → 扩展表 eb_pt_normal_order
3. OrderCenterServices::createRecord()      → 中台表 eb_order_center
4. Handler::afterOrder()                    → 幂等校验
```

模块级路径写入中台表的 `extra_info` 结构（与 API 路径一致，无 `delivery` 节点——`shipping_type`/`store_id` 已归属实体列）：

```php
'extra_info' => [
    'business_status'     => [...],
    'coupon_price'        => '0.00',
    'member_level_discount' => '0.00',
    'vip_discount'        => '0.00',
    'deduction_price'     => '0.00',
    'use_integral_num'    => 0,
    'sku_info'            => $this->buildSkuInfo(...),
    'contact'             => [
        'name'    => $addressInfo['real_name'] ?? '',
        'phone'   => $addressInfo['phone'] ?? '',
        'address' => [
            'province'    => '',
            'city'        => '',
            'district'    => '',
            'detail'      => '',
            'postal_code' => '',
        ],
    ],
],
```

> **注意**：`shipping_type`/`store_id`/`verify_code` 已写入中台表实体列，下单流程 `extra_info` 中不重复存储配送方式的实体值。详情页直接从实体列读取配送方式和门店信息。自提订单的门店详情请在 `extra_info.store_info` 中保存。

## 五、OrderCreateEngine 渲染逻辑

```vue
<template>
  <view class="order-page">
    <DeliverySection ... />
    <OrderProductCard ... />
    <OrderDiscountPanel ... />
    <OrderParamsList :visible="features.params_list" />
    <OrderCustomForm ref="customFormRef" v-model="customFormData" :fields="customForm" :visible="features.custom_form" />
    <OrderRemarkInput v-model="remark" :visible="features.remark" />
    <OrderInvoicePanel :visible="features.invoice" />
    <OrderPriceDetail ... />
    <OrderSubmitBar :pay-price="payPriceText" @submit="handleConfirm" />
  </view>
</template>
```

## 六、产品类型需要做的事

### 6.1 确认 `{Type}ProductTypeHandler::getSkuInfo()` 正确返回

下单前中台会调用产品类型 `Handler`（位于 `modules/product/{type}/Handler.php`，继承 `BaseProductTypeHandler`、实现 `ProductTypeHandlerInterface`）的 `getSkuInfo()` 获取 SKU 价格和库存：

```php
/**
 * 获取 SKU 信息
 * 必须返回标准格式，用于价格计算和库存校验
 */
public function getSkuInfo(int $productId, string $skuUnique): ?array
{
    return [
        'id'       => 1,
        'unique'   => 'abc123',
        'price'    => '199.00',
        'ot_price' => '299.00',
        'cost'     => '0.00',
        'stock'    => 1000,
        'image'    => '',
        'suk'      => '默认',
    ];
}
```

### 6.2 确认 `{Type}ProductTypeHandler::getShippingInfo()` 正确返回

非虚拟商品需要返回运费配置：

```php
/**
 * 获取商品运费配置
 * 用于运费计算
 */
public function getShippingInfo(int $productId): array
{
    return [
        'freight' => 1,    // 1按重量 2固定运费 3包邮
        'temp_id' => 1,    // 运费模板ID
        'postage' => 0.00, // 固定运费
        'weight'  => 0.00,
        'volume'  => 0.00,
    ];
}
```

### 6.3 确认 `{Type}ProductTypeHandler::getDetailExtend()` 返回自定义表单和商品参数

虚拟/卡密/票务等商品可能需要收集用户信息：

```php
/**
 * 获取商品扩展信息（自定义表单 + 商品参数）
 * 中台会在 confirm 接口中调用，将返回数据注入 features
 */
public function getDetailExtend(int $productId): array
{
    /** @var \app\dao\product\CenterProductDao $centerProductDao */
    $centerProductDao = app(\app\dao\product\CenterProductDao::class);
    $product = $centerProductDao->getInfo($productId);
    if (is_object($product)) {
        $product = $product->toArray();
    }
    if (!$product) {
        return [];
    }

    return [
        'custom_form' => !empty($product['custom_form'])
            ? (is_string($product['custom_form']) ? (json_decode($product['custom_form'], true) ?: []) : $product['custom_form'])
            : [],
        'params_list' => !empty($product['params_list'])
            ? (is_string($product['params_list']) ? (json_decode($product['params_list'], true) ?: []) : $product['params_list'])
            : [],
    ];
}
```

### 6.4 实现 `{Type}ProductTypeHandler::createOrderExtend()` 和 `afterOrder()`

这两个方法写扩展表，并确保 `source_type`/`source_id` 正确：

**`createOrderExtend()`** — 创建扩展表记录，必须返回 `$extendId`（int）：
```php
/**
 * 创建扩展表记录
 * 由中台下单流程调用，用于写入产品类型专属数据
 * @param string $orderNo   订单号
 * @param array  $cartInfo  购物车数据（首个商品）
 * @param array  $context   上下文数据（当前包含：store_id, shipping_type, verify_code）
 * @return int              扩展表主键ID
 */
public function createOrderExtend(string $orderNo, array $cartInfo, array $context): int
{
    $dao = app(\modules\product\ticket\dao\TicketOrderDao::class);
    $result = $dao->save([
        'order_no'   => $orderNo,
        'product_id' => $cartInfo['product_id'] ?? 0,
        'uid'        => $cartInfo['uid'] ?? 0,
        'status'     => TicketOrderAdapter::EXT_STATUS_PENDING_PAYMENT,
        'add_time'   => time(),
    ]);
    return $result->id ?? 0;
}
```

**`afterOrder()`** — 幂等校验，更新中台表 `source_type`/`source_id`：
```php
/**
 * 下单完成后处理
 * 幂等校验：如果 createOrderExtend 成功但中台表写入失败，此处回填
 */
public function afterOrder(string $orderNo, array $cartInfo, array $context): void
{
    $dao = app(\modules\product\ticket\dao\TicketOrderDao::class);
    $exist = $dao->getOne(['order_no' => $orderNo], 'id');
    if (!$exist) {
        // 幂等：createOrderExtend 可能已写入，回查不到时重试
        $this->createOrderExtend($orderNo, $cartInfo, $context);
        $exist = $dao->getOne(['order_no' => $orderNo], 'id');
    }
    $extendId = $exist['id'] ?? 0;
    if (!$extendId) return;

    $centerDao = app(\app\dao\order\OrderCenterDao::class);
    $centerDao->update(['order_no' => $orderNo], [
        'source_type' => TicketOrderAdapter::getSourceType(),
        'source_id'   => $extendId,
    ]);
}
```

#### `createOrderExtend()` 的拓展字段存储说明

`createOrderExtend()` 接收的 `$context` 包含 `store_id`（门店ID）、`shipping_type`（配送方式类型）、`verify_code`（核销码）字段。**当前 `eb_pt_normal_order` 表没有对应的实体列**，如果需要在扩展表持久化这些字段：

- **方案1**：在扩展表新增列（如实物商品的 `eb_pt_normal_order` 加 `store_id`/`shipping_type`/`verify_code`）
- **方案2**：扩展表新增 JSON 列统一存储（更灵活，适合多个扩展字段的场景）

> 当前代码中 `Handler::createOrderExtend()` 尝试写入 `store_id`、`verify_code`、`shipping_type` 到扩展表，但因表无对应列而被静默丢弃。这些值已写入中台表实体列，扩展表无需重复存储。

### 6.5 如果需要下单扣库存

中台创建订单时会调用 `{Type}ProductTypeHandler::decStock()`：

```php
/**
 * 下单扣库存
 * @param int    $productId 商品ID
 * @param string $skuUnique SKU唯一码
 * @param int    $num       购买数量
 * @return bool
 */
public function decStock(int $productId, string $skuUnique, int $num): bool
{
    $dao = app(\modules\product\ticket\dao\TicketProductSkuDao::class);
    $sku = $dao->getOne(['product_id' => $productId, 'unique_code' => $skuUnique]);
    if (!$sku) return false;
    $sku = is_object($sku) ? $sku->toArray() : $sku;
    return $dao->decStock(['id' => $sku['id']], $num);
}
```

完整接口定义：[`ProductTypeHandlerInterface`](../../../../../backend/crmeb/trade/product/ProductTypeHandlerInterface.php)
基类实现：[`BaseProductTypeHandler`](../../../../../backend/crmeb/trade/product/BaseProductTypeHandler.php)

### 6.6 产品类型需实现的完整方法清单

| 方法 | 必须 | 说明 |
|------|------|------|
| `getSkuInfo()` | ✅ | 获取 SKU 价格库存 |
| `getShippingInfo()` | ✅ 非虚拟商品 | 获取运费配置 |
| `getDetailExtend()` | ❌ | 返回自定义表单/商品参数（按需） |
| `createOrderExtend()` | ✅ | 写扩展表，返回 `extendId` |
| `afterOrder()` | ✅ | 幂等校验，更新 `source_type`/`source_id` |
| `decStock()` | ❌ | 不下库存可以不实现 |
| `getOrderSourceType()` (Adapter) | ✅ | 定义 `source_type` 常量 |

## 七、前端 features 控制

后端 `CenterOrderController::confirm()` 通过 `features` 对象控制前端展示哪些模块（见["响应关键字段"](#21-订单确认数据展示)表格）。其中配送方式相关字段独立控制：

```php
// CenterOrderController::confirm() 中构造 features
$features = [
    'express_shipping' => $expressShipping,  // 是否支持快递配送（由产品类型 Handler 决定）
    'store_pickup'     => $storePickup,      // 是否支持到店自提（由产品类型 Handler 决定）
    'door_service'     => $doorService,      // 是否支持上门服务（由产品类型 Handler 决定）
    'door_service_postage' => $doorServicePostage, // 上门服务费用（元）
    'remark'        => true,                                          // 买家留言，与配送方式无关
    'invoice'       => (bool)sys_config('invoice_func_status', 0),    // 发票，与商品类型无关
    'integral'      => !$isActivityOrder && $integralOpen,              // 积分抵扣，与商品类型无关
    'coupon'        => !$isActivityOrder && !empty($couponList),        // 优惠券，与商品类型无关
    'custom_form'   => !empty($formattedCustomForm),                    // 自定义表单，与商品类型无关
    'params_list'   => !empty($detailExtend['params_list'] ?? []),        // 商品参数，与商品类型无关
    'visitor_form'  => !empty($detailExtend['visitor_form_config'] ?? []),  // 游客信息表单（门票专属）
];
```

> 注意：`features.remark` 控制的是买家留言输入框，对应订单表 `mark` 字段；管理员后台添加的订单备注是 `eb_order_center.remark` 字段，不由前端 features 控制。

虚拟商品应将 `custom_form` 按需开启，`remark`、`invoice` 保持 true（虚拟商品也需要留言和发票）；`express_shipping` 和 `store_pickup` 由产品类型 `Handler` 的 `requiresShipping()`/`supportsPickup()` 决定。

## 八、自定义表单数据收集

前端 `OrderCustomForm` 组件收集用户填写的自定义表单，提交时格式：

```json
{
  "custom_field_0": "张三",
  "custom_field_1": "13800138000"
}
```

后端下单时需要将字段 key 转换为带标题的结构化数据存储：

```php
$customFormData = [];
$customFormDefinition = $detailExtend['custom_form'] ?? [];
foreach ($submittedCustomForm as $key => $value) {
    $index = (int) str_replace('custom_field_', '', $key);
    $field = $customFormDefinition[$index] ?? [];
    $customFormData[] = [
        'label' => $field['label'] ?? '',
        'title' => $field['title'] ?? '',
        'value' => $value,
    ];
}
// 写入订单中台表 eb_order_center.custom_form
```

> 设计说明：自定义表单数据属于订单级信息，随订单详情高频读取，统一存储在 `eb_order_center.custom_form` JSON 字段中，可避免与扩展表 JOIN，简化详情页查询。当前中台下单流程（`CenterOrderController::create()`）已内置该转换并写入中台表，产品类型 Handler 无需额外处理存储。

## 九、注意事项

1. `order_key` 用于缓存订单数据，有效期通常 600 秒，必须在有效期内提交
2. 中台下单接口**必须**正确设置 `eb_order_center` 的 `product_type`、`source_type`、`source_id`
3. 支付回调时根据 `source_type` 找到对应 `OrderAdapter` 推进状态
4. 下单前务必校验库存，避免超卖
5. 如果产品类型支持购物车多商品下单，需要额外实现购物车相关逻辑
6. 配送信息已拆分存储：`shipping_type`/`store_id`/`verify_code` 写入 `eb_order_center` 实体列；自提订单的门店详情写入 `extra_info.store_info`。详情页优先从实体列读取配送信息
7. API 路径和模块级路径写入 `extra_info` 的内容不完全一致（模块级缺少 `store_info` / `items`），需注意兼容
8. **扩展表写失败检测**：`createOrderExtend()` 返回 `extendId` 后，若中台表写入失败，事务回滚；若中台表成功但扩展表更新失败，`afterOrder()` 幂等处理
