# 用户端购物车实现

购物车是用户端下单链路的关键环节，承担「加入购物车 / 立即购买 / 合单结算」三类入口。v8.1 中购物车**尚未完全中台化**：控制器与路由仍保留在旧版 `app/api/controller/v1/store/` 路径下，但服务层 `StoreCartServices` 已通过 `ProductTypeRegistry`、`TradeModeRegistry`、`CenterProductServices` 对接中台，各产品类型通过 `Handler::getCartStrategy()` 声明加购契约，前端零硬编码判断产品类型。

## 一、页面入口与文件清单

### 1.1 用户端（uni-app）

| 角色 | 路径 |
|------|------|
| 购物车页面（tabBar 第 3 项） | `frontend/uni-app/src/pages/order_addcart/order_addcart.vue` |
| 商品详情页（加购入口） | `frontend/uni-app/src/pages/goods_details/index.vue` |
| 规格选择/加购弹窗 | `frontend/uni-app/src/components/productWindow/index.vue` |
| 详情页底部按钮栏 | `frontend/uni-app/src/subpackage/diyComponents/productBottom.vue` |
| 售卖形态容器（多产品类型） | `frontend/uni-app/src/subpackage/diyComponents/productSections/specPreview.vue` |
| Block 子渲染器（6 个） | `frontend/uni-app/src/subpackage/diyComponents/productSections/blocks/Block{Chips,Matrix,PriceList,TimeSlots,Calendar,Policy}.vue` |
| 门店购物车弹层 | `frontend/uni-app/src/components/cartList/index.vue` |
| 底部 tabBar（角标） | `frontend/uni-app/src/components/pageFooter/index.vue` + `components/BaseBadge/index.vue` |
| 购物车 API | `frontend/uni-app/src/api/order.js`、`frontend/uni-app/src/api/store.js` |
| Pinia（cartNum 状态） | `frontend/uni-app/src/store/modules/indexData.js` |
| 角标全局 watch | `frontend/uni-app/src/App.vue` |
| 下单页引擎（购物车模式接入） | `frontend/uni-app/src/dynamic-pages/engines/OrderCreateEngine.vue` |

### 1.2 后端

| 角色 | 路径 |
|------|------|
| 控制器 | `backend/app/api/controller/v1/store/StoreCartController.php` |
| 核心服务 | `backend/app/services/order/StoreCartServices.php` |
| DAO | `backend/app/dao/order/StoreCartDao.php` |
| Model | `backend/app/model/order/StoreCart.php` |
| 用户端路由 | `backend/app/api/route/v1.php`（`cart/*` 与 `v2/*` 分组） |
| 中台订单确认（购物车接入） | `backend/app/api/controller/v1/order/CenterOrderController.php::cartConfirm()` |
| 事件监听器 | `backend/app/listener/user/UserCartListener.php` |

> 购物车**没有独立的中台控制器**（如 `CenterCartController`），合单下单通过 `CenterOrderController::confirm()` 检测 `cartId` 参数时走 `cartConfirm()` 分支接入。

## 二、后端接口

### 2.1 v1 购物车接口（`app/api/route/v1.php` mark=cart 分组）

| Method | Path | Controller@Method | 说明 |
|--------|------|-------------------|------|
| GET | `cart/list` | `StoreCartController@lst` | 购物车列表（按 `status` 分有效/无效） |
| POST | `cart/add` | `StoreCartController@add` | 加入购物车 / 立即购买（`new=1`） |
| POST | `cart/del` | `StoreCartController@del` | 删除购物车项（ids 逗号串） |
| POST | `cart/num` | `StoreCartController@num` | 修改购物车数量 |
| GET | `cart/count` | `StoreCartController@count` | 购物车数量统计（角标） |

### 2.2 v2 购物车接口（需授权分组）

| Method | Path | Controller@Method | 说明 |
|--------|------|-------------------|------|
| POST | `reset_cart` | `StoreCartController@resetCart` | 购物车重选规格（合并同 SKU 数量） |
| GET | `cart_list` | `StoreCartController@getCartList` | 购物车列表（过滤活动商品，仅普通商品） |
| POST | `set_cart_num` | `StoreCartController@setCartNum` | 首页/分类页加购（type: -1设置/0减/1加） |

### 2.3 中台订单接口（购物车接入点）

```
GET /api/center/order/confirm?cartId=1,2,3&new=0   # 购物车合单确认
GET /api/center/order/confirm?cartId=cacheKey&new=1 # 立即购买确认
POST /api/center/order/create                        # 下单（成功后清除购物车）
```

