# 用户端订单列表实现

订单中台统一提供用户订单列表接口，各产品类型通过 `{ProductType}OrderAdapter`（继承 `BaseOrderTypeAdapter`、实现 `OrderTypeAdapterInterface`）提供状态推导、操作按钮、扩展筛选条件等。

## 一、页面入口

订单列表页（当前实际使用的页面）：
- 页面文件：`frontend/uni-app/src/pages/goods/order_list/index.vue`（已在 `pages.json` 注册，导航栏标题"我的订单"）
- 引擎文件：`frontend/uni-app/src/dynamic-pages/engines/OrderListEngine.vue`
- 接口文件：`frontend/uni-app/src/api/order-center.js`

> **注意**：`frontend/uni-app/src/pages/center/order/` 下仅有 `aftersale/`、`create/`、`detail/` 三个目录，**不存在 `list/` 目录**。订单列表页实为旧路径 `pages/goods/order_list/index.vue`，页面内容已切换为中台接口（由 `OrderListEngine` 渲染）。新建页面时请勿沿用 `pages/center/order/list/index.vue` 这一不存在路径。

## 二、后端接口

### 2.1 订单列表

```
GET /api/center/order/list
```

控制器：`backend/app/api/controller/v1/order/CenterOrderController.php::list()`
用户端服务层：`backend/app/services/order/OrderCenterUserServices.php::getList()`
管理端服务层：`backend/app/services/order/OrderCenterServices.php`

请求参数：

| 参数 | 类型 | 说明 |
|------|------|------|
| status | string | **语义化状态筛选**：all（全部，传空）/pending_payment/pending_delivery/pending_use/pending_comment/after_sale。由后端 `CenterOrderController::mapSemanticStatus()` 映射为中台状态码：pending_delivery/pending_use → processing（并按 `product_type` 细分：待收货=normal，待使用=非 normal）；pending_comment → completed；after_sale → refunding,refunded。前端无需维护状态映射表 |
| product_type | string | 产品类型筛选 |
| order_category | string | 订单大类筛选（goods/points/member/recharge） |
| page | int | 页码 |
| limit | int | 每页数量 |

前端顶部 Tab 传语义化状态（all/pending_payment/pending_delivery/pending_use/pending_comment，见第四节），后端负责映射为中台统一状态码（pending_payment/processing/pending_review/completed/canceled/after_sale/refunded）。

### 2.2 订单统计

```
GET /api/center/order/stat
```

用于顶部 Tab 数字展示。

## 三、响应数据结构

```json
{
  "status": 200,
  "msg": "ok",
  "data": {
    "list": [
      {
        "order_no": "cp123456789",
        "product_type": "normal",
        "source_type": "normal_order",
        "status_code": "completed",
        "status_text": "已完成",
        "add_time": 1753987200,
        "image": "https://...",
        "title": "无线蓝牙耳机",
        "subtitle": "",
        "pay_price": "199.00",
        "total_num": 1,
        "extra_info": {
          "contact": {
            "name": "张三",
            "phone": "13800138000",
            "address": "广东省深圳市南山区科技园"
          },
          "sku_info": {
            "suk": "红色;M码"
          }
        },
        "business_status": null,
        "source_status": "completed",
        "extend_text": "已发货：顺丰快递 SF1234567890",
        "after_sale": {
          "has_after_sale": false,
          "count": 0,
          "types": [],
          "statuses": [],
          "is_processing": false
        },
        "order_buttons": [
          { "text": "再次购买", "action": "repurchase", "primary": true }
        ]
      }
    ],
    "total": 100
  }
}
```

字段说明：

