# Admin 插件 UI 与 UMD 扩展

## 两种插件 UI 模式

### 源码集成式插件 UI

将代码放在 `frontend/admin/src/pages/plugin/<key>/`，并通过 `routes.js` 导出路由。`router/routers.js` 用 `import.meta.glob` 在构建期收集这些路由（新增 routes.js 后需重启 dev server）。该模式支持普通懒加载路由组件，现有 `lottery`、`cardkey`、`hotel`、`wechat_mini` 等插件均采用这一结构。

当插件随本仓库发布，且不需要在运行时移除其编译代码时使用该模式。

### 运行时 UMD 扩展

当启用、禁用或安装后端插件时，需要独立部署或删除 admin 资源，则使用此模式。

源码目录必须包含：

```text
src/pages/plugin/<key>/
  ext.json
  index.umd.js
  App.vue
  routes.js             可选路由元数据
  pages/                扩展页面
```

`ext.json` 至少包含构建用 `key` 和面向用户的 `title`。`scripts/build-extensions.js` 扫描 `src/pages/plugin/` 与 `src/pages/product/type/` 下同时具有 `ext.json` 和 `index.umd.js` 的目录，用 `scripts/vite.ext.config.js` 以 Vite lib 模式（UMD）构建，全局名为 `__ext_<key>`，输出：

```text
dist/static/extensions/<key>/
  app.umd.js
  app.umd.css
```

构建会把 `vue` 和 `element-plus` 设为 external，由宿主 Admin 提供，运行时从 `window.Vue` / `window.ElementPlus` 取（由 `src/main.js` 暴露）。

## PageContainer 契约

`src/pages/system/plugin/PageContainer.vue` 从以下地址加载生产资源：

```text
/admin/static/extensions/<key>/app.umd.js
/admin/static/extensions/<key>/app.umd.css
```

扩展组件接收：

- `typeKey`：扩展 key
- `section`：业务区域
- `action`：类似 list/read/save/update 的动作
- `params`：从路由解析出的参数

全局导出必须兼容 `window.__ext_<key>`。复制当前可用的相近扩展入口形式，并同时验证开发环境 `import()` 和生产环境脚本加载，因为两条路径的导出解析方式不同。

## 路由与菜单

- 后端 Installer 的 `menu_path` 必须与不带 `/admin` 的路由路径一致。
- 前端路由使用 `routePre + menu_path`。
- 详情/编辑页面的 `meta.activeMenu` 使用带 `routePre` 的完整列表路径。
- 如果路由只是 UMD 组件内部使用的元数据，只有其顶层包含 `component` 时，`router/routers.js` 才会注册；不得默认它一定会被加入主路由。

## 构建与生命周期

构建单个扩展：

```bash
npm run build:ext -- --ext <key>
```

后端 Installer 将两个构建文件复制到 `backend/public/admin/static/extensions/<key>/`。安装/启用时部署，禁用/卸载时只删除该扩展目录。覆盖生命周期钩子时调用 `parent::onEnable()` 和 `parent::onDisable()`。

不得手工编辑生成的 UMD 文件。验证生产环境全局变量存在、组件能够渲染所有支持的 section/action、样式不会污染全局，并确保插件禁用后不会留下指向已删除资源的可用菜单。
