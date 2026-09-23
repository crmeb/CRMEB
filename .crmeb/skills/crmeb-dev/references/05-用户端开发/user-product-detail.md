# 用户端产品详情接口（DIY + display_sections 数据驱动架构）

## 一、架构概述

产品详情页基于 **DIY 装修系统 + display_sections 数据驱动**，详情页**只读展示**，不采集下单数据（数量/表单/地址归下单页 `OrderCreateEngine`）。新增产品类型时，前端核心**零改动**，由后端监听器输出 `display_sections` 的 blocks 组合即可。

```
接口返回（扁平结构 + display_sections）
├── storeInfo              商品基础信息（旧版兼容字段，扁平）
├── productAttr/productValue  规格信息（实物/虚拟多规格）
├── display_sections        展示块数组（各产品类型/营销监听器注入，按 order 排序）
│   └── section.component ∈ {spec_preview, info_card, store_card, time_card}
└── selection_spec / cart_strategy / cart_button   选择/加购契约（前端按此渲染底部栏）
```

### 核心设计

| 设计点 | 说明 |
|--------|------|
| **DIY 布局 + 数据驱动分离** | `theme.detail_data` 存布局/样式配置，`display_sections` 存业务数据，两者解耦 |
| **三层组件库** | 通用层 DIY 组件（不动）+ 4 个通用超级组件（数据驱动）+ 6 个封闭 block 子渲染器 |
| **售卖形态统一为 blocks** | spec_preview 通过 `data.blocks` 数组表达所有业态售卖形态，不再用 spec_type 分发 |
| **详情页只读** | 详情页不下单采集，底部栏"立即购买"直接跳转下单页；多规格保留选规格看价/图/库存交互但不采集 |
| **visible 后端预算** | section.visible 为布尔值，由后端监听器根据数据计算，前端不做表达式求值 |
| **事件注入** | 中台触发 `ProductDetailExtend` 事件，监听器向 `extensions` 与 `display_sections` 注入数据 |
| **order 区间约定** | 产品类型 10-99 / 交易模式 100-199 / 营销 200-299 / 通用 300+，防多插件冲突 |
| **block 封闭有限** | 6 种 block 构件覆盖所有常见业态，新增产品类型无需新增 block 类型 |

### 文件清单

| 角色 | 路径 |
|------|------|
| 用户端详情页 | goods_details/index.vue（当前路径：`frontend/uni-app/src/pages/goods_details/index.vue`） |
| DIY 渲染器 | pageDesign.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/pageDesign.vue`） |
| 超级组件目录 | `frontend/uni-app/src/subpackage/diyComponents/productSections/` |
| block 子渲染器 | `frontend/uni-app/src/subpackage/diyComponents/productSections/blocks/` |
| 后端服务 | [CenterProductServices.php](../../../../../backend/app/services/product/CenterProductServices.php) |
| 后端控制器 | [CenterProductController.php](../../../../../backend/app/api/controller/v1/product/CenterProductController.php) |
| 前端 API 调用 | product-center.js（当前路径：`frontend/uni-app/src/api/product-center.js`） |

> **组件目录说明**：实际使用的 4 个超级组件与 6 个 block 子渲染器位于 `frontend/uni-app/src/subpackage/diyComponents/productSections/`，`pageDesign.vue` 通过同目录相对路径导入。

---

## 二、接口规范

### 2.1 接口地址

```
GET /api/product/detail/{id}
```

### 2.2 响应结构（扁平 + display_sections）

```json
{
  "status": 200,
  "msg": "ok",
  "data": {
    "uid": 1,
    "storeInfo": {
      "id": 1,
      "store_name": "...",
      "price": "99.00",
      "ot_price": "199.00",
      "stock": 100,
      "slider_image": ["https://..."],
      "description": "...",
      "product_type": "normal",
      "cart_button": 1,
      "selection_spec": { "...": "选择契约" },
      "cart_strategy": { "cart_mode": "cart" }
    },
    "productAttr": [...],
    "productValue": {...},
    "display_sections": [
      {
        "key": "spec",
        "component": "spec_preview",
        "visible": true,
        "order": 10,
        "data": { "blocks": [/* 6 种 block 构件组合 */] }
      },
      {
        "key": "fulfillment",
        "component": "info_card",
        "title": "配送",
        "visible": true,
        "order": 20,
        "data": { "items": [/* 键值对列表 */] }
      }
    ]
  }
}
```

> **兼容旧扁平结构**：接口直接返回 `storeInfo`/`productAttr`/`productValue` 等旧版字段，旧前端可直接消费；`display_sections` 为增量叠加，新前端按需消费。

### 2.3 前端 API 调用

product-center.js（当前路径：`frontend/uni-app/src/api/product-center.js`） 封装三个接口：

```javascript
// 获取商品详情
export function getCenterProductDetail(id) {
  return request.get(`${BASE}/product/detail/${id}`);
}
// 获取商品列表
export function getCenterProductList(params) {
  return request.get(`${BASE}/products`, params);
}
// 获取产品类型列表
export function getProductTypeList() {
  return request.get(`${BASE}/product/type/list`);
}
```

> **注意（路由修正）**：产品类型列表的真实后端路由是 `GET /api/center/product/type_list`（[app/api/route/center.php:27](../../../../../backend/app/api/route/center.php#L27) → `CenterProductController::typeList()`），**不存在** `GET /api/product/type/list`。当前 `frontend/uni-app/src/api/product-center.js` 的 `getProductTypeList()` 仍调旧路由 `/product/type/list`，属于**前端代码 bug**（本次仅文档修正，未改代码），调用该接口会 404，需修改为 `center/product/type_list` 后即可使用。

---

## 三、返回数据结构

### 3.1 storeInfo 扁平字段（旧版兼容）

[CenterProductServices::getDetail()](../../../../../backend/app/services/product/CenterProductServices.php#L485) 从 `eb_product_center` 读取基础数据，按旧版字段名映射构建 `storeInfo`：

| 字段分类 | 关键字段 | 说明 |
|---------|---------|------|
| ID/图片 | id, image, slider_image, video_link | 轮播图/视频 |
| 基本信息 | store_name←name, store_info←intro, keyword, unit_name | 列名映射到旧版字段名 |
| 价格 | price, vip_price, ot_price←original_price, cost | 价格体系 |
| 库存/销量 | stock, sales(+virtual_sales), browse←views, ficti←virtual_sales | |
| 状态位 | is_show, is_hot, is_benefit, is_best, is_new, is_virtual, is_postage, is_del, is_sub, is_vip, is_gift, is_limit, limit_num, min_qty | |
| 规格/类型 | spec_type, activity, spu, label_id, product_type | |
| 预售 | presale, presale_start_time, presale_end_time, presale_day, presale_pay_status | presale_pay_status 由中台计算 |
| 物流 | postage, freight, logistics, temp_id | |
| 扩展字段 | custom_form, params_list, protection_list, label_list | 由 `ProductDetailExtend` 事件注入 |
| 选择/加购契约 | selection_spec, cart_strategy, cart_button | 由 Handler 提供 |

### 3.2 选择与加购契约

详情页底部栏按以下契约渲染，由产品类型 Handler 提供：

```php
// CenterProductServices::getDetail() 末尾注入
$storeInfo['selection_spec'] = $handler->getSelectionSpec();   // 选择契约（前端渲染选择区）
$storeInfo['cart_strategy']  = $handler->getCartStrategy();    // 加购策略
// cart_button 由 cart_strategy.cart_mode 决定：direct 模式不展示加入购物车按钮
$storeInfo['cart_button'] = ($storeInfo['cart_strategy']['cart_mode'] ?? 'cart') === 'direct' ? 0 : 1;
```

> **cart_button 计算铁律**：必须由 `cart_strategy.cart_mode` 决定（direct=0/cart=1），不可再用 custom_form/is_virtual/presale 旧逻辑判断，否则带自定义表单的 normal 商品会错误隐藏加入购物车按钮。

### 3.3 display_sections 数组

由中台 `getDetail()` 创建空容器，`ProductDetailExtend` 事件监听器写入，最终按 `order` 升序排序下发：

```php
// CenterProductServices::getDetail() L731-753
$extensions = [];
$displaySections = [];
$eventData = [
    'extensions'        => &$extensions,
    'display_sections'  => &$displaySections,
    'product_type'      => $productType,
    'activity_type'     => 'normal',
    'product_id'        => $id,
    'uid'               => $uid,
    'handler'           => $handler,
    'info'              => $info,
    'specs'             => $specs,
];
event('ProductDetailExtend', $eventData);

