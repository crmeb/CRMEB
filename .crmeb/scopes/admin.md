# AGENTS.md — frontend/admin 管理后台

本文件是 admin 端约定真源。代码路径相对 `frontend/admin/`；先读取仓库根 `.crmeb/AGENTS.md` 及其指定规则与技能。

CRMEB 开源版（KY）管理后台，基于 **Vue 3.5 + element-plus 2.9 + Pinia 2 + vue-router 4**（**Vite 5** 构建，纯 JS 不用 TS）。
与后端 `backend/`（ThinkPHP 8，本地默认通过宿主机端口 8011 提供 `/adminapi`）、移动端 `frontend/uni-app/`（Vue 3）配套。Docker Compose 仅是可选运行方式。

> 2026-09 已从 Vue 2.7 / vue-cli / Vuex / element-ui 整体迁移至 Vue3 + Vite；迁移规则沉淀在 `docs/vue3-migration/`（page-conversion-guide.md、store-mapping.md）。

## 常用命令

- Node 需 `>=18.20.0`，npm >= 7；使用 `npm ci` 安装锁定依赖。为兼容 Node 18，`sass`、`unplugin-auto-import`、`unplugin-vue-components` 固定版本，升级时检查间接依赖并验证启动、主包及扩展构建。
- `npm run dev` — Vite dev server，端口 **1619**（被占用时自动顺延为 1620 等，以终端实际输出为准），自动开浏览器；`/adminapi` 代理到 `http://localhost:8011`（需后端在跑）。
- `npm run build` — 生产构建到 `dist/`（`base: '/admin'`）。
- `npm run build:ext` / `npm run dev:ext` — 扩展 UMD 包（`scripts/build-extensions.js` + `scripts/vite.ext.config.js`，支持 `--ext <key>` / `--watch`）；`build:all` = build + build:ext。
- `npm run eslint` — 检查并自动修复 `src`；`npm run prettier`。
- **无测试套件**；ESLint 即 prettier 推荐配置，无额外规则。

## 接口地址配置

- `src/setting.js` 的 `apiBaseURL`：`import.meta.env.VITE_APP_API_URL` 优先（`.env`/`.env.dev`/`.env.production`），留空回退同源 `${location.origin}/adminapi`。旧版「硬编码 `||` 链短路 env」的坑已修复，切换后端地址改 `.env*` 即可。
- `routePre: '/admin'`，与 Vite `base: '/admin'` 一致（非根路径部署，构建产物挂在 `/admin` 下）。
- `.env.production` 的 `VITE_APP_API_URL` 留空 = 同源 `/adminapi`（同域部署默认形态）；websocket 地址在 `.env` 的 `VITE_APP_WS_*`。

## 目录结构（src/）

```
├── api/            # 接口层：每业务模块一个文件，统一 import request from '@/libs/request'
├── pages/          # 页面：按模块分目录；plugin/ 最大（kefu/seckill/bargain 等，每个插件子目录自带 routes.js）
├── components/     # 公共组件：一组件一目录（uploadPictures 等；SchemaPage/ 为 schema 驱动的通用页面系统：列表/表单/详情）
├── router/         # index.js（createRouter + 全局守卫）+ routers.js（import.meta.glob 聚合）+ modules/
├── store/          # Pinia（defineStore setup 风格），module/ 下各模块；pinia-plugin-persistedstate 持久化
├── libs/           # request.js = 真正的 axios 封装（旧 axios.js/api.request.js 已删除）；echarts.js/monaco.js 为按需加载统一入口，新图表类型/语言在对应文件补充注册
├── utils/          # modalForm.js / public.js / bus.js（mitt 总线）等工具
├── diy/            # DIY/主题模块（别名 @diy）
├── directive/ filters/ i18n/ layout/ styles/ theme/
└── setting.js      # 路由前缀 + 接口地址（见上）
```

别名：`@`→src，`_c`→src/components，`@api`→src/api，`@diy`→src/diy；`vue` 别名指向 `vue/dist/vue.esm-bundler.js`（保留运行时模板编译，勿删）。

## 编码约定

