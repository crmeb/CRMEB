# 前后端目录架构说明（Directory Structure）

> 本文是 `crmeb-dev` 技能的子文档，完整二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。目录结构基于当前分支实测，二开定位代码时先看本节。

## 一、总体结构

```
crmebbz/
├── backend/           PHP 后端（ThinkPHP 8，Docker 容器内运行）
├── frontend/
│   ├── admin/         管理后台（Vue 3 + Element Plus + Vite）
│   └── uni-app/       用户端（Vue 3 + uni-app CLI + Vite）
├── docker/            Docker 配置与容器辅助文件
├── help/
│   └── docs/          中文项目文档与 API 索引
└── run.sh             一键安装/启停脚本
```

## 二、后端 backend/ 目录架构

### 2.1 顶层职责

| 目录 | 职责 | 依赖铁律 |
|------|------|---------|
| `app/` | 业务应用层（统一管理中台） | 可调 `crmeb/`，**禁止调 `modules/`** |
| `modules/` | 业务扩展层（4 大扩展点插件，自包含） | 可调 `crmeb/`、`app/` |
| `crmeb/` | 核心底层（稳定不变） | 可调 `driver/`，**禁止调 app/modules** |
| `driver/` | 第三方驱动底座 | 仅被 `crmeb/services` 调用 |
| `config/` | 全局配置（database/queue/timer/workerman 等） | |
| `public/install/` | 安装器（install.lock、crmeb_table.sql、crmeb_data.sql、index.php） | |
| `route/` | 应用路由（app.php） | |
| `database/` | 驱动建表 SQL（pay_channel_tables.sql 等） | |

### 2.2 app/ 业务应用层

```
app/
├── adminapi/            管理后台入口
│   ├── controller/      控制器（cms/diy/driver/export/file/finance/freight/kefu/marketing/
│   │                    merchant/message/notification/order/personnel/plugin/product/serve/
│   │                    setting/statistic/system/user + 顶层 Auth/Login/Common/Public/
│   │                    Test/Upgrade；会员卡等会员相关在 user/member/ 下）
│   ├── route/           26 个路由文件（order.php/product.php/user.php/driver.php/center.php/
│   │                    plugin.php/module.php/notify.php/message.php 等）
│   └── config/          路由配置
├── api/                 用户端入口
│   ├── controller/v1/   用户端控制器（route/v1.php 路由）
│   └── route/           v1.php（主路由）+ admin.php + center.php（中台）
├── kefuapi/             客服接口入口（route/route.php）
├── outapi/              开放平台接口入口（route/route.php）
├── services/            服务层（继承 BaseService）
│   ├── order/           订单服务（OrderCenterServices/AfterSaleServices 等）
│   ├── product/         商品服务（CenterProductServices 等）
│   ├── user/            用户服务（LoginServices/UserServices）
│   ├── system/          系统服务（admin/config/menus）
│   ├── driver/          驱动服务（pay/sms/email/upload/logistics/express/invoice/printer/bot/copyproduct）
│   ├── message/         消息通知（Notice 门面 + 渠道实现 channel/）
│   ├── pay/             支付服务
│   └── ...
├── dao/                 数据访问层（继承 BaseDao，与 services 同构：order/product/user/system/driver/message...）
├── model/               模型层（与 dao 同构）
├── listener/            系统事件监听器（user/order/pay/product/marketing/wechat/out/crontab）
├── jobs/                异步任务（OrderCreateAfterJob/RefundOrderJob/TakeOrderJob 等）
└── command/             命令行（PluginInstall/Timer/Workerman/NoticeCache 等）
```

**定位链路**：路由 `app/adminapi/route/xxx.php` → 控制器 `app/adminapi/controller/xxx/` → 服务 `app/services/xxx/` → DAO `app/dao/xxx/` → 模型 `app/model/xxx/`。

### 2.3 modules/ 业务扩展层（4 大扩展点落地）

