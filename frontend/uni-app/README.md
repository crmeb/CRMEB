# CRMEB 开源版 — Uni-App 前端

## 项目简介

CRMEB 开源版 v8 的 Uni-App 前端项目，基于 **uni-app（Vue 3 + `<script setup>`）** + **Pinia** + **Vue-i18n v9** 构建，支持一套代码发布到 **H5、微信小程序、支付宝小程序、APP (iOS/Android)** 等多端。

工程为 **uni-app 官方 CLI（Vite）结构**，全部开发、调试、打包均可通过 npm 命令行完成，不依赖 HBuilderX。

## 技术栈

| 技术 | 说明 |
|------|------|
| uni-app | 3.0.0-5020420260813003（CLI / Vite 5，对应 HBuilderX 5.02.04 编译器） |
| Vue | ^3.4 |
| Pinia | ^2.1 |
| Vue-i18n | ^9（legacy 模式，保留 `this.$t` / `i18n.t()` 双写法） |
| Sass | ~1.78（dart-sass） |
| Node | >= 18 |

## 快速开始

```bash
# 0. 先启动后端（仓库根）：./run.sh install 或 ./run.sh start
npm ci                 # 使用镜像加速可追加 --registry=https://registry.npmmirror.com
npm run dev:h5         # H5 开发（浏览器访问 http://localhost:5173）
```

> H5 接口地址已在 [.env.development](./.env.development) 配置为 `http://localhost:8011`（直连后端，后端已内置 CORS）；小程序 / APP 真机联调同样使用该地址，部署环境请改用 `.env.production` 或 `.env.*.local` 覆盖。

## 命令一览（dev 启动 / build 打包）

| 平台 | 启动 | 打包 | 产物目录 |
|------|------|------|----------|
| H5 | `npm run dev:h5` | `npm run build:h5` | `dist/build/h5` |
| 微信小程序 | `npm run dev:mp-weixin` | `npm run build:mp-weixin` | `dist/{dev,build}/mp-weixin` |
| 支付宝小程序 | `npm run dev:mp-alipay` | `npm run build:mp-alipay` | `dist/{dev,build}/mp-alipay` |
| APP | `npm run dev:app` | `npm run build:app` | `dist/{dev,build}/app` |

### 小程序调试

1. `npm run dev:mp-weixin`（或 build）后，用 **微信开发者工具** 导入 `dist/dev/mp-weixin`（或 `dist/build/mp-weixin`）
2. 小程序 appid 在 `src/manifest.json` 的 `mp-weixin.appid` 中配置（编译时会写入产物 `project.config.json`；未配置时为游客 appid，真机预览需填入自己的 appid）
3. 支付宝小程序同理，用支付宝开发者工具导入 `dist/dev/mp-alipay`

### APP 打包说明

- `npm run build:app` 生成本地 APP 资源（`dist/build/app`），可用于 **wgt 热更新包**（将该目录压缩为 zip 改名 .wgt）
- 打 **apk / ipa** 安装包仍需 HBuilderX 云打包或离线 SDK（DCloud 平台限制，CLI 无法生成原生安装包）；HBuilderX 亦可直接导入本 CLI 工程操作

## 目录结构

```
├── index.html               # H5 模板（CLI 工程约定：位于项目根）
├── package.json             # npm 依赖与 dev/build 脚本
├── vite.config.js           # Vite 配置（uni 插件 + UNI_APP_API_URL 注入）
├── .env / .env.development / .env.production   # 环境变量（接口地址）
└── src/                     # 源码根（@/ 别名指向此处）
    ├── api/                 # 接口层（order-center.js / product-center.js 为中台新接口）
    ├── composables/         # 组合式函数：useColorTheme / useSendVerifyCode / useSharePoster
    ├── dynamic-pages/       # 动态页面渲染引擎层（订单中心 page_config 驱动）
    ├── pages/               # 页面（主包仅 5 个 tab/guide 页，其余全部分包）
    ├── subpackage/          # diyComponents 分包（DIY 装修组件 + productSections）
    ├── components/          # 公共组件（mp-html 富文本等）
    ├── plugin/              # 本地第三方库（dayjs / clipboard / jweixin 等）
    ├── static/              # 静态资源
    ├── store/               # Pinia（app / hotWords / indexData）
    ├── libs/ utils/ config/ # 工具与配置
    ├── App.vue  main.js     # 应用入口
    ├── manifest.json        # uni-app 平台配置（appid、各端设置）
    ├── pages.json           # 路由配置（带注释 JSON，10 个分包）
    └── uni.scss             # 全局样式变量
```

架构细节（动态渲染引擎、产品类型扩展、编码规范）见 [AGENTS.md](./AGENTS.md)。

## 环境配置

接口地址通过 `.env` 系列文件管理（变量 `UNI_APP_API_URL`）：

- `.env.development` — 开发环境（dev 命令生效）
- `.env.production` — 生产环境（build 命令生效）
- `.env.local` / `.env.*.local` — 本地覆盖（已 gitignore，不入库）

优先级：`.env.local` > `.env.[mode]` > `.env`；未配置时回退 `src/config/app.js` 内各端默认值（H5 自动取当前域名）。`vite.config.js` 负责把该变量注入 `process.env.UNI_APP_API_URL`。

## 技术说明

- **请求封装**：`src/utils/request.js` 统一处理请求拦截、Token 注入、错误处理
- **状态管理**：Pinia（`src/store/`），禁止新增 Vuex 用法
- **国际化**：`src/utils/lang.js` 多语言支持，语言包由后端下发
- **条件编译**：`#ifdef H5 / MP / MP-WEIXIN / APP-PLUS` 等实现多端差异
- **小程序端动态组件限制**：MP 端不支持 `<component :is>`，订单详情 section 在 MP 端经 `SectionDispatcher` 枚举分发（H5/APP 端仍为动态组件），新增 detail 组件需同步补一行分支

## 相关项目

- **后端 API**：CRMEB 开源版 PHP 后端（ThinkPHP 8，多应用 `/adminapi` / `/api` / `/outapi`），见仓库 `backend/`
- **管理后台**：CRMEB 开源版 Admin 前端（Vue 3 + Element Plus），见仓库 `frontend/admin/`
