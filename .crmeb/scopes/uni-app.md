# AGENTS.md — frontend/uni-app 移动端

本文件是 uni-app 端约定真源。代码路径相对 `frontend/uni-app/`；先读取仓库根 `.crmeb/AGENTS.md` 及其指定规则与技能。

CRMEB 开源版（KY）移动端，基于 **uni-app（Vue 3 + `<script setup>`）+ Pinia + Sass**，一套代码发布 H5 / 微信小程序 / 支付宝小程序 / APP。
与后端 `backend/`（ThinkPHP 6 订单中台/产品中台）、管理端 `frontend/admin/` 配套。

> **Vue3 迁移说明**：本目录已从 Vue 2（Vuex + vue-i18n v8 + jyf-parser）整体迁移至 Vue 3（分支 `feat/vue3-migration`）。
> 迁移要点见文末「Vue3 迁移注记」。

## 构建与运行（CLI 工程）

- **uni-app 官方 CLI（Vite 5）工程**，npm 命令行完成全部开发/打包，不依赖 HBuilderX（HBuilderX 仍可导入本工程作为备选工具链）。
- 依赖版本：全部 `@dcloudio/*` 锁定同一版本（`3.0.0-5020420260813003`，对应 HBuilderX 5.02.04 编译器），升级时必须整体同步替换。
- Node >= 18；`npm install` 后常用命令：

| 平台 | 启动 | 打包 | 产物 |
|------|------|------|------|
| H5 | `npm run dev:h5` | `npm run build:h5` | `dist/build/h5` |
| 微信小程序 | `npm run dev:mp-weixin` | `npm run build:mp-weixin` | `dist/{dev,build}/mp-weixin` |
| 支付宝小程序 | `npm run dev:mp-alipay` | `npm run build:mp-alipay` | `dist/{dev,build}/mp-alipay` |
| APP | `npm run dev:app` | `npm run build:app` | `dist/{dev,build}/app` |

- 小程序：开发者工具导入 `dist/dev/mp-weixin`；appid 配置在 `src/manifest.json` `mp-weixin.appid`。
- APP：`build:app` 产出本地资源（可作 wgt 热更新包）；apk/ipi 需 HBuilderX 云打包或离线 SDK（平台限制）。

### 接口地址配置（.env × src/config/app.js 两层链路）

- **链路**：`.env*` 的 `UNI_APP_API_URL` 由 `vite.config.js` 的 `define` 在**编译期**做字符串替换注入；`src/config/app.js` 读取后导出 `HTTP_REQUEST_URL`，并负责**各端兜底**。产物中即写死该地址，不存在运行时覆盖；修改 `.env*` 须**重启 dev server**（define 不热更新）。
- **取值规则**：`process.env.UNI_APP_API_URL || 兜底值` —— 环境文件配了**非空值**则一律按环境文件执行（`app.js` 里的域名完全不参与）；**留空/未配置**才走兜底（MP/APP：demo 演示地址；H5：自动取当前浏览器域名，即前后端同域部署模式）。
- **环境文件优先级**（Vite `loadEnv`，高覆盖低）：`.env.[mode].local` > `.env.[mode]` > `.env.local` > `.env`；`npm run dev:*` 读 `.env.development`，`npm run build:*` 读 `.env.production`。
- **H5 同域部署注意**：`.env.production` 一旦配了值，H5 生产包不再自动取当前域名；打算与后端同域部署时须将其留空。
- **发版注意**：正式发小程序/APP 前，务必在 `.env.production` 配置正式域名，勿依赖 `app.js` 的 demo 兜底地址。
- **勿删 `app.js`**：该文件除接口地址外还导出 `HEADER / TOKENNAME / LIMIT / TIMEOUT / EXPIRE`（全项目 74 处引用，`utils/request.js` 的请求基址、鉴权头、超时均取自此处）。

## 目录结构（v8 关键变化加粗）

```
├── index.html            # H5 模板（CLI 约定：必须在项目根，不能放 src/）
├── package.json          # @dcloudio/* 同版本锁定 + 四端 dev/build 脚本
├── vite.config.js        # uni 插件 + UNI_APP_API_URL define 注入
├── .env / .env.development / .env.production   # 环境变量
└── src/                  # 源码根（@/ 别名指向 src/）
    ├── api/                  # 接口层。order-center.js / product-center.js 为中台新接口
    ├── composables/          # 【Vue3 新增】组合式函数：useColorTheme / useSendVerifyCode / useSharePoster
    ├── dynamic-pages/        # 【v8 新增】动态页面渲染引擎层（订单中心 page_config 驱动）
    │   ├── api/routes.js     #   全部页面路由常量（跳转一律引用此处，禁止硬编码路径）
    │   ├── api/action-map.js #   订单按钮 action 字符串 → 执行函数 映射表
    │   ├── engines/          #   6 个引擎：OrderList/OrderDetail/OrderCreate/Cashier/AfterSale/PayStatus
    │   └── components/order/ #   基元组件：common/ create/ detail/ 三组
    ├── pages/                # 页面（主包仅 5 个 tab/guide 页，其余全部分包）
    │   ├── center/           # 【v8 新增】订单中心子包：order/create|detail|aftersale、pay/index|status
    │   └── goods/order_list  # 旧路径保留，内部已替换为 OrderListEngine
    ├── subpackage/diyComponents/  # DIY 装修组件分包；【v8 新增】productSections/（详情页展示块）
    ├── components/ store/ utils/ libs/ config/   # 常规公共层（components/mp-html 为富文本组件，勿改内部实现）
    └── pages.json            # 路由配置（带注释 JSON）
```

