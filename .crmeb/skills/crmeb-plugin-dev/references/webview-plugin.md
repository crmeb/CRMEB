# 插件页面：小程序 webview 模式

新插件的移动端页面放在 H5 里，由小程序里的一个固定壳页面用 `<web-view>` 打开。壳页面随小程序发过一次之后，后续插件只安装后端、发布 H5，不用为每个插件重新打包小程序。

核心已经提供、插件不要改的入口：

- 壳页面：`frontend/uni-app/src/pages/annex/plugin_web_view/index.vue`
- 票据：`POST /api/webview/ticket`、`POST /api/webview/ticket/exchange`

页面目录、`routes.js` 和分包扫描仍按 [uniapp-pages.md](uniapp-pages.md)。本文只写 webview 这条打开方式。清单、安装和卸载的通用约束见 [manifest-and-lifecycle.md](manifest-and-lifecycle.md)。

## 1. 什么时候用

用 webview 模式：

- 列表、详情、表单、领券、多页跳转
- 要跟随后台主题色
- 要登录后的用户接口
- 希望插件上线时不再发一版小程序

不用 webview 模式，仍把页面打进小程序分包：

- 微信支付、订阅消息、手机号授权、小程序分享卡片
- 必须调用只存在于小程序端的接口

这些能力要回到壳外面的原生页完成。`#ifdef MP` 在 H5 构建里会被删掉，不要把小程序 API 写进插件页。

已经按分包发布的旧插件（秒杀、抽奖等）保持原路径，不要改成这条壳链接。它们的装修分类仍在「商城页面 / 营销链接」。

## 2. 打开时实际发生的事

```text
装修选中的链接
  /pages/annex/plugin_web_view/index?path=%2Fpages-modules%2F<目录>%2Findex
        │
        ▼
壳页面（小程序原生页，包里只有这一页）
  未登录 → 现有登录页，登录后回到壳页面
  已登录 → POST /api/webview/ticket { path }
        │
        ▼
<web-view src="{H5 域名}/pages-modules/<目录>/index?ticket=...">
        │
        ▼
插件页
  POST /api/webview/ticket/exchange { ticket, path }
  写入本页 token，地址栏去掉 ticket
  再请求本插件自己的接口
        │
        ▼
页内 uni.navigateTo 到详情
  不再签发票据，沿用刚换到的 token
```

H5 开发时，壳页面和插件页在同一个源，web-view 就是一个 iframe。小程序里，web-view 打开的是 `HTTP_REQUEST_URL` 上的已发布 H5，这个域名必须已经配进小程序业务域名。

## 3. 核心已提供的部分

插件不要复制或修改这些文件：

| 能力 | 位置 |
| --- | --- |
| 壳页面 | `frontend/uni-app/src/pages/annex/plugin_web_view/index.vue` |
| 签发、兑换 | `backend/app/api/controller/v1/WebviewTicketController.php` |
| 票据规则 | `backend/app/services/user/WebviewTicketServices.php` |
| 装修弹窗读链接 | `diy/get_page_category` 把链接挂在末级分类的 `links` 上 |
| 未安装提示 | 用户端 miss 路由。插件路由未加载时返回「插件 [名称] 未安装或未启用，请先安装/启用后再访问」 |

票据规则：

- 只有已登录用户能签发，有效期 120 秒，使用一次后作废
- `path` 只能是 `/pages-modules/<目录>/<页面>`，可带查询参数，不能带 `ticket`
- 兑换时的 `path` 必须和签发时完全一致，否则票据作废
- 不能用票据打开商城原有页面或任意网址

## 4. 插件要写的文件

目录名用后端模块路径的最后一段。清单 key 仍是 `marketing_xxx` 这种复合标识，不要拿来当页面目录名。

```text
backend/modules/marketing/example/
├── plugin.json
├── App.php
├── Installer.php
├── route/user.php
└── controller/user/v1/ExampleController.php

frontend/uni-app/src/pages-modules/example/
├── routes.js
├── api.js
├── session.js          换票据，见下文
├── pages.json
├── index.vue
└── detail/index.vue
```

`App.php` 没有自己的注册逻辑时，`register()` / `boot()` 留空。有事件时在 `boot()` 里加载本模块 `event.php`。

