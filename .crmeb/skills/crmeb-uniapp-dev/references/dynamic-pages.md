# 动态页面契约

## 当前目录结构

```text
dynamic-pages/
  api/
    action-map.js        订单按钮动作分发
    routes.js            共享导航路径
  engines/
    OrderListEngine.vue
    OrderDetailEngine.vue
    OrderCreateEngine.vue
    AfterSaleEngine.vue
    CashierEngine.vue
    PayStatusEngine.vue
  components/order/
    common/              共享订单卡片、状态、价格组件
    create/              后端驱动的下单表单区块
    detail/              详情区块基础组件和注册表
```

`pages/center/` 下的轻量页面包装器把路由数据传入这些引擎。导航仍需在 `pages.json` 注册；引擎是组件，不是独立页面。

## 订单列表契约

`OrderListEngine` 调用订单中台列表和统计 API。重要的后端字段包括：

- `pending_payment`、`pending_delivery`、`pending_use`、`pending_comment` 等语义状态分组
- `status_code` 和 `status_text`
- `type_label` 和 `extend_text`
- `order_buttons[]`，至少包含 `action`、`text`，可选 `primary`、`params`
- 用于商品和规格展示的 `extra_info.items`、`extra_info.sku_info`

不得为每种产品类型或交易模式增加前端 switch。应扩展后端 Adapter/Handler，使其提供通用字段。只有出现真正全新的用户操作行为时，才在 `api/action-map.js` 增加映射。

## 订单详情契约

当前详情响应为声明式结构：

```text
page_config
  sections[]
    key/component/visible/order/props
  extensions
```

`OrderDetailEngine` 对 sections 排序，并通过 `components/order/detail/index.js` 中的详情组件注册表进行渲染。如果后端模块可通过现有组件和 `props` 表达功能，就不要修改引擎。只有展示或交互本身属于全新类型时，才新增基础组件及注册项。

模块专属数据应放在 `page_config.extensions` 或带命名空间的区块载荷中。引擎不得导入模块专属代码。

## 下单、支付与售后

- `OrderCreateEngine` 渲染后端提供的配送、自定义表单、游客、优惠、发票、备注、价格和提交契约。
- `CashierEngine` 负责支付方式选择与调起。
- `PayStatusEngine` 使用相同的声明式页面配置渲染支付结果。
- `AfterSaleEngine` 使用后端提供的可用售后类型、表单配置、详情区块和请求动作。

前后端都需要验证：前端验证改善交互，后端 Handler 对价格、库存、兼容性、资格和安全具有最终决定权。

## 商品详情扩展

商品详情不在 `dynamic-pages/` 目录中，但遵循同一原则。后端 `ProductDetailExtend` 监听器填充 `display_sections`，可复用渲染组件位于 `components/productSections/`。

优先组合现有信息、门店、时间或规格等区块组件。新增产品类型通常只需增加后端区块数据，不应在核心页面加入硬编码 `product_type` 分支。

## 扩展检查清单

1. 确认现有组件或 action 是否已经能表达该功能。
2. 明确记录后端载荷和可选字段缺失时的默认值。
3. 保持排序稳定，并一致处理 `visible === false`。
4. `v-for` 使用稳定 key。
5. 对旧记录或短期版本落后的后端保持合理回退。
6. 测试所有受影响生命周期：列表、详情、下单、支付、取消、履约、售后和状态结果。
