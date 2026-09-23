# 插件 Admin 扩展资源

## 源码与产物

Admin 扩展源码在 `frontend/admin` 中构建，通常位于 `src/pages/plugin/<key>/`。构建扫描器要求同时存在 `ext.json` 和 `index.umd.js`。

```bash
cd frontend/admin
npm run build:ext -- --ext <key>
```

预期产物：

```text
frontend/admin/dist/static/extensions/<key>/app.umd.js
frontend/admin/dist/static/extensions/<key>/app.umd.css
```

宿主加载 JavaScript 全局变量 `window.__ext_<key>`，并向扩展组件传入 `section`、`action`、`params`。具体导出形式应参考当前能正常工作的相近扩展，并进行验证。

## 后端部署

当资源需要随插件状态变化时，将可分发文件保存在模块根下的 `./frontend/dist/` 中，并让 Installer 将其复制到：

```text
backend/public/admin/static/extensions/<key>/
```

运行时 URL 为：

```text
/admin/static/extensions/<key>/app.umd.js
/admin/static/extensions/<key>/app.umd.css
```

安装和启用时部署资源，禁用和卸载时只删除当前 key 对应的扩展目录。调用父类生命周期钩子，对已经存在或已经缺失的文件保持幂等；必需源码资源缺失时应明确报错，不能留下指向空页面的可见菜单。

## 协同检查清单

- Manifest/Installer key、`ext.json` key、全局变量名、输出目录和 PageContainer `typeKey` 完全一致。
- Installer 菜单路径能解析到已注册的宿主路由及正确 section/action。
- `activeMenu` 与可见列表菜单一致。
- 扩展复用宿主 Vue 3 和 Element Plus external（`window.Vue` / `window.ElementPlus`），不得打包第二份不兼容的 Vue 运行时。
- 样式使用 scoped，或统一限制在扩展专属根 class 下。
- 源码集成式开发加载和生产 UMD 加载均能渲染。
- 禁用/卸载状态应先移除或隐藏菜单，再删除资源。

不得在生产安装请求中现场构建资源。应在打包前完成构建，保证安装过程确定可复现。