| 字段 | 类型 | 说明 |
|------|------|------|
| status_code | string | 中台统一状态码：pending_payment / processing / pending_review / completed / canceled / after_sale / refunded |
| status_text | string | 中台统一状态码的中文名（如"待支付""进行中""已完成"） |
| extra_info | object | JSON 解析后的关联数组，包含 contact（收货信息）、sku_info（规格文本）、delivery（物流信息）等 |
| business_status | object|null | 业务状态快照（如 "已退款"），从 extra_info 提取到顶层，供前端首页卡片直接判断 |
| source_status | string | 扩展表子状态（如 eb_pt_normal_order.status），精确到具体产品类型的履约阶段 |
| extend_text | string | 扩展文案，展示在列表项底部（如"还剩 1 天自动确认收货"） |
| after_sale | object | 售后概要信息（has_after_sale / count / types / statuses / is_processing） |
| shipping_type | int | 配送类型：1=快递配送，2=到店自提（仅实物订单，由中台统一存储） |
| store_id | int | 门店 ID（自提订单时有效） |
| verify_code | string | 核销码（自提订单时有效） |

中台在返回前会触发 `OrderCenterListExtend` 事件，传入 `['list' => &$list]`。如果产品类型需要给列表项注入额外字段（如拼团状态、核销状态），需在 listener 中直接修改 `$event['list']`（注意是引用传参），而不是依赖返回值合并。

## 四、OrderListEngine 渲染逻辑

`OrderListEngine.vue` 已重构为**语义化状态 Tab + 多商品列表**（v8.1.1）：

```vue
<template>
  <scroll-view scroll-y class="engine-scroll">
    <view :style="colorStyle">
      <view class="my-order">
        <!-- 顶部统计（orderData.all 为总消费/总单数） -->
        <view class="header bg-color">
          <view class="picTxt acea-row row-between-wrapper">
            <view class="text">
              <view class="name">{{ $t(`订单信息`) }}</view>
              <view>
                <text class="mr-40">{{ $t(`消费订单`) }}：{{ orderData.all && orderData.all.count || 0 }} 单 </text>
                {{ $t(`总消费`) }}：{{ $t(`￥`) }}{{ orderData.all && orderData.all.amount || 0 }}
              </view>
            </view>
          </view>
        </view>

        <!-- 状态 Tab（v8.1 重构：all/pending_payment/pending_delivery/pending_use/pending_comment）
             传值即语义化状态，后端 mapSemanticStatus() 映射为中台状态码 -->
        <view class="nav acea-row row-around">
          <view class="item" :class="orderStatus == 'all' ? 'on' : ''" @click="statusClick('all')">
            <view>{{ $t(`全部`) }}</view>
            <view class="num">{{ orderData.all && orderData.all.count || 0 }}</view>
          </view>
          <view class="item" :class="orderStatus == 'pending_payment' ? 'on' : ''" @click="statusClick('pending_payment')">
            <view>{{ $t(`待支付`) }}</view>
            <view class="num">{{ orderData.grouped && orderData.grouped.pending_payment && orderData.grouped.pending_payment.count || 0 }}</view>
          </view>
          <view class="item" :class="orderStatus == 'pending_delivery' ? 'on' : ''" @click="statusClick('pending_delivery')">
            <view>{{ $t(`待收货`) }}</view>
            <view class="num">{{ orderData.grouped && orderData.grouped.pending_delivery && orderData.grouped.pending_delivery.count || 0 }}</view>
          </view>
          <view class="item" :class="orderStatus == 'pending_use' ? 'on' : ''" @click="statusClick('pending_use')">
            <view>{{ $t(`待使用`) }}</view>
            <view class="num">{{ orderData.grouped && orderData.grouped.pending_use && orderData.grouped.pending_use.count || 0 }}</view>
          </view>
          <view class="item" :class="orderStatus == 'pending_comment' ? 'on' : ''" @click="statusClick('pending_comment')">
            <view>{{ $t(`待评价`) }}</view>
            <view class="num">{{ orderData.grouped && orderData.grouped.pending_comment && orderData.grouped.pending_comment.count || 0 }}</view>
          </view>
        </view>

        <!-- 订单列表 -->
        <view class="list">
          <view v-for="(order, index) in orders" :key="order.order_no" class="item">
            <view @click="goDetail(order)">
              <view class="title acea-row row-between-wrapper">
                <view class="acea-row row-middle">
                  <!-- 类型标签直接使用后端下发的 order.type_label（非前端方法计算） -->
                  <text v-if="order.type_label" class="sign cart-color acea-row row-center-wrapper">{{ order.type_label }}</text>
                  <view>{{ formatTime(order.add_time) }}</view>
                </view>
                <StatusBadge :status-code="order.status_code" :status-text="order.status_text" />
              </view>
              <view class="item-info acea-row row-between row-top">
                <!-- v8.1.1 多商品：遍历 extra_info.items 展示所有商品 -->
                <template v-if="getOrderItems(order).length > 1">
                  <view class="multi-goods">
                    <view v-for="(item, idx) in getOrderItems(order)" :key="idx" class="goods-row">
                      <image :src="item.image" mode="aspectFill" class="goods-img"></image>
                      <view class="goods-text">
                        <text class="goods-name line2">{{ item.store_name || '' }}</text>
                        <text v-if="getItemSkuText(item)" class="goods-sku line1">{{ getItemSkuText(item) }}</text>
                        <view class="goods-meta">
                          <text class="goods-price">¥{{ item.true_price || item.price || '0.00' }}</text>
                          <text class="goods-num">x{{ item.cart_num || 1 }}</text>
                        </view>
                      </view>
                    </view>
                  </view>
                </template>
                <!-- 单商品或无 items 降级：保持原有展示 -->
                <template v-else>
                  <view class="pictrue">
                    <image :src="order.image" mode="aspectFill"></image>
                  </view>
                  <view class="text">
                    <view class="title-row">
                      <text class="name line2">{{ order.title || '' }}</text>
                      <view class="money">
                        <view>{{ $t(`￥`) }}{{ order.pay_price || '0.00' }}</view>
                        <view>x{{ order.total_num || 0 }}</view>
                      </view>
                    </view>
                    <view v-if="getSkuText(order)" class="sku-text line1">
                      {{ getSkuText(order) }}
                    </view>
                  </view>
                </template>
              </view>
              <view v-if="getOrderExtendText(order)" class="extend-text line1">
                {{ getOrderExtendText(order) }}
              </view>
            </view>
            <view class="bottom acea-row row-right row-middle">
              <button
                v-for="btn in (order.order_buttons || [])"
                :key="btn.action"
                class="bnt"
                :class="getBtnClass(btn)"
                @click.stop="handleAction(order, btn)"
              >{{ btn.text }}</button>
            </view>
          </view>
        </view>
      </view>
    </view>
  </scroll-view>
</template>
```

