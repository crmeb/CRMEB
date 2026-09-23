# CRMEB 管理后台（frontend/admin）

CRMEB 标准版管理端，基于 **Vue 3.5 + Element Plus 2.9 + Pinia 2 + Vue Router 4 + Vite 5**（纯 JS）构建。

- 页面路由前缀 `base: /admin`；开发服务端口 `1619`
- 开发期 `/adminapi` 请求自动代理到后端 `http://localhost:8011`（见 `vite.config.js`），需先启动后端
- 架构与 UI 规范：[AGENTS.md](./AGENTS.md)（真源在 `.crmeb/skills/crmeb-admin-dev` 与 `.crmeb/skills/crmeb-admin-ui`）

## Node 版本要求

Node.js 最低 **18.20.0**，支持 18.20.x / 20 / 22（终端 `node -v` 确认）。`sass`、`unplugin-auto-import`、`unplugin-vue-components` 已固定为兼容 Node 18 的版本；保留 `package-lock.json`，建议 `npm ci` 安装锁定依赖。

## 快速开始

```bash
# 1. 先启动后端（仓库根）：./run.sh install 或 ./run.sh start
# 2. 启动管理端
cd frontend/admin
npm ci
npm run dev        # 自动打开浏览器 http://localhost:1619
```

默认账号 `admin` / `crmeb.com`（本地 Docker 安装向导完成后生效）。

## 命令一览

| 命令 | 用途 | 产物 |
|------|------|------|
| `npm run dev` | 开发模式（自动开浏览器，`--mode dev`） | — |
| `npm run build` | 主应用生产构建 | `dist/` |
| `npm run build:ext` | 构建插件管理端扩展资源（UMD，可被后台动态加载） | `dist/static/extensions/` |
| `npm run build:all` | 主应用 + 扩展一起构建（发布推荐） | `dist/` |
| `npm run dev:ext` | 扩展构建监听模式（开发插件页面时热更新） | `dist/static/extensions/` |
| `npm run preview` | 本地预览生产构建 | — |
| `npm run eslint` | ESLint 检查并自动修复 src 下 .js/.vue | — |
| `npm run prettier` | Prettier 格式化 | — |

## 调试

- **接口代理**：dev 模式下 `/adminapi` → `http://localhost:8011`（`vite.config.js` 的 `server.proxy` 已内置）；跨主机调试改 [.env.dev](./.env.dev) 的 `VITE_APP_API_URL`
- **H5 商城联动**：管理端预览 / DIY 装修跳转的商城地址由 `VITE_APP_H5_URL` 控制，默认已指向 uni-app H5 dev server `http://localhost:5173`（同时启动 `frontend/uni-app` 的 `npm run dev:h5` 即可联调）
- **插件扩展页面**：dev 模式下扩展源码经 `src/plugin/` 开发加载器惰性加载并支持 HMR；插件 UMD 构建入口是 `scripts/build-extensions.js`，插件资源复用宿主 Vue / Element Plus 环境（不要把 vue/element-plus 打进扩展包）。修改插件页面后发布前必须 `npm run build:ext`，只构建主应用不包含扩展

## 环境变量

通过 `.env` 系列文件管理（Vite `import.meta.env`）：

| 变量 | 说明 |
|------|------|
| `VITE_APP_API_URL` | 接口地址；**留空 = 同源 `/adminapi`**（生产同域部署默认形态） |
| `VITE_APP_H5_URL` | H5 商城地址；留空为同源 |
| `VITE_APP_ENV` / `VITE_APP_TITLE` | 环境标识与页面 title |

- `.env.dev` — dev 命令生效；`.env.production` — build 命令生效
- 同一事实以 `src/setting.js` 的取值为准：`VITE_APP_API_URL` 覆盖 `apiBaseURL`，`VITE_APP_H5_URL` 覆盖 `h5BaseURL`，`routePre` 固定 `/admin`

## 打包与部署

```bash
npm run build:all   # 主应用 + 插件扩展
```

- 产物输出到 `frontend/admin/dist/`；Vite `base` 为 `/admin`，生产资源最终需发布到后端 `public/admin/` 可访问位置（发布流程需显式同步，构建不会自动写入）
- H5 商城产物来自 `frontend/uni-app`（`npm run build:h5`），部署链路见 [帮助文档 · 部署指南](../../help/docs/部署指南.md)

