<div align="center">

# CRMEB 高品质开源商城系统 v8（PHP 版）

**中台化架构 · 模块化扩展 · AI 原生 · 三端一体**

</div>

<div align="center">
    <a href='https://gitee.com/ZhongBangKeJi/CRMEB/stargazers'>
       <img src='https://gitee.com/ZhongBangKeJi/CRMEB/badge/star.svg?theme=gvp' alt='star'></img>
    </a>
    <img src="https://img.shields.io/badge/Licence-apache2.0-green.svg?style=flat" />
    <img src="https://img.shields.io/badge/Edition-8.0.0-blue.svg" />
</div>

<div align="center" style="font-size: 15px;">
  用心做开源，我们也很需要你的鼓励！右上角Star🌟，等你点亮
</div>

---

### 📝 **项目介绍**

**开源自由**

CRMEB 开源商城系统代码 100% 开源，基于 **Apache-2.0 协议** 免费商用，无任何隐藏费用或功能限制，真正实现"零成本"部署与二次开发自由！

**中台化架构**

v8 全新升级为中台化电商架构：**商品中台**统一管理多产品类型商品，**订单中台**聚合多来源订单的状态与操作，**售后退款中台**提供统一售后单与幂等退款。产品类型（实物、网盘）与交易模式（普通、会员）解耦为两个独立维度，通过注册中心自由组合扩展。

**模块化插件体系**

业务能力按**模块**（`modules/`）与**驱动**（`driver/`）两类可插拔扩展组织：当前内置 7 个官方模块（实物商品、网盘产品、H5 渠道、微信小程序渠道、签到活动、AI Agent、MCP 服务）与 15 个官方驱动（微信支付、云存储、短信、邮件、物流查询、电子面单、电子发票、商品采集、机器人、云打印），新装时默认全部安装，均支持后台可视化安装、卸载、启用、禁用；扩展遵循统一规范，插件二开零侵入。

**AI 原生**

内置 **AI Agent 模块**，提供后台 AI 对话、流式响应与自动化任务；**MCP 服务**通过 `/api/mcp` 端点向 AI 客户端开放系统工具，让人与 AI 协同运营商城。

**多端一体**

一套 uni-app 源码覆盖 **H5、微信小程序、支付宝小程序、APP**，管理后台基于 Vue 3 + Element Plus，前后端分离设计，数据实时互通，助力商家一站式运营多渠道业务。

---

### 🧩 **中台化核心能力**

| 中台 | 说明 |
| --- | --- |
| **商品中台** | 商品主记录与产品类型扩展分离，跨类型共享分类、SKU、属性、评价与统计；产品类型模块化，内置实物商品、网盘产品，支持自定义新产品类型 |
| **交易模式注册中心** | "商品怎么卖"与"商品是什么"解耦：内置普通购买、会员交易两种模式，负责活动定价、下单校验与生命周期钩子，更多玩法按注册中心规范注册扩展 |
| **订单中台** | 多订单类型统一汇总，关联商品快照与状态日志；订单类型适配器统一输出状态树、支付状态、可操作按钮与订单进度 |
| **售后退款中台** | 统一售后申请与商品明细，产品类型通过规则注册中心声明可用售后类型与时限；统一退款单 + 退款副作用幂等记录，支持仅退款、退货退款、换货、维修 |
| **用户与资产** | 以用户为中心聚合地址、标签、等级、会员、余额、积分、佣金、优惠券、发票与推广关系 |

---

### 📦 **模块与驱动生态**

当前内置 7 个模块与 15 个驱动，新装时默认全部安装，均可在后台「扩展管理」中管理（实物商品为基础保护模块，不可卸载）。

**内置模块**

| 模块 | 能力 |
| --- | --- |
| 实物商品 | 通用商品与 SKU、物流/配送/自提履约（基础保护模块） |
| 网盘产品 | 数字资源类商品，支付后自动发放 |
| H5 浏览器 | H5 渠道接入与用户认证 |
| 微信小程序 | 微信小程序渠道接入与用户认证 |
| 签到活动 | 连续/累计签到规则、积分奖励、签到记录 |
| AI Agent | 后台 AI 对话、流式响应、自动化任务与运行记录 |
| MCP 服务 | `POST /api/mcp` 向 AI 客户端开放系统工具，独立密钥认证 |

