# 后台管理端前端扩展

产品类型的后台管理页面（商品列表、添加/编辑商品、订单列表、订单详情等）可以通过 UMD 扩展包的方式注入到后台系统中，无需修改后台管理端核心源码。

## 一、扩展机制

后台管理端使用 `PageContainer.vue` 作为扩展容器，动态加载 UMD 包：

- 容器文件：`frontend/admin/src/pages/system/plugin/PageContainer.vue`
- 加载路径约定：`{publicPath}/admin/static/extensions/{typeKey}/`
- 必须包含两个文件：
  - `app.umd.min.js` — UMD 构建产物
  - `app.umd.css` — UMD 样式文件
- 全局挂载名：`window.__ext_{typeKey}`

## 二、扩展目录结构

在产品类型插件目录下创建前端源码和构建产物：

> **落地现状说明**：目前仅 virtual 落地了模块根下的 `./frontend/dist`（`backend/modules/product/virtual/frontend/dist/`），其余现存产品类型的管理端页面位于 `frontend/admin/src/pages/plugin/{key}/`，插件目录下未必包含前端源码与构建产物。下文目录结构属目标形态，第五节软链示例依赖自行创建目录。

```
backend/modules/product/{type}/frontend/
├── package.json          # 扩展包依赖与构建脚本
├── vue.config.js         # Vue CLI / webpack 配置，输出 UMD
├── src/
│   ├── index.js          # 入口文件，导出 Vue 组件
│   ├── App.vue           # 扩展根组件
│   ├── routes.js         # 扩展内部路由（可选）
│   ├── api/              # 扩展专用接口
│   ├── views/
│   │   ├── ProductList.vue
│   │   ├── ProductForm.vue
│   │   ├── OrderList.vue
│   │   └── OrderDetail.vue
│   └── components/       # 扩展专用组件
└── dist/                 # 构建产物目录
    ├── app.umd.min.js
    └── app.umd.css
```

## 三、最小 UMD 扩展示例

### 3.1 入口文件 `src/index.js`

```js
import App from './App.vue';

// UMD 包全局挂载名：window.__ext_{typeKey}
// 例如票务类型 typeKey=ticket，则挂载到 window.__ext_ticket
export default App;

// 可选：导出内部路由
export { default as routes } from './routes';
```

### 3.2 根组件 `src/App.vue`

```vue
<template>
  <div class="ticket-extension">
    <!-- section 和 action 由 PageContainer 传入 -->
    <ProductList v-if="section === 'product' && action === 'list'" />
    <ProductForm v-else-if="section === 'product' && action === 'save'" :params="params" />
    <ProductForm v-else-if="section === 'product' && action === 'update'" :params="params" />
    <OrderList v-else-if="section === 'order' && action === 'list'" />
    <OrderDetail v-else-if="section === 'order' && action === 'read'" :params="params" />
    <div v-else>未知页面：{{ section }}/{{ action }}</div>
  </div>
</template>

<script>
import ProductList from './views/ProductList.vue';
import ProductForm from './views/ProductForm.vue';
import OrderList from './views/OrderList.vue';
import OrderDetail from './views/OrderDetail.vue';

export default {
  name: 'TicketExtension',
  components: { ProductList, ProductForm, OrderList, OrderDetail },
  props: {
    section: { type: String, default: 'product' },
    action: { type: String, default: 'list' },
    params: { type: Object, default: () => ({}) },
  },
};
</script>
```

### 3.3 vue.config.js

```js
const { defineConfig } = require('@vue/cli-service');

module.exports = defineConfig({
  // 库模式打包 UMD
  configureWebpack: {
    output: {
      library: `__ext_ticket`, // 与 typeKey 一致
      libraryTarget: 'umd',
      filename: 'app.umd.min.js',
    },
  },
  css: {
    extract: {
      filename: 'app.umd.css',
    },
  },
  // 扩展包通常不需要独立运行，关闭部分默认配置
  productionSourceMap: false,
});
```

> 注意：如果多个扩展使用同一套构建工具，可以通过环境变量动态注入 `library` 名称，避免每个扩展单独维护一份 vue.config.js。

## 四、后台菜单配置

`Installer::getMenuConfig()` 中，将 `menu_path` 指向 `/product/{type}`，系统会自动路由到 `PageContainer`，并传入 `typeKey`。

示例（票务）：

```php
[
    'pid'           => $productPid,
    'menu_name'     => '票务',
    'controller'    => 'ticketProduct',
    'action'        => 'index',
    'module'        => 'admin',
    'menu_path'     => '/product/ticket',
    'unique_auth'   => 'admin-ticket-product',
    'icon'          => 'el-icon-Tickets',
    'sort'          => 30,
    'is_show'       => 1,
    'is_show_path'  => 1,
    'access'        => 1,
    'auth_type'     => 1,
],
[
    'pid'           => '@票务',
    'menu_name'     => '商品管理',
    'controller'    => 'ticketProduct',
    'action'        => 'index',
    'menu_path'     => '/product/ticket/list',
    'api_url'       => 'ticket/product/list',
    'unique_auth'   => 'admin-ticket-product-list',
    'is_show'       => 1,
    'auth_type'     => 1,
],
[
    'pid'           => '@票务',
    'menu_name'     => '添加商品',
    'controller'    => 'ticketProduct',
    'action'        => 'save',
    'menu_path'     => '/product/ticket/add',
    'api_url'       => 'ticket/product',
    'unique_auth'   => 'admin-ticket-product-add',
    'is_show'       => 1,
    'auth_type'     => 1,
],
```

