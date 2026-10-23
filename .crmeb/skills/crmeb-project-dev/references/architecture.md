# 项目架构

> **强制约定真源在 `.crmeb/rules/`**：依赖铁律见 [架构规范.md](../../../rules/架构规范.md)，分层与命名见 [代码规范.md](../../../rules/代码规范.md)，接口契约同步见 [前端规范.md](../../../rules/前端规范.md)。本文只承载"项目结构现状与代码定位"，不重复规则正文。

## 当前技术栈

| 区域 | 当前实现 |
|---|---|
| 后端 | PHP 8.2-8.4、ThinkPHP 8、MySQL 5.7+（推荐 8.0）、Redis |
| Admin | Vue 3.5、Vue Router 4、Pinia 2、Element Plus 2.9、Vite 5 |
| 用户端 | uni-app CLI（Vite 5）工程，Vue 3 + `<script setup>`、Pinia 2、Sass |
| 本地默认运行环境 | 宿主机 PHP 8.2-8.4 + MySQL 5.7+（推荐 8.0）+ Redis；HTTP 端口 `8011`，数据服务按 `backend/.env` 实际配置 |
| 可选容器环境 | Docker Compose：Nginx `8011`、MySQL `33061`、Redis `63791` |

版本信息以 `backend/composer.json`、`frontend/admin/package.json`、`frontend/uni-app/package.json` 和当前代码为准。管理端与用户端均已完成 Vue 3 迁移，README 中残留的 Vue 2 / Vuex / element-ui / ThinkPHP 6 描述属于过期资料。

## 仓库结构

```text
backend/
  app/             共享业务应用层及全部 HTTP 应用
  crmeb/           框架契约、注册中心、基础设施服务
  modules/         可安装业务扩展
  driver/          支付、短信、上传、邮件等服务商实现
  config/          ThinkPHP 配置
  route/           前端入口与兜底路由
  public/          Web 根目录及已部署前端资源
frontend/
  admin/           管理端 SPA
  uni-app/         H5、小程序和 App 客户端
docker/            本地 Docker 镜像与服务配置
```

ThinkPHP 当前应用入口位于 `backend/app/adminapi` 与 `backend/app/api`。客服和开放平台已作为模块接入对应场景路由，不再使用独立的 kefuapi/outapi 应用目录。Admin 客户端默认请求 `${location.origin}/adminapi`；uni-app 请求封装调用 `${HTTP_REQUEST_URL}/api/...`。

## 依赖方向

依赖方向单向、不可逆：

```text
modules -> app -> crmeb -> driver
```

各层「可调用 / 不可调用」范围、反向依赖禁令、同级模块不得互引、控制器不得实例化 `driver/` 类等**判定标准见 [架构规范.md](../../../rules/架构规范.md)「依赖铁律」**。本节只补充一条定位说明：`crmeb/` 需要应用行为时，在核心定义接口并由 `app/AppService.php` 绑定实现；`AppService.php` 负责共享容器绑定和启动模块发现，它不是具体模块类的目录表。

## 后端分层

普通 app 或 module HTTP 功能遵循：

```text
route -> Controller -> Services -> Dao -> Model
                     -> 核心契约 / 事件 / 管理器
```

分层与注入的**强制要求**（控制器 Services 属性、Services DAO 属性、`@var` PHPDoc）见 [代码规范.md](../../../rules/代码规范.md)。以下是各层职责定位：

- Controller：处理授权上下文、`Request::getMore()`、验证器调用、Service 调用和 JSON 响应。
- Services：处理业务决策、数据聚合、事务、事件派发和 DTO/响应组装。
- DAO：处理可复用的持久化查询和 Model 访问，遵循现有 `BaseDao` 模式。
- Model：处理表元数据、类型转换、关联、访问器和搜索器，继承 `BaseModel`。
- Validate：请求规则和验证场景，放在 app/module 对应验证目录。

## 扩展通信

事件名使用业务字符串（强制规范与全量事件清单见 [事件规范.md](../../../rules/事件规范.md)）：

```php
event('OrderPaySuccess', [$orderNo]);
```

事件 key 不带 `Listener` 后缀。核心监听器注册在 `backend/app/event.php`；模块监听器注册在各模块 `event.php`，由 `App::boot()` 加载。

只有调用方明确允许监听器丰富共享数据时才使用引用载荷。模块结果应放在 `extend.<module>` 这类命名空间 key 下，不得把模块专属字段提升到核心响应顶层（即 [架构规范.md](../../../rules/架构规范.md)「5 个高频违规点」⑤）。

主要注册中心包括：

- `crmeb/trade/product/ProductTypeRegistry`
- `crmeb/trade/mode/TradeModeRegistry`
- `crmeb/trade/order/type/OrderTypeRegistry`
- `crmeb/trade/order/aftersale/AfterSaleRuleRegistry`
- `crmeb/services/notice/NoticeChannelInterface`（渠道适配器，类名登记在渠道表 adapter_class）

## 数据库与安装一致性

- 普通查询通过 DAO/Model API 完成（强制要求见 [代码规范.md](../../../rules/代码规范.md) 第三节）；Installer 和迁移代码负责 DDL/DML 生命周期，因此可以直接使用 `Db` 操作。
- 表结构变更需同步的安装 SQL 文件清单、`crmeb.sql.bak` 禁改、模块自有数据不得写入核心安装 SQL，统一见 [架构规范.md](../../../rules/架构规范.md)「数据库变更规范（强约束）」；表前缀/`utf8mb4`/整数时间戳等字段约定见 [数据库规范.md](../../../rules/数据库规范.md)。

## 契约变更检查清单

接口契约变更（路由、出入参字段）必须同步的全部位置见 [前端规范.md](../../../rules/前端规范.md) 第三节（唯一真源，本文不复制）。当已有移动端或 admin 部署可能落后于后端版本时，优先采用增量契约和兼容性回退。