## 5. 清单和安装器

`plugin.json` 至少包含 `key`、`name`、`type`、`version`、`path`。`path` 与目录一致，例如 `modules/marketing/example`。`name` 会用在未安装提示和装修分类名上，写成商家能看懂的插件名。

Installer 继承 `crmeb\module\BaseModuleInstaller`，实现 `getKey()`、`getName()`、`getType()`、`getMenuConfig()`、`runMigrate()`、`rollbackMigrate()`。没有后台菜单就让 `getMenuConfig()` 返回 `[]`。没有自有表，迁移方法留空。

装修链接挂在一级分类「插件页面」下，每个插件一个子分类。子分类名用插件名，不要再挂到「营销链接」。

写入的 `url` 不是 `routes.js` 里的页面路径，而是壳页面地址。`path` 参数把插件路径编码一次：

```php
private const LINKS = [
    [
        'name' => '示例首页',
        'url'  => '/pages/annex/plugin_web_view/index?path=%2Fpages-modules%2Fexample%2Findex',
    ],
    [
        'name' => '示例详情',
        'url'  => '/pages/annex/plugin_web_view/index?path=%2Fpages-modules%2Fexample%2Fdetail%2Findex',
    ],
];

protected function seed(): int
{
    $cateId = $this->ensurePluginPageCategory($this->getName());
    $count = $this->seedPageLinks($cateId, self::LINKS);
    // 链接以前写错过分类时，按 url 归到现在的子分类
    $count += $this->claimPageLinks($cateId, array_column(self::LINKS, 'url'));
    return $count;
}

protected function cleanData(): int
{
    return $this->cleanPluginPageLinks([$this->getName()], array_column(self::LINKS, 'url'));
}
```

`seedPageLinks()` 按 url 全局查重，已存在的不重复插入。`claimPageLinks()` 只把这几条 url 挪到本插件分类。`cleanPluginPageLinks()` 删掉这些链接；子分类空了才删分类；「插件页面」下没有其他插件时才删这个一级分类。

`onEnable()` 会再跑一遍 `seed()`。改了链接或分类后，对已安装插件执行一次禁用再启用即可，不必卸掉重装。

详情若还要带业务参数，编码进 `path`，不要另写一个未编码的 `?id=` 在壳地址外面：

```text
/pages/annex/plugin_web_view/index?path=%2Fpages-modules%2Fexample%2Fdetail%2Findex%3Fid%3D1
```

插件页 `onLoad` 从 `options.id` 读取。`path` 里不要自己加 `ticket`。

## 6. 用户接口

路由放在本模块 `route/user.php`，加载器不会补中间件，文件里自己写上跨域、站点开关和登录：

```php
Route::group('v2', function () {
    Route::get('example/profile', [ExampleController::class, 'profile']);
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->middleware(\app\api\middleware\AuthTokenMiddleware::class, true);
```

路径里要有模块目录名 `example`。未安装或未启用时，路由不会加载，miss 会按这一段目录名找到插件并返回可读说明。第一段是 `v2` 时也要能对上，不要只把插件名放在查询参数里。

控制器只返回当前登录用户自己的数据，用户 id 用 `$request->uid()`，不要信任前端传来的 uid。

## 7. 插件页

`routes.js` 只保存插件页自己的路径，不写壳地址：

```js
const MODULE_NAME = 'example';

export default {
  index: `/pages-modules/${MODULE_NAME}/index`,
  detail: `/pages-modules/${MODULE_NAME}/detail/index`,
};
```

模块根 `pages.json` 的 `path` 写 `index`、`detail/index`，不要带 `pages-modules/example/` 前缀。

页面打开时先换票据，再请求业务接口。没有票据时沿用页内已经换到的 token，这样详情页不用再要一次票据。两种都没有，就提示从插件入口打开，不要在插件页里调用 `toLogin()`。登录只由壳页面处理，登录页会按 `login_back_url` 回到壳页面。

```js
import { onLoad } from '@dcloudio/uni-app';
import { applyTheme } from '@/utils/theme.js';
import routes from './routes.js';
import { acceptWebviewTicket } from './session.js';
import { getExampleProfile } from './api.js';

onLoad(async (option) => {
  applyTheme();
  const ready = await acceptWebviewTicket(option.ticket || '', routes.index);
  if (!ready) {
    errorText.value = '请从插件入口打开';
    return;
  }
  const res = await getExampleProfile();
  profile.value = res.data;
});
```

