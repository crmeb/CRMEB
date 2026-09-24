# CRMEB v8 电商系统

> 基于 ThinkPHP 8.0 的三端一体电商 CRM 系统：后端 + 管理端（Vue 3）+ 移动端（uni-app），支持 PC / H5 / 小程序 / APP。

**后端运行环境**：PHP 8.2-8.4 + MySQL 8.0 + Redis ｜ **前端构建**：Node >= 18.20
**主开发分支**：`v8.0.dev`，正式发布以 Release / Tag 为准

> 本仓库提供统一的 AI 开发入口（AGENTS.md 标准）。使用 Claude Code、Cursor、Codex、Copilot 等 AI 工具开发本项目前，请让 AI 先读取 [AGENTS.md](AGENTS.md) → [.crmeb/AGENTS.md](.crmeb/AGENTS.md)。详见下文[用 AI 开发本项目](#用-ai-开发本项目)。

---

## 快速开始（宿主机，默认）

```bash
cd backend
composer install

# 配置 backend/.env 并确保 MySQL 8、Redis 已在宿主机运行后启动
php think run --host 127.0.0.1 --port 8011
```

数据库和 Redis 连接以 `backend/.env` 为准，宿主机常见端口为 MySQL `3306`、Redis `6379`。首次启动后访问 **http://localhost:8011** 进入安装向导。

## 前端一键命令

```bash
./run.sh admin:dev    # 管理端开发测试启动（端口 1619）
./run.sh admin:build  # 管理端编译打包并发布到 backend/public/admin
./run.sh uni:dev      # 移动端 H5 开发测试启动
./run.sh uni:build    # 移动端 H5 编译打包并发布到 backend/public
```

也可直接执行 `./run.sh` 打开交互式菜单（前端命令对应编号 11/12/21/22）。

## 可选 Docker Compose

只有明确选择容器环境时使用：

```bash
# 首次且允许清空本地数据时才执行
./run.sh install

# 日常启停和检查
./run.sh start
./run.sh stop
./run.sh restart

./run.sh ps
./run.sh logs
```

### 容器说明

| 服务 | 端口 |
|------|------|
| Nginx（Web 入口） | `8011` |
| MySQL 8.0 | `33061` |
| Redis | `63791` |
| Workerman（WebSocket） | `40001` |

另有 `phpfpm`、`timer`（定时任务）、`queue`（队列消费）为内部服务，无需对外访问。

Dev Containers 配置（`.devcontainer/`）不在仓库内，已在 `.gitignore` 中忽略。需要容器化开发环境者可在仓库根自建，编辑器即可「Reopen in Container」，无需入库。

---

## 首次安装配置

1. 创建数据库，导入 `backend/public/install/crmeb_table.sql`（表结构）与 `backend/public/install/data/` 下的基础数据（推荐直接访问安装向导自动完成）
2. 复制 `backend/.env.example` 为 `backend/.env`，按实际环境修改（文件内有逐项注释）
3. 设置 `backend/runtime`、`backend/public` 目录权限为 777
4. 访问 `http://域名/admin`，默认账号 `admin` / 密码 `crmeb.com`

### .env 配置参考

```ini
APP_DEBUG = true

[APP]
DEFAULT_TIMEZONE = Asia/Shanghai
APP_KEY = your_app_key

[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1
HOSTPORT = 3306
DATABASE = crmeb
USERNAME = root
PASSWORD = root
PREFIX = eb_
CHARSET = utf8mb4

[LANG]
default_lang = zh-cn

[CACHE]
DRIVER = redis
CACHE_PREFIX = cache_xxx:
CACHE_TAG_PREFIX = cache_tag_xxx:

[REDIS]
REDIS_HOSTNAME = 127.0.0.1
PORT = 6379
REDIS_PASSWORD =
SELECT = 0

[QUEUE]
QUEUE_NAME = your_queue_prefix
```

> 完整配置模板与逐项注释见 [backend/.env.example](backend/.env.example)。

---

## 管理端（frontend/admin）

```bash
cd frontend/admin
npm install
npm run dev        # 开发模式，自动打开浏览器
```

- 开发地址 **http://localhost:1619**；Vite 已把 `/adminapi` 代理到 `http://localhost:8011`（见 [vite.config.js](frontend/admin/vite.config.js)），需先启动后端
- 生产构建：`npm run build`，产物部署到后端 `public/admin/`
- 接口地址由 `VITE_APP_API_URL` 控制，留空 = 同源 `/adminapi`

## 移动端（frontend/uni-app）

```bash
cd frontend/uni-app
npm install
npm run dev:h5     # H5 开发模式
```

- H5 开发地址默认 **http://localhost:5173**；接口地址 `UNI_APP_API_URL` 已在 `.env.development` 默认指向 `http://localhost:8011`
- 微信小程序：`npm run dev:mp-weixin`，再用微信开发者工具导入 `dist/dev/mp-weixin`；支付宝小程序用 `dev:mp-alipay`；APP 用 `dev:app`
- 生产构建：`npm run build:h5` / `build:mp-weixin` / `build:app`

---

## 后台服务

### 消息队列
```bash
cd backend
php think queue:listen --queue
```

### 定时任务
（自动收货、库存预警等）
```bash
php think timer start --d    # 启动
php think timer stop         # 停止
php think timer restart      # 重启
```

### 长连接服务（WebSocket）
（H5 聊天、管理员消息通知）
```bash
php think workerman start --d
php think workerman stop
php think workerman restart
```

---

## 项目文档

| 文档 | 说明 |
|------|------|
| [文档索引](help/docs/文档索引.md) | 项目、功能、开发、部署与运维阅读导航 |
| [架构设计](help/docs/项目架构.md) | 系统架构、技术栈、目录结构 |
| [模块与驱动](help/docs/模块与驱动.md) | 默认安装清单与各业务模块功能说明 |
| [管理端 API](help/docs/api/管理端接口.md) | AdminAPI 接口文档 |
| [用户端 API](help/docs/api/用户端接口.md) | 用户端 API 接口文档 |
| [MCP 接口](help/docs/api/MCP接口.md) | 独立 MCP 服务的接入、认证、工具与排障说明 |
| [数据库设计](help/docs/数据库设计.md) | 数据库表结构设计 |
| [开发指南](help/docs/开发指南.md) | 开发环境、规范、流程 |

外部资源：
- [CRMEB 使用手册](https://doc.crmeb.com)
- [ThinkPHP 8.0 开发手册](https://doc.thinkphp.cn/v8_0/)

---

## 多应用结构

URL 前缀自动路由到对应应用：

| 前缀 | 应用 | 说明 |
|------|------|------|
| `/admin` | 管理端静态页面 | Vue 管理后台入口，不是 API 前缀 |
| `/adminapi` | `app/adminapi/` + 模块 `route/admin.php` | 管理后台 API |
| `/api` | `app/api/` + 模块 `route/user.php` | 商城用户 API、模块用户端接口 |

---

## 开发规范

### 命名规范（PSR-2 + PSR-4）

| 元素 | 规范 | 示例 |
|------|------|------|
| 类名 / 接口 / Trait | 大驼峰 | `User`, `UserType` |
| 方法（控制器除外） | 小驼峰 | `getUserName()` |
| 属性 / 变量 | 小驼峰 | `tableName` |
| 控制器方法 | 小写+下划线 | `get_client_ip` |
| 目录 | 小写+下划线 | `store_product/` |
| 常量 / 环境变量 | 大写+下划线 | `APP_DEBUG` |
| 数据表 / 字段 | 小写+下划线，`eb_` 前缀 | `eb_user`, `user_name` |

### CRMEB 核心规范

- **验证**：数据验证放在模块的 `validates/` 目录下
- **JSON 响应**：统一使用 `app('json')->success()` / `app('json')->fail()`
- **错误处理**：抛异常统一控制输出 —— `throw new AuthException('错误信息', 400)`
- **数据库**：统一使用模型操作，**禁止 `Db::table()`**
- **请求参数**：使用 `$request->getMore([...])` 获取表单数据
- **分层职责**：
  - Model 层 —— 仅写搜索条件和关联关系
  - Services 层 —— 数据组合查询、业务逻辑（创建命令：`php make:services api@user/User`）
- **代码风格**：使用 PHP 8 新语法，严格类型声明

---

## 用 AI 开发本项目

本仓库为所有 AI 编码工具提供统一约定：规则、技能、子智能体唯一真源在 [.crmeb/](.crmeb/AGENTS.md)，各工具入口只做指引、不承载正文。

| 工具 | 使用方式 |
|------|------|
| Codex / Cursor / Gemini CLI 等支持 AGENTS.md 标准的工具 | 自动读取根 [AGENTS.md](AGENTS.md)，无需配置 |
| Claude Code | 自动读取 [CLAUDE.md](CLAUDE.md) |
| 支持 llms.txt 的工具 / 网页抓取类 AI | 读取 [llms.txt](llms.txt) |

所有入口最终指向 [.crmeb/AGENTS.md](.crmeb/AGENTS.md)：其中定义了项目硬约束（依赖方向、分层、插件零侵入红线）、16 条开发规则、8 个开发技能与 3 个专项子智能体，AI 按索引按需加载即可获得与内部开发者一致的约定。

### 连接本项目的 MCP 开发工具

内置 MCP 服务（`service_mcp` 模块，默认安装）向任意支持 MCP 的 AI 工具开放实时项目知识：

完整的安装启用、认证、业务工具、写入授权和排障说明见 [MCP 接口文档](help/docs/api/MCP接口.md)。

| 工具 | 用途 |
|------|------|
| `crmeb_dev_docs_search` | 检索开发规则 / 技能手册 / 项目文档（架构铁律、插件规范、事件规范等） |
| `crmeb_dev_route_search` | 按关键词 / 应用检索接口路由定义（定位「接口在哪、控制器是谁」） |
| `crmeb_dev_table_schema` | 查询运行库真实表结构：字段、注释、索引（含已安装插件表） |

接入步骤：

1. 启动后端后，登录管理后台「设置 → MCP 服务」，开启开关并复制访问密钥（appkey）；
2. 在 AI 工具的 MCP 配置中添加（Claude Code 写入 `.mcp.json`，Cursor / Trae 等在各自 MCP 设置中填写）：

```json
{
  "mcpServers": {
    "crmeb": {
      "type": "http",
      "name": "CRMEB 商城",
      "url": "http://localhost:8011/api/mcp",
      "headers": { "X-Mcp-Key": "你的appkey" },
      "timeout": 15,
      "description": "CRMEB 商城管理工具集：查询/管理商城数据（商品、订单、售后、配置等，以账号已授权工具为准）。",
      "disabled": false
    }
  }
}
```

3. Docker 部署无需额外配置（`docker-compose.yml` 已为容器只读挂载文档目录）；文档检索工具定位仓库根依赖 `CRMEB_REPO_ROOT` 环境变量，默认取 `backend/` 的父目录。

工具的返回为检索摘要，正文以仓库文件为准；工具实现位于 `backend/modules/service/mcp/tools/`，按约定新增 `*Tool.php` 即自动注册。

---

## 参与开发

提交流程、检查清单与规范入口见 [CONTRIBUTING.md](CONTRIBUTING.md)；上游项目参阅 [CRMEB](https://github.com/crmeb/CRMEB)。

---

## 版权信息

版权所有 Copyright © 2016~2026 by CRMEB (http://www.crmeb.com)

All rights reserved.

CRMEB® 商标和著作权所有者为西安众邦网络科技有限公司。
