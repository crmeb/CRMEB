# Uni-App 多端开发指南

## 项目结构

`frontend/uni-app/` 是 uni-app 官方 **CLI（Vite 5）工程**，源码在 `src/`，`index.html` 与 `package.json`/`vite.config.js` 在项目根：

```text
index.html            H5 模板（CLI 约定必须在根目录）
package.json          @dcloudio/* 同版本锁定 + 四端 dev/build 脚本
vite.config.js        uni 插件 + UNI_APP_API_URL 编译期注入
src/
  api/                领域 API 函数（order-center.js / product-center.js 为中台接口）
  components/         共享组件和业务组件（mp-html 为富文本组件）
  composables/        组合式函数：useColorTheme / useSendVerifyCode / useSharePoster
  config/app.js       HTTP 地址、请求头、超时、分页默认值（被 utils/request.js 引用，勿删）
  dynamic-pages/      后端驱动的订单/支付/售后引擎（api/ engines/ components/）
  libs/               登录、权限、微信、聊天和 uni 工具
  pages/              页面（主包仅 tab/guide 页，其余在分包）
  store/              Pinia 模块
  subpackage/         分包（diyComponents/ DIY 装修、productSections/ 详情展示块）
  utils/request.js    CRMEB 响应包和认证流程
  main.js             createApp（createSSRApp + Pinia + i18n + 全局组件）
  manifest.json       平台 ID、权限、SDK、原生打包配置
  pages.json          主包页面、分包、tab bar、页面样式
```

不要把 `src/plugin/` 当成后端可安装插件系统，它存放 dayjs、clipboard、图片工具等客户端工具库。

## 请求与认证

API 函数传入相对于 `/api/` 的路径：

```js
import request from '@/utils/request.js';

export function getItem(id) {
  return request.get(`items/${id}`);
}
```

请求封装会拼接 `HTTP_REQUEST_URL + '/api/' + path`，添加 `Authori-zation` 和当前 `Cb-lang`，处理未登录跳转，并在 status 为 200 时返回 CRMEB 响应包。

选项通过第三个参数传入：

```js
request.get('public/resource', params, { noAuth: true });
```

只有后端中间件同样允许匿名访问时才能使用 `noAuth`。只有调用方确实需要原始或非标准响应时才使用 `noVerify`。

## 页面与导航

- 每个页面都必须在 `src/pages.json` 的主包 `pages` 或某个 `subPackages[].pages` 中声明。
- 大型流程放入合适的现有分包，不要无必要扩大主包。
- tab bar 页面使用 `uni.switchTab`，普通页面栈导航使用 `uni.navigateTo`；只有确实需要相应历史行为时才用 `redirectTo` 或 `reLaunch`。
- 已使用 `dynamic-pages/api/routes.js` 的核心流程继续在其中统一维护路由，不得散落硬编码路径。
- 页面生命周期从 `@dcloudio/uni-app` 引入：`onLoad(options)` 读参数，`onShow` 刷新可能变化的动态数据。
- 筛选条件变化时同时重置分页，并用 `loading`、`finished` 防止触底重复请求。

## 平台边界

使用 uni-app 支持的条件编译：

```js
// #ifdef H5
// 浏览器或微信 JS SDK 行为
// #endif

// #ifdef MP-WEIXIN
// 微信小程序行为
// #endif

// #ifdef APP-PLUS
// 原生 App 行为
// #endif
```

模板和样式使用各自对应的注释语法。条件块必须成对、保持平衡。`window`、`document`、DOM 选择器、浏览器存储及浏览器专用库只能出现在 H5 条件块中。

优先使用 `uni.getStorageSync`、`uni.setStorageSync`、`uni.request`、`uni.uploadFile` 等跨平台 API。新增相机、定位、相册、蓝牙等敏感 API 前，检查权限和隐私声明。

## 布局与交互

- 使用 `rpx` 和现有 Flex/布局工具类实现响应式页面。
- 遵循周边页面的自定义导航栏和底部安全区处理。
- 保持触控区域可用，避免固定提交栏遮挡内容。
- 主题/颜色/验证码/海报复用 `composables/`（`useColorTheme` / `useSendVerifyCode` / `useSharePoster`），不要新增 mixin。
- 保持 `$t()` 国际化，不使用会限制翻译语序的字符串拼接方式。
- 小程序端不支持 `<component :is>`；新增 detail 组件需在 `dynamic-pages/components/order/detail/SectionDispatcher.vue` 补分发分支。

## 敏感配置

`src/config/app.js` 和 `src/manifest.json` 包含域名、App ID、平台权限和 SDK 配置。功能开发不得把个人或生产凭据写入仓库。新功能需要额外权限或 SDK 能力时，说明仍需完成的平台侧配置。

## 验证矩阵

用 CLI 构建（`npm run dev:h5` / `dev:mp-weixin` 等）并检查：

| 改动类型 | 最低验证平台 |
|---|---|
| 通用页面/组件 | H5 和需求指定的小程序/App 平台 |
| H5 专属认证/分享 | 普通浏览器 H5；相关时再验证微信内浏览器 |
| 小程序 API | 对应小程序开发者工具（导入 `dist/dev/<platform>`） |
| App 原生 API | 明确受影响的 Android/iOS 端 |
| `pages.json` 或 manifest | 配置块发生变化的全部平台 |

测试未登录、已登录、token 过期、慢请求、空响应、后端错误、返回页面后的刷新及重复提交行为。改到命名/契约相关文件时跑 `npm run lint:naming`。