PageContainer 会根据路径解析出 `typeKey=ticket`、`section=product`、`action=list/save/update/read`。

## 五、开发模式热更新

`PageContainer.vue` 在 `process.env.NODE_ENV === 'development'` 时，会通过 `import()` 直接加载 `./{typeKey}/index.umd.js`，支持 HMR。

开发时需要在 `frontend/admin/src/pages/plugin/` 下创建符号链接或软链：

```bash
cd frontend/admin/src/pages/plugin/
ln -s ../../../../../../backend/modules/product/ticket/frontend/src ticket
```

确保 `index.umd.js` 文件名与 `loadDevExtension()` 中的 `import` 路径匹配。

## 六、部署到生产

在 `Installer` 中实现前端资源部署：

```php
protected function deployFrontendAssets(): void
{
    $sourceDir = __DIR__ . '/frontend/dist/';
    $targetDir = public_path('admin/static/extensions/ticket/');
    if (!is_dir($sourceDir)) {
        return;
    }
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    foreach (['app.umd.min.js', 'app.umd.css'] as $file) {
        $source = $sourceDir . $file;
        $target = $targetDir . $file;
        if (is_file($source)) {
            copy($source, $target);
        }
    }
}
```

在 `install()` 和 `onEnable()` 中调用：

```php
public function install(): array
{
    $result = parent::install();
    if ($result['status'] === 'success' || $result['status'] === 'skipped') {
        $this->deployFrontendAssets();
    }
    return $result;
}

public function onEnable(): void
{
    parent::onEnable();
    $this->deployFrontendAssets();
}
```

卸载时清理：

```php
protected function removeFrontendAssets(): void
{
    $targetDir = public_path('admin/static/extensions/ticket/');
    if (!is_dir($targetDir)) return;
    foreach (['app.umd.min.js', 'app.umd.css'] as $file) {
        $target = $targetDir . $file;
        if (is_file($target)) unlink($target);
    }
    if (empty(glob($targetDir . '*'))) rmdir($targetDir);
}
```

## 七、扩展内部调用后端接口

扩展包内部通过 axios 调用模块自身的 admin 接口，如：

```js
import request from '@/utils/request'; // 后台管理端通用请求封装

export function getTicketProductList(params) {
  return request({
    url: '/admin/ticket/product/list',
    method: 'get',
    params,
  });
}
```

接口路由定义在 `backend/modules/product/{type}/route/admin.php` 中。

## 八、activeMenu 配置

如果扩展内部有详情/编辑页，需要在路由 `meta` 中配置 `activeMenu`，让侧边栏高亮对应列表页：

```js
// routes.js
export default [
  {
    path: '/product/ticket/list',
    name: 'TicketProductList',
    component: () => import('./views/ProductList.vue'),
  },
  {
    path: '/product/ticket/add',
    name: 'TicketProductAdd',
    component: () => import('./views/ProductForm.vue'),
    meta: { activeMenu: '/product/ticket/list' },
  },
];
```

**注意四点**：
1. **位置**：必须放在子路由（`path: ''` 或具体页面路由）的 `meta` 中，不能放父路由 `meta`。vue-router 匹配时 `to.meta` 取最深匹配路由。
2. **值格式**：使用路径格式（如 `/product/ticket/list`），**不要**加 `/admin` 前缀。后台菜单系统会根据数据库 `menu_path` 自动拼接 `/admin`。
3. **与菜单路径一致**：`activeMenu` 的值必须与 `Installer::getMenuConfig()` 中对应列表页的 `menu_path` 完全一致，否则侧边栏无法高亮。
4. **不能用路由名称**：如 `` `${pre}product` `` 这种写法无法匹配菜单系统的 `:index`。

## 九、常见问题

### Q1: 扩展加载失败，提示「扩展已安装」但页面空白
A: 检查 `public/admin/static/extensions/{typeKey}/app.umd.min.js` 是否存在；检查 UMD 包是否正确挂载到 `window.__ext_{typeKey}`。

### Q2: 开发模式修改扩展代码后页面没更新
A: 确认软链正确，且 `loadDevExtension()` 的 import 路径匹配；必要时重启 HMR 服务。

### Q3: 扩展页面样式污染全局
A: 在 `App.vue` 根元素上加独立 class，所有样式写在该 class 作用域下，或使用 scoped CSS / CSS Modules。

### Q4: 菜单路径配置正确但 404
A: 确认后台路由已注册 `/product/{type}` 到 PageContainer；检查 `route/admin.php` 中是否有对应路由。