## 核心架构：dynamic-pages 动态渲染

订单相关页面由**后端下发 `page_config`（sections + extensions）驱动**，前端只负责渲染，新增订单/产品类型时前端尽量零改动。

数据流：引擎调 `api/order-center.js` → 后端返回 `page_config.sections`（每段含 `component`/`key`/`order`/`visible`/`props`）→ 引擎按 `section.component` 从组件表解析组件逐段渲染 → 组件 emit `click/preview/action` 由引擎统一分发。

关键约定：

1. **薄包装页 + 引擎**：`pages/center/*` 下的页面只做 onLoad 取参 + 挂引擎，业务全在 `dynamic-pages/engines/``（全部 `<script setup>`）。引擎是子组件，收不到 onLoad/onShow，需要包装页通过 props/模板 ref 转发（参考 `pages/center/pay/index.vue`：引擎 `defineExpose({ initFromOptions, handlePageShow })`，包装页 `engineRef.value?.xxx()`）。
2. **组件自动注册**：`dynamic-pages/components/order/detail/index.js` 用 `import.meta.glob('./*.vue', { eager: true })` 扫描目录，按组件静态声明的 `componentKey` 注册。新增 detail 组件 = 放 .vue 文件 + 并列普通 `<script>` 块声明 `componentKey`（支持数组、支持 `component:key` 复合键），引擎和注册表零修改。**例外：小程序端不支持 `<component :is>`**，`OrderDetailEngine` 在 MP 端经 `detail/SectionDispatcher.vue` 做 v-if 枚举分发（判断复用注册表），新增 detail 组件需在该分发器补一行分支；H5/APP 端仍是动态组件。
3. **订单按钮**：后端 `order_buttons` 下发 `{action, ...params}`，前端在 `api/action-map.js` 查表执行；新操作只需追加一条映射。
4. **路由常量**：所有跳转引用 `dynamic-pages/api/routes.js`，路径调整只改这一处。

## 产品类型扩展（后端 modules/product/）

商品详情统一走 `GET /api/product/detail/:id`，返回 `display_sections` 展示块（由产品类型/营销监听器注入），驱动 `subpackage/diyComponents/productSections/` 下的 infoCard / storeCard / timeCard / specPreview 组件（经 pageDesign 渲染，仅分包存此一份），内部用 Block* 基元（BlockCalendar 日历、BlockTimeSlots 场次、BlockPriceList 价目、BlockMatrix、BlockChips、BlockPolicy）。

当前产品类型：`normal` 实物、`virtual` 网盘、`cardkey` 卡密（对应后端 `backend/modules/product/`）。详情接口文档见 `docs/产品详情接口文档.md`。

## 编码规范（Vue3）

- **Vue 3 `<script setup>`**（不使用 TS、不使用 Options API 写新代码）。页面生命周期 `onLoad/onShow/onReachBottom/...` 从 `@dcloudio/uni-app` import；组件生命周期 `onMounted/onBeforeUnmount` 从 `vue` import。
- 状态 **Pinia**：`useAppStore()`（`store/modules/app.js`）/ `useIndexDataStore()` / `useHotWordsStore()`，配合 `storeToRefs` 解构。禁止新增 Vuex 用法。
- 主题色用 `composables/useColorTheme.js`（`const { colorStyle } = useColorTheme()`）；验证码倒计时用 `useSendVerifyCode`；商品海报用 `useSharePoster`。禁止新增 mixin。
- i18n：模板 `$t('中文')`；script 内 `import i18n from '@/utils/lang.js'` + `i18n.t('中文')`。
- 多端差异用条件编译 `#ifdef H5 / MP-WEIXIN / APP-PLUS`。
- 请求统一走 `utils/request.js`；缓存 `utils/cache.js`（`import Cache from '@/utils/cache'`）。
- `this.$util → import util from '@/utils/util.js'`；`this.$wechat → #ifdef H5 内 import wechat from '@/libs/wechat'`；WebSocket 单例 `import { socketInstance } from '@/libs/new_chat.js'`。
- 事件总线统一 `uni.$on/uni.$emit`（on/off 必须成对且同一具名函数引用，onMounted/onBeforeUnmount 配置）。
- 小程序组件选项（如 `styleIsolation`）与注册键（如 detail 组件 `componentKey`）用并列普通 `<script>` 块声明（此时组件 name 也放该块，勿用 defineOptions）。

