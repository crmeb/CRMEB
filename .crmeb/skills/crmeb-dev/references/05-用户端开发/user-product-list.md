# 用户端产品列表实现

产品中台统一提供商品列表接口，各产品类型通过 `Handler::getListExtend()` 注入类型专属展示字段（`type_info`）。后端中台接口已就绪，前端旧列表页过渡期仍用旧接口，新中台列表页待对接。

## 一、现状说明

| 维度 | 状态 |
|------|------|
| 后端中台接口 | ✅ 已就绪（`GET /api/products`） |
| 前端 API 封装 | ✅ 已就绪（`getCenterProductList`） |
| 前端中台列表页 | ⏳ 待对接（无 `pages/center/product/list/` 页面） |
| 旧列表页 | 🟡 过渡期使用（`pages/goods/goods_list/index.vue` 调旧接口） |
| 卡片样式分发（card_style） | 📋 设计契约已定义，前端分发机制未落地 |

> 文档中提到的 `ProductListEngine.vue`、`pages/center/product/list/index.vue`、`CardStandard.vue` **均不存在**，第五章给出对接指南与参考实现。

## 二、后端接口

### 2.1 接口地址

```
GET /api/products
```

- 控制器：[CenterProductController::productList()](../../../../../backend/app/api/controller/v1/product/CenterProductController.php#L31)
- 服务层：[CenterProductServices::getUserList()](../../../../../backend/app/services/product/CenterProductServices.php#L3791)
- 路由注册：[app/api/route/v1.php:366](../../../../../backend/app/api/route/v1.php#L366)

### 2.2 请求参数

| 参数 | 类型 | 说明 |
|------|------|------|
| keyword | string | 搜索关键词（控制器映射为 `store_name` 查询） |
| cate_id | string/int | 分类ID，多个用逗号分隔，控制器转数组 |
| priceOrder | string | 价格排序 asc/desc |
| salesOrder | string | 销量排序 asc/desc |
| product_type | string | 产品类型筛选（如 ticket），透传到 `eb_product_center.product_type` |
| page | int | 页码 |
| limit | int | 每页数量，默认 30 |

> 控制器 [productList()](../../../../../backend/app/api/controller/v1/product/CenterProductController.php#L31) 通过 `$request->getMore()` 提取参数，`cate_id` 自动转整型数组，`keyword` 映射为 `store_name` 字段名。

### 2.3 响应数据结构

```json
{
  "status": 200,
  "msg": "ok",
  "data": [
    {
      "id": 1,
      "product_type": "cardkey",
      "store_name": "视频会员月卡",
      "image": "https://...",
      "recommend_image": "https://...",
      "price": "199.00",
      "ot_price": "299.00",
      "vip_price": "0.00",
      "sales": 100,
      "stock": 500,
      "spec_type": 0,
      "unit_name": "张",
      "cate_id": 5,
      "sort_order": 0,
      "is_vip": 0,
      "is_virtual": 0,
      "activity": "",
      "presale": 0,
      "custom_form": "",
      "min_qty": 1,
      "label_list": "",
      "store_info": "",
      "star": "5.0",
      "cart_button": 1,
      "type_info": {
        "card_style": "standard",
        "type_label": "商品",
        "unit_name": "件"
      }
    }
  ]
}
```

**字段来源说明**：

| 字段 | 来源 | 说明 |
|------|------|------|
| `store_name` | 中台列 `name` 映射 | `name as store_name`，兼容旧版前端字段名 |
| `ot_price` | 中台列 `original_price` 映射 | `original_price as ot_price` |
| `sales` | 中台列 `sales + virtual_sales` 求和 | `IFNULL(sales,0) + IFNULL(virtual_sales,0)` |
| `recommend_image` | 中台列 `image` 映射 | `image as recommend_image` |
| `is_virtual` | 由 `product_type` 推导 | `isVirtualProduct()` 判断 |
| `cart_button` | 由 `cart_strategy.cart_mode` 决定 | `getCartModeByType()` 计算，direct=0/cart=1 |
| `activity`/`presale`/`custom_form`/`min_qty`/`label_list`/`store_info`/`star` | 默认值补充 | 中台表无对应列，按默认值填充 |
| `type_info` | **控制器附加**（非 getUserList 返回） | 遍历列表调用 `Handler::getListExtend()` |

### 2.4 响应格式

控制器直接 `app('json')->success($list)` 返回数组，前端通过 `res.data` 获取数组，用 `list.length < limit` 判断是否加载完毕。响应**不含 total/count 字段**。

---

## 三、后端实现流程

### 3.1 控制器流程

[CenterProductController::productList()](../../../../../backend/app/api/controller/v1/product/CenterProductController.php#L31) 流程：

```php
public function productList(Request $request, CenterProductServices $services)
{
    // 1. 提取并预处理查询参数
    $where = $request->getMore([
        ['keyword', '', '', 'store_name'],   // keyword → store_name 字段
        ['priceOrder', ''],
        ['salesOrder', ''],
        ['cate_id', ''],
        ['limit', 30],
    ]);
    [$page, $limit] = $services->getPageValue();
    if (!empty($where['limit'])) $limit = (int)$where['limit'];
    // cate_id 逗号分隔转整型数组
    if ($where['cate_id'] !== '') {
        $where['cate_id'] = explode(',', $where['cate_id']);
        foreach ($where['cate_id'] as $keys => &$items) {
            $where['cate_id'][$keys] = (int)$items;
        }
    } else {
        $where['cate_id'] = [];
    }

    // 2. 调用服务层查询（返回字段映射后的数组）
    $list = $services->getUserList($where, $page, $limit);

    // 3. 为每个商品附加 type_info（通过 Handler 动态获取）
    $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
    foreach ($list as &$item) {
        $type = $item['product_type'] ?? 'normal';
        $handler = $registry->get($type);
        $item['type_info'] = $handler->getListExtend((int)($item['id'] ?? 0));
    }
    unset($item);

    // 4. 图片缩略图水印处理
    return app('json')->success(get_thumb_water($list, 'big', ['image', 'recommend_image']));
}
```

### 3.2 服务层 getUserList 字段映射

[CenterProductServices::getUserList()](../../../../../backend/app/services/product/CenterProductServices.php#L3791) 查询 `eb_product_center` 中 `is_show=1, is_del=0` 的商品：

```php
public function getUserList(array $where, int $page, int $limit): array
{
    $where['is_show'] = 1;
    $where['is_del'] = 0;

    // 查询字段：中台列 → 前端期望字段名
    $field = [
        'id',
        'name as store_name',           // 中台 name → 前端 store_name
        'cate_id',
        'image',
        'IFNULL(sales, 0) + IFNULL(virtual_sales, 0) as sales',  // 销量求和
        'price',
        'stock',
        'original_price as ot_price',   // 中台 original_price → 前端 ot_price
        'spec_type',
        'image as recommend_image',
        'unit_name',
        'product_type',
        'sort_order',
        'is_vip',
        'vip_price',
    ];

    $list = $this->dao->getSearchList($where, $page, $limit, $field);

    // 为每项补充前端需要但中台表没有的字段（默认值）
    foreach ($list as &$item) {
        $item['activity'] = '';
        $item['vip_price'] = '0.00';
        $item['is_virtual'] = $this->isVirtualProduct($item['product_type'] ?? 'normal');
        $item['presale'] = 0;
        $item['custom_form'] = '';
        $item['min_qty'] = 1;
        $item['label_list'] = '';
        $item['store_info'] = '';
        $item['star'] = '5.0';
        // cart_button 由 cart_strategy.cart_mode 决定：direct 模式不展示加入购物车按钮
        $item['cart_button'] = $this->getCartModeByType($item['product_type'] ?? 'normal') === 'direct' ? 0 : 1;
    }

    return $list;
}
```

> **type_info 不在 getUserList 返回**：服务层只负责字段映射与默认值补充，`type_info` 由控制器遍历列表调用 `Handler::getListExtend()` 附加。这样服务层不依赖 Handler，控制器承担扩展点调度。

### 3.3 cart_button 计算铁律

`cart_button` 字段必须由 `getCartModeByType($productType)` 返回的 `cart_mode` 决定（direct=0/cart=1），不可再用 custom_form/is_virtual/presale 旧逻辑判断。CenterProductServices 共 5 处 cart_button 计算（getDetail / buildDefaultPageConfig / getProducts 列表 / productDetail / getUserList）需统一走该方法（带静态缓存），否则带自定义表单的 normal 商品会错误隐藏加入购物车按钮。

---

## 四、getListExtend 扩展契约

### 4.1 接口定义

[ProductTypeHandlerInterface::getListExtend()](../../../../../backend/crmeb/trade/product/ProductTypeHandlerInterface.php#L140) 声明：

```php
/**
 * 列表扩展数据
 * 子类覆盖：返回卡片样式、类型标签、单位名等信息
 * @param int $productId 商品ID（用于查询商品专属展示数据）
 * @return array {card_style, type_label, unit_name, ...}
 */
public function getListExtend(int $productId): array;
```

### 4.2 返回字段约定

| 字段 | 类型 | 说明 |
|------|------|------|
| `card_style` | string | 卡片样式枚举：`standard`（标准）等，前端按此分发卡片组件 |
| `type_label` | string | 类型标签，显示在商品卡片上（如"实物商品"/"网盘产品"） |
| `unit_name` | string | 单位名（如"件"/"张"/"间"） |

> `card_style` 是设计契约字段，用于前端按产品类型分发不同卡片样式。当前所有已实现 Handler 均返回 `standard`，前端分发机制未落地（详见 5.4）。

### 4.3 基类默认实现

[BaseProductTypeHandler::getListExtend()](../../../../../backend/crmeb/trade/product/BaseProductTypeHandler.php#L107) 提供默认值，未覆盖的 Handler 使用此默认：

```php
public function getListExtend(int $productId): array
{
    return [
        'card_style' => 'standard',
        'type_label' => '商品',
        'unit_name'  => '件',
    ];
}
```

### 4.4 各产品类型实现清单

| 产品类型 | Handler | card_style | type_label | unit_name | 状态 |
|---------|---------|-----------|-----------|-----------|------|
| normal（实物） | [normal/Handler.php:569](../../../../../backend/modules/product/normal/Handler.php#L569) | standard | 实物商品 | 件 | ✅ 已实现 |
| virtual（网盘） | [virtual/Handler.php:518](../../../../../backend/modules/product/virtual/Handler.php#L518) | standard | 网盘产品 | 件 | ✅ 已实现 |
| cardkey（卡密） | — | standard | 商品 | 件 | 🟡 用基类默认 |

> cardkey 未覆盖 `getListExtend()`，使用基类默认值（type_label="商品"，unit_name="件"）。新增产品类型若需自定义标签，应在 Handler 中覆盖此方法。

### 4.5 新增产品类型实现指南

```php
// modules/product/{type}/Handler.php
public function getListExtend(int $productId): array
{
    return [
        'card_style' => 'standard',   // 卡片样式：standard / ticket / hotel 等
        'type_label' => '票务',        // 类型标签，显示在商品卡片上（多语言需用 lang() 包裹）
        'unit_name'  => '张',          // 单位名
    ];
}
```

**约束**：
1. 展示文案（`type_label`/`unit_name`）应通过多语言服务处理，不能硬编码中文
2. 若需查询商品专属展示数据（如场次摘要），可在方法内 `app()->make(XxxDao::class)` 查询
3. 方法应轻量，避免逐商品查询导致列表接口性能问题（N+1 问题）

---

## 五、前端对接指南

### 5.1 API 封装

product-center.js（当前路径：`frontend/uni-app/src/api/product-center.js`） 已封装中台列表接口：

```javascript
import request from '@/utils/request.js';

const BASE = '';

/**
 * 获取商品列表
 * @param {Object} params { page, limit, keyword, priceOrder, salesOrder, cate_id, product_type }
 */
export function getCenterProductList(params) {
  return request.get(`${BASE}/products`, params);
}
```

> 当前前端**无页面消费此 API**。新中台列表页需创建 `pages/center/product/list/index.vue` 并调用 `getCenterProductList`。

### 5.2 旧列表页现状

pages/goods/goods_list/index.vue（当前路径：`frontend/uni-app/src/pages/goods/goods_list/index.vue`） 过渡期仍使用旧接口：

```javascript
// 旧列表页从 @/api/store.js 导入 getProductslist（旧接口）
import { getProductslist } from '@/api/store.js';
// 调用旧接口（非中台 /products）
getProductslist(that.where).then(res => { ... });
```

**迁移建议**：新页面优先对接中台 `getCenterProductList`；旧 `goods_list` 视迁移进度逐步替换，过渡期两者并存。

### 5.3 中台列表页对接参考实现

新中台列表页 `pages/center/product/list/index.vue` 参考实现：

```vue
<template>
  <view class="product-list">
    <empty-page v-if="products.length === 0 && !loading" text="暂无商品" />
    <view class="product-card" v-for="product in products" :key="product.id" @click="goDetail(product)">
      <image class="product-image" :src="product.image" mode="aspectFill" />
      <view class="product-info">
        <view class="product-name">{{ product.store_name }}</view>
        <view class="product-type" v-if="product.type_info && product.type_info.type_label">
          {{ product.type_info.type_label }}
        </view>
        <view class="product-price">
          <text class="price">¥{{ product.price }}</text>
          <text class="ot-price" v-if="product.ot_price && product.ot_price !== product.price">¥{{ product.ot_price }}</text>
          <text class="sales">已售{{ product.sales }}</text>
        </view>
      </view>
    </view>
    <uni-load-more :status="loading ? 'loading' : (finished ? 'noMore' : '')" />
  </view>
</template>

<script>
import { getCenterProductList } from '@/api/product-center.js';

export default {
  data() {
    return {
      products: [],
      page: 1,
      limit: 20,
      loading: false,
      finished: false,
      where: {
        keyword: '',
        cate_id: '',
        product_type: '',
        priceOrder: '',
        salesOrder: '',
      },
    };
  },
  onLoad(options) {
    // 支持从分类页/搜索页带入查询条件
    if (options.cate_id) this.where.cate_id = options.cate_id;
    if (options.keyword) this.where.keyword = options.keyword;
    if (options.product_type) this.where.product_type = options.product_type;
    this.loadProducts();
  },
  onReachBottom() {
    // 触底加载下一页
    this.loadProducts();
  },
  methods: {
    async loadProducts() {
      if (this.loading || this.finished) return;
      this.loading = true;
      try {
        const res = await getCenterProductList({
          ...this.where,
          page: this.page,
          limit: this.limit,
        });
        const list = res.data || [];
        // 响应无 total/count，通过 list.length < limit 判断结束
        if (list.length < this.limit) this.finished = true;
        this.products = this.page === 1 ? list : [...this.products, ...list];
        this.page++;
      } catch (e) {
        uni.showToast({ title: '加载失败', icon: 'none' });
      } finally {
        this.loading = false;
      }
    },
    goDetail(product) {
      uni.navigateTo({
        url: `/pages/goods_details/index?id=${product.id}`,
      });
    },
  },
};
</script>
```

**关键点**：
- 响应为数组（非 `{count, data}` 分页结构），通过 `list.length < limit` 判断是否加载完毕
- 字段用 `store_name`（非 `name`）、`ot_price`（非 `original_price`），与后端字段映射对齐
- `type_info` 由后端控制器附加，直接读取 `product.type_info.type_label` 即可
- 详情页跳转到 `/pages/goods_details/index`（DIY 详情页）

### 5.4 卡片样式分发机制（设计契约，前端未落地）

`type_info.card_style` 设计用于按产品类型分发不同卡片组件，当前所有 Handler 返回 `standard`，前端分发机制未实现。若未来需要某产品类型专属卡片样式：

**步骤 1**：Handler 覆盖 `getListExtend()` 返回自定义 `card_style`：

```php
// modules/product/{type}/Handler.php
public function getListExtend(int $productId): array
{
    return [
        'card_style' => 'ticket',
        'type_label' => '票务',
        'unit_name'  => '张',
    ];
}
```

**步骤 2**：前端列表页按 `card_style` 动态选择卡片组件：

```vue
<component
  :is="getCardComponent(product.type_info.card_style)"
  v-for="product in products"
  :key="product.id"
  :product="product"
/>
```

```javascript
import CardStandard from '@/components/product/CardStandard.vue';
import CardTicket from '@/components/product/CardTicket.vue';

export default {
  components: { CardStandard, CardTicket },
  methods: {
    getCardComponent(style) {
      const map = { standard: 'CardStandard', ticket: 'CardTicket' };
      return map[style] || 'CardStandard';
    },
  },
}
```

**步骤 3**：在实际使用该卡片的页面或子包组件目录下创建对应组件 `CardTicket.vue`，并按当前页面的显式导入方式注册。

> 当前未落地，新增产品类型默认用 `standard` 标准卡片即可，无需创建专属卡片组件。

---

## 六、其他列表相关接口

### 6.1 管理后台选商品（list）

[CenterProductController::list()](../../../../../backend/app/api/controller/v1/product/CenterProductController.php#L107) 用于管理后台选商品场景：

```php
public function list(CenterProductServices $services)
{
    $where = [
        'product_type' => request()->param('product_type', ''),
        'keyword'      => request()->param('keyword', ''),
        'cate_id'      => request()->param('cate_id', 0),
    ];
    $page = (int) request()->param('page', 1);
    $limit = (int) request()->param('limit', 10);
    return app('json')->success($services->getList($where, $page, $limit));
}
```

### 6.2 商品搜索（search）

[CenterProductController::search()](../../../../../backend/app/api/controller/v1/product/CenterProductController.php#L122) 提供关键词搜索：

```php
public function search(CenterProductServices $services)
{
    $keyword = request()->param('keyword', '');
    $page = (int) request()->param('page', 1);
    $limit = (int) request()->param('limit', 10);
    return app('json')->success($services->search($keyword, $page, $limit));
}
```

### 6.3 产品类型列表（typeList）

[CenterProductController::typeList()](../../../../../backend/app/api/controller/v1/product/CenterProductController.php#L133) 返回所有已注册产品类型，供前端筛选/后台创建商品选择：

```php
public function typeList(CenterProductServices $services)
{
    return app('json')->success($services->getTypeList());
}
```

服务层 `getTypeList()` 调用 `ProductTypeRegistry::allInfo()` 动态读取已注册产品类型，前端调用：

```javascript
import { getProductTypeList } from '@/api/product-center.js';
// GET /product/type/list
const res = await getProductTypeList();
```

### 6.4 商品实时价格（realPrice）

[CenterProductController::realPrice()](../../../../../backend/app/api/controller/v1/product/CenterProductController.php#L95) 用于详情页 SKU 价格查询、会员等级折扣、VIP 会员价、优惠券抵扣等完整逻辑，全部下沉至 `CenterProductServices::realPrice()`。

---

## 七、注意事项

1. **接口路径**：用户端列表接口为 `GET /api/products`（非 `/api/center/product/list`），前端 API 封装在 product-center.js（当前路径：`frontend/uni-app/src/api/product-center.js`） 的 `getCenterProductList`

2. **字段映射**：中台表 `eb_product_center` 列名与前端期望字段名不同，`getUserList()` 做了映射：
   - `name` → `store_name`
   - `original_price` → `ot_price`
   - `sales + virtual_sales` → `sales`
   - `image` → `recommend_image`
   前端消费时必须用映射后的字段名

3. **type_info 由控制器附加**：`getUserList()` 不返回 `type_info`，控制器遍历列表调用 `Handler::getListExtend()` 附加。新增产品类型只需在 Handler 覆盖 `getListExtend()`，无需改中台/控制器

4. **响应无分页字段**：响应直接返回数组，不含 `total`/`count`，前端通过 `list.length < limit` 判断是否加载完毕

5. **cart_button 计算铁律**：必须由 `getCartModeByType($productType)` 返回的 `cart_mode` 决定（direct=0/cart=1），不可再用 custom_form/is_virtual/presale 旧逻辑判断

6. **图片处理**：控制器返回前调用 `get_thumb_water($list, 'big', ['image', 'recommend_image'])` 处理缩略图与水印

7. **N+1 性能风险**：`getListExtend()` 在控制器层逐商品调用，若 Handler 实现内查询数据库（如场次摘要），列表场景会有 N+1 问题。建议 `getListExtend()` 保持轻量，仅返回静态标签数据；需要查询专属数据的，考虑批量预加载或缓存

8. **product_type 筛选**：前端传 `product_type` 参数时，会作为查询条件透传到 `eb_product_center.product_type`，`getSearchList()` 自动过滤

9. **多语言**：`getListExtend()` 返回的展示文案（`type_label`/`unit_name`）应通过多语言服务处理，不能硬编码中文

10. **旧列表页过渡**：goods_list/index.vue（当前路径：`frontend/uni-app/src/pages/goods/goods_list/index.vue`） 仍用 `@/api/store.js` 的 `getProductslist` 旧接口，新产品类型优先对接中台列表接口；旧页面视迁移进度逐步替换

11. **卡片样式分发未落地**：`type_info.card_style` 是设计契约字段，当前所有 Handler 返回 `standard`，前端未实现按 `card_style` 分发卡片组件的机制。需要时按 5.4 节步骤落地

---

## 变更记录

| 日期 | 版本 | 变更 |
|------|------|------|
| 2026-08-06 | v2.0 | 全面重写对齐实际实现：①接口路径修正为 `GET /api/products`（非 `/api/center/product/list`）；②响应字段对齐实际映射（store_name/ot_price/sales 求和）；③说明 type_info 由控制器附加（非 getUserList 返回）；④补充 getUserList 字段映射与默认值补充逻辑；⑤getListExtend 实现清单对齐（normal/virtual/hotel 已实现，ticket/cardkey 用基类默认）；⑥移除虚构的 ProductListEngine.vue/pages/center/product/list/index.vue/CardStandard.vue，改为前端对接指南与参考实现；⑦新增现状说明表、其他列表接口（list/search/typeList/realPrice）、N+1 性能风险提示 |
| — | v1.0 | 初版（含虚构的前端引擎/页面/卡片组件，接口路径与字段名有误） |