- **全部组件 `<script setup>`**（defineOptions 定义 name；子组件被父级 `$refs` 调用的方法需 `defineExpose`）。
- **API 函数模式**：`export function userList(data) { return request({ url: 'user/user', method: 'get', params: data }); }`，带 `@description` JSDoc；url 相对 baseURL（不含 `/adminapi` 前缀）。
- 请求头是 **`Authori-zation: Bearer <token>`**（非标准拼写，后端约定，勿"修正"）。
- 权限：路由级 `meta.auth: ['perm-string']`，按钮级 `v-auth="['perm-string']"`；守卫在 `src/router/index.js`。
- 新增 plugin 子目录的 `routes.js` 不会被热更新——**需重启 dev server**（`import.meta.glob` 构建期收集时机）。
- 表单大量走 `proxy.$modalForm(apiPromise)`：后端下发 form-create JSON schema 渲染；删除确认用 `utils/public.js` 的 `modalSure`。
- Store：`useXxxStore()` 直接调用（勿再 import vuex，已卸载）；模块映射见 `docs/vue3-migration/store-mapping.md`。
- 事件总线：`@/utils/bus`（mitt）；`off` 必须传与 `on` 相同的具名函数。
- Element Plus：dialog 用 `v-model`；radio/checkbox-group 用 `@change`；date-picker format 用 dayjs 大写 token（`YYYY-MM-DD`）；旧 `el-icon-*` 类名由 `styles/el-icon-compat.css` 的 mask 方案兼容，模板可继续用。
- 富文本统一 `@/components/wangEditor`（**wangeditor v5**；自定义工具栏按钮经 `Boot.registerMenu` 注册，模块级只注册一次；注意 `defaultConfig` 内不能写 `onChange/onCreated` 等回调，须用组件事件）。
- 命名：目录/页面/组件小驼峰；页面结构 `src/pages/<module>/<feature>/` 下 `index.vue + components/ + tableFrom.vue/tableList.vue + handle/`。
- 样式 SCSS；`styles/common.scss` 勿随意改；`--prev-*` 主题变量桥接 Element Plus `--el-*` 在 `src/theme/app.scss`。
- vue-i18n（legacy 模式）已挂载但仅登录/首页翻译过，UI 文案现状基本硬编码中文，跟随现状即可。
- **同一文件的多处修改必须逐处顺序编辑**：同批并行编辑同一文件会互相覆盖、静默丢失改动（已实际踩坑）；不同文件才可并行。
- 版权头注释：**新写文件不添加** CRMEB 版权头；存量文件中的版权头**不再强制保留**，按用户指令处理（2026-09 起 SchemaPage 全目录已应用户要求删除）。
- Prettier：printWidth 120、2 空格、单引号、带分号、trailingComma `all`、LF。

## Git 提交约定

- 遵循全仓库唯一真源 [.crmeb/rules/提交规范.md](../rules/提交规范.md)：提交信息格式（中文描述 + 英文类型前缀）、按主题分批提交与 hunk 拆分、禁止提交本地环境/密钥/临时产物、提交前检查。

## 扩展系统（UMD）

`scripts/build-extensions.js` 扫描 `src/pages/product/type/`、`src/pages/plugin/` 下含 `ext.json` 的目录，逐个经 `scripts/vite.ext.config.js` 打成 UMD（external vue/element-plus → `window.Vue` / `window.ElementPlus`，由 main.js 暴露），输出 `dist/static/extensions/{key}/app.umd.js + css`；运行时由 `src/pages/system/plugin/PageContainer.vue` 承载，dev 走 `dev-extension-loader`（`import.meta.glob` 懒加载源码，仅开发构建）。

**开发扩展插件时改源码即时 HMR 生效，无需执行 `build:ext`**（构建产物只在生产/正式部署时需要，勿因「页面没变化」误判需要构建——先确认访问的 dev server 端口是否为当前实例）。

## 改动前先读

- 本目录 `README.md`：模块命名表 + 目录说明 + 部署方式。
- UI 设计验收标准：[前端规范第四节](../rules/前端规范.md#四管理端-ui-设计验收标准)；实现流程、设计稿冲突裁定与存量差异：[crmeb-admin-ui](../skills/crmeb-admin-ui/SKILL.md)。新页面和 UI 整改按标准验收，存量业务修复不顺带全量改版；跨工具总入口见仓库根 `AGENTS.md`。
- 仓库根 `help/docs/`（[文档索引](../../help/docs/文档索引.md)、[项目架构](../../help/docs/项目架构.md)、[数据库设计](../../help/docs/数据库设计.md)、`api/` 等）；管理端本地 `frontend/admin/docs/vue3-migration/`（迁移手册）。
- 涉及 DIY 可视化编辑器（mobilePage/mobileConfig/mobileConfigRight 与 uni-app 联动）：先看 `.crmeb/skills/crmeb-admin-dev/references/project-map.md`（`@diy` 别名与目录结构）、`.crmeb/skills/crmeb-uniapp-dev/SKILL.md`（DIY 组件跨端命名契约）。
