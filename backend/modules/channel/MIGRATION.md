# 端渠道模块（channel）架构说明

## 一、模块定位

`modules/channel/` 是**端渠道容器模块**，统一管理商城各客户端（公众号、小程序、H5、App）的渠道适配能力，包括：

- **OAuth 登录**：各端的授权登录与用户标识获取
- **消息模板通知**：公众号模板消息、小程序订阅消息等
- **端专属业务**：公众号关注回复/菜单/素材/渠道码、小程序外链/URL Scheme 等

channel 自身是**容器目录**（与 `trade`、`marketing`、`agent` 同级且性质相同），不直接承载业务表，真正的端能力分散在各端子目录中。

## 二、目录结构

```
modules/channel/
├── App.php                  # 容器入口：注册并调度各端 App
├── MIGRATION.md             # 本文档
│
├── wechat_mp/               # 微信公众号端（有数据表 + 后台菜单 + Installer）
│   ├── App.php
│   ├── Installer.php        # ★ 安装/卸载器（自有 9 张表 + 公众号菜单）
│   ├── auth/                # OAuth 登录（storage/Wechat.php）
│   ├── controller/          # admin: Menus/Reply/WechatNewsCategory/WechatQrcode/WechatTemplate
│   ├── dao/ model/ service/ # 关键词回复、素材、行为记录、图文、渠道码、关注用户
│   ├── route/               # admin.php / user.php
│   ├── sdk/                 # WechatService.php
│   └── template/            # 模板消息（storage/Wechat.php）
│
├── wechat_mini/             # 微信小程序端（有数据表 + 后台菜单 + Installer）
│   ├── App.php
│   ├── Installer.php        # ★ 安装/卸载器（自有 1 张表 routine_scheme + 小程序菜单）
│   ├── auth/                # code2Session 登录（storage/MiniProgram.php、TouTiao.php）
│   ├── controller/          # admin: RoutineCI/RoutineScheme/RoutineTemplate
│   ├── dao/ model/ service/ # 小程序外链、CI 上传、订阅消息
│   ├── route/
│   ├── sdk/                 # MiniProgramService.php
│   └── template/            # 订阅消息（storage/Subscribe.php）
│
├── h5/                      # H5 端（仅 OAuth，无数据表，无 Installer）
│   ├── App.php
│   └── auth/
│
└── app/                     # App 端（仅 OAuth，无数据表，无 Installer）
    ├── App.php
    └── auth/
```

> **说明**：
> - `h5`、`app` 两端的 C 端业务接口与全端通用代码（`app/api/controller/v1` 等）耦合，无法独立剥离，故 channel 下仅保留 OAuth 授权骨架，同样**不提供 Installer**。两端消息通知均通过系统消息（站内信）走，不走微信模板消息。
> - 仅 `wechat_mp`、`wechat_mini` 两端有自有数据表与后台菜单，需要安装/卸载（见第四章）。

## 三、容器调度机制

`modules/channel/App.php` 维护各端 `App` 类映射，按端名取实例：

```php
protected static $channels = [
    'wechat_mp'    => \modules\channel\wechat_mp\App::class,
    'wechat_mini'  => \modules\channel\wechat_mini\App::class,
    'h5'           => \modules\channel\h5\App::class,
    'app'          => \modules\channel\app\App::class,
];
```

每个端 `App` 提供 `register()` / `boot()` 钩子，用于注册端内服务绑定与启动路由/监听器。

## 四、安装与卸载

channel 各端复用全局模块安装框架 `crmeb\module\BaseModuleInstaller`，安装器位于端子目录根部 `modules/channel/{端}/Installer.php`。

### 4.1 与其它模块安装器的关系

