# 商品详情页 Mock 数据集

本目录用于存放不同产品类型的详情页接口 mock 数据，供前端开发、接口联调、UI 验证使用。

## 文件说明（.json 与 .jsonc 双版本）

每个产品类型提供两个文件：

| 文件 | 用途 | 说明 |
|------|------|------|
| `*.json` | 前端本地调试 | 标准 JSON，无注释，可被 `import` 直接消费 |
| `*.jsonc` | 接口开发参考 | 带 `//` 行内注释，逐字段说明用途/类型/取值含义，VSCode/IDE 可高亮 |

> JSON 标准不支持注释，故将带注释版本独立为 `.jsonc` 文件。前端调试用 `.json`，后端开发人员参考 `.jsonc`。

## 文件清单

| 文件 | 产品类型 | virtual_type | 选择模型 | 加购策略 | 弹窗类型 | 单位 | 说明 |
|------|----------|--------------|----------|----------|----------|------|------|
| `normal.json(c)` | 实物商品 | 0 | matrix | cart | editable | item | 多规格矩阵选择，双按钮加购 |
| `virtual.json(c)` | 网盘产品 | 1 | matrix | cart | editable | item | 套餐版本规格，自动发货，SKU 含 download_url/download_password |
| `cardkey.json(c)` | 卡密商品 | 2 | none | cart | readonly | item | 无规格选择，单件购买，multi_item=false |

## 数据结构说明

每个 JSON 文件结构严格对齐后端 `CenterProductServices::getDetail()` 返回格式：

```jsonc
{
  "code": 200,
  "msg": "ok",
  "data": {
    // 顶层 result 字段
    "uid": 27,
    "is_money_level": 0,
    "spec_unique": "NORMAL_BLK_M_002",   // 单规格时为 SKU unique，多规格/无规格时为空
    "svip_open": true,                    // 会员卡开关
    "svip_price_open": 1,                 // 会员价开关
    "priceName": 0,                       // 分销价标识
    "mer_id": 0,
    "mapKey": "TXABCD-1234-EFGH-5678-IJKL", // 腾讯地图 key
    "store_self_mention": 1,              // 是否开启到店自提
    "routine_contact_type": 0,
    "activity": [],                       // 活动入口
    "coupons": [],                        // 优惠券列表
    "reply": [],                          // 评价列表
    "replyChance": 96,                    // 好评率
    "replyCount": 326,                    // 评价数
    "good_list": [],                      // 推荐商品
    "productAttr": [],                    // 规格属性（temporal/none 为空数组）
    "productValue": {},                   // SKU 映射（temporal/none 为空对象）
    "display_sections": [],               // 动态展示块数组（按 order 排序）
    "storeInfo": {                        // 商品基础信息（82+ 字段）
      // ===== 产品类型 =====
      "product_type": "normal",
      "product_type_name": "实物商品",

      // ===== 图片 =====
      "id": 1001,
      "image": "...",
      "image_base": "...",
      "slider_image": [],
      "recommend_image": "...",
      "small_image": "...",
      "attrPics": [],                    // 规格图片集合

      // ===== 基本信息 =====
      "store_name": "...",               // 商品名称
      "store_info": "...",               // 副标题/简介
      "keyword": "...",
      "bar_code": "",
      "cate_id": "12",
      "unit_name": "件",
      "spu": "...",
      "label_id": "1,2,3",
      "video_link": "",
      "command_word": "",
      "recommend_list": "",
      "code_path": "",
      "soure_link": "",
      "default_sku": "...",
      "add_time": 1722422400,
      "sort": 0,

      // ===== 价格体系 =====
      "price": "99.00",
      "vip_price": "89.00",
      "ot_price": "159.00",              // 原价
      "postage": "0.00",                 // 运费
      "cost": "45.00",
      "give_integral": "0.00",
      "svip_economize_price": "10.00",
      "gift_price": "0.00",

      // ===== 库存/销量 =====
      "stock": 86,
      "sales": 326,
      "fsales": "326",
      "ficti": 50,                       // 虚拟销量
      "browse": 8541,                    // 浏览量

      // ===== 状态标识 =====
      "is_show": 1,
      "is_hot": 1,
      "is_benefit": 0,
      "is_best": 0,
      "is_new": 1,
      "is_virtual": 0,
      "virtual_type": 0,                 // 0实物 / 1网盘 / 2卡密
      "is_postage": 1,                   // 是否包邮
      "is_del": 0,
      "is_seckill": 0,
      "is_bargain": null,
      "is_good": 0,
      "is_sub": 0,
      "is_vip": 0,
      "is_gift": 0,
      "vip_product": 0,                  // 会员商品可见性 0不限制/1仅会员可见可买/2所有人可见仅会员可买
      "vip_product_type": 0,             // 【已废弃】v6.1.1已删除该列，恒返回0，语义已迁移到 vip_product

      // ===== 限购 =====
      "is_limit": 1,
      "limit_type": 1,                   // 1 单次限购 / 2 周期限购
      "limit_num": 5,
      "min_qty": 1,                      // 起购数量

      // ===== 规格 =====
      "spec_type": 1,                    // 0 单规格 / 1 多规格
      "activity": "",

      // ===== 预售 =====
      "presale": 0,
      "presale_start_time": "1970-01-01 08:00",
      "presale_end_time": "1970-01-01 08:00",
      "presale_day": 0,
      "presale_pay_status": 0,           // 0 非预售 / 1 待开始 / 2 进行中 / 3 已结束

      // ===== 物流 =====
      "logistics": "express",
      "freight": 1,                      // 1 包邮 / 2 固定运费 / 3 运费模板
      "temp_id": 0,
      "mer_id": 0,
      "mer_use": 0,

      // ===== 详情描述 =====
      "description": "<p>HTML 富文本...</p>",
      "wechat_code": "",

      // ===== 用户态 =====
      "userCollect": false,
      "userLike": false,

      // ===== 扩展字段（由 ProductDetailExtend 事件注入）=====
      "custom_form": [],                 // 自定义表单
      "params_list": [],                 // 参数列表（材质/款式/季节等）
      "protection_list": [],             // 保障服务
      "label_list": [],                  // 商品标签

      // ===== 选择/加购契约（前端按此渲染）=====
      "selection_spec": {
        "selection_model": "matrix",     // matrix | temporal | none
        "confirm_window": "editable",     // editable | readonly | none
        "required_selections": ["sku"],   // 必选项 key 数组
        "cart_unit": "item"              // item | day
      },
      "cart_strategy": {
        "cart_mode": "cart",             // cart | direct
        "multi_item": true,              // 是否支持一次买多件
        "button_text": ""                // direct 模式按钮文案
      },
      "cart_button": 1,                  // 0 不显示加购按钮 / 1 显示（由 cart_mode 计算）
    }
  }
}
```