// display_sections 按 order 升序排序
usort($displaySections, function ($a, $b) {
    return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
});
```

---

## 四、display_sections 数据契约

### 4.1 section 字段规范

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `key` | string | 是 | section 唯一标识，用于前端 v-for key |
| `component` | string | 是 | 组件类型枚举：`spec_preview` / `info_card` / `store_card` / `time_card` |
| `title` | string | 否 | 区块标题，空则不显示标题栏 |
| `visible` | bool | 是 | 是否可见，前端按此过滤 |
| `order` | int | 是 | 排序权重，升序排列（遵守 4.5 order 区间约定） |
| `data` | object | 是 | 组件数据，结构由 component 决定 |

### 4.2 component 枚举（4 种）

| component | 用途 | data 结构 | 承接组件 |
|-----------|------|-----------|---------|
| `spec_preview` | 售卖形态展示位（规格/票种/房型等） | `{blocks: [...]}` 6 种 block 组合 | specPreview.vue |
| `info_card` | 万能信息卡（履约/须知/规则键值对） | `{items: [{label, value, icon, action?}]}` | infoCard.vue |
| `store_card` | 门店/场馆/酒店地址卡 | `{name, address, lat, lng, store_type, distance?, phone?}` | storeCard.vue |
| `time_card` | 时间线（预售期/有效期/场次时间） | `{periods: [{label, time, status}]}` | timeCard.vue |

### 4.3 spec_preview.data.blocks（6 种封闭 block 构件）

售卖形态位通过 `data.blocks` 数组统一表达所有业态。blocks 是 6 种通用 block 构件的有序组合，前端 specPreview 按 `block.type` 渲染对应子组件，**新增产品类型前端零改动**。

| block.type | 名称 | 结构 | 覆盖场景 |
|-----------|------|------|---------|
| `chips` | 属性标签 | `{items:[{label,value}]}` | 卡密面值、会员卡面值、实物当前选中规格 |
| `matrix` | 规格矩阵 | `{attrs, skus, current_sku?}` | 实物/虚拟多规格完整预览（复用现有只读逻辑） |
| `price_list` | 起价列表 | `{items:[{name,price_from,tags,image?,stock_status?,stock_count?}]}` | 门票票种、酒店房型、套餐份量 |
| `time_slots` | 时段列表 | `{items:[{name,time,stock_status?,stock_count?}]}` | 门票场次、预约时段、课程课时 |
| `calendar` | 日历概览 | `{config, dates:[{date,status,min_price,stock_level}]}` | 门票/酒店/课程/租赁日历 |
| `policy` | 政策键值对 | `{items:[{label,value}]}` | 酒店入住政策、门票退改、限购规则、库存说明 |

#### block 数据结构示例

**chips（属性标签）**：
```json
{
  "type": "chips",
  "title": "当前规格",
  "items": [
    {"label": "面值", "value": "¥100"},
    {"label": "有效期", "value": "30天"},
    {"label": "发放方式", "value": "自动发放"}
  ]
}
```

**matrix（规格矩阵，复用 [NormalProductHandler::getDetailSpecs()](../../../../../backend/modules/product/normal/Handler.php#L455) 输出）**：
```json
{
  "type": "matrix",
  "title": "规格预览",
  "attrs": [{"name": "颜色", "values": ["黑", "白"]}],
  "skus": [{"suk": "黑,S", "price": 99, "stock": 10, "image": ""}],
  "current_sku": {"颜色": "黑色"}
}
```
> BlockMatrix 封装现有多规格选择交互（选规格看价/图/库存），**不采集下单数据，不动现有体验**。

**price_list（起价列表）**：
```json
{
  "type": "price_list",
  "title": "房型",
  "items": [
    {"name": "大床房", "price_from": 299, "tags": ["双早", "免费取消"], "stock_status": "sufficient", "stock_count": 5},
    {"name": "套房", "price_from": 599, "tags": [], "stock_status": "sold_out", "stock_count": 0}
  ]
}
```

**time_slots（时段列表）**：
```json
{
  "type": "time_slots",
  "title": "场次",
  "items": [
    {"name": "上午场", "time": "09:00-12:00", "stock_status": "sufficient", "stock_count": 50}
  ]
}
```

**calendar（日历概览）**：
```json
{
  "type": "calendar",
  "title": "选择日期",
  "config": {"mode": "single", "show_stock": true},
  "dates": [
    {"date": "2026-08-15", "status": "sufficient", "min_price": 99, "stock_level": "high"},
    {"date": "2026-08-16", "status": "tight", "min_price": 129, "stock_level": "low"},
    {"date": "2026-08-17", "status": "sold_out", "min_price": null, "stock_level": "none"}
  ]
}
```
| 字段 | 说明 |
|------|------|
| `config.mode` | `single`单日选择（门票）/ `range`日期范围（酒店入住） |
| `status` | 该日整体可购性（取该日所有票种/房型/场次中最宽松状态）：`sufficient`/`tight`/`sold_out` |
| `min_price` | 该日所有组合中最低价，售罄时为 null |
| `stock_level` | 该日整体库存量级：`high`/`medium`/`low`/`none` |

**policy（政策键值对，含限购规则）**：
```json
{
  "type": "policy",
  "title": "购买须知",
  "items": [
    {"label": "入住", "value": "14:00后"},
    {"label": "限购", "value": "每账号每日限购5张"},
    {"label": "库存说明", "value": "库存实时变化，以下单为准"}
  ]
}
```

### 4.4 各业态 blocks 组合对照

| 业态 | blocks 组合 | spec section 其他配套 section |
|------|-----------|------|
| normal/virtual（实物/虚拟） | `chips` + `matrix` | info_card（配送/履约）+ store_card（自提门店）+ time_card（预售期） |
| cardkey/coupon（卡密/卡券） | `chips` | info_card（卡密发放/使用说明）+ time_card（有效期）+ store_card（可用门店） |
| 餐饮套餐 | `price_list` | 套餐明细走 info_card |
| 预约服务 | `price_list` + `time_slots` | — |
| 会员卡 | `chips` | 权益走 info_card |
| 课程 | `chips` + `calendar` | — |
| 租赁 | `matrix` + `time_slots` + `policy` | — |

### 4.5 order 排序区间约定（防多插件冲突）

一个商品详情页可能同时有产品类型监听器、交易模式监听器、营销插件监听器向 `display_sections` 写入 section。为防止不同插件的 `order` 撞车，约定以下区间分配：

| 来源 | order 区间 | 示例 section |
|------|-----------|------------|
| 产品类型固有展示 | 10 - 99 | spec / fulfillment / store / validity |
| 交易模式进度 | 100 - 199 | seckill_countdown / group_progress / bargain_progress |
| 营销玩法 | 200 - 299 | coupon_entry / promotion_banner / live_entry |
| 通用展示（评价/图文/推荐等） | 300+ | 由 DIY 通用组件承载，一般不进 display_sections |

**规则**：
1. 各监听器只能在自己的区间内分配 `order`，不得越界
2. 同一区间内多个 section 按业务优先级细分（如产品类型内 spec=10、fulfillment=20、store=30）
3. 中台 `getDetail()` 合并后按 `order` 升序统一排序
4. 区间保留 buffer，便于未来新增

---

## 五、前端组件库（三层架构）

### 5.1 三层组件库总览

```
【第1层：通用层 DIY 组件 - 已有，完全不动】
├─ productInfo / productService / productDesc / reviews / customComponent
├─ 轮播 / 评价 / 图文详情 / 推荐 / 优惠券 / 活动 / 分销悬浮框
└─ 所有产品类型通用，运营 DIY 拖拽编排