`session.js` 写成：

```js
import { exchangeWebviewTicket } from '@/api/webview.js';
import { useAppStore } from '@/store/modules/app';

export async function acceptWebviewTicket(ticket, path) {
  if (!ticket) {
    return !!useAppStore().token;
  }
  const res = await exchangeWebviewTicket(ticket, path);
  useAppStore().LOGIN({
    token: res.data.token,
    time: res.data.expires_time,
  });
  // #ifdef H5
  if (window.history && window.location.search.indexOf('ticket=') !== -1) {
    const current = new URL(window.location.href);
    current.searchParams.delete('ticket');
    const search = current.searchParams.toString();
    window.history.replaceState({}, '', current.pathname + (search ? `?${search}` : '') + current.hash);
  }
  // #endif
  return true;
}
```

`exchangeWebviewTicket` 所在的 `@/api/webview.js` 由核心提供，兑换请求带 `noAuth: true`。业务接口不带 `noAuth`，路径写成 `v2/example/profile`，不要自己加 `/api/`。

请求失败时展示接口返回的 `msg`。未安装时用户看到的是「插件 [示例插件] 未安装或未启用，请先安装/启用后再访问」，不是「系统错误」。

页内跳转：

```js
uni.navigateTo({ url: routes.detail });
```

主题色用 `applyTheme()` 拉 `theme_info/theme`，样式写 `var(--view-theme)`。webview 读不到小程序本地缓存里的主题色，不要假设 `viewColor` 已经存在。

## 8. 装修里怎么被选到

后台 DIY「选择链接」读取 `diy/get_page_category`。弹窗左侧出现一级分类「插件页面」，点进插件名，右侧是 `seedPageLinks()` 写入的名称。选中后组件里保存的就是壳页面地址。

商家从首页点进去时，客户端打开壳页面，壳页面再打开对应插件页。不需要商家理解 `pages-modules`。

## 9. 发布和小程序入口

第一次使用前，小程序包里必须已经有 `pages/annex/plugin_web_view/index`，H5 也要发布到小程序业务域名。这是一次性的。

之后新增插件：

1. 安装并启用后端插件
2. 发布包含 `pages-modules/<目录>` 的 H5
3. 不用重新提交小程序

开发者工具里直接看壳页面时，启动页填：

```text
pages/annex/plugin_web_view/index
```

启动参数：

```text
path=%2Fpages-modules%2Fexample%2Findex
```

不带前导 `/` 的是启动页，`path` 的值要编码。必须先处于登录态。

H5 本地确认时打开：

```text
http://localhost:5173/pages/annex/plugin_web_view/index?path=%2Fpages-modules%2Fexample%2Findex
```

端口以终端输出为准。接口地址要指向已经装了这个插件的后端。

## 10. 验收

按这个顺序核对，任一步失败都还不能把插件交给商家：

1. 未登录调用 `POST /api/webview/ticket` 得到「请登录」。
2. `path` 写成站外地址、商城原页面，或自己带 `ticket`，签发失败。
3. 已登录签发的票据是 32 位；用别的路径兑换后，原票据不能再用；正确路径只能兑换一次。
4. 兑换得到的 token 能访问本插件需要登录的接口；不带 token 访问得到「请登录」。
5. 停用插件后再访问该接口，返回「插件 [名称] 未安装或未启用」；重新启用后恢复。
6. 装修分类树是「插件页面 / 插件名」，下面能看到首页和详情；「营销链接」下没有这个插件。
7. 卸载后这两条链接和空的插件分类消失；再安装写回去；连续安装第二次不重复插入。
8. 浏览器打开壳链接：未登录进入登录页，登录后回到壳页面；已登录能看到插件内容。地址栏里的 `ticket` 被去掉。
9. 插件页点进详情，仍然是同一用户。标题颜色来自主题色。
10. 不带票据、也没有登录态，直接打开 `/pages-modules/<目录>/index`，页面提示「请从插件入口打开」。
11. 壳链接的 `path` 不是插件页时，页面提示「插件页面路径不正确」，不会去打开那个地址。