`CenterOrderController::confirm()` 检测到 `cartId` 参数时走 `cartConfirm()` 分支，调用 `StoreCartServices::getUserProductCartListV1()` 获取购物车数据。

### 2.4 控制器方法清单

[`StoreCartController`](../../../../../backend/app/api/controller/v1/store/StoreCartController.php) 共 9 个方法：

| 方法 | 参数 | 职责 |
|------|------|------|
| `lst(Request)` | `[$status]` 默认 1 | 购物车列表 v1 |
| `add(Request)` | `productId/cartNum/uniqueId/new/combinationId/secKillId/bargainId/advanceId/pinkId` | 加购/立即购买，根据活动 ID 判定 type |
| `del(Request)` | `['ids']` | 删除购物车项 |
| `num(Request)` | `['id','number']` | 修改数量 |
| `count(Request)` | `[$numType]` 默认 true | 数量统计（true=条数，false=件数） |
| `reChange(Request)` | `[$cart_id,$product_id,$unique]` | 重选 v1（**未注册路由，遗留代码**） |
| `resetCart(Request)` | `[$id,$unique,$num,$product_id]` | 重选 v2 |
| `getCartList(Request)` | 无 | 列表 v2（仅普通商品） |
| `setCartNum(Request)` | `[$product_id,$num,$unique,$type]` | 首页加购（-1/0/1） |

## 三、数据库表

### 3.1 `eb_store_cart`（购物车表）

| 字段 | 类型 | 默认 | 说明 |
|------|------|------|------|
| `id` | bigint unsigned | AUTO_INCREMENT | 购物车表ID |
| `uid` | int unsigned | 0 | 用户ID |
| `type` | varchar(32) | '' | 类型（0普通/1秒杀/2砍价/3拼团/6预售） |
| `product_id` | int unsigned | 0 | 商品ID（中台商品ID） |
| `product_attr_unique` | varchar(16) | '' | SKU 唯一码 |
| `cart_num` | smallint unsigned | 0 | 商品数量 |
| `add_time` | int unsigned | 0 | 添加时间 |
| `is_pay` | tinyint(1) | 0 | 0=未购买 1=已购买 |
| `is_del` | tinyint(1) | 0 | 是否删除 |
| `is_new` | tinyint(1) | 0 | 是否为立即购买（立即购买模式实际不入库，走缓存） |
| `combination_id` | int unsigned | 0 | 拼团id |
| `seckill_id` | int unsigned | 0 | 秒杀商品ID |
| `bargain_id` | int unsigned | 0 | 砍价id |
| `advance_id` | int | 0 | 预售商品id |
| `status` | tinyint(1) | 1 | 购物车商品状态（0=无效，1=有效，NULL 兼容老数据） |

**索引**：`goods_id(product_id)`、`uid(uid,is_pay)`、`uid_2(uid,is_del)`、`uid_3(uid,is_new)`、`type(type)`

> 立即购买（`new=1`）**不写入此表**，仅写入 Redis 缓存（key=雪花ID，TTL=3600秒）。

### 3.2 订单商品快照

旧表 `eb_store_order_cart_info` 已删除。新订单的商品明细与规格快照统一写入 `eb_order_center.extra_info.items`，订单详情、打印与退款链路从中台快照读取，不再依赖独立购物车快照 Service、DAO 或 Model。

## 四、加入购物车流程

### 4.1 接口

```
POST /api/cart/add
```

请求参数：

| 参数 | 类型 | 说明 |
|------|------|------|
| productId | int | 商品ID |
| cartNum | int | 加购数量 |
| new | int | 0=加入购物车，1=立即购买 |
| uniqueId | string | SKU 唯一码 |
| combinationId | int | 拼团活动ID |
| secKillId | int | 秒杀活动ID |
| bargainId | int | 砍价活动ID |
| advanceId | int | 预售活动ID |
| pinkId | int | 拼团团队ID |

### 4.2 后端处理流程

入口：`StoreCartController@add` → [`StoreCartServices::setCart()`](../../../../../backend/app/services/order/StoreCartServices.php)