| 维度 | 产品类型 / 营销活动 | 端渠道（channel） | 独立业务模块（agent） |
|------|--------------------|-------------------|----------------------|
| Installer 位置 | `modules/product/type/{type}/Installer.php`、`modules/activity/{type}/Installer.php` | `modules/channel/{端}/Installer.php` | `modules/{module}/Installer.php` |
| 命名空间 | `modules\product\type\{type}\Installer` 等 | `modules\channel\{端}\Installer` | `modules\agent\Installer` |
| getKey() | `product/hotel`、`activity/seckill` | `channel/wechat_mp`、`channel/wechat_mini` | `agent` |
| getType() | `product_type` / `activity` | `channel` | `business` |
| 调度器扫描 | `modules/product/type/*/Installer.php`、`modules/activity/*/Installer.php` | `modules/channel/*/Installer.php` | `modules/*/Installer.php`（排除容器目录） |

> `crmeb\module\ModuleInstaller::getInstallers()` 已新增对 `modules/channel/*/Installer.php` 的扫描，因此 channel 端会被一并纳入 `module:list`、安装/卸载 API。容器目录 channel 仍保留在独立业务模块扫描的 `excludeDirs` 中，避免对 `modules/channel/App.php` 误判。

### 4.2 已实现的端 Installer

| 端 | getKey | 自有表 | 后台菜单 | 种子数据 |
|----|--------|--------|---------|---------|
| 微信公众号 wechat_mp | `channel/wechat_mp` | `wechat_key`、`wechat_media`、`wechat_message`、`wechat_news_category`、`wechat_qrcode`、`wechat_qrcode_cate`、`wechat_qrcode_record`、`wechat_reply`、`wechat_user` | 公众号 / 微信菜单 / 自动回复 / 图文管理 / 渠道码 | 3 条默认渠道码分类 |
| 微信小程序 wechat_mini | `channel/wechat_mini` | `routine_scheme` | 小程序 / 小程序外链 | 无 |

### 4.3 安装 / 卸载约定

- **建表**：`runMigrate()` 用 `hasTable()` 幂等建表，v6 已存在则跳过，新部署时创建。
- **卸载**：`rollbackMigrate()` 用 `DROP TABLE IF EXISTS` 删除本端自有表；`removeMenus()` 移除对应后台菜单；`cleanData()` 在删表前清理业务数据。
- **核心表保护**：公众号粉丝表 `wechat_user.uid` 关联核心表 `eb_user`，安装/卸载**只操作本端自有表，绝不删除或修改核心 `eb_user`**。

### 4.4 安装 / 卸载入口

通过后台模块管理 API（`app\adminapi\controller\setting\ModuleController`）调用：

```
GET  /adminapi/setting/module/list?type=channel   # 查看端渠道安装状态
POST /adminapi/setting/module/install             # body: module_key=channel/wechat_mp
POST /adminapi/setting/module/uninstall           # body: module_key=channel/wechat_mp
```

`getStatusListByType('channel')` 可单独筛出端渠道模块列表。

## 五、命名空间映射

| 文件 | 命名空间 |
|------|---------|
| modules/channel/App.php | `modules\channel` |
| modules/channel/wechat_mp/**/*.php | `modules\channel\wechat_mp\*` |
| modules/channel/wechat_mini/**/*.php | `modules\channel\wechat_mini\*` |
| modules/channel/h5\|app/**/*.php | `modules\channel\{端}\*` |

## 六、控制器命名规范

> **【强制】** `modules/` 下所有控制器类名与文件名**统一以 `Controller` 结尾**。

- 公众号端（wechat_mp）后台控制器：`MenusController`、`ReplyController`、`WechatNewsCategoryController`、`WechatQrcodeController`、`WechatTemplateController`。
- 小程序端（wechat_mini）后台控制器：`RoutineCIController`、`RoutineSchemeController`、`RoutineTemplateController`。
- OAuth / 消息回调入口控制器 `AuthController`（wechat_mini）、`WechatController`（wechat_mp）本身已带后缀，无需调整。
- 路由引用统一使用 `XxxController::class` 形式。

## 七、变更记录