**内置驱动**

| 类别 | 内置驱动 |
| --- | --- |
| 支付 | 微信支付、微信支付 V3 |
| 云存储 | 本地存储、阿里云 OSS |
| 短信 / 邮件 | 一号通短信、QQ 邮箱 |
| 机器人 | 企业微信机器人、钉钉机器人、飞书机器人 |
| 物流与履约 | 一号通物流查询、一号通电子面单、一号通电子发票、一号通商品采集 |
| 云打印 | 飞鹅云、易联云 |

---

### 🫧 **技术特点**

~~~
关于二开：
1.代码规范：遵循PSR-12命名规范、Restful标准接口、Controller→Services→Dao→Model严格分层、注释齐全、统一响应体{status,msg,data}；
2.权限管理：内置强大灵活的权限管理，菜单即权限规则，可以控制到每一个菜单与操作按钮；
3.模块脚手架：php think make:module 一键生成合规模块骨架（插件、产品类型、驱动、交易模式、营销观察者）；
4.架构守护：php think arch:check 自动扫描三层依赖铁律、跨模块互引、事件与注释规范，可接入CI拦截；
5.系统事件：核心与模块双事件注册表，业务监听器按事件锚点挂载，模块监听随安装状态加载；
6.定时任务：系统与模块双定时任务体系，随模块安装自动注册执行周期；
7.接口管理：后台可视化路由管理、接口导入与在线调试；
8.表单引擎：后端 form-builder PHP 快速生成表单，前端 @form-create 渲染；
9.代码生成：快速生成后台菜单、页面与增删改查；
10.开发文档：help/docs 内置中文项目文档（项目说明、架构、功能、数据库设计、部署、运维），后台可连接 MCP 开发知识服务（文档检索/路由检索/表结构查询）；
11.AI 开发体系：.crmeb 目录内置 AI 编码规则、技能与子智能体，兼容主流 AI 编码工具。
~~~
~~~
性能与拓展：
1.系统安全：JWT认证、角色权限校验、操作日志、系统生产日志、文件校验、数据备份；
2.高性能：Redis缓存、think-queue消息队列、Workerman长连接网关、多种云存储、支持集群部署；
3.多语言：管理端与用户端均支持多语言（vue-i18n），自动识别浏览器语言；
4.驱动扩展：支付、上传、短信、邮件、物流、打印、机器人、采集、发票十大类驱动，同一能力多家供应商自由切换；
5.实时通信：统一 WebSocket 网关（/ws），支撑后台实时通知等场景；
6.一号通：通用第三方扩展，支持短信、物流查询、电子面单、电子发票、商品采集。
~~~

---

### 📖 **系统功能**

<details>
<summary><b>管理端功能总览（点击展开）</b></summary>

- **工作台**：经营数据汇总、订单/用户趋势、交易排行、待处理提醒
- **商品管理**：商品中台、分类、多规格 SKU、属性模板、评价、采集导入导出
- **订单管理**：订单中台统一列表详情、发货/拆单发货、改价备注、物流轨迹、发票
- **售后退款**：售后审核、退货收货、统一退款单、原路退款、幂等副作用
- **用户管理**：用户分组标签等级、资产调整、注销管理
- **会员体系**：会员套餐、会员权益、付费会员
- **营销中心**：优惠券、积分、签到活动
- **页面装修**：商城 DIY 拖拽装修、主题一键换色、底部导航、开屏广告、文章资讯
- **门店配送**：门店自提、店员核销、运费模板、电子面单、云打印
- **财务管理**：资金流水、支付渠道配置、经营统计
- **消息通知**：站内信、短信、邮件、公众号模板消息、小程序订阅消息、机器人
- **微信生态**：公众号 OAuth/菜单/素材/回复、小程序登录/订阅消息
- **扩展管理**：模块与驱动的安装卸载启用禁用、管理员角色权限、配置项、定时任务、系统日志与备份

</details>

<details>
<summary><b>用户端功能总览（点击展开）</b></summary>

- **浏览购物**：DIY 首页、分类搜索、商品详情、购物车、优惠券/积分/余额组合结算
- **订单支付**：多状态订单管理、微信支付/余额支付、物流轨迹、评价
- **售后服务**：动态售后表单、售后进度时间线、退货物流填写
- **个人中心**：余额账单、积分、优惠券、会员权益、收货地址、消息中心
- **营销互动**：签到领积分、优惠券领取