**多商品支持（v8.1.1）**：`getOrderItems(order)`（约 L204）从 `order.extra_info.items` 读取商品数组；`length > 1` 时走多商品布局，逐项展示 `image`/`store_name`/`sku`/`true_price`/`cart_num`；单商品或无 items 时降级为原单商品布局（`order.image`/`order.title`/`order.total_num`）。`getItemSkuText(item)`（约 L214）取单项规格文本。

**方法清单**（约 L181-227，均为前端展示辅助方法）：
- `getSkuText(order)`：取 `extra_info.sku_info.suk`（单商品规格文本，兼容旧字段 attrInfo/suk）
- `getOrderItems(order)`：取 `extra_info.items` 多商品数组
- `getItemSkuText(item)`：取单个商品的规格文本（`item.sku` 或 `attr_info.suk`）
- `getOrderExtendText(order)`：直接返回后端下发的 `extend_text` 扩展文案
- `getBtnClass(btn)`：按后端 `btn.primary` 决定主/次按钮样式

## 五、产品类型需要做的事

产品类型需要创建 `{ProductType}OrderAdapter` 类，继承 `BaseOrderTypeAdapter`，位于 `modules/product/{type}/handler/{ProductType}OrderAdapter.php`。

**注册机制**：适配器通过 `OrderTypeRegistry` **显式注册**（无自动扫描）。各模块在 `App.php` 的 `register()` 中静态调用 `OrderTypeRegistry::register('{source_type}', Adapter::class)` 完成注册，例如：
- `modules/product/normal/App.php` 注册 `normal_order` → `NormalOrderAdapter`
- `modules/product/virtual/App.php` 注册 `virtual_order`、`cardkey/App.php` 注册 `cardkey_order`，交易模式模块（integral）同理

