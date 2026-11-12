# 插件用户端页面：放入 pages-modules 之后怎么用

把目录放进 `frontend/uni-app/src/pages-modules/<标识>/` 并重新编译客户端之后，页面地址是 `/pages-modules/<标识>/...`。后期从代码、装修、消息里打开它，都用该模块 `routes.js` 里的那条路径。

适用两类事：新插件要独立的 H5 / 小程序 / APP 页面；页面已经在 `pages-modules` 里，后续要跳转、让商家选用、或带参数打开。

订单和商品详情优先走已有的 `page_config`、`display_sections`（见 [SKILL.md](../SKILL.md) 开发流程第 10 步）。独立活动页才新建这里的页面。

扫描如何合并进路由、磁盘上的 `pages.json` 为何保持不动，见 [uni-app 端约定](../../../scopes/uni-app.md)「pages-modules 自动分包注册」。目录与文件名见 [命名规范](../../../../frontend/uni-app/docs/命名规范.md) §2.1。插件不得改移动端核心页面、核心 `api` 与 `src/pages.json`，见 [插件开发规范](../../../rules/插件开发规范.md)。本文只写放入之后怎么用。

## 1. 放入后的目录

复制 `frontend/uni-app/src/pages-modules/_template/`，粘成 `src/pages-modules/<标识>/`。目录名用后端模块目录的最后一段：`backend/modules/marketing/lottery` 对应 `lottery`，与命名规范 §2.1 的对照表一致。清单 key（如 `activity_lottery`）留在 `plugin.json` 和 Installer，不作为这个目录名。

下文用 `example` 演示路径形状。仓库里目前没有这个目录，正式名字仍按命名规范对照表取。

```text
src/pages-modules/example/
├── index.vue                      模块首页
├── routes.js                      本模块全部路径，后期跳转只读这里
├── api.js                         可选，本模块接口
├── pages.json                     可选，标题与导航样式
├── record/index.vue               子页面
├── components/PrizeCard.vue       局部组件，不成为页面
└── static/                        图片，不成为页面
```

`_template` 以及任何 `_` 开头的目录不参与编译。页面文件是 snake_case 目录加 `index.vue`。局部组件只放 `components/`，文件名 PascalCase，并在页面里 `import`。easycom 只自动扫描 `src/components/`，扫不到这里。`components/`、`component/`、`static/` 里的 `.vue` 不会被注册成页面。

把 `record.vue` 直接放在模块里会触发 `npm run lint:naming` 报错。新页面写成 `record/index.vue`。

## 2. 页面地址

| 文件 | 打开地址 |
| --- | --- |
| `index.vue` | `/pages-modules/example/index` |
| `record/index.vue` | `/pages-modules/example/record/index` |

地址以 `/` 开头，包含 `pages-modules` 和模块名。查询参数接在后面：`/pages-modules/example/record/index?id=1`。

`routes.js` 里的 `MODULE_NAME` 与目录名相同。模板默认是 `_template`，复制后必须改掉，否则常量会指向不会被编译的 `_template` 目录。每增加一个页面，加一条常量。模板见 `frontend/uni-app/src/pages-modules/_template/routes.js`。

```js
const MODULE_NAME = 'example';

export default {
  index: `/pages-modules/${MODULE_NAME}/index`,
  record: `/pages-modules/${MODULE_NAME}/record/index`,
};
```

## 3. 后期从代码打开

模块根上的 `index.vue` 与 `routes.js` 同级：

```js
import routes from './routes.js';

uni.navigateTo({ url: routes.record + '?id=' + id });
```

子页面 `record/index.vue` 向上一级引用，组件同样显式引入：

```js
import routes from '../routes.js';
import PrizeCard from '../components/PrizeCard.vue';
```

模块外部（其他页面、DIY 组件）用 `@/` 别名，`@` 指向 `src/`：

```js
import exampleRoutes from '@/pages-modules/example/routes.js';

uni.navigateTo({ url: exampleRoutes.index });
```

页面用 `onLoad` 读取参数，参数名与装修链接里的 `param` 一致：

```js
import { onLoad } from '@dcloudio/uni-app';

onLoad((options) => {
  const id = options.id;
});
```

订单中台页面继续用 `frontend/uni-app/src/dynamic-pages/api/routes.js`。插件页的常量留在各模块自己的 `routes.js`。

插件页用 `uni.navigateTo` 打开。底部 tab 仍只指向主包里已有的 tab 页。

## 4. 后期从装修、消息里打开

商家在后台装修的链接选择器里选用。Installer 把同一条路径写入链接表。分类、查重和卸载清理见 [manifest-and-lifecycle.md](manifest-and-lifecycle.md)「DIY 页面链接」。

新页面的 `url` 与 `routes.js` 逐字相同：

```php
$cateId = $this->ensurePageLinkCategory(['商城页面', '营销链接', '示例链接']);
$count += $this->seedPageLinks($cateId, [
    ['name' => '示例首页', 'url' => '/pages-modules/example/index'],
    [
        'name'    => '示例记录',
        'url'     => '/pages-modules/example/record/index',
        'param'   => 'id',
        'example' => '/pages-modules/example/record/index?id=1',
    ],
]);
```

`cleanData()` 里用同一组 url 调用 `cleanPageLinks(['示例链接'], [...])`。分类名用本插件自己的名字。`抽奖链接`、`秒杀链接` 已经由现役模块占用。