</details>

---

### 🔐 **运行环境**

| **运行环境**     | **要求**                                                                 |
|------------------|--------------------------------------------------------------------------|
| **操作系统**     | Linux / Windows                                                          |
| **WEB 服务**     | Nginx / Apache / IIS                                                     |
| **PHP 版本**     | PHP 8.2 ~ 8.4                                                            |
| **数据库**       | MySQL 8.0（引擎：InnoDB，字符集：utf8mb4，表前缀：eb_）                    |
| **缓存**         | Redis（生产环境必装，消息队列依赖）                                        |
| **管理器**       | Supervisor（用于管理消息队列等常驻进程）                                   |
| **Node.js**      | >= 18（仅前端工程构建需要）                                                |
| **PHP 扩展**     | bcmath、curl、mbstring、json、zip、simplexml、fileinfo（可选）、redis（可选） |
| **需解除禁用函数** | `proc_open`, `pcntl_signal`, `pcntl_signal_dispatch`, `pcntl_fork`, `pcntl_wait`, `pcntl_alarm`（Workerman 与队列需要） |
| **消息队列**     | 运行命令：`php think queue:listen --queue`（使用 Supervisor 守护）          |
| **定时任务**     | 运行命令：`php think timer start --d`（命令行执行）                         |
| **长连接**       | 运行命令：`php think workerman start --d`（命令行执行，默认端口 40001）      |

---

### 📺 **开发环境及使用技术**

### **开发环境：**