app 层内置适配器（`offline_receipt`，线下收银记录，原 `other_order` 更名）由 `app/listener/system/OrderTypeRegisterListener.php` 监听 `OrderTypeRegister` 事件注册。注册 key 即 `source_type`，注册完成后中台即可按 `source_type` 找到对应适配器，无需其它配置。

完整接口定义：`backend/crmeb/trade/order/type/OrderTypeAdapterInterface.php`
基类实现：`backend/crmeb/trade/order/type/BaseOrderTypeAdapter.php`
可参考现有实现：[`NormalOrderAdapter`](../../../../../backend/modules/product/normal/handler/NormalOrderAdapter.php)

### 5.1 必须实现的方法

```php
// 身份标识
public static function getSourceType(): string;        // 源表类型，如 'cardkey_order'
public static function getOrderCategory(): string;     // 大类，如 'goods' / 'points' / 'member' / 'recharge'
public static function getOrderCategoryLabel(): string; // 中文标签，如 '商品订单'
public static function getModelClass(): string;         // 扩展订单模型类名

// 状态树定义（核心！唯一数据源）
protected function defineStatusTree(): array;

// 状态推导
public function resolveStatusCode(array $sourceOrder): string;
public function resolvePayStatus(array $sourceOrder): int;
public function resolveRefundStatus(array $sourceOrder): int;
public function resolveBusinessStatus(array $sourceOrder): ?array;

// 按钮获取（基类已提供默认实现，子类可按需覆写）
public function getStatusButtons(string $statusCode): array;
public function getOrderButtons(array $detail): array;

// 扩展筛选（约定方法，不在接口中，管理端列表使用）
public static function resolveExtensionFilter(array $filter): array;
```

### 5.2 核心方法：defineStatusTree()

`defineStatusTree()` 是 **唯一数据源**，集中定义：
- 订单有哪些扩展状态
- 每个状态对应哪个中台统一状态码
- 每个状态的名字、badge、筛选条件
- 每个状态的管理端/用户端按钮
- 进入该状态时触发的回调

```php
protected function defineStatusTree(): array
{
    return [
        OrderFlowNode::make(self::EXT_STATUS_PENDING_PAYMENT)
            ->centerStatus(self::STATUS_PENDING_PAYMENT)
            ->label('待支付')
            ->userButtons([
                Button::make('取消订单', 'cancelOrder'),
                Button::primary('去支付', 'payOrder'),
            ]),

        OrderFlowNode::make(self::EXT_STATUS_COMPLETED)
            ->centerStatus(self::STATUS_COMPLETED)
            ->label('已完成')
            ->userButtons([
                Button::make('申请售后', 'applyAfterSale'),
                Button::primary('再次购买', 'repurchase'),
            ]),
    ];
}
```

完整链式方法：

| 方法 | 参数 | 说明 |
|------|------|------|
| `centerStatus()` | 中台状态码常量 | 如 `self::STATUS_PROCESSING` |
| `label()` | string | 状态中文名 |
| `badge()` | code, text, detail? | 前端业务徽标（可选） |
| `adminButtons()` | array | 管理端操作按钮 |
| `userButtons()` | array | 用户端操作按钮 |
| `filter()` | array | 管理端列表筛选条件（可选） |
| `onEnter()` | string | 进入该状态的回调方法名（可选） |

### 5.3 实现 resolveStatusCode()

将扩展表状态推导为中台统一状态码。优先级：取消 > 退款完成 > 售后中 > 未支付 > 扩展状态映射。

```php
public function resolveStatusCode(array $sourceOrder): string
{
    $status = (string) ($sourceOrder['status'] ?? '');
    if ($status === self::EXT_STATUS_COMPLETED) return self::STATUS_COMPLETED;
    if ($status === self::EXT_STATUS_CANCELED)  return self::STATUS_CANCELED;
    return self::STATUS_PENDING_PAYMENT;
}
```

### 5.4 获取操作按钮

基类 `BaseOrderTypeAdapter::getStatusButtons()` 已提供默认实现：从 `defineStatusTree()` 定义的状态树中按 `$statusCode` 查找 `user_buttons`。子类无需覆写，如果有动态按钮需求可覆写 `getOrderButtons()`：