【第2层：通用超级组件 - 4 个独立 DIY 组件，数据驱动】
├─ infoCard    万能信息卡（履约/须知/规则等键值对列表）
├─ storeCard   门店/场馆/酒店地址卡（带地图入口）
├─ timeCard    时间线（预售期/有效期/场次时间）
└─ specPreview 售卖形态展示位（运营拖一次，自动按 blocks 渲染）

【第3层：block 子渲染器 - 6 个封闭有限组件，写一次永不再改】
└─ blocks/
   ├─ BlockChips.vue      属性标签
   ├─ BlockMatrix.vue     规格矩阵（封装现有多规格选择交互，不采集下单数据）
   ├─ BlockPriceList.vue  起价列表
   ├─ BlockTimeSlots.vue  时段列表
   ├─ BlockCalendar.vue   日历概览
   └─ BlockPolicy.vue     政策键值对
```

### 5.2 通用超级组件（4 个）

位于 `frontend/uni-app/src/subpackage/diyComponents/productSections/`，由 `pageDesign.vue` 通过同目录相对路径导入：

| 组件 | 文件 | props | 渲染逻辑 |
|------|------|-------|---------|
| infoCard | infoCard.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productSections/infoCard.vue`） | `dataConfig, sections` | 遍历 `sections`（component=info_card 的所有项），渲染 title + items 键值对列表 |
| storeCard | storeCard.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productSections/storeCard.vue`） | `dataConfig, sections` | 渲染门店/场馆/酒店地址卡，含地图入口 |
| timeCard | timeCard.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productSections/timeCard.vue`） | `dataConfig, sections` | 渲染时间线（预售期/有效期/场次时间） |
| specPreview | specPreview.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productSections/specPreview.vue`） | `dataConfig, section` | 容器：遍历 `section.data.blocks`，按 `block.type` 分发到 6 个子渲染器 |

**specPreview 分发逻辑**：
```javascript
const blockComponentMap = {
  chips:      BlockChips,
  matrix:     BlockMatrix,
  price_list: BlockPriceList,
  time_slots: BlockTimeSlots,
  calendar:   BlockCalendar,
  policy:     BlockPolicy,
};
// 模板：<component :is="blockComponentMap[block.type]" :block="block" />
```

### 5.3 block 子渲染器（6 个，封闭有限）

位于 `frontend/uni-app/src/subpackage/diyComponents/productSections/blocks/`：

| 子渲染器 | 渲染职责 |
|---------|---------|
| BlockChips.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productSections/blocks/BlockChips.vue`） | 标签芯片横排展示 `{label: value}` |
| BlockMatrix.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productSections/blocks/BlockMatrix.vue`） | 规格矩阵，保留现有规格选择交互（选规格看价/图/库存），**不采集下单数据** |
| BlockPriceList.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productSections/blocks/BlockPriceList.vue`） | 起价列表，每项 `名称 ¥起价 标签 库存状态` |
| BlockTimeSlots.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productSections/blocks/BlockTimeSlots.vue`） | 时段卡片列表，每项 `名称 时段 库存状态` |
| BlockCalendar.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productSections/blocks/BlockCalendar.vue`） | 月历组件，按日期高亮库存状态与价格 |
| BlockPolicy.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/productSections/blocks/BlockPolicy.vue`） | 键值对列表（同 InfoCard 视觉，但语义独立） |

> **不再需要 register.js 注册机制**。6 个 block 子渲染器是封闭有限的，覆盖所有常见业态。新增产品类型时后端输出对应 blocks 组合即可，前端 specPreview 容器与 6 个子渲染器**全部零改动**。

---

## 六、渲染流程

### 6.1 整体调用链

```
goods_details/index.vue
  ├─ getCenterProductDetail(id) 取 res.data
  ├─ this.displaySections = res.data.display_sections || []
  └─ <PageDesign :displaySections="displaySections" ... />