## 命名规范（文件与目录）

完整规范与红线清单见 `docs/命名规范.md`，要点：

- 新 `.vue` 组件一律 **PascalCase**（easycom 组件目录同名、页面局部组件同此）；存量 camelCase 组件冻结不迁移。js 模块（api/store/utils/libs/config）camelCase 或全小写单词，composables 保持 `useXxx.js`；static 图片 kebab-case 全小写带语义前缀。
- 新页面目录 **snake_case** + 入口 `index.vue`。页面路径是 backend PHP（订阅消息/支付回跳/菜单过滤）、安装 SQL `eb_page_link`、admin 默认数据（~97 处）的跨系统硬契约，**禁止随手改名**，确需改须四处同步（见规范 §4）。
- 契约冻结不可改：`componentKey` 值、DIY 组件 `defineOptions name` 与模板 `item.name` 比较字符串、easycom 既有标签名、`subpackage/diyComponents/` 文件名（与 DB `eb_diy` 存量数据对齐）。
- 检查：`npm run lint:naming`（存量违规在 `scripts/naming-baseline.json` 白名单，只拦新增；重建用 `npm run lint:naming:update`）。

## Git 提交约定

- 遵循全仓库唯一真源 [.crmeb/rules/提交规范.md](../rules/提交规范.md)：提交信息格式（中文描述 + 英文类型前缀）、按主题分批提交与 hunk 拆分、禁止提交本地环境/密钥/临时产物（含本端 `manifest.json`、`dist/`）、提交前检查。


## v8 相对 v6 的移除项

- `pages/admin/` 商家管理整包已移除（订单/商品/用户/统计等），`api/admin.js` 一并删除，勿再引用。
- 例外：仅恢复核销页 `pages/admin/order_cancellation/index` + `api/writeoff.js`（接口 `order/order_verific`），路径为 DIY/`page_link`/个人中心菜单硬契约，勿改名。
- Vue2 遗留的 `mixins/`（color/SendVerifyCode/sharePoster/skuSelect/debounce）已被 composables 取代或内联，勿再引用。

## Vue3 迁移注记

- `manifest.json` `vueVersion: "3"`；`main.js` 为 `export function createApp()`（createSSRApp + Pinia + i18n + 全局组件/属性挂载）。
- vue-i18n v9（legacy 模式）：`this.$t` 与 `i18n.t()`（lang.js 已补绑定）均可用；语言包仍由后端下发至 storage。
- 富文本统一 `components/mp-html/`（easycom 自动注册，`<mp-html :content="html" :tag-style="..." />`）。
- 自定义组件 v-model 契约为 `modelValue` + `update:modelValue`（Vue3 标准）。

## HBuilderX → CLI 迁移注记（本分支）

- 工程从 HBuilderX 原生结构（源码在根目录）迁移为官方 CLI 结构：源码整体 `git mv` 至 `src/`，`index.html` 留在项目根（CLI 约定），`@/` 别名指向 `src/`，历史相对路径引用零改动。
- 依赖：`pinia`、`vue-i18n` 在 HBuilderX 由内置提供，CLI 工程需显式安装；`stylus` 为 3 个历史页面（scan_login/login_mobile/feedback）的预处理器依赖。
- 环境变量：uni 的 vite 管线不自动注入 `process.env.UNI_APP_*`（webpack 时代行为），由 `vite.config.js` 的 `define` 显式注入；dev 模式经 vite `env.mjs` 挂到 `globalThis.process.env`。
- 迁移期间修复的编译差异（新管线严格化）：
  - SCSS `url()` 内别名不再支持 webpack 的 `~@/` 前缀（Vite 写法为 `@/`）；
  - dart-sass 拒绝「嵌套规则后再写声明」（mixed-decls），`payment_on_behalf` 已调整顺序；
  - 模板标签必须闭合（`live_list` 的 `<image>` 未闭合）；
  - 模板静态资源根路径 `/pages/...` 在 CLI 下不可解析（vite root 为项目根），改用 `@/pages/...`；
  - rollup 严格校验命名导出：`libs/wechat.js`（纯 H5 模块）补非 H5 空默认导出、`api.js` 的 `switchH5Login` 补 `H5 || APP-PLUS` 分支；
  - `App.vue` 全局样式块需 `lang="scss"`（其中 `@import` 了含 SCSS 注释的 .scss 文件）。
- 已知事项：微信主包体积实测 ~1.56MB（2026-08 `build:mp-weixin` 产物口径，主包 = 总量 − 各分包 root），未超 2MB 上限；此前「~3.2MB」记录疑为开发者工具统计口径差异，若上传报超限再专项优化——可优化点：主包页面（index/user 等）直接引用的 `components/` 组件、`dynamic-pages/` 全量、static 未瘦身（productSections 卡片已收敛至 diyComponents 分包）；CSS 中 `url(/pages/...)` 绝对路径与 p/img/span 标签选择器在小程序端的警告为存量问题。
