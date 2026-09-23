---
name: crmeb-admin-dev
description: 开发和修改 frontend/admin 下的 CRMEB Admin 管理端：Vue 3 页面与组件、Element Plus 表单与表格、API 客户端、权限、路由、插件页面和 UMD 扩展。适用于纯管理端前端任务；界面样式与 UI 规范细节用 crmeb-admin-ui，不用于 uni-app 客户端或纯后端改动。
---

# CRMEB Admin 开发

本管理端使用 **Vue 3.5 + Element Plus 2.9 + Pinia 2 + vue-router 4**，**Vite 5** 构建，纯 JavaScript 不用 TS。不得引入 Vue 2 Options API 或 element-ui 写法。

> 2026-09 已从 Vue 2.7 / vue-cli / Vuex / element-ui 整体迁移。若看到 `this.$xxx`、`Vuex`、`element-ui`、`require.context`、`npm run serve`，那是迁移前写法，不要照抄。
> 设计验收标准见 [前端规范第四节](../../rules/前端规范.md#四管理端-ui-设计验收标准)，实现方法与存量差异见 [crmeb-admin-ui](../crmeb-admin-ui/SKILL.md)。

## 选择 UI 形态

- 核心管理功能：`src/pages/{domain}/` + `src/api/` 对应领域 API 文件 + `src/router/modules/{domain}.js`。
- 可安装模块 UI：`src/pages/plugin/{key}/`，其中 `routes.js` 会被 `src/router/routers.js` 用 `import.meta.glob` 自动发现。
- 运行时隔离扩展：目录内放 `ext.json` + `index.umd.js`，由 `src/pages/system/plugin/PageContainer.vue` 承载。采用此模式前阅读 [references/plugin-ui.md](references/plugin-ui.md)。
- 插件零前端接入：优先用 `SchemaPage`（`src/components/SchemaPage/`），不要为一次性需求另写列表页。

请求封装、路由/菜单约定、状态管理、样式和构建命令见 [references/project-map.md](references/project-map.md)。

## 开发流程

1. 检查同一领域最接近的页面，复用其表格、表单、分页、上传和弹窗逻辑；界面任务先按 UI 技能区分设计目标与存量尺寸，不能将旧样式直接当作新页面标准。
2. 先新增或修改 API 函数，再接入页面。从 `@/libs/request` 导入共享客户端；API 路径相对于 `Setting.apiBaseURL`（不含 `/adminapi`）。
3. 通过 `setting.routePre` 添加带 `/admin` 前缀的路由。路由名必须唯一，`meta.auth` 必须与后端 `unique_auth` 对齐。
4. 创建或详情子页面应将 `meta.activeMenu` 设置为包含 `routePre` 的准确列表菜单路径，保持侧边栏高亮。
5. 完整处理加载、空数据、错误、提交中和分页状态，不改变全局布局行为。
6. 用户可见文案跟随周边中文界面现状，不做 i18n 改造。

## 本地不变量

- 使用 Vue 3 `<script setup>` 与现有组件库；不得为单个功能引入新的 UI 框架。
- Store 用 `useXxxStore()`（Pinia setup 风格），不要再 import Vuex（已卸载）。
- 当 `status === 200` 时，Axios 封装返回完整 CRMEB 响应对象；页面代码通常读取 `res.data`。
- 认证头由公共逻辑统一管理，名称为 `Authori-zation`（非标准拼写，后端约定）。不得重复编写 token 注入逻辑。
- 插件 `routes.js` 必须导出路由对象或数组，且顶层条目包含 `component`；新增会被扫描的路由文件后需要**重启 dev server**（`import.meta.glob` 构建期收集）。
- 页面样式应保持局部化（`scoped`）。只有确实需要管理端全局共享时才修改 `theme/*` 或 `styles/*`。
- 不得手工编辑 `dist/` 或 `backend/public/admin/`；应修改源码并通过现有流程构建、部署生成资源。
- 同一文件的多处修改必须逐处顺序编辑，同批并行编辑同一文件会互相覆盖。

## 验证

Node 版本与依赖安装约定见 [admin 端约定](../../scopes/admin.md)。尽量对变更文件执行定向 lint（`npm run eslint`）；修改路由、配置或共享组件时运行 `npm run build`。扩展用 `npm run build:ext -- --ext <key>` 构建，确认 `dist/static/extensions/<key>/` 下只有 `app.umd.js` 和 `app.umd.css`。