1. **type 判定**：根据活动 ID 确定数字 type（0=普通、1=秒杀、2=砍价、3=拼团、6=预售）
2. **拼团校验**：`pinkId` 存在时调 `StorePinkServices::isPinkStatus()` 校验团队有效期
3. **VIP 商品校验**：普通商品（type=0）调 `checkVipGoodsBuy()` 校验付费会员专属商品购买资格
4. **限购校验**：普通商品调 [`checkLimit()`](../../../../../backend/app/services/order/StoreCartServices.php)
   - `limit_type=1` 单次限购：购物车已有数量 + 本次数量 ≤ `limit_num`
   - `limit_type=2` 永久限购：购物车 + 已购 - 退款 + 本次数量 ≤ `limit_num`
5. **库存校验**：[`checkProductStock()`](../../../../../backend/app/services/order/StoreCartServices.php)
   - 活动商品：`TradeModeRegistry::getByLegacyType($type)->checkCartStock()`
   - 普通商品：`ProductTypeHandler::getSkuInfo()` 校验 SKU 有效性与库存
   - 库存不足自动调整：若 `nowStock < (cartNum + stockNum)`，将已有购物车数量调整为剩余库存
6. **分支处理**：
   - `new=1`（立即购买）：雪花算法生成缓存 key，组装 cartInfo 写入 Redis（3600 秒），返回 key
   - `new=0`（加入购物车）：派发 `ProductLogJob`（cart 日志），查询同 SKU 记录累加或新增，触发 `UserCartListener` 事件

### 4.3 立即购买 vs 加入购物车

| 维度 | 立即购买（new=1） | 加入购物车（new=0） |
|------|-------------------|---------------------|
| 数据存储 | Redis 缓存（key=雪花ID，TTL=3600秒） | MySQL `eb_store_cart` 表 |
| 数量累加 | 不累加（每次生成新 key） | 同 SKU 累加 cart_num |
| 限购校验 | 不校验购物车已有数量 | 校验（含购物车已有数量） |
| 事件触发 | 不触发 | 触发 `CustomEventListener` + `UserCartListener` |
| 日志记录 | 不记录 | 派发 `ProductLogJob`（cart 类型） |
| 返回值 | 缓存 key（字符串） | 购物车记录 ID（int） |
| 下单后清理 | 无需清理（缓存自动过期） | 下单成功后 `removeUserCart()` 物理删除 |

### 4.4 立即购买缓存 key 生成（雪花算法）

```php
public function getCartId($prefix)
{
    $snowflake = new \Godruoyi\Snowflake\Snowflake();
    if (PHP_INT_SIZE == 4) {
        $id = abs((int)$snowflake->id());
    } else {
        $id = $snowflake->setStartTimeStamp(strtotime('2020-06-05') * 1000)->id();
    }
    return $prefix . $id;
}
```

## 五、SKU 唯一码规则

**重要**：SKU 唯一码（`product_attr_unique` / `unique_code`）由各产品类型 Handler 自行生成与解析，购物车只接收并校验。

### 5.1 常规产品类型（normal/virtual/cardkey）

`unique_code` 为 `md5('{产品类型}_{商品ID}_{SKU序号}')` 的 8 位小写哈希，由 `char(8)` 字段截断。生成逻辑位于 [`ProductSaveServices.php`](../../../../../backend/modules/product/normal/service/ProductSaveServices.php)：

```php
'unique_code' => strtolower(md5('normal_' . $productId . '_' . $index)),
// 示例：d32b9be6
```