```
modules/
├── trade/                    交易域（交易模式扩展点：决定怎么卖）
│   ├── bargain/              砍价
│   ├── combination/          拼团
│   ├── integral/             积分商城
│   ├── recharge/             充值
│   └── seckill/              秒杀
├── product/                  商品域（产品类型扩展点：决定卖什么）
│   ├── normal/               实物商品（Handler + App + Installer + handler/ 目录，含 NormalOrderAdapter/NormalAfterSaleHandler）
│   ├── virtual/              虚拟商品（网盘）
│   └── cardkey/              卡密商品
├── channel/                  用户渠道扩展点
│   ├── app/  h5/  pc/  wechat_mini/  wechat_mp/
├── marketing/                营销玩法扩展点
│   ├── channel_code/  lottery/  newuser/  signin/
├── agent/                    分销代理（独立顶级模块）
└── drivers/                  插件化驱动（AllinPay/yunxin，runMigrate 部署到 driver/）
```

每个插件自包含：`App.php`（register/boot）+ `Installer.php` + `plugin.json` + `event.php` + `Handler.php` + `controller/` + `service/` + `dao/` + `model/` + `route/`（admin.php/user.php/manage.php）+ `listener/` + `jobs/`（**复数**，队列任务；单数 `job/` 已废弃）+ `handler/`（`trade/` 域放 `{Mode}ActivityHandler`；`product/` 域放 `{Type}OrderAdapter`/`{Type}AfterSaleHandler`）+ `validate/`（按需）。子目录命名全 modules 统一，完整规范见 `backend/modules/README.md`「模块内部目录规范」。

### 2.4 crmeb/ 核心底层

```
crmeb/
├── basic/              BaseDao/BaseModel/BaseService/BaseController/BaseValidate/
│                       BaseJobs/BaseManager/BaseCrontab/BaseStorage（BaseChannel 在 app/services/channel/）
├── trade/              交易领域
│   ├── product/        ProductTypeRegistry/BaseProductTypeHandler/ProductTypeHandlerInterface
│   ├── order/          OrderTypeRegistry/BaseOrderTypeAdapter/OrderTypeAdapterInterface/
│   │                   AbstractOrderCreatePipeline/BaseAfterSaleHandler/AfterSaleRuleRegistry/
│   │                   Button/OrderFlowNode
│   ├── TradeModeRegistry.php / BaseTradeModeHandler.php / DefaultTradeModeHandler.php
│   └── TradeModeHandlerInterface.php
├── services/           核心服务
│   ├── notice/         消息通知核心契约（NoticeChannelInterface/NoticeMessage/AbstractNoticeChannel）
│   ├── pay/  sms/  email/  bot/  upload/  logistics/  invoice/  printer/  yihaotong/
│   └── crud/           代码生成器（Make/Controller/Dao/Model/Route/Service/Validate/View*）
├── plugin/             ModuleRegistry/PluginManager/ExtensionRegistry/ExtensionInfo（模块发现注册）
├── command/            命令行（MakeModuleCommand/Npm；Timer/Workerman 实际在 app/command/）
├── exceptions/         AdminException/ApiException/PayException 等
├── interfaces/  traits/  utils/  `traits/JwtAuthModelTrait`、`utils/Http/JwtAuth/FormBuilder`
```

### 2.5 driver/ 第三方驱动底座

```
driver/
├── pay/          支付宝/微信支付/V3微信/通联（AllinPay）—— 含 App.php + DEVELOPMENT.md
├── sms/          阿里云/腾讯/创蓝/一号通/云信（yunxin 带 Installer）
├── email/        阿里云DM/QQ/自建SMTP
├── upload/       Local/OSS/COS/OBS/Jdoss
├── logistics/  express/  invoice/  printer/  bot/  copyproduct/
```

每个驱动目录 `{ext}/{name}/` 含 `info.php`（元信息）+ `Driver.php`（实现），`{ext}/App.php` 实现 `scanDrivers()`（glob `*/info.php`）、`getDriverClass()`、`getChannels()`。

## 三、前端 frontend/admin（管理后台，Vue 3）

```
admin/src/
├── api/             接口封装（与后端 controller 一一对应：order.js/product.js/user.js/
│                    driver/ 下的 pay.js/smsDriver.js/botDriver.js 等）
├── pages/           页面（account 登录/center 中台/product、driver/pay|smsDriver、
│                    finance、cms、crud 等）
├── components/      通用组件（uploadImg/Pagination/cards 等）
├── layout/          布局（navMenu/header/aside）
├── libs/            工具（request.js/axios.js/socket.js/wechat.js）
├── directive/       auth/permission/draggable 指令
├── filters/  i18n/  config/
└── App.vue  main.js
```

