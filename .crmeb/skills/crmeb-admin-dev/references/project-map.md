# Admin 项目结构

## 运行与构建

Admin 位于 `frontend/admin/`，使用 **Vite 5**；Node 版本与依赖安装约定见 [admin 端约定](../../../scopes/admin.md)。

```bash
npm run dev            # Vite dev server，端口 1619（占用时顺延），/adminapi 代理到 localhost:8011
npm run build          # 生产构建到 dist/（base: '/admin'）
npm run build:ext      # 构建 UMD 扩展（--ext <key> 指定单个，--watch 监听）
npm run build:all      # build + build:ext
npm run eslint         # 检查并自动修复 src
npm run prettier
npx eslint src/path/to/file.vue   # 定向检查
```

`src/setting.js` 将 `routePre` 设为 `/admin`，API 基础地址优先取 `VITE_APP_API_URL`（`.env*`），留空回退同源 `${location.origin}/adminapi`。`.env.dev` 指向 `http://localhost:8011/adminapi`。

## 源码目录

```text
src/
  api/                 核心领域 API 客户端
  components/          共享管理端组件（含 SchemaPage 通用页面系统）
  layout/              Admin 外壳布局
  libs/request.js      Axios 实例及 CRMEB 响应处理（axios.js 是废弃遗留，勿用）
  pages/               核心功能页面
  pages/plugin/        可安装模块 UI 和扩展入口
  pages/product/type/  商品类型扩展入口（与 pages/plugin/ 同被扩展构建扫描）
  router/index.js      createRouter + 全局守卫
  router/routers.js    import.meta.glob 聚合路由
  router/modules/      核心静态路由模块
  store/module/        Pinia 模块（defineStore setup 风格）
  styles/              全局/共享样式
  theme/               主题令牌与 Element Plus 覆盖
  setting.js           路由前缀和 API 基础地址
```

使用现有别名：`@` 指向 `src`，`@api` 指向 `src/api`，`_c` 指向 `src/components`，`@diy` 指向 `src/diy`。

## API 客户端契约

从 `@/libs/request` 导入请求封装：

```js
export function listApi(params) {
  return request({
    url: 'domain/items',
    method: 'get',
    params,
  });
}
```

- `Setting.apiBaseURL` 已包含 `/adminapi`，url 写相对路径。
- 请求封装会注入 `Authori-zation: Bearer <token>` 并处理 admin 登录过期。
- CRMEB 成功响应会返回完整对象，业务数据通过 `res.data` 读取。
- 文件上传沿用现有 `file` 选项/模式，由请求封装设置 multipart 请求头。
- 除非接口确实采用不同传输契约，不得创建第二个 Axios 实例。

## 路由、菜单与权限

核心路由模块使用 `setting.routePre`：

```js
const routePre = setting.routePre;

{
  path: routePre + '/domain/item/edit/:id?',
  name: 'domain_itemEdit',
  meta: {
    auth: true,
    title: '编辑',
    activeMenu: routePre + '/domain/item/list',
  },
  component: () => import('@/pages/domain/item/edit'),
}
```

需要权限的路由，其 `meta.auth` 必须与后端菜单 `unique_auth` 一致。后端可见菜单的 `menu_path` 不带 `/admin` 前缀；前端路由和 `activeMenu` 通过 `routePre` 添加该前缀。

插件路由由 `src/router/routers.js` 用 `import.meta.glob('../pages/plugin/**/routes.js')` 发现。新增路由文件后需要重启 dev server。只有包含 `component` 的顶层导出项会被加入路由表。

## 状态管理

Pinia（`defineStore` setup 风格），模块在 `src/store/module/`，用 `pinia-plugin-persistedstate` 持久化。直接 `useXxxStore()`，不要 import Vuex。迁移前后的模块映射见 `frontend/admin/docs/vue3-migration/store-mapping.md`。

## 页面约定

- 使用 Element Plus 以及现有共享表单、表格、上传和选择组件；UI 细则见 `crmeb-admin-ui` 技能。
- 分页参数名和响应结构与同领域最接近的页面保持一致。
- 防止重复提交（`v-db-click` + `:loading`），只有请求成功后才关闭弹窗。
- 数据变更后只刷新最小必要列表或记录。
- 使用 `ElMessage`、现有确认工具和当前错误传播方式，不使用浏览器 alert。
- 页面由后端菜单链接时，保持 route query/params 稳定。

如果某插件的整套 UI 需要随模块移除，应将 API 函数、页面和路由全部保留在对应插件目录中。