pageDesign.vue
  ├─ 按 detail_data.value 数组顺序遍历 item
  ├─ 按 item.name 匹配 v-else-if 分支（infoCard/storeCard/timeCard/specPreview/...）
  ├─ displaySectionsByType(type) 过滤 display_sections 中对应 component 的所有 section
  └─ displaySectionByKey(key) 取指定 key 的单个 section（specPreview 用）
```

### 6.2 goods_details 透传 display_sections

goods_details/index.vue:1154-1160（当前路径：`frontend/uni-app/src/pages/goods_details/index.vue`） 从接口取数据并透传：

```javascript
// 透传后端 display_sections（产品类型/营销监听器输出的展示块）
// 驱动 PageDesign 中 infoCard/storeCard/timeCard/specPreview 4 个 DIY 组件
// 本地调试：useMockDisplaySections=true 时强制用 mock 数据，验证渲染效果
const sectionsData = that.useMockDisplaySections
  ? that.mockDisplaySections
  : res.data.display_sections || [];
that.$set(that, "displaySections", sectionsData);
```

> 变量名为 `useMockDisplaySections`（data 中定义于约 L378，使用于约 L1157），**非** `useMock`。

PageDesign 接收 props：
```javascript
// pageDesign.vue L483-486
displaySections: {
  type: Array,
  default: () => [],
}
```

### 6.3 pageDesign.vue 渲染分支

pageDesign.vue:229-260（当前路径：`frontend/uni-app/src/subpackage/diyComponents/pageDesign.vue`） 在现有 `v-else-if` 链中新增 4 个分支：

```vue
<!-- ===== 产品类型扩展组件（display_sections 数据驱动，新增产品类型前端零改动）===== -->
<infoCard
  v-else-if="item.name == 'infoCard'"
  :dataConfig="item"
  :sections="displaySectionsByType('info_card')"
></infoCard>
<storeCard
  v-else-if="item.name == 'storeCard'"
  :dataConfig="item"
  :sections="displaySectionsByType('store_card')"
></storeCard>
<timeCard
  v-else-if="item.name == 'timeCard'"
  :dataConfig="item"
  :sections="displaySectionsByType('time_card')"
></timeCard>
<specPreview
  v-else-if="item.name == 'specPreview'"
  :dataConfig="item"
  :section="displaySectionByKey('spec')"
></specPreview>
```

### 6.4 数据过滤方法

pageDesign.vue:583-605（当前路径：`frontend/uni-app/src/subpackage/diyComponents/pageDesign.vue`） 提供两个过滤方法：

```javascript
// 按 component 类型过滤 display_sections（用于 infoCard/storeCard/timeCard）
displaySectionsByType(type) {
  if (!Array.isArray(this.displaySections) || !type) return [];
  return this.displaySections
    .filter(s => s.component === type && s.visible !== false);
}