```php
// 接口签名（OrderTypeAdapterInterface）
public function getStatusButtons(string $statusCode): array;

// 基类默认实现（从 defineStatusTree 读取 user_buttons）
// BaseOrderTypeAdapter 的 getOrderButtons() 会读取 $detail 中的信息
// 自动为每个按钮注入 order_no 参数
public function getOrderButtons(array $detail): array;
```

### 5.5 实现 resolveExtensionFilter()

管理端列表按扩展状态筛选时，中台会静态调用该辅助方法（约定方法，不在接口中）。返回匹配的 `order_no` 列表，无结果时返回 `['__NONE__']` 以避免 SQL 全表扫描：

```php
public static function resolveExtensionFilter(array $filter): array
{
    /** @var TicketOrderDao $dao */
    $dao = app()->make(TicketOrderDao::class);
    return $dao->columnByFilters($filter, 'order_no') ?: ['__NONE__'];
}
```

## 六、前端 action-map

`frontend/uni-app/src/dynamic-pages/api/action-map.js` 定义按钮点击行为：

```js
export default {
  payOrder(order, params) {
    uni.navigateTo({ url: routes.cashier + '?order_no=' + order.order_no + '&from_type=order' });
  },
  cancelOrder(order, params) {
    uni.$emit('order:cancel', { order_no: order.order_no });
  },
  confirmReceive(order, params) {
    uni.$emit('order:confirm', { order_no: order.order_no });
  },
  repurchase(order, params) {
    uni.navigateTo({ url: routes.orderCreate + '?from_order=' + order.order_no });
  },
  // ...
};
```

新增操作按钮时，需要：
1. 在 `{ProductType}OrderAdapter` 的 `userButtons` 中返回新的 `action`
2. 在 `action-map.js` 中补充处理函数

## 七、订单类型标签

`getOrderTypeLabel(order)` 方法**已删除**（源码中不存在）。实际模板（见第四节）直接使用后端下发的 `order.type_label` 字段渲染类型标签：

```vue
<text v-if="order.type_label" class="sign cart-color acea-row row-center-wrapper">{{ order.type_label }}</text>
```

`type_label` 由后端在 `OrderCenterListExtend` 事件监听器中为列表项注入（产品类型 `getOrderCategoryLabel()` 返回大类标签，但前端不会自动调用后端方法）。如果需要显示产品类型专属标签，在 `OrderCenterListExtend` 事件监听器中为列表项注入 `type_label` 字段：

```php
// modules/product/{type}/listener/OrderCenterListExtendListener.php
public function handle($event): void
{
    $list = &$event['list'];
    foreach ($list as &$item) {
        if ($item['product_type'] === 'ticket') {
            $item['type_label'] = '演唱会';
        }
    }
}
```

## 八、注意事项

1. 订单列表统一从 `eb_order_center` 读取，不直接查询扩展表
2. 状态筛选时，中台状态码（如 `processing`）可能对应扩展表多个子状态，由 `resolveExtensionFilter()` 处理
3. 按钮行为优先通过 `action-map.js` 统一处理，避免每个引擎重复写逻辑
4. 移动端订单列表接口**不返回** `timeline` 字段，详情接口才返回
5. 顶部 Tab 数字由 `/api/center/order/stat` 返回 `{all, status_counts, grouped}` 结构（`OrderCenterUserServices::getUserStatistics()`）。`grouped` 按 Tab 语义化状态分组：pending_payment / pending_delivery（processing+product_type=normal）/ pending_use（processing+product_type≠normal）/ pending_comment / completed / after_sale（含 refunding+refunded）
6. 自提订单的 `shipping_type=2`、`store_id`、`verify_code` 由中台统一写入 `eb_order_center`，产品类型无需单独处理。但需要在 `OrderCenterListExtend` 监听器中补充自提相关的扩展文案（如"请到店核销"）
7. `OrderCenterListExtend` 事件传入的是引用参数 `['list' => &$list]`，监听器必须通过 `$event['list']` 直接修改（`&$item` 遍历），不要依赖返回值合并
8. 事件调用使用字符串 key 方式：`event('OrderCenterListExtend', $eventData)`，不使用类常量方式