选择型业态（如按日期/场次售卖）可自定义复合编码，解析约定见 [十三、13.3 复合唯一码解析](#133-复合唯一码解析如日期场次票种)。

## 六、购物车列表

### 6.1 接口

```
GET /api/cart/list?status=1   # 有效商品
GET /api/cart/list?status=0   # 失效商品
GET /api/v2/cart_list         # v2 列表（仅普通商品）
```

### 6.2 核心处理方法

[`StoreCartServices::handleCartList()`](../../../../../backend/app/services/order/StoreCartServices.php)（约 190 行）是购物车数据处理核心：

1. **用户等级与会员价**：读取用户等级折扣（discount）、付费会员状态（vipStatus）
2. **运费不送达校验**：收集商品 `temp_id`，查询开启了不送达的运费模板，判断指定城市是否送达
3. **遍历购物车项**：
   - 设置 `min_qty`（起购量）
   - 兼容 `store_name`（中台商品用 `name` 字段）
   - **配送方式聚合**：从 `delivery_methods` 聚合 `express_shipping`/`store_pickup`/`door_service`，兼容旧版 `logistics` 字段
   - **SKU 信息读取**：优先调 `ProductTypeRegistry::get()->getSkuInfo()`，降级使用关联数据
   - **价格计算**：活动商品用活动价；普通商品调 `CenterProductServices::setLevelPrice()` 计算会员价/VIP 价
   - **有效/无效分组**：`status=0` 无效；按 `shipping_type` 校验配送方式（1=快递不送达无效，2=不支持自提无效）
4. **返回**：`[cartList, valid, invalid]`

### 6.3 配送方式聚合（关键代码）

```php
// 从 delivery_methods 聚合配送能力（与单品直购 confirm 语义一致）
if (empty($item['productInfo']['delivery_methods'])) {
    $dm = $item['productInfo']['delivery_methods'] ?? [];
    if (is_string($dm)) {
        $dm = json_decode($dm, true) ?: [];
    }
    if (!is_array($dm)) {
        // 兼容旧版 logistics 字段的历史数据回填
        $dm = [];
        if (!empty($item['productInfo']['express_delivery'])) $dm[] = 'express';
        if (!empty($item['productInfo']['store_mention'])) $dm[] = 'pickup';
    }
    $item['productInfo']['delivery_methods'] = array_values(array_unique(array_filter($dm)));
}
$item['productInfo']['express_shipping'] = in_array('express', (array)($item['productInfo']['delivery_methods'] ?? []), true);
$item['productInfo']['store_pickup']     = in_array('pickup',  (array)($item['productInfo']['delivery_methods'] ?? []), true);
$item['productInfo']['door_service']     = in_array('door_service', (array)($item['productInfo']['delivery_methods'] ?? []), true);
```

### 6.4 数量修改与库存校验

[`changeUserCartNum()`](../../../../../backend/app/services/order/StoreCartServices.php) 修改数量时：
- 校验起购量：`number < min_qty` 抛异常
- 校验限购：`number > limit_num` 抛异常
- 校验库存：调 `CenterProductServices::getProductStock()`

## 七、与产品类型 Handler 的交互

### 7.1 加购契约：`getCartStrategy()`

产品类型通过 [`ProductTypeHandlerInterface::getCartStrategy()`](../../../../../backend/crmeb/trade/product/ProductTypeHandlerInterface.php) 声明加购契约，基类 `BaseProductTypeHandler` 提供默认实现：

```php
public function getCartStrategy(): array
{
    return [
        'cart_mode'   => 'cart',   // cart=双按钮（加入购物车+立即购买）| direct=单按钮直接下单
        'multi_item'  => true,     // 是否支持多品加购
        'button_text' => '',       // 主按钮自定义文案（如"立即预订"）
    ];
}
```

各产品类型的 cart_mode：

| 产品类型 | cart_mode | cart_button | confirm_window | 说明 |
|---------|-----------|-------------|----------------|------|
| normal（实物） | cart | 1 | editable | 双按钮，规格弹窗可选 |
| virtual（虚拟） | cart | 1 | editable | 双按钮 |
| cardkey（卡密） | cart | 1 | readonly | 双按钮，规格只读高亮 |

### 7.2 cart_button 字段转换

`cart_button` 由 `CenterProductServices::getCartModeByType()` 转换（带静态缓存）：

```php
// direct 模式返回 0（不展示加入购物车按钮），cart 模式返回 1
$storeInfo['cart_button'] = ($storeInfo['cart_strategy']['cart_mode'] ?? 'cart') === 'direct' ? 0 : 1;
```

> 中台 `CenterProductServices` 共 5 处 cart_button 计算（`getDetail`/`buildDefaultPageConfig`/`getProducts`列表/`productDetail`/`getUserList`）需统一走 `getCartModeByType()` 辅助方法，避免带自定义表单的 normal 商品错误隐藏加入购物车按钮。

### 7.3 SKU 信息获取

[`StoreCartServices::getProductSkuInfo()`](../../../../../backend/app/services/order/StoreCartServices.php) 通过 `ProductTypeRegistry` 获取 SKU：

```php
protected function getProductSkuInfo(int $productId, string $unique): ?array
{
    $productServices = app()->make(CenterProductServices::class);
    $product = $productServices->isValidProduct($productId, 'product_type');
    $typeKey = $product ? ($product['product_type'] ?? 'normal') : 'normal';
    $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
    $handler = $registry->get($typeKey);
    $skuInfo = $handler->getSkuInfo($productId, $unique);
    return $skuInfo ?: null;
}
```

### 7.4 活动商品库存校验

[`checkProductStock()`](../../../../../backend/app/services/order/StoreCartServices.php) 通过 `TradeModeRegistry` 分发：

```php
// 通过注册中心按旧版数字 type 解析交易模式处理器
$registry = app()->make(TradeModeRegistry::class);
$handler = $registry->getByLegacyType($type);
$activityId = $seckillId ?: ($bargainId ?: ($combinationId ?: ($advanceId ?: 0)));
// 活动类交易模式：交由各交易模式 Handler 校验活动状态、限购与库存
$checkResult = $handler->checkCartStock($uid, $cartNum, $unique, (int)$activityId, (int)$productId);
```

## 八、合单下单流程

### 8.1 接入点

`CenterOrderController::confirm()` 检测到 `cartId` 参数时走 [`cartConfirm()`](../../../../../backend/app/api/controller/v1/order/CenterOrderController.php) 分支。

### 8.2 流程

1. `cartConfirm($uid)` 读取 `cartId`（逗号分隔的购物车 ID 串或缓存 key）
2. 调 `StoreCartServices::getUserProductCartListV1($uid, $cartId, $new, $addr, $shippingType, $isGift)`
3. 内部根据 `$new` 分支：true 走缓存读取（立即购买），false 走 DB 查询
4. 调 `handleCartList()` 处理价格、配送、有效/无效
5. **产品类型一致性校验**：所有商品 `product_type` 必须一致，否则提示"不同产品类型的商品请分开下单"
6. 调 `StoreOrderComputedServices::getOrderPriceGroup()` 计算价格组（含运费）
7. 调 `OrderCenterUserServices::cacheOrderInfo()` 缓存订单信息，返回 orderKey
8. 下单成功后（`create()` 方法）：若 `isCartMode && !cartNewMode`，调 `removeUserCart()` 清除已下单的购物车记录

### 8.3 产品类型一致性校验（关键代码）

```php
// 获取购物车数据（含价格计算、有效/无效分离、不送达校验）
$cartServices = app()->make(StoreCartServices::class);
$cartGroup = $cartServices->getUserProductCartListV1($uid, $cartId, $new, $addr, $shippingType, $isGift);
$validCartInfo = $cartGroup['valid'] ?? [];
if (empty($validCartInfo)) {
    return app('json')->fail('暂无可购买商品，请重新选择');
}
// 校验所有商品产品类型一致：相同产品类型可合并下单，不同产品类型提示分开下单
$productTypes = array_values(array_unique(array_map(function ($item) {
    return (string)($item['productInfo']['product_type'] ?? 'normal');
}, $validCartInfo)));
if (count($productTypes) > 1) {
    return app('json')->fail('不同产品类型的商品请分开下单');
}
```

### 8.4 下单成功后清除购物车

```php
// 购物车模式：下单成功后清除已下单的购物车记录（缓存/立即购买模式无需清除）
if ($isCartMode && !$cartNewMode && $cartIdsForRemove) {
    try {
        $cartIds = array_filter(array_map('intval', explode(',', $cartIdsForRemove)));
        if ($cartIds) {
            $cartServices = app()->make(StoreCartServices::class);
            $cartServices->removeUserCart($uid, $cartIds);
        }
    } catch (\Throwable $e) {
        // 购物车清除失败不影响下单结果
        \think\facade\Log::warning('下单成功后清除购物车失败: ' . $e->getMessage());
    }
}
```

## 九、事件机制

### 9.1 加入购物车事件

[`setCart()`](../../../../../backend/app/services/order/StoreCartServices.php) 第 306-318 行触发两个事件：

```php
// 自定义事件-加入购物车
event('CustomEventListener', ['user_add_cart', [
    'product_id' => $product_id,
    'uid' => $uid,
    'cart_num' => $cart_num,
    'add_time' => time(),
]]);
// 购物车事件-加入购物车
event('UserCartListener', ['add', [
    'uid' => $uid,
    'product_id' => $product_id,
    'sku_id' => $product_attr_unique,
    'cart_num' => $cart_num,
    'add_time' => time(),
]]);
```

### 9.2 事件监听器

[`app/listener/user/UserCartListener.php`](../../../../../backend/app/listener/user/UserCartListener.php) 支持四种类型：`add`/`delete`/`update`/`clear`。

> **现状**：监听器各 `handle*` 方法目前为空实现（仅有注释模板）。仅 `add` 事件在 `setCart()` 中触发，`delete`/`update`/`clear` 事件**未在任何地方触发**。如需扩展购物车行为（如积分、统计），可在此监听器补充逻辑并在对应操作处补发事件。

### 9.3 事件注册

- `app/event.php`：`'UserCartListener' => [\app\listener\user\UserCartListener::class]`
- `app/services/system/SystemEventServices.php`：`'UserCartListener' => '购物车事件'`

## 十、前端实现

### 10.1 购物车页面（`order_addcart.vue`）

页面结构分四大块：

1. **顶部保证条**：`100% 正品保证 / 精挑细选 / 售后无忧`
2. **导航条**：左侧"购物数量 N"，右侧"管理/取消"切换管理模式
3. **商品列表区**：
   - `valid-list`：有效商品，`checkbox` 多选 + `qty-ctrl` 数量增减；`attrStatus=false` 显示"重选"
   - `invalid-section`：失效商品（可折叠），可清空
4. **底部 footer-bar**：全选 + 选中数量 + 合计金额 + 操作按钮组（管理:收藏/删除；普通:立即下单）
5. **空购物车**：`no-thing.png` + 推荐商品 `recommend`

### 10.2 合单结算校验（前端）

`order_addcart.vue::subOrder()`（当前路径：`frontend/uni-app/src/pages/order_addcart/order_addcart.vue`） 结算前校验 `product_type` 一致：

```js
subOrder() {
  if (this.selectValue.length > 0) {
    // 结算前校验：选中商品的产品类型必须一致，不同产品类型需分开下单
    const valid = this.cartList.valid;
    const types = [];
    for (const item of valid) {
      if (this.selectValue.includes(item.id.toString())) {
        const t = (item.productInfo && item.productInfo.product_type) || "normal";
        if (!types.includes(t)) types.push(t);
      }
    }
    if (types.length > 1) {
      this.$util.Tips({ title: this.$t(`不同产品类型的商品请分开下单`) });
      return;
    }
    uni.navigateTo({
      url: "/pages/center/order/create/index?cartId=" + this.selectValue.join(","),
    });
  } else this.$util.Tips({ title: this.$t(`请选择产品`) });
}
```

> 前端在「选中」和「下单」两处双重校验 `product_type` 一致，避免后端 confirm 阶段才拦截。

### 10.3 角标刷新机制

**双层刷新**：原生 tabBar badge + DIY 自定义底部导航 BaseBadge

1. **Pinia 状态**（`store/modules/indexData.js`）：`cartNum` 为单一数据源
2. **全局 watch**（`App.vue`）：监听 `cartNum` 变化，刷新原生 tabBar badge
3. **各入口主动调用** `getCartCounts` 后调用 store 的 `setCartNum`：首页、详情页、购物车页、分类页
4. **DIY 底部导航**（`pageFooter/index.vue`）：用 `BaseBadge` 显示角标，状态更新后自动响应

```js
// App.vue 全局 watch
cartNum(newCart, b) {
  this.$store.commit("indexData/setCartNum", newCart + "");
  if (newCart > 0) {
    uni.setTabBarBadge({
      index: Number(uni.getStorageSync("FOOTER_ADDCART")) || 2,
      text: newCart + "",
    });
  } else {
    uni.hideTabBarRedDot({ index: Number(uni.getStorageSync("FOOTER_ADDCART")) || 2 });
  }
}
```

### 10.4 详情页加购流程（`goCat` 三分支）

详情页 `goCat(news)`（当前路径：`frontend/uni-app/src/pages/goods_details/index.vue`） 根据契约字段分发三个分支：

| 分支 | 触发条件 | 行为 |
|------|---------|------|
| `isReadOnlyAttr` | 卡密等（`confirm_window=readonly`） | 打开只读弹窗高亮已选值，点"确定"后下单 |
| 默认 editable | normal/virtual（`confirm_window=editable`） | 打开规格弹窗，用户选规格/数量后下单 |

**前端 `news` 参数语义**：
- `news === undefined` → 加入购物车（`new:0`），提示"添加成功"
- `news === true` → 立即购买（`new:1`），跳转下单页

### 10.5 底部按钮栏（`productBottom.vue`）

`productBottom.vue`（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productBottom.vue`） 根据 `cart_strategy.cart_mode` 渲染：

```js
cartMode() { return this.storeInfo.cart_strategy?.cart_mode || 'cart'; },
isDirectMode() { return this.cartMode === 'direct'; },
buyButtonText() {
  const text = this.storeInfo.cart_strategy?.button_text;
  return text ? this.$t(text) : this.$t(`立即购买`);
},
isCartButtonVisible() {
  // direct 模式不显示加入购物车按钮
  if (this.isDirectMode) return false;
  return !!this.storeInfo.cart_button && this.showCartButton;
},
```

按钮渲染 4 种组合：
1. 售罄 + direct：单按钮"已售罄"
2. 售罄 + cart：双按钮"加入购物车（可点）+ 已售罄"
3. 有库存 + direct：单按钮 `buyButtonText`（如"立即预订"）→ emit `goBuy`
4. 有库存 + cart：双按钮"加入购物车 + 立即购买"

### 10.6 规格弹窗（`productWindow.vue`）readonly 支持

`productWindow.vue`（当前路径：`frontend/uni-app/src/components/productWindow/index.vue`） 通过 `readOnlyAttr` prop 支持只读模式：

```html
<view class="itemn"
  :class="[item.index === itemn.attr ? 'on' : '', readOnlyAttr ? 'on-readonly' : '']"
  v-for="(itemn, indexn) in item.attr_value"
  @click="!readOnlyAttr && tapAttr(indexw, indexn)">
  {{ $t(itemn.attr) }}
</view>
```

`readOnlyAttr=true` 时：规格项只读高亮（`on-readonly` 类），不响应点击；保留数量选择器；"确定"按钮通过 `iScart=1` 显示。

### 10.7 多产品类型展示差异

详情页通过 `storeInfo.selection_spec` 和 `storeInfo.cart_strategy` 两个契约字段驱动，前端无 hardcode 判断产品类型：

| 契约字段 | normal | virtual | cardkey |
|---------|--------|---------|---------|
| selection_model | matrix | matrix | none |
| confirm_window | editable | editable | readonly |
| required_selections | [sku] | [sku] | [] |
| cart_unit | item | item | item |
| cart_mode | cart | cart | cart |
| multi_item | true | true | false |
| button_text | (空) | (空) | (空) |
| cart_button | 1 | 1 | 1 |

`display_sections` 中 `key=spec` 的 section 含 `data.blocks`，由 `specPreview.vue`（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productSections/specPreview.vue`） 按 `block.type` 分发到 6 个子渲染器：

| block.type | 子组件 | 用途 |
|-----------|--------|------|
| `chips` | BlockChips | 属性标签 |
| `matrix` | BlockMatrix | 规格矩阵（normal/virtual） |
| `price_list` | BlockPriceList | 起价列表（票种/房型/套餐份量等） |
| `time_slots` | BlockTimeSlots | 时段列表（场次/预约时段等） |
| `calendar` | BlockCalendar | 日历（single 单日 / range 日期范围） |
| `policy` | BlockPolicy | 退改政策 |

### 10.8 `block_data` 透传链路

详情页 `selectedBlockData` → URL `block_data` 参数（encodeURIComponent JSON）→ 下单页 `OrderCreateEngine.created` 解析 → 提交订单时携带，用于选择型业态的日期/场次等选择信息后端落库。

```js
// OrderCreateEngine.vue
created() {
  this.cartId = this.$route?.query?.cartId || '';
  this.news = this.$route?.query?.new === '1' ? 1 : 0;
  this.isCartMode = !!this.cartId;
  var blockDataStr = this.$route?.query?.block_data || '';
  if (blockDataStr) {
    try { this.selectedBlockData = JSON.parse(blockDataStr); } catch (e) { /* ... */ }
  }
}
```

> **经验教训**：详情页 `block_data`（含日期/场次/票种选择数据）必须透传到下单页 create 请求，否则后端无法提取 `visit_date` 等字段，导致 SQL 约束错误（`Column 'visit_date' cannot be null`）。

## 十一、StoreCartServices 核心方法清单

| 方法 | 职责 |
|------|------|
| `setCart()` | 加购/立即购买主入口 |
| `checkProductStock()` | 库存校验（交易模式 Handler 分发） |
| `getProductSkuInfo()` | 通过 `ProductTypeRegistry` 获取 SKU |
| `getUserProductCartListV1()` | 订单确认页核心：获取购物车数据（缓存/DB 分支） |
| `handleCartList()` | 购物车数据处理核心（会员价/配送/SKU/有效无效） |
| `getUserCartList()` | 购物车列表 v1 |
| `getUserCartCount()` | 购物车数量统计（含会员价计算） |
| `changeUserCartNum()` | 修改数量（校验起购/限购/库存） |
| `setCartNum()` | 首页加购（-1设置/0减/1加） |
| `removeUserCart()` | 物理删除购物车项 |
| `resetCart()` | 重选规格 v2（合并同 SKU） |
| `checkLimit()` | 限购校验（单次/永久，永久含已购减退款） |
| `checkVipGoodsBuy()` | 付费会员专属商品校验 |
| `changeStatus()` | 商品下架时置购物车项为无效 |
| `getCartId()` | 雪花算法生成立即购买缓存 key |

## 十二、注意事项

1. **购物车未完全中台化**：控制器和路由仍在 `v1/store/` 路径下，未迁移至 `CenterCartController`。但服务层已对接中台（`CenterProductServices`、`TradeModeRegistry`、`ProductTypeRegistry`）。新增产品类型只需实现 Handler 契约，无需改购物车代码。

2. **合单下单强约束**：购物车页前端「选中」和「下单」双重校验 `product_type` 一致，后端 `cartConfirm()` 再次校验。不同产品类型必须分开下单。

3. **立即购买不入库**：`new=1` 走 Redis 缓存（雪花 key，TTL=3600秒），不写入 `eb_store_cart`，下单后无需清理；`new=0` 写入 DB，下单后 `removeUserCart()` 物理删除。

4. **SKU 唯一码由产品类型自定义**：
   - 常规类型（normal/virtual/cardkey）：`md5('{type}_{productId}_{index}')` 8 位哈希
   - 选择型类型可自定义复合编码（如 `{date}_{sessionId}_{skuId}`），见 13.3
   - 购物车只接收并校验，不生成唯一码

5. **`cart_button` 必须由 `cart_strategy.cart_mode` 决定**：不可用 `custom_form`/`is_virtual`/`presale` 等旧逻辑判断，否则带自定义表单的 normal 商品会错误隐藏加入购物车按钮。`CenterProductServices` 5 处 cart_button 计算需统一走 `getCartModeByType()`。

6. **`block_data` 必须透传**：选择型业态的日期/场次等选择数据，必须从详情页透传到下单页 create 请求，否则后端无法提取对应字段。

7. **`StoreCart` Model 的 `attrInfo` 关联为临时方案**：旧表 `eb_store_product_attr_value` 已弃用，`attrInfo()` 临时指向 `CenterProductModel`（非真实 SKU 表），真实 SKU 信息需通过 `ProductTypeHandler::getSkuInfo()` 获取，`handleCartList()` 中已实现降级逻辑。

8. **遗留代码**：
   - `reChange` 方法未注册路由，已被 `resetCart`（v2）替代
   - `getUserCartList` 引用了不存在的 `discount_id` 字段（历史残留）
   - `UserCartListener` 监听器各 `handle*` 方法为空实现，`delete`/`update`/`clear` 事件未触发

9. **事件用字符串 key**：`event('UserCartListener', ['add', [...]])`，不用类常量。

## 十三、产品类型扩展需要做的事

新产品类型接入购物车，只需实现 Handler 契约，购物车代码零改动：

### 13.1 实现 `getCartStrategy()`

```php
public function getCartStrategy(): array
{
    return [
        'cart_mode'   => 'cart',     // cart=双按钮 | direct=单按钮直接下单
        'multi_item'  => true,       // 是否支持多品加购
        'button_text' => '',         // 主按钮文案（direct 模式生效）
    ];
}
```

### 13.2 实现 `getSkuInfo()`

购物车加购/列表/下单均会调用，必须返回标准格式：

```php
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

### 13.3 复合唯一码解析（如日期+场次+票种）

若产品类型采用复合唯一码（如 `{date}_{sessionId}_{skuId}`），需在 `getSkuInfo()` 中解析：

```php
public function getSkuInfo(int $productId, string $skuUnique): ?array
{
    // 解析复合唯一码 {visit_date}_{sessionId}_{skuId}
    $parts = explode('_', $skuUnique);
    $visitDate = $parts[0] ?? '';
    $sessionId = $parts[1] ?? '0';
    $skuId = $parts[2] ?? '';

    // 按 visit_date + sessionId + skuId 查询 SKU
    $dao = app(\modules\product\ticket\dao\TicketProductSkuDao::class);
    $sku = $dao->getOne([
        'product_id' => $productId,
        'id'         => $skuId,
    ]);
    // ...返回标准格式
}
```

### 13.4 前端契约输出

通过 `getPageConfig('detail')` 或监听器输出 `selection_spec` 和 `cart_strategy` 契约字段，前端 `productBottom` 与 `specPreview` 据此渲染，零硬编码。