// 按 key 取单个 section（用于 specPreview，spec section 全商品仅一个）
displaySectionByKey(key) {
  if (!Array.isArray(this.displaySections) || !key) return null;
  return this.displaySections.find(s => s.key === key && s.visible !== false) || null;
}
```

### 6.5 可见性优先级规则

后台 DIY 组件有运营控制的 `isHide` 字段（布局层开关），`display_sections` 的 section 有后端计算的 `visible` 字段（数据层开关）。优先级规则：

```
最终显示 = (运营 isHide == false)  AND  (后端 visible == true)  AND  (对应 section 存在)
```

- 运营 `isHide=true`（手动隐藏）→ 无论后端有无数据，组件不渲染
- 后端未输出对应 section（如虚拟商品无 `store_card`）→ 组件无数据，不渲染
- 后端输出 section 但 `visible=false` → 组件不渲染
- 三者都满足 → 组件渲染
- **后台装修预览不受此规则约束**：装修时用 mock 数据渲染（都有数据、`visible=true`）

### 6.6 下单跳转流程

详情页底部栏"立即购买"按钮点击后，goods_details/index.vue:1741-1792（当前路径：`frontend/uni-app/src/pages/goods_details/index.vue`） 携带 `display_sections` block 选中数据跳转下单页：

```javascript
// 携带 display_sections block 选中数据（票种/场次/日期）到下单页
uni.navigateTo({
  url: `/pages/order_details/index?...&block_data=${encodeURIComponent(JSON.stringify(selectedBlockData))}`
});
```

> **详情页不采集下单数据**：底部栏仅触发跳转，规格选择交互（BlockMatrix）保留"选规格看价/图/库存"体验但不采集，数量/表单/地址/日期选择全部归下单页 `OrderCreateEngine`。

---

## 七、扩展契约：ProductDetailExtend 事件

### 7.1 事件触发

[CenterProductServices::getDetail() L731-748](../../../../../backend/app/services/product/CenterProductServices.php#L731) 组装完基础 storeInfo 后触发事件，扩展模块监听器向 `extensions` 与 `display_sections` 注入字段：

```php
$extensions = [];
$displaySections = [];
$eventData = [
    'extensions'        => &$extensions,        // 引用传递，监听器写入
    'display_sections'  => &$displaySections,   // 引用传递，监听器写入
    'product_type'      => $productType,
    'activity_type'     => 'normal',
    'product_id'        => $id,
    'uid'               => $uid,
    'handler'           => $handler,
    'info'              => $info,
    'specs'             => $specs,
];
event('ProductDetailExtend', $eventData);
```

### 7.2 已实现的监听器清单

各产品类型监听器位于 `modules/product/{type}/listener/ProductDetailExtendListener.php`：

| 产品类型 | 监听器文件 | 注入 extensions | 输出 display_sections |
|---------|-----------|-----------------|---------------------|
| normal | [normal/.../ProductDetailExtendListener.php](../../../../../backend/modules/product/normal/listener/ProductDetailExtendListener.php) | custom_form/params_list/label_list/protection_list/type_info/fulfillment_preview | spec(matrix) + fulfillment + store + presale |
| virtual | [virtual/.../ProductDetailExtendListener.php](../../../../../backend/modules/product/virtual/listener/ProductDetailExtendListener.php) | custom_form/params_list/label_list/protection_list/type_info/fulfillment_preview | spec(matrix) + fulfillment |
| cardkey | [cardkey/.../ProductDetailExtendListener.php](../../../../../backend/modules/product/cardkey/listener/ProductDetailExtendListener.php) | custom_form/params_list/label_list/protection_list/type_info/fulfillment_preview | spec(chips) + fulfillment（自动发放说明） |

### 7.3 监听器实现规范

监听器实现遵循当前[架构规范](../../../../rules/架构规范.md)与[事件规范](../../../../rules/事件规范.md)，接口契约以现有代码为准。以下是实现要点：

```php
// modules/product/{type}/listener/ProductDetailExtendListener.php
public function handle($event): void
{
    try {
        if (($event['product_type'] ?? '') !== '{type}') return;

        $handler = $event['handler'];
        $productId = $event['product_id'];

        // 1. 注入 extensions（兼容旧扁平结构）
        $extend = $handler->getDetailExtend($productId);
        $event['extensions']['custom_form']    = $extend['custom_form'] ?? [];
        $event['extensions']['params_list']   = $extend['params_list'] ?? [];
        // ... 其他扩展字段

        // 2. 输出 display_sections（数据驱动详情页渲染）
        $event['display_sections'][] = [
            'key'       => 'spec',
            'component' => 'spec_preview',
            'visible'   => true,
            'order'     => 10,  // 产品类型固有展示区间 10-99
            'data'      => ['blocks' => $this->buildSpecBlocks($handler, $productId)],
        ];
        // ... 其他 section（fulfillment/store/validity 等）
    } catch (\Throwable $e) {
        \think\facade\Log::error('{Type} ProductDetailExtend 监听器异常: ' . $e->getMessage());
        // 不抛出，不影响其他监听器和整体 getDetail
    }
}
```

**约束清单**：
1. **自读扩展表**：监听器需要的数据由监听器自行从扩展表读取，不依赖 Handler 新增接口方法
2. **异常容错**：监听器 `handle()` 内用 try-catch 包裹业务逻辑，异常时记录日志但不抛出
3. **多语言**：所有展示文案（`title`、`items[].label`、`items[].value` 等）必须通过 `lang()` 或项目翻译函数包裹
4. **order 区间遵守**：产品类型监听器只能使用 10-99 区间

### 7.4 Handler 职责收敛

产品类型 Handler 在详情页场景的职责：

| 方法 | 详情页用途 | 现状 |
|------|-----------|------|
| `getDetailExtend($productId)` | 注入业态展示字段到 extensions（兼容旧扁平结构） | 已就绪，保留 |
| `getDetailSpecs($productId)` | 提供实物/虚拟多规格只读数据（matrix block 复用） | 已就绪，不动 |
| `getSelectionSpec()` | 提供选择契约（前端渲染底部栏选择区） | 已就绪 |
| `getCartStrategy()` | 提供加购策略（决定 cart_button 显隐） | 已就绪 |
| `getPageConfig('detail')` | **详情页场景不再使用**（布局归 DIY 管） | [normal/Handler.php:578](../../../../../backend/modules/product/normal/Handler.php#L578) 返回空数组 |

> Handler::getPageConfig('detail') 详情页场景不再被调用（返回空数组即使用中台默认 DIY 布局）。`buildDefaultPageConfig()` 保留给下单页或其他声明式场景使用（详见第九章）。

---

## 八、业态矩阵

### 8.1 各产品类型 display_sections 输出对照

| 产品类型 | spec blocks 组合 | info_card | store_card | time_card | 扩展注入来源 |
|---------|-----------------|-----------|------------|-----------|-------------|
| normal（实物） | `matrix` | 配送/时效/运费 | 自提门店 | 预售期 | normal listener |
| virtual（虚拟） | `matrix` | 自动发放/到账时间 | — | — | virtual listener |
| cardkey（卡密） | `chips`（面值/有效期/发放） | 卡密发放/使用说明 | — | 有效期 | cardkey listener |
| coupon（卡券） | `chips`（面值/有效期） | 核销方式/使用规则 | 可用门店 | 有效期 | coupon listener |

### 8.2 业态售卖形态本质区别

不同产品类型的售卖模型本质不同，不能用实物多规格统一承载。以「日期驱动型业态」（如门票、酒店这类按日期售卖的场景）为例：

| 维度 | 实物/虚拟 | 日期驱动型业态 |
|------|----------|------|
| 规格属性 | 离散（颜色=黑/白/红） | 票种/房型 + 场次 |
| 库存分配 | 单数字，永久库存 | 按日期×场次/房型分配，每天独立库存 |
| 价格 | SKU 固定价 | 随日期变化（周末/节假日浮动），按晚计价 |
| 选择维度 | 颜色×尺寸（离散组合） | 票种×场次×日期（日期连续） |

**核心结论**：
1. 日期是连续维度，不是离散规格，不可能为每天建一个 SKU
2. 库存模型根本不同，单字段承载不了日期×场次/房型二维矩阵
3. 计价模型不同，单值承载不了日期价格表
4. 因此此类业态需要专属售卖形态模型，通过 blocks 组合表达，不复用多规格

---

## 九、buildDefaultPageConfig 保留说明（声明式场景备用）

### 9.1 方法定位

[CenterProductServices::buildDefaultPageConfig() L833-948](../../../../../backend/app/services/product/CenterProductServices.php#L833) 生成声明式 `{sections, bottom_bar, submit_api}` 配置，**详情页场景不再调用**（详情页布局归 DIY 管），保留给下单页或其他声明式场景使用。

### 9.2 默认 sections 清单（41 项，声明式场景参考）

| order | key | component | visible 条件 |
|-------|-----|-----------|-------------|
| 10 | carousel | image_carousel | 始终 |
| 15 | notice | notice_bar | extensions.fulfillment_preview 非空 |
| 20 | product_info | product_info | 始终 |
| 22 | social_proof | social_proof | 始终 |
| 25 | price_tags | price_tags | price.svip_open 为真 |
| 28 | service_promise | service_promise | promises 非空 |
| 30 | activity | activity_entry | activity 非空 |
| 35 | coupon | coupon_entry | coupons 非空 |
| 36 | promotion | promotion_banner | marketing 非空 |
| 40 | sku | sku_selector | 始终 |
| 42 | sessions | session_picker | extensions.sessions 非空 |
| 44 | date_inventory | date_inventory_calendar | extensions.date_inventory 非空 |
| 46 | date_range | date_range_picker | extensions.booking 非空且 hotel |
| 48 | room_types | room_type_list | extensions.room_types 非空 |
| 50 | custom_form | form_builder | extensions.custom_form 非空 |
| 52 | realname | realname_form | extensions.realname 非空 |
| 54 | booking | booking_form | extensions.booking 非空且 hotel/onsite |
| 56 | appointment | appointment_picker | extensions.onsite 非空 |
| 58 | service_staff | service_staff | extensions.onsite 非空 |
| 60 | params | params_list | extensions.params_list 非空 |
| 62 | protection | protection_list | extensions.protection_list 非空 |
| 64 | label | label_list | extensions.label_list 非空 |
| 65 | address | address_selector | shipping.requires_shipping |
| 66 | shipping | shipping_info | shipping.requires_shipping |
| 68 | store | store_info | shipping.supports_pickup |
| 70 | merchant | merchant_card | extensions.merchant 非空 |
| 72 | package | package_content | extensions.package 非空 |
| 74 | stores | store_list | extensions.stores 非空 |
| 76 | ticket | ticket_info | extensions.ticket 非空 |
| 78 | verify | verify_info | extensions.verify 非空 |
| 80 | facility | facility_list | extensions.facilities 非空 |
| 82 | service_area | service_area | extensions.onsite 非空 |
| 84 | map | map_card | location.latitude 非空 |
| 86 | reply | reply_section | reply.reply 非空 |
| 88 | video | video_show | extensions.video 非空 |
| 90 | recommend | recommend_list | good_list 或 recommend_list 非空 |
| 92 | purchase_notice | purchase_notice | notices 非空 |
| 94 | spread_float | spread_float | extensions.spread 非空且 spread_switch |
| 95 | share_poster | share_poster | extensions.spread 非空且 spread_switch |
| 96 | spread_material | spread_material | extensions.spread 非空 |
| 97 | description | rich_text | 始终 |

> 该清单仅作为声明式渲染场景（如下单页 sections 配置）的参考。详情页已迁移到 DIY + display_sections 架构，**不消费此 sections 配置**。

---

## 十、后台 DIY 编辑器集成

### 10.1 三件套 + 自动注册架构

后台 DIY 编辑器（[diyIndex.vue](../../../../../frontend/admin/src/diy/pages/devise/diyIndex.vue)）采用"三件套 + 自动注册"架构，新增一个可拖拽组件需要配套三类产物：

| 产物 | 目录 | 职责 | 命名约定 |
|------|------|------|---------|
| 预览渲染组件 | `src/diy/components/mobilePage/` | 中间预览区用 `<component :is="item.name">` 动态渲染，**内置 mock 数据** | `home_xxx.vue` |
| 配置面板组件 | `src/diy/components/mobileConfig/` | 右侧用 `<component :is="item.configName">` 动态渲染，定义可配置项 | `c_xxx.vue` |
| 默认配置 | `src/diy/store/legacy/moren.js` 的 `defaultConfig` | 组件首次拖入时的初始配置回退值 | 按组件 name 为 key |

**自动注册机制**：`mobilePage/index.js` 与 `mobileConfig/index.js` 均使用 `require.context('./', false, /\.vue$/)` 自动扫描目录下所有 `.vue` 文件并注册。新增组件只需把 `.vue` 文件放进对应目录即自动注册，**无需手动修改 index.js**。

### 10.2 详情页 4 个组件三件套清单

| 组件 | 预览渲染 | 配置面板 | 默认配置 key | type |
|------|---------|---------|-------------|------|
| 万能信息卡 | `home_info_card.vue` | `c_info_card.vue` | （无） | 3（商品组件） |
| 门店/场馆卡 | `home_store_card.vue` | `c_store_card.vue` | （无） | 3 |
| 时间线 | `home_time_card.vue` | `c_time_card.vue` | （无） | 3 |
| 售卖形态展示位 | `home_spec_preview.vue` | `c_spec_preview.vue` | （无） | 3 |

> **默认配置说明**：4 个组件的**预览渲染组件文件存在**（`src/diy/components/mobilePage/home_info_card.vue` 等），但 `src/diy/store/legacy/moren.js` 的 `defaultConfig` 中**没有** `home_info_card`/`home_store_card`/`home_time_card`/`home_spec_preview` 对应的默认配置 key（即"默认配置 key"一列为空）。组件拖入时的缺省配置由预览组件内部默认值兜底，若需要后台可配置项默认值，需自行在 `defaultConfig` 中补充对应 key。
>
> `type=3` 使 4 个组件归入"商品组件"分组，在详情页编辑器左侧可见（home/user 页面不显示）。

### 10.3 配置项设计原则

配置项仅控制**视觉样式与显隐**，不控制业务数据。业务数据（items/periods/blocks）由后端 `display_sections` 驱动，运营在后台无法编辑具体业务数据。

**通用样式配置**（4 个组件共有）：`setUp.tabVal`（上下边距开关）、`mbConfig.val`（上下边距）、`lrConfig.val`（左右边距）、`titleShow`（是否显示区块标题）

### 10.4 装修预览数据说明

**不存在独立的"预览产品类型切换器"**：`diyIndex.vue` 顶部工具栏无"预览产品类型"切换器代码，也不存在单独的 `detailPreviewMock.js` 配置文件。

后台装修预览时，4 个详情组件（infoCard/storeCard/timeCard/specPreview）依赖各自预览组件（`home_info_card.vue`/`home_store_card.vue`/`home_time_card.vue`/`home_spec_preview.vue`）**内置的 mock 数据**渲染，不读取外部 mock 配置文件。

> 装修预览仅用于视觉排版预览，**不写入 `detail_data`**。`detail_data` 保持全局单套，用户端真实渲染时由后端按商品实际产品类型输出 `display_sections`。

### 10.5 detail_data 存储结构

`detail_data.value` 数组只存**布局与样式配置**（组件顺序/显隐/边距/样式），业务数据由后端 `display_sections` 实时下发，两者解耦。

---

## 十一、注意事项

### 11.1 详情页与下单页职责边界（强约束）

| 职责 | 详情页 | 下单页 |
|------|--------|--------|
| 规格选择 | 保留规格选择交互（选规格看价/图/库存），不采集下单数据 | 可交互选择规格、数量，采集提交 |
| 配送/地址 | 展示"支持快递/自提"能力 | 选择地址、选择配送方式 |
| 表单 | 不出现 | 自定义表单、实名、预约填写 |
| 业态数据 | 只读预览（场次概览、房型概览） | 可选场次、可选房型、可选日期 |
| **库存展示** | **概览级：各维度独立可购性** | **精确级：交叉维度实时库存，下单时原子扣减** |
| 底部栏 | 立即购买/加购 → 跳转下单页 | 提交订单 |
| 价格 | 展示当前 SKU 价 / 日历最低价 | 重新计算（优惠券/积分/运费） |

详情页 DIY 组件库里**不应出现**任何"采集类"组件（form_builder、address_selector、session_picker 交互版），这些全部属于下单页。

### 11.2 库存展示策略（演出/酒店/预约专用）

演出、酒店、预约等业态的库存是"日期×场次×票种"等多维矩阵，详情页采用**概览级展示策略**：

1. **日历（calendar）**：每个日期展示整体可购性 `status` + 最低价 `min_price` + 库存量级 `stock_level`，不展开该日各票种/场次的明细库存
2. **时段（time_slots）**：每个场次展示跨票种的最宽松可购性
3. **起价列表（price_list）**：每个票种/房型展示跨日期/场次的最宽松可购性
4. **限购规则**：用 `policy` block 表达（限购数量、库存说明等）
5. **取值规则**：各 block 的 `stock_status` 一律取"该维度下所有组合中最宽松的状态"，保证详情页"显示有票"时用户进入下单页至少有一种组合可买
6. **实时性**：详情页库存为快照，可能延迟；下单页展示实时库存并做下单时校验。`policy` 中应包含"库存实时变化，以下单为准"提示

### 11.3 block 类型封闭性

当前 6 种 block 构件类型（`chips`/`matrix`/`price_list`/`time_slots`/`calendar`/`policy`）已覆盖实物/虚拟/卡密/卡券/门票/酒店/餐饮/预约/会员卡/课程/租赁等所有常见业态的详情页只读展示。

未来出现全新售卖形态（如订阅制、拼团套餐），优先检查能否用现有 6 种 block 组合表达；若确实需要全新展示形态，才新增 block 类型 + 对应子渲染器（specPreview 容器的 `blockComponentMap` 追加一项）。block 类型扩展频率极低，预计 1-2 年才会新增一次。

### 11.4 "前端零改动"的准确界定

**第一层：一次性基础设施改造（做一次，后续不再改）**

| 改造项 | 文件 | 说明 |
|--------|------|------|
| 接口透传 | goods_details/index.vue（当前路径：`frontend/uni-app/src/pages/goods_details/index.vue`） | 从接口取 `display_sections` 透传给 PageDesign |
| 渲染器分支 | pageDesign.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/pageDesign.vue`） | 新增 4 个 `v-else-if` 分支 |
| 新建组件 | `subpackage/diyComponents/productSections/` 目录 | 4 个 DIY 组件 + 6 个 block 子渲染器 |
| 后台装修 | `diyIndex.vue` + `mobilePage/home_*.vue` ×4 + `mobileConfig/c_*.vue` ×4 | 4 个预览组件 + 4 个配置组件（无独立默认配置 key，见 10.2） |

