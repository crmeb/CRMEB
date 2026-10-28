---
name: crmeb-uniapp-dev
description: 开发和修改 frontend/uni-app 下的 CRMEB 移动端（uni-app 官方 CLI + Vue 3 + script setup + Pinia + Sass），包括 H5/微信小程序/支付宝小程序/APP 页面、API 封装、条件编译、分包及后端驱动的订单/商品动态渲染。适用于用户侧多端客户端任务，不用于管理端或后端。
---

# CRMEB Uni-App 开发

`frontend/uni-app` 是 **uni-app 官方 CLI（Vite 5）工程 + Vue 3 + `<script setup>` + Pinia + Sass**，一套代码发布 H5 / 微信小程序 / 支付宝小程序 / APP。完整工程约定见 `frontend/uni-app/AGENTS.md`。

> 已从 Vue 2 / Vuex / HBuilderX 原生工程迁移。看到 Options API、Vuex、`mixins/`、`this.$util`、"无 npm CLI" 都是迁移前写法，不要照抄。

## 构建与运行

| 平台 | 启动 | 打包 | 产物 |
| --- | --- | --- | --- |
| H5 | `npm run dev:h5` | `npm run build:h5` | `dist/build/h5` |
| 微信小程序 | `npm run dev:mp-weixin` | `npm run build:mp-weixin` | `dist/{dev,build}/mp-weixin` |
| 支付宝小程序 | `npm run dev:mp-alipay` | `npm run build:mp-alipay` | `dist/{dev,build}/mp-alipay` |
| APP | `npm run dev:app` | `npm run build:app` | `dist/{dev,build}/app` |

Node >= 18；全部 `@dcloudio/*` 锁定同一版本，升级必须整体同步。小程序开发者工具导入 `dist/dev/mp-weixin`。接口地址链路（`.env*` → `vite.config.js` define → `src/config/app.js`）见 `frontend/uni-app/AGENTS.md`，改 `.env*` 需重启 dev server。

## 确定改动位置

- 普通页面：在 `src/pages/` 下创建或修改页面，在 `src/pages.json` 注册，并放入合适的分包（主包只保留 tab/guide 页）。
- 独立业务模块页面：复制 `src/pages-modules/_template/` 为 `<snake_case 模块名>/` 即自动注册为分包，免改 `src/pages.json`；约定与边界见 `frontend/uni-app/AGENTS.md`「pages-modules 自动分包注册」。
- 共享行为：优先复用相近组件、composables、`utils/`、`libs/`，不要把平台逻辑复制到多个页面。
- HTTP 接口：在 `src/api/` 下添加领域函数并调用 `@/utils/request.js`；路径不要带 `/api/`，封装层会自动添加。
- 订单、下单、支付、售后：走 `src/dynamic-pages/` 引擎与契约，阅读 [references/dynamic-pages.md](references/dynamic-pages.md)。

页面生命周期、请求/认证、平台条件、路由、样式与验证方式见 [references/multi-platform.md](references/multi-platform.md)。

## 开发流程

1. 明确全部目标平台；修改共享代码前，检查同一流程在 H5 和至少一个小程序端的实现。
2. 新代码用 Vue 3 `<script setup>`：页面生命周期（`onLoad/onShow/onReachBottom` 等）从 `@dcloudio/uni-app` 引入，组件生命周期（`onMounted/onBeforeUnmount`）从 `vue` 引入。
3. 状态用 Pinia（`useAppStore()` 等 + `storeToRefs`），禁止新增 Vuex；主题色/验证码/海报用 `composables/`（`useColorTheme` / `useSendVerifyCode` / `useSharePoster`），禁止新增 mixin。
4. 导航、请求、存储、上传、权限和反馈统一使用 `uni.*` API；多端差异用 `#ifdef H5 / MP-WEIXIN / APP-PLUS` 条件编译保护。
5. 订单相关跳转一律引用 `dynamic-pages/api/routes.js` 的常量；其他新页面必须注册到 `pages.json`，并使用准确路径导航。
6. 后端下发的 `page_config` 区块、按钮、状态文案和扩展数据视为契约；模块可扩展的业务类型不得新增前端硬编码映射。
7. 提供适合触屏和小屏幕的加载、空数据、重试、错误和禁止重复提交状态。

## 本地不变量

- 用 CLI 命令构建；不得声称"HBuilderX 编译、无 npm CLI"。小程序/APP 发布前须在 `.env.production` 配置正式域名。
- 保留模板、脚本、样式和 JSON 中的 `#ifdef`、`#ifndef` 及平台专属代码块。
- 不得硬编码生产域名、App ID、密钥或凭据；`src/config/app.js` 与 `manifest.json` 含环境敏感配置，`app.js` 还被 `utils/request.js` 等大量引用，勿删。
- 请求封装在 `status === 200` 时返回 CRMEB 响应包，并统一处理 token 与登录流程；只有真正公开的接口才能用 `{ noAuth: true }`。
- 命名规范是跨系统硬契约：新 `.vue` 组件 PascalCase、新页面目录 snake_case + `index.vue`；`componentKey`、DIY 组件 `name`、easycom 标签名、`subpackage/diyComponents/` 文件名禁止随手改（详见 `docs/命名规范.md`），改完跑 `npm run lint:naming`。
- 使用 `rpx` 与现有响应式组件，同时处理安全区、自定义导航栏和小程序限制。
- 文案跟随现有 i18n：模板 `$t('中文')`，script 内 `import i18n from '@/utils/lang.js'` 后用 `i18n.t('中文')`。
- 小程序端不支持 `<component :is>`，detail 组件新增分支需在 `dynamic-pages/components/order/detail/SectionDispatcher.vue` 补一行。

## 验证

**日常修改完成后不做构建检查**：不必每次改动都跑 `build:*`，编译错误由对应 dev 命令（如 `dev:h5`）实时暴露，功能验证在已运行的 dev 环境里检查导航、认证过期、空数据/错误状态和条件编译分支。构建仅在交付/发布（H5 发布到 `backend/public`、小程序/APP 出正式包）或用户明确要求时执行。改到命名/契约相关文件时跑 `npm run lint:naming`。若缺少平台凭据或真机环境，必须明确说明限制。