**二开扩展**：插件前端以 UMD 构建产物（`app.umd.js/css`）放插件根下的 `./frontend/dist/`，由 `scripts/build-extensions.js` 构建，`routes.js` 注册新路由（含旧路径兼容）；新建页面的 `activeMenu` 必须放子路由 meta，值为 `routePre + '/xxx/xxx'` 路径格式。

## 四、前端 frontend/uni-app（用户端，H5/小程序/APP）

> uni-app 已迁移为官方 CLI（Vite 5）工程，源码在 `src/` 下（`index.html`、`package.json`、`vite.config.js` 在项目根）；Vue2 遗留的 `mixins/` 已被 `composables/` 取代。

```
uni-app/src/
├── api/             接口封装（order-center.js/product-center.js/order.js/user.js/store.js/
│                    points_mall.js/activity.js）
├── dynamic-pages/   ★ v8.1 动态引擎（核心）
│   ├── engines/     引擎组件：OrderCreateEngine/OrderDetailEngine/OrderListEngine/
│   │                PayStatusEngine/AfterSaleEngine/CashierEngine（共 6 个引擎，无 RefundEngine，
│   │                售后退款走 AfterSaleEngine；产品详情/列表页暂用 DIY 装修系统 +
│   │                display_sections 数据驱动，未独立引擎化）
│   ├── components/  组件库（仅 order/ 域，无独立 product/ 或顶层 common/）
│   │   └── order/   订单组件（detail/ 下 StatusBar/ProductCard/AddressCard/InvoiceCard/
│   │                StoreCard/OrderTimeline 等；create/ 下 DeliverySection/OrderCustomForm 等；
│   │                common/ 下 OrderPriceDetail/StatusBadge 等）
│   └── api/          路由与 action 映射（routes.js/action-map.js）
├── pages/            常规页面
│   ├── center/       ★ v8.1 新版：order/{aftersale,create,detail}、pay/（index + status）
│   └── activity/ goods/ admin/ users/ 等（旧版页面，部分由引擎页替代）
├── components/       全局通用组件（cartList/goodList/home 等）
├── libs/  composables/  store/  config/  App.vue  pages.json  manifest.json
```

**v8.1 动态页面机制**：`pages/center/order/detail` 等页面内嵌 `OrderDetailEngine` 引擎，引擎按后端返回的 `sections` 动态拼装组件（组件未注册自动降级 `InfoList`）；产品详情页基于 DIY 装修系统 + `display_sections` 数据驱动渲染（非引擎化）；选择类组件需实现 `getData()` + `validate()` 供引擎收集提交数据。

## 五、二开定位指南（快速上手）

| 想做什么 | 去哪里找代码 |
|---------|------------|
| 加后台管理页面 | 后端 `app/adminapi/controller/{业务}/` + `route/xxx.php`；前端 `admin/src/pages/` + `admin/src/api/` |
| 加用户端页面 | 后端 `app/api/controller/v1/` + `route/v1.php`；前端 `uni-app/src/pages/` + `uni-app/src/api/` |
| 改商品/订单逻辑 | 后端 `app/services/product|order/`（CenterProductServices/OrderCenterServices）+ `app/dao/` + `app/model/` |
| 新增产品类型 | `modules/product/{type}/`（详参 03-系统商品说明） |
| 新增交易模式 | `modules/trade/{type}/`（详参 01-插件机制） |
| 新增营销插件 | `modules/marketing/{type}/`（详参 01-插件机制） |
| 新增支付/短信等驱动 | `driver/{ext}/` + `app/adminapi/controller/driver/{ext}/`（详参 02-驱动机制） |
| 改商品/订单详情展示 | `uni-app/dynamic-pages/components/product|order/`（组件）+ 后端 sections 配置 |
| 改数据库表 | 核心结构改 `backend/public/install/crmeb_table.sql`；模块结构改对应 `Installer.php::runMigrate()` |