这层改造在 P3-P4 阶段完成，是**一次性投入**，与产品类型数量无关。

**第二层：每次新增产品类型（真正零改动）**

新增任何产品类型（门票/酒店/预约/课程/租赁/会员卡/餐饮套餐等），只需：

| 改动项 | 位置 | 说明 |
|--------|------|------|
| 后端监听器 | `modules/product/{type}/listener/` | 输出 display_sections（blocks 组合） |
| 后端 Handler + 扩展表 + App.php | `modules/product/{type}/` | 产品类型注册与业务实现 |
| 数据库 | `eb_product_center.product_type` + 扩展表 | 数据存储 |

**前端不改任何代码**：specPreview 容器与 6 个 block 子渲染器封闭有限，新增产品类型的 blocks 组合全部由现有子渲染器承接。

**验证标准**：新增产品类型时，`frontend/` 目录下零 diff，即达到零改动目标。

### 11.5 中台层禁止处理扩展独有字段

中台 [CenterProductServices](../../../../../backend/app/services/product/CenterProductServices.php) 不得直接处理 `modules/` 层扩展独有的业务字段。这类字段应通过事件机制由对应扩展模块的监听器处理。

- `extensions` 与 `display_sections` 是中台的正当职责（准备容器、触发事件、排序下发）
- 但从中台 `extensions` 中**提取特定 key 到顶层并 unset**，属于扩展层业务逻辑
- 新增扩展独有的 `extensions` 字段时，务必在对应扩展模块创建事件监听器处理，不要在中台追加提取逻辑