## 6 种 block 类型

`display_sections` 中 `spec` section 的 `data.blocks` 数组支持以下 6 种封闭 block 构件：

| 类型 | 用途 | 字段示例 |
|------|------|---------|
| `chips` | 属性标签 | `{ type, title, items: [{label, value}] }` |
| `matrix` | 规格矩阵 | `{ type, title, attrs: [{name, values}], skus: [{suk, price, stock, image}] }` |
| `price_list` | 起价列表 | `{ type, title, items: [{id, name, price_from, tags, stock_status}] }` |
| `time_slots` | 场次列表 | `{ type, title, items: [{id, name, time, stock_status}] }` |
| `calendar` | 日历概览 | `{ type, title, config: {mode: single\|range}, dates: [{date, status, min_price, stock_level, sku_detail}] }` |
| `policy` | 退改政策 | `{ type, title, items: [{label, value}] }` |

### 库存状态枚举

- `sufficient`：充足（≥20）
- `tight`：紧张（5-19）
- `limited`：少量（1-4）
- `sold_out`：售罄（0）

### `stock_level` 枚举

- `high`：库存高
- `low`：库存低
- `none`：无库存

## 3 种产品类型契约对照

### 选择契约 + 加购策略

| 契约字段 | normal | virtual | cardkey |
|---------|--------|---------|---------|
| selection_model | matrix | matrix | none |
| confirm_window | editable | editable | readonly |
| required_selections | [sku] | [sku] | [] |
| cart_unit | item | item | item |
| cart_mode | cart | cart | cart |
| multi_item | true | true | false |
| button_text | (空) | (空) | (空) |

### 商品属性差异

| 字段 | normal | virtual | cardkey |
|------|--------|---------|---------|
| is_virtual | 0 | 1 | 1 |
| virtual_type | 0 | 1（网盘） | 2（卡密） |
| spec_type | 1（多规格） | 1（多规格） | 0（单规格） |
| requires_shipping | true | false | false |
| cart_button | 1 | 1 | 1 |
| limit_num | 5 | 0 | 5 |
| unit_name | 件 | 份 | 张 |