- **2026-06-30**
  - 移除已废弃的旧 `wechat/` 目录与未落地的 `alipay_mini/` 端，`channel/App.php` 同步清理 `alipay_mini` 引用。
  - 新增 `wechat_mp/Installer.php`、`wechat_mini/Installer.php`，为两端补齐安装/卸载能力。
  - `crmeb\module\ModuleInstaller` 新增 `modules/channel/*/Installer.php` 扫描，端渠道纳入统一安装框架。
  - PC 端业务代码归位：将 `app/api/controller/pc`（7 个控制器）、`app/services/pc`（7 个服务）、`app/api/route/pc.php` 迁入 `modules/channel/pc/`（controller/service/route/user.php），命名空间改为 `modules\channel\pc\*`，路由 URL 前缀 `pc/` 保持不变（由 `app/api/route/v1.php` 的 `glob('modules/channel/*/route/user.php')` 自动加载）。
  - 统一控制器命名规范：wechat_mp、wechat_mini 两端后台控制器类名与文件名均补齐 `Controller` 后缀，`route/admin.php` 引用同步更新。

- **2026-08-22**
  - **PC 端渠道模块彻底删除**：移除 `modules/channel/pc/` 整个目录（controller/service/auth/route/App.php/Installer.php/plugin.json）；`backend/route/route.php` 删除 `home` 分支及 `public/home` 目录判断；`app/api/route/v1.php` 删除 `custom_pc_js` 路由，`app/api/controller/v1/PublicController.php` 删除 `customPcJs()` 方法。
  - 数据库清理：`eb_module_install_log` 删除 `channel/pc` 记录、`eb_system_group` 删除 `pc_home_banner/pc_home_menus/pc_home_links`、`eb_system_route` 删除 `pc/%` 21 条、`eb_system_route_cate` 删除 PC 分类 145/146/147/148。
  - 安装 SQL 清理：`crmeb_data.sql` 删 21 条 PC 路由、`crmeb_data.sql` 删 4 条 PC 分类、`crmeb_data.sql` 删 `custom_pc_js` 配置并修正 4 处含"PC"文案、`crmeb.sql.bak` 基线同步清理（pc_logo/wechat_open_app_id~first_number/custom_pc_js/PC站点配置 tab(75)/pc 控制器与服务/菜单 903/1008/1010/路由 1428-1448/route_category 145-148）。
  - `app/command/ChannelMenuCheck.php` 的 `CHANNELS` 常量移除 `pc`；`modules/README.md`、`modules/channel/README.md` 同步移除 PC 引用。
  - admin 前端 PC 残留清理：删除 `pages/system/group/pc.vue`（PC商城装修页）、`router/modules/setting.js` 的 `system_group_data/pc/:id`、`pc_group_data` 两条路由；`api/setting.js` 删 `pcHomeMenus/pcHomeMenusSave`、`api/system.js` 删 `pcLogoApi/pcLogoSave`；`pages/cms/article/index.vue` 移除"PC端链接"复制项；`pages/system/onlineUpgrade/index.vue` 移除"PC端源码"按钮；`pages/driver/pay/index.vue` 渠道列表移除 `pc`；`filters/index.js` `sourceType` 移除 `0=>PC端`；商品添加（product/plugin-normal）`OtherSetting.vue` 两处 SEO 文案移除"PC端"字样。
  - 删除过期 PC 端 API 文档 `public/web/api.md`（由 tarslib 生成，记录已删除的 `/pc/*` 接口）。
  - 核心 app 层 PC 残留清理：`OrderPayServices::afterPay` 移除支付宝 `platform === 'pc'` 死分支（PlatformDetector 已不再返回 pc）；`UpgradeServices` 移除 PC 端源码包下载/进度/解压/状态检测逻辑（`_pc_package` 缓存键、`pc_package_link`、PC端解压）；`UserServices`/`OutUserServices` 移除 `user_type == 'pc'` 显示映射；`LoginServices`/`UserLoginListener`/`UserLogoutListener` 登录渠道注释移除 pc。保留：`Request::isPc()` 终端检测 helper、支付驱动 `plugin.json` 的 `pc` 平台声明及 `eb_pay_channel` 预置数据（PC 扫码支付为通用支付能力）、`StoreCouponIssueDao::getPcIssueCouponList`（移动端商品详情通用调用，注释已修正）。