### 11.6 cart_button 计算铁律

`cart_button` 字段必须由 `cart_strategy.cart_mode` 决定（direct=0/cart=1），不可再用 custom_form/is_virtual/presale 旧逻辑判断。

CenterProductServices 共 5 处 cart_button 计算（getDetail / buildDefaultPageConfig / getProducts 列表 / productDetail / getUserList）需统一走 `getCartModeByType()` 辅助方法（带静态缓存），否则带自定义表单的 normal 商品会错误隐藏加入购物车按钮。

### 11.7 visible 与 isHide 控制权分离

| 层级 | 字段 | 控制方 | 语义 |
|------|------|--------|------|
| 布局层 | `isHide` | 运营（后台 DIY） | 运营排版决定：这个位置要不要放这个组件 |
| 数据层 | `visible` | 后端（监听器） | 业务数据决定：当前商品有没有这块内容（如虚拟商品无自提门店） |

两者语义不同，优先级规则见 6.5 节。后台装修预览不受此规则约束。

### 11.8 超级组件的通用性边界

通用超级组件（InfoCard/StoreCard/TimeCard）覆盖"键值对类"展示差异，能满足 90% 场景。若某业态有极致视觉专属需求（如 3D 模型预览、座位图选择），超出 InfoCard 表达范围——但这类通常属于**下单页交互**，详情页只读预览用超级组件足够。真有详情页极致视觉需求，再走"业态专属 DIY 组件"补充（作为 DIY 组件库新成员，带 `product_types` 限定）。