## 目录结构

```
├── index.html                 # HTML 模板
├── package.json               # 依赖与脚本（engines: node >=18.20）
├── vite.config.js             # Vite 配置（base /admin、/adminapi 代理、扩展静态服务、Monaco）
├── scripts/
│   └── build-extensions.js    # 插件扩展 UMD 构建入口
├── .env.dev / .env.production # 环境变量（接口地址等）
└── src/
    ├── api/                   # 接口封装（一个业务域一个文件，如 product.js / order.js）
    ├── config/                # 常量配置
    ├── diy/                   # DIY 装修组件与 API
    ├── filters/               # 全局过滤器
    ├── i18n/                  # 多语言（vue-i18n 9）
    ├── layout/                # 整体布局
    ├── libs/                  # axios 封装、socket、auth、start 等
    ├── plugin/                # 插件扩展加载器（dev HMR / 生产 UMD 动态加载）
    ├── router/                # 路由（routers.js 汇总 + 动态路由注册）
    ├── store/                 # Pinia 状态管理
    ├── styles/ theme/         # 样式与主题（含深色模式，只用 --prev-* 设计令牌）
    ├── utils/                 # 工具（上传、校验、Excel 等）
    ├── App.vue / main.js      # 应用入口
    └── setting.js             # 业务配置（apiBaseURL / routePre / h5BaseURL）
```

## 开发规范

- 页面用卡片分块（搜索卡 + 数据卡），按钮带 `v-db-click`，权限按钮带 `v-auth`，样式只用 `--prev-*` 令牌、禁止硬编码主题色——完整规范见 [.crmeb/skills/crmeb-admin-ui/SKILL.md](../../.crmeb/skills/crmeb-admin-ui/SKILL.md)
- 接口契约变更必须全链路同步（后端路由/控制器 + `src/api` + 菜单权限），清单见 [.crmeb/rules/前端规范.md](../../.crmeb/rules/前端规范.md)
- 提交前：`npm run eslint`；涉及扩展时本地 `npm run build:ext` 验证
- AI 开发入口见根 [AGENTS.md](../../AGENTS.md)

## 页面组织与编码约定（速记）

完整约定真源：[.crmeb/scopes/admin.md](../../.crmeb/scopes/admin.md)「目录结构」与「编码约定」。高频要点：

- **页面**：`src/pages/<模块>/<功能>/` 小驼峰，标准结构 `index.vue + components/ + tableFrom.vue/tableList.vue + handle/`
- **组件**：一组件一目录（公共组件在 `src/components/`，DIY 三件套、SchemaPage 通用页面系统在内）
- **API**：一业务域一文件，`export function userList(data) { return request({...}) }` 模式带 JSDoc，url 相对 baseURL（不含 `/adminapi`）；请求头为约定拼写 `Authori-zation: Bearer <token>`（勿"修正"）
- **状态**：Pinia setup 风格 `useXxxStore()`；**Vuex 已卸载，勿再引入**；`src/libs/axios.js` 是废弃遗留，封装用 `src/libs/request.js`
- **别名**：`@`→src、`_c`→components、`@api`→api、`@diy`→diy；`vue` 别名指向 esm-bundler（保留运行时模板编译，勿改）
- **表单/确认**：`proxy.$modalForm(apiPromise)` 渲染后端 form-create schema；删除确认用 `utils/public.js` 的 `modalSure`；事件总线 `@/utils/bus`（mitt，off 必须传具名函数）
- **权限**：路由 `meta.auth: ['perm']`、按钮 `v-auth="['perm']"`
- **样式**：SCSS；`styles/common.scss` 勿随意改；只用 `--prev-*` 主题令牌（桥接见 `theme/app.scss`）
- **插件路由**：新增 plugin 子目录的 `routes.js` 不参与热更新，需重启 dev server（import.meta.glob 构建期收集）

## 相关项目

- **后端 API**：仓库 `backend/`（ThinkPHP 8，多应用 `/adminapi` / `/api` / `/outapi`）
- **移动端**：仓库 `frontend/uni-app/`（uni-app CLI 工程，H5 / 微信小程序 / 支付宝小程序 / APP）