| 工具          | 版本               | 下载链接                                                                 |
|--------------|--------------------|--------------------------------------------------------------------------|
| **PHP**      | 8.2 ~ 8.4          | [PHP 官方下载](https://www.php.net/downloads.php)                          |
| **Composer** | 2.x                | [Composer 官网](https://getcomposer.org/)                                 |
| **MySQL**    | 8.0                | [MySQL 官网](https://www.mysql.com/)                                       |
| **Redis**    | 6.0+               | [Redis 官网](https://redis.io/download)                                    |
| **Nginx**    | 1.22+              | [Nginx 官网](http://nginx.org/en/download.html)                            |
| **Node.js**  | 18 / 20+           | [Node.js LTS 版本](https://nodejs.org/en/download/releases/)               |

### 后端技术栈

| **技术**            | **名称**                                                                 | **网址**                                                                 |
|---------------------|-----------------------------------------------------------------------------|--------------------------------------------------------------------------|
| ThinkPHP 8          | 核心框架，多应用模式（adminapi / api / outapi 三入口）                          | https://www.thinkphp.cn/                                                 |
| think-queue         | 消息队列（Redis 驱动）                                                        | https://github.com/top-think/think-queue                                 |
| workerman           | 高性能 Socket 服务，长连接网关与定时任务调度                                    | https://www.workerman.net                                                |
| easywechat 6        | 微信生态开发（公众号/小程序/支付）                                              | https://github.com/w7corp/easywechat                                     |
| php-jwt             | JWT 令牌生成与验证                                                            | https://github.com/firebase/php-jwt                                      |
| form-builder        | 快速构建表单的 PHP 工具                                                       | https://form-create.com                                                  |
| phpspreadsheet      | Excel 导入导出                                                                | https://github.com/PHPOffice/PhpSpreadsheet                              |
| guzzlehttp          | HTTP 客户端库                                                                 | https://guzzle-cn.readthedocs.io/zh-cn/latest                            |
| alipaysdk           | 支付宝 SDK                                                                    | https://github.com/alipay/alipay-easysdk                                 |
| snowflake           | 分布式唯一 ID                                                                 | https://github.com/godruoyi/php-snowflake                                |
| aliyun-oss / qiniu  | 云存储 SDK                                                                    | https://github.com/aliyun/aliyun-oss-php-sdk                             |

### Admin 管理端技术栈

| **技术** | **名称** | **官网** |
| --- | --- | --- |
| vue 3.5 | Vue 框架 | https://vuejs.org/ |
| element-plus 2.9 | UI 框架 | https://element-plus.org/ |
| pinia 2 | 状态管理库 | https://pinia.vuejs.org/ |
| vue-router 4 | 路由管理 | https://router.vuejs.org/ |
| vite 5 | 构建工具 | https://vitejs.dev/ |
| vxe-table 4 | 高级表格组件 | https://vxetable.cn/ |
| echarts 5 | 图表库 | https://echarts.apache.org/ |
| wangeditor 5 | 富文本编辑器 | https://www.wangeditor.com/ |
| monaco-editor | 代码编辑器 | https://microsoft.github.io/monaco-editor/ |
| axios | HTTP 客户端 | https://axios-http.com/ |
| vue-i18n 9 | 多语言 | https://vue-i18n.intlify.dev/ |
| form-create | 动态表单渲染 | https://form-create.com |
| xlsx / exceljs | Excel 处理库 | https://sheetjs.com/ |
| sass | CSS 预处理器 | https://sass-lang.com/ |

### 移动端技术栈

| **技术** | **名称** | **官网** |
| --- | --- | --- |
| uni-app（官方 CLI + Vite 5） | 跨端框架，一套代码编译 H5 / 微信小程序 / 支付宝小程序 / APP | https://uniapp.dcloud.net.cn/ |
| vue 3 | Vue 框架 | https://vuejs.org/ |
| pinia | 状态管理库 | https://pinia.vuejs.org/ |
| vue-i18n | 多语言 | https://vue-i18n.intlify.dev/ |
| sass | CSS 预处理器 | https://sass-lang.com/ |

---

### 🚀 **快速开始**

**宿主机环境（推荐）**

```bash
# 1. 后端
cd backend
composer install
php think run --host 127.0.0.1 --port 8011

# 2. 管理后台（开发服务器默认端口 1619，/adminapi 代理到 8011）
cd frontend/admin
npm ci
npm run dev

# 3. 移动端
cd frontend/uni-app
npm install
npm run dev:h5          # H5；微信小程序：npm run dev:mp-weixin
```

**后台常驻进程**

```bash
cd backend
php think queue:listen --queue      # 消息队列（Supervisor 托管）
php think timer start --d           # 定时任务
php think workerman start --d       # WebSocket 长连接网关
```

**Docker Compose（可选）**

```bash
./run.sh start    # 启动（Nginx 8011 / MySQL 33061 / Redis 63791 / WebSocket 40001）
./run.sh ps       # 查看状态
./run.sh logs     # 查看日志
```

> 默认访问地址：`http://localhost:8011`，管理后台 `http://localhost:8011/admin`。
> 数据库连接等配置以 `backend/.env` 为准；详细部署说明见 [部署指南](help/docs/部署指南.md)。

---

### 📖 **项目文档**

系统内置完整中文文档（`help/docs/`）：

| 文档 | 内容 |
| --- | --- |
| [项目说明](help/docs/项目说明.md) | 项目定位、角色、入口、仓库结构、快速开始 |
| [项目功能](help/docs/项目功能.md) | 管理端/用户端全量功能清单与核心业务流程 |
| [项目架构](help/docs/项目架构.md) | 分层架构、模块与驱动体系、注册中心 |
| [模块与驱动](help/docs/模块与驱动.md) | 内置模块与驱动的清单与说明 |
| [数据库设计](help/docs/数据库设计.md) | 核心数据表结构与关系 |
| [开发指南](help/docs/开发指南.md) | 本地开发、回归脚本、调试方法 |
| [部署指南](help/docs/部署指南.md) | 宿主机与 Docker 部署 |
| [运维手册](help/docs/运维手册.md) | 日常运维、备份恢复、故障排查 |
| [安全与配置](help/docs/安全与配置.md) | 配置项说明与安全基线 |
| [API 文档](help/docs/api/) | 管理端/用户端接口索引 |

🔗 <a href="https://gitee.com/xazbkj/crmebbz/issues" target="_blank">提交反馈</a> | 📩 <a href="https://gitee.com/xazbkj/crmebbz/pulls" target="_blank">提交代码</a>

---

###  📞 **交流互动**

欢迎通过 Gitee Issues 提交 Bug 与建议，通过 Pull Request 参与共建。

---

❤️ 以商业之名，护航开源之心

基于 CRMEB 开源版深度升级，遵循 Apache-2.0 协议开源。

