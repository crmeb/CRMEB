---
name: crmeb-dev
description: CRMEB v8 二开流程导航。需要选择插件、驱动、产品类型等扩展点，或查找用户端商品、购物车、订单及安装部署流程时使用。已明确归属的专项修改优先使用对应开发技能。
---

# CRMEB 二开导航

本技能用于选择扩展点和查找操作手册，不重复定义项目规则或详细接口契约。先从 [项目入口](../../AGENTS.md) 确认任务归属，只读取与本次任务有关的参考文件。

## 选择主技能

| 任务 | 主技能 |
| --- | --- |
| 项目框架、共享服务、API、数据库、跨端功能 | [crmeb-project-dev](../crmeb-project-dev/SKILL.md) |
| 插件、驱动与 modules 业务模块（产品类型、交易模式、营销、渠道、分销） | [crmeb-plugin-dev](../crmeb-plugin-dev/SKILL.md) |
| 订单域开发：生命周期、下单支付、履约、售后退款、交易模式 | [crmeb-order-dev](../crmeb-order-dev/SKILL.md) |
| 管理端页面、组件、接口与路由 | [crmeb-admin-dev](../crmeb-admin-dev/SKILL.md)，界面任务补充 [crmeb-admin-ui](../crmeb-admin-ui/SKILL.md) |
| uni-app 用户侧页面与多端能力 | [crmeb-uniapp-dev](../crmeb-uniapp-dev/SKILL.md) |

按实际修改范围组合技能与参考手册，不要求每次二开加载所有内容。

## 规则与验证

- 架构和扩展边界：[架构规范](../../rules/架构规范.md)、[架构规范详解](../../rules/架构规范详解.md)。
- 编码、事件与持久化：[代码规范](../../rules/代码规范.md)、[事件规范](../../rules/事件规范.md)、[数据库规范](../../rules/数据库规范.md)。
- 订单行为：[订单中台规范](../../rules/订单中台规范.md)；安全、接口、日志、性能等规则按[规则索引](../../AGENTS.md)选择。
- 安装与版本策略以架构规范中的「当前发布阶段」为准，不照搬旧教程的升级流程。
- 运行命令与验证范围见 [验证与运行环境](../crmeb-project-dev/references/verification.md)。安装、迁移、卸载、清理数据库等操作仍需任务授权。

## 扩展点与前端集成

详细教程用于查找实现方法。涉及插件身份、菜单权限、数据归属等约束时，以当前规则、专项技能和已检入代码为准，不从旧例子重新定义规范。

| 场景 | 参考手册 |
| --- | --- |
| 系统插件管理机制（运行时注册、安装/卸载/启用/禁用、清单与身份、路由加载） | [01-插件机制/module-mechanism.md](references/01-插件机制/module-mechanism.md) |
| 插件与生命周期的完整示例（创建/打包） | [crmeb-plugin-dev/references/manifest-and-lifecycle.md](../crmeb-plugin-dev/references/manifest-and-lifecycle.md) |
| 系统驱动机制（管理、注册、调用链、配置存储、生命周期） | [02-驱动机制/driver-mechanism.md](references/02-驱动机制/driver-mechanism.md) |
| 第三方驱动实现与后台集成 | [crmeb-plugin-dev/references/driver-plugin.md](../crmeb-plugin-dev/references/driver-plugin.md) |
| 产品类型、Handler、订单适配器 | [03-系统商品说明/product-type.md](references/03-系统商品说明/product-type.md) |
| 系统订单架构（订单中台代码定位、调用链、排查路径） | [15-系统订单架构/order-architecture.md](references/15-系统订单架构/order-architecture.md) |
| 管理端 UMD 扩展 | [04-前端扩展/backend-frontend.md](references/04-前端扩展/backend-frontend.md) |
| SchemaPage 通用管理页面 | [12-通用页面/schema-page.md](references/12-通用页面/schema-page.md) |

## 用户端业务流程

| 页面或流程 | 参考手册 |
| --- | --- |
| 商品列表 | [user-product-list.md](references/05-用户端开发/user-product-list.md) |
| 商品详情与 DIY 展示 | [user-product-detail.md](references/05-用户端开发/user-product-detail.md) |
| 购物车与结算选择 | [user-cart.md](references/05-用户端开发/user-cart.md) |
| 创建订单 | [user-order-create.md](references/05-用户端开发/user-order-create.md) |
| 订单列表 | [user-order-list.md](references/05-用户端开发/user-order-list.md) |
| 订单详情 | [user-order-detail.md](references/05-用户端开发/user-order-detail.md) |

## 其他专题

| 专题 | 参考手册 |
| --- | --- |
| Docker 安装部署（仅用户明确选择 Docker 或目标实例已在容器中时） | [06-安装部署/install-deploy.md](references/06-安装部署/install-deploy.md) |
| 控制器服务注入 | [controller-services.md](references/07-编码规范/controller-services.md) |
| 路由方法命名整理 | [apiroute.md](references/07-编码规范/apiroute.md) |
| 事件使用 | [event.md](references/07-编码规范/event.md) |
| 路由检查流程 | [route-test.md](references/07-编码规范/route-test.md) |
| 表结构与归属 | [08-数据库/table-structure.md](references/08-数据库/table-structure.md) |
| 前后端目录 | [09-目录架构/directory-structure.md](references/09-目录架构/directory-structure.md) |
| 消息通知 | [10-消息通知/notice.md](references/10-消息通知/notice.md) |
| 分销、积分、经验、异常 | [fenxiao.md](references/11-业务领域/fenxiao.md)、[integral.md](references/11-业务领域/integral.md)、[experience.md](references/11-业务领域/experience.md)、[exceptions.md](references/11-业务领域/exceptions.md) |
| 工具类总览与全局函数 | [utils-overview.md](references/13-工具类/utils-overview.md)、[helper-functions.md](references/13-工具类/helper-functions.md) |
| 表单、异步、网络、基础工具 | [form-builder.md](references/13-工具类/form-builder.md)、[async-tools.md](references/13-工具类/async-tools.md)、[http-tools.md](references/13-工具类/http-tools.md)、[basic-tools.md](references/13-工具类/basic-tools.md) |
| 系统定时任务架构（调度/执行隔离/分发/部署排查） | [14-定时任务/crontab.md](references/14-定时任务/crontab.md) |

项目、架构与模块说明见 [项目文档](../../../help/docs/文档索引.md)，历史资料不作为当前开发指令。