订阅消息、分享、支付回跳要打开插件页时，下发同一条路径。路径由插件自己的代码或种子数据产生。

已经在线上的页面保持原路径，不要改成 `/pages-modules/...`，也不要给原路径再加 `/index`：

| 页面 | 现役地址 | 依据 |
| --- | --- | --- |
| 抽奖主页 | `/pages/goods/lottery/grids/index` | `pages.json` 分包 root `pages/goods`，页面 `lottery/grids/index`；`backend/modules/marketing/lottery/Installer.php` |
| 中奖记录 | `/pages/goods/lottery/grids/record` | 页面文件是 `grids/record.vue`，地址没有 `/index` |
| 秒杀列表 | `/pages/activity/goods_seckill/index` | 分包 root `pages/activity`；`backend/modules/trade/seckill/Installer.php` |

## 5. 标题与导航

模块根没有 `pages.json` 时，扫描到的页面 `style` 为空，跟随全局导航样式。

需要标题、下拉刷新或自定义导航时，在模块根新增 `pages.json`（允许注释）：

```json
{
  "pages": [
    { "path": "index", "style": { "navigationBarTitleText": "示例" } },
    {
      "path": "record/index",
      "style": {
        "navigationBarTitleText": "示例记录",
        "enablePullDownRefresh": true
      }
    }
  ]
}
```

`path` 相对模块根，写成 `index`、`record/index`。不写 `pages-modules/example/` 前缀，不带 `.vue`。片段允许注释。模块根一旦有这份片段，该模块只注册片段里列出的页面；没写进去的 `.vue` 不会成为路由。

前缀写进 `path` 时，扫描仍会打印「合并自检：通过」，但地址会变成 `/pages-modules/example/pages-modules/example/index`，页面打不开。打印结果里出现两段 `pages-modules/` 时，把片段 `path` 改回模块内相对路径。

## 6. 页面里请求接口

接口放在模块内 `api.js`，调用 `@/utils/request.js`。传入的路径不含 `/api/`，封装会拼上 `/api/`。需要登录的接口不传 `noAuth: true`。路径字符串必须是本插件 `route/user.php` 里已经注册的路由，下面的 `v2/example/info/` 只说明写法，仓库里没有这条接口。

```js
import request from '@/utils/request.js';

export function getExampleInfo(id) {
  return request.get('v2/example/info/' + id);
}
```

首页用 `import { getExampleInfo } from './api.js'`。`record/index.vue` 用 `import { getExampleInfo } from '../api.js'`。

现役抽奖接口在 `backend/modules/marketing/lottery/route/user.php`，分组是 `v2`，详情为 `lottery/info/:factor/[:lottery_id]`，并且走登录中间件。调用时写成 `v2/lottery/info/${factor}` 或再接活动 id，不传 `noAuth`。响应体见 [接口与响应规范](../../../rules/接口与响应规范.md)。

## 7. 放进去之后怎么确认

页面随客户端源码一起编译。后台安装插件不会把页面送进已经发布的 H5 或小程序；只上传后端插件包时，端上还没有这个目录。后台卸载会按 url 清理链接选择器中的条目。已经保存进装修数据的跳转地址仍是原来的字符串。已发布的小程序要等下一次发版，才会带上或去掉这些页面。

在 `frontend/uni-app` 目录执行：

```bash
node scripts/uni-auto-subpackages.mjs
npm run lint:naming
```

只有 `_template` 时，脚本打印「未发现可注册模块」，合并自检仍然通过。放入 `example` 且含首页和 `record/index.vue`、又没有模块根 `pages.json` 时，输出为：

```text
▸ pages-modules/example（2 页）
    /pages-modules/example/index
    /pages-modules/example/record/index
合并自检：通过（subPackages 共 N 个分包）
```

末尾的 N 包含系统原有分包。「合并自检：通过」只表示 JSON 能合并。还要核对打印出的地址与 `routes.js`、Installer 的 `url` 三者一致，再把链接写入种子。

H5 用 `npm run dev:h5`，或在仓库根执行 `./run.sh uni:dev`。H5 路由是 history、base 为空，浏览器打开 `http://localhost:5173/pages-modules/example/index`（端口以终端输出为准）。

微信小程序把开发者工具导入 `dist/dev/mp-weixin`，编译模式的启动页填 `pages-modules/example/index`（这条不带前导 `/`）。

dev 已经在跑时，增删 `.vue` 会在约 15 秒内触发重编。列表里看不到新页面时，重启一次 dev。日常改页面不必执行 `build:*`。交付小程序包时执行 `npm run build:mp-weixin`，确认 `frontend/uni-app/dist/build/mp-weixin/app.json` 的 `subPackages` 含 `pages-modules/example`。

## 8. 后期使用对照

| 要做的事 | 用什么 |
| --- | --- |
| 从别的页面打开 | `import` 该模块 `routes.js`，再 `uni.navigateTo` |
| 装修里让商家选用 | `seedPageLinks` 的 `url` 等于 `routes.js` 的值 |
| 带商品或活动 id | query 接在路径后；页面 `onLoad(options)` 读取；`param` 写同样的参数名 |
| 改标题、下拉刷新 | 模块根 `pages.json` 片段 |
| 调本插件接口 | 模块内 `api.js`，后端用插件 `route/user.php` |
| 确认路径已注册 | `node scripts/uni-auto-subpackages.mjs` |

每个模块是一个分包。微信小程序单包 2MB、总包 30MB 的限制同样适用。