---

## 十二、新增产品类型标准流程（二开范式）

以新增「票务 ticket」产品类型为例（假设性示例）：

### 后端（modules/product/ticket/）

1. **Handler.php** — 实现 `ProductTypeHandlerInterface`
   - `getTypeKey()` 返回 `ticket`
   - `requiresShipping()` 返回 false，`isVirtual()` 视情况
   - `getDetailExtend()` 返回票务专属字段
   - `getDetailSpecs()` 返回场次规格（供下单页用）
   - `getCompatibility()` 声明支持的交易模式
2. **listener/ProductDetailExtendListener.php** — 监听 `ProductDetailExtend`，输出 spec section（blocks 组合：`price_list`+`time_slots`+`calendar`+`policy`）+ info_card/store_card/time_card section
3. **dao/TicketDao.php + dao/TicketSessionDao.php** — 门票扩展表数据访问
4. **model/Ticket.php + model/TicketSession.php** — 模型
5. **App.php** — 注册 Handler 到 `ProductTypeRegistry`
6. **event.php** — 注册监听器

### 前端

7. **无需任何改动**。门票监听器输出的 blocks 组合（`price_list`/`time_slots`/`calendar`/`policy`）全部由 specPreview 的 6 个通用 block 子渲染器承接，无需门票插件提供前端组件。

### 数据库

8. `eb_product_center.product_type` 写入 `ticket`
9. 扩展表 `eb_pt_ticket` / `eb_pt_ticket_session` / `eb_pt_ticket_stock`（日期库存矩阵）

### 收益

- 中台 [CenterProductServices](../../../../../backend/app/services/product/CenterProductServices.php) 零修改
- 前端 PageDesign / 4 个通用超级组件 / 6 个 block 子渲染器零修改
- 现有 DIY 通用组件库零修改
- 实物/虚拟多规格体验零影响
- **新增产品类型前端完全零改动**
- 完全符合 v8.1 三层架构铁律

---

## 变更记录

| 日期 | 版本 | 变更 |
|------|------|------|
| 2026-08-06 | v2.0 | 全面重写：从虚构的"双层声明式架构（page_config+data）/ProductDetailEngine/41 个组件/validate+getData"改为实际落地的"DIY + display_sections 数据驱动"架构。对齐 dev_docs 方案 v1.4：①接口返回扁平结构 + display_sections（非 {page_config, data}）；②前端渲染器为 pageDesign.vue（非 ProductDetailEngine.vue）；③三层组件库（通用 DIY 组件 + 4 个超级组件 + 6 个 block 子渲染器）；④详情页只读不采集下单数据；⑤buildDefaultPageConfig 降级为声明式场景备用；⑥补充实际监听器清单、order 区间、visible/isHide 优先级、cart_button 计算铁律 |
| 2026-08-03 | v1.0 | 初版（基于虚构架构，与实际实现不符，已废弃） |