### productAttr / productValue 差异

| 类型 | productAttr | productValue | 说明 |
|------|-------------|--------------|------|
| normal | 多规格（颜色×尺码） | 12 个 SKU | 完整规格矩阵 |
| virtual | 单维度（套餐版本） | 3 个 SKU | 每个 SKU 含 download_url/download_password |
| cardkey | 单规格（默认） | 1 个 SKU | 无多规格选择 |

### display_sections 差异

| 类型 | blocks 构成 |
|------|------------|
| normal | matrix（规格预览） |
| virtual | matrix（套餐版本） |
| cardkey | chips（卡密属性） |

## 使用方式

### 1. 前端本地调试

在 `pages/goods_details/index.vue` 的 `getProductDetail` 调用处，将接口返回替换为本地 mock：

```javascript
// 引入 mock 数据
import normalMock from './mock/normal.json'

// 替换接口调用（开发环境）
if (process.env.NODE_ENV === 'development') {
  return Promise.resolve(normalMock)
}
```

### 2. 配合 useMockDisplaySections 调试

`index.vue` 内置 `useMockDisplaySections` 开关，置为 `true` 时使用本地 `mockDisplaySections` 渲染。如需测试某种产品类型的 `display_sections`，可将其 `display_sections` 内容复制到 `mockDisplaySections` 变量中。

### 3. 接口参考

后端开发人员可直接参考各 JSON 文件的结构返回数据，确保字段名、嵌套层级与 mock 一致，前端无需适配层即可直接消费。

## 关键字段对应（前端 → 后端）

| 前端消费位置 | 接口字段 | 说明 |
|------------|---------|------|
| `productBottom.vue` 按钮渲染 | `storeInfo.cart_strategy.cart_mode` | 决定显示"加入购物车+立即购买"双按钮还是单按钮 |
| `productBottom.vue` 按钮文案 | `storeInfo.cart_strategy.button_text` | direct 模式下按钮文案 |
| `productWindow.vue` 弹窗模式 | `storeInfo.selection_spec.confirm_window` | readonly 时规格不可点击 |
| `index.vue` goCat 分发 | `storeInfo.selection_spec.required_selections` | 校验必选项是否齐全 |
| `specPreview.vue` blocks 渲染 | `display_sections[spec].data.blocks` | 按 type 分发到对应子组件 |
| 限购提示 | `storeInfo.is_limit` + `limit_type` + `limit_num` | 1 单次限购 / 2 周期限购 |
| 起购数量 | `storeInfo.min_qty` | 最少购买数量 |
| 商品详情富文本 | `storeInfo.description` | HTML 富文本，需 v-html 渲染 |
| 评价 | `reply` + `replyChance` + `replyCount` | 评价列表 + 好评率 + 评价数 |
| 优惠券 | `coupons` | 可领优惠券列表 |
| 推荐商品 | `good_list` | 推荐商品列表 |
| 活动 | `activity` | 关联活动（秒杀/砍价/拼团等） |
| 会员价 | `svip_open` + `svip_price_open` + `vip_price` | 会员价展示三件套 |
| 预售 | `presale` + `presale_pay_status` | 1 待开始 / 2 进行中 / 3 已结束 |
| 配送方式 | `logistics` + `freight` + `temp_id` | express/pickup + 1包邮/2固定/3模板 |

## 数据更新维护

- 修改 mock 数据后，请同步更新本 README 的契约对照表
- 新增产品类型时，新增对应 JSON 文件并在表格中补充一行
- mock 中的图片地址为占位 URL，生产环境不可用，需替换为真实 CDN 路径
- `storeInfo` 字段应与 `CenterProductServices::getDetail()` 返回字段一一对应，新增字段时同步补全所有 JSON 文件

## 关联文档

- 加购契约：[用户端购物车手册](../../../../../../.crmeb/skills/crmeb-dev/references/05-用户端开发/user-cart.md)
- 架构规则：[架构规范.md](../../../../../../.crmeb/rules/架构规范.md)
- 后端 Handler 实现：[modules/product/](../../../../../../backend/modules/product/)
- 后端详情接口：[CenterProductServices::getDetail()](../../../../../../backend/app/services/product/CenterProductServices.php)
