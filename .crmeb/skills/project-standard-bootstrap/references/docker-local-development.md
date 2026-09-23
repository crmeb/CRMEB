# Docker 本地开发规范

本规范用于建立可复现的本地容器开发环境。除用户明确要求“仅审计”外，Skill 必须读取当前项目后端实际技术栈、依赖清单和启动入口，自动生成或完善根 `docker-compose.yml`；不要把示例端口、镜像或命令当成项目事实。

## 目标结构

```text
repository/
  docker-compose.yml              本地开发统一 Compose 入口
  run.sh                          macOS/Linux 标准命令入口
  run.ps1                         Windows 标准命令入口（需要支持时）
  .env.docker.example             Compose 变量样例，不含真实秘密
  backend/                        后端源码、依赖清单、迁移
  frontend/
    <app-name>/                   可独立运行的前端应用
  docker/                         只保存 Docker 配置和脚本，不保存运行数据
    backend/Dockerfile.dev        后端开发镜像
    frontend/<app>/Dockerfile.dev 前端开发镜像（确有容器化需要时）
    nginx/                        本地反向代理配置（按需）
    scripts/                      容器入口和健康检查脚本（按需）
  help/docs/docker-development.md 使用者文档
```

根 `docker-compose.yml` 和 `run.sh` 是所有项目统一入口，后端统一映射到 `backend/`，前端统一映射到 `frontend/<应用名>/`。旧项目尚未完成目录迁移时，先在 `.crmeb/project.json` 记录兼容路径并制定迁移计划，不得为了形式统一直接移动大量源码。生产镜像和部署编排应与本地开发配置分开，避免源码挂载、调试扩展和开发端口进入生产。

## 自动分析与生成

先运行环境分析器，核对后端运行时和版本、包管理器、启动命令、全部前端、数据库/缓存/队列、端口、worker/scheduler/WebSocket、现有 Docker 文件和配置样例。只读取非敏感样例，不读取或复制真实 `.env`。

生成器分两步运行：默认只预览；`--write-missing` 只创建缺失文件。已有 `docker-compose.yml`、`run.sh` 或 `docker/` 配置必须保留并语义合并。机械脚本无法可靠识别运行时、启动命令或服务时应停止猜测，但 AI 必须继续读取代码入口、技术栈 Profile、CI 和现有脚本完成生成；除非项目本身缺少必要事实且已经形成明确阻塞，否则不得把“待确认”当作最终结果。禁止用 `sleep infinity`、无效端口或虚假的 healthy 状态掩盖缺口。

## 后端环境到 Compose 的强制映射

生成 `docker-compose.yml` 前必须建立证据映射：

| 后端事实 | Compose/Docker 输出 |
| --- | --- |
| 语言及版本约束 | 固定到兼容的基础镜像版本和构建参数 |
| manifest、lockfile、系统扩展 | Dockerfile 的依赖安装，依赖卷及重建条件 |
| Web/CLI 启动入口 | `command`、工作目录、监听地址、应用端口和健康检查 |
| 数据库、缓存、队列、对象存储 | 对应服务、版本、健康检查、网络、非敏感变量和具名卷 |
| worker、scheduler、WebSocket | 独立 Compose 服务，复用后端镜像和源码但使用真实命令 |
| migration、seed、初始化脚本 | 首次启动说明和可重复执行的初始化入口，不塞进普通重启 |
| 上传、日志和持久化目录 | 明确开发卷、权限和保留策略，不提交运行数据 |

PHP 必须继续核对 Composer `ext-*`、FPM/CLI 和 Web 入口；Node 必须核对 Node 版本、包管理器、lockfile 与真实 script；Python 必须核对依赖管理器和 ASGI/WSGI 入口；Java 必须核对 JDK、Maven/Gradle 与启动产物；Go 必须核对 Go 版本、构建入口和运行参数。框架 Profile 只能帮助发现事实，不能替代项目分析。

## Compose 必备信息

根据项目实际需要定义服务，常见职责如下：

| 服务 | 代码/数据来源 | 必须说明 |
| --- | --- | --- |
| `backend` | `backend/` 源码挂载 | 工作目录、启动命令、应用端口、健康检查 |
| `frontend-<app>` | `frontend/<app>/` 源码挂载 | 包管理器、开发命令、HMR 端口、API 地址 |
| `db` | 命名数据卷 | 数据库版本、库名、健康检查、初始化/迁移方式 |
| `redis` | 命名数据卷或临时卷 | 版本、端口、持久化策略 |
| `proxy` | `docker/nginx/` 等配置 | 对外入口、转发目标、静态文件来源 |
| worker/scheduler | 与后端相同源码或镜像 | 命令、依赖服务、重启策略、日志位置 |

必须满足以下约束：

1. 使用明确的镜像版本，不使用不可追溯的 `latest` 作为长期基线。
2. `docker-compose.yml` 中的 build context、Dockerfile、working directory 与 `backend/`、`frontend/` 目录一致。
3. 开发容器挂载本地源码，实现代码修改立即生效；不要把宿主机的依赖目录直接覆盖容器依赖。
4. 依赖目录使用容器命名卷，例如 `/workspace/backend/vendor`、`/workspace/frontend/admin/node_modules`，或使用经验证的宿主机策略。
5. 数据库、缓存等有状态服务使用具名卷；清理卷是破坏性操作，文档必须单独警告。
6. 密码和令牌通过未入库的 `.env` 注入，仓库只提供 `.env.example` 与非敏感默认值。
7. 服务依赖优先结合 `healthcheck` 判断就绪；`depends_on` 的启动顺序不等于依赖服务已经可用。
8. 应用监听容器内 `0.0.0.0`，端口映射可由环境变量覆盖，并在文档中列出默认值。
9. macOS/Windows 说明文件监听、权限和卷性能差异；Linux 说明 UID/GID 映射策略。
10. 本地 Compose 不复用生产数据库、生产凭据或生产网络。

## 源码挂载模式

Compose 配置应表达以下关系，具体镜像和命令从项目事实生成：

```yaml
services:
  backend:
    build:
      context: .
      dockerfile: docker/backend/Dockerfile.dev
    working_dir: /workspace/backend
    volumes:
      - ./backend:/workspace/backend
      - backend-dependencies:/workspace/backend/vendor
    env_file:
      - .env
    depends_on:
      db:
        condition: service_healthy

  frontend-admin:
    build:
      context: .
      dockerfile: docker/frontend/admin/Dockerfile.dev
    working_dir: /workspace/frontend/admin
    volumes:
      - ./frontend/admin:/workspace/frontend/admin
      - frontend-admin-dependencies:/workspace/frontend/admin/node_modules
    environment:
      FILE_WATCH_POLLING: "false"

volumes:
  backend-dependencies:
  frontend-admin-dependencies:
  database-data:
```

上例只展示目录和卷策略。生成目标项目配置时必须替换为真实的依赖目录、环境变量和服务命令；非 PHP 后端不应保留 `vendor`，非 Node 前端不应保留 `node_modules`。仅在原生文件监听失效时才为对应平台启用轮询，避免无谓 CPU 消耗。

## 必须生成的本地运行文档

`help/docs/docker-development.md` 至少包含以下章节。

### 1. 适用范围

- Docker 是默认开发方式、可选兼容方式还是仅用于某些依赖；
- 已验证的 Docker Engine、Compose 与 CPU 架构；
- 与宿主机直接运行方式的区别和不能覆盖的场景。

### 2. 服务与端口

列出服务名、容器端口、宿主机默认端口、健康检查、数据卷和用途。明确端口由哪个环境变量覆盖，避免只写一个可能冲突的固定值。

### 3. 首次启动

按可执行顺序记录：

1. 检查 Docker 与 Compose 版本；
2. 从 `.env.example` 创建本地 `.env`；
3. 构建开发镜像；
4. 启动基础设施并等待健康；
5. 安装后端和前端依赖；
6. 创建数据库、执行迁移或导入最小数据；
7. 启动应用、worker 和 scheduler；
8. 通过健康检查、页面或最小 API 验证成功。

每一步都要写明执行目录和成功信号。只有实际存在且验证过的命令才能进入文档。

### 4. 日常开发

文档应给出项目真实对应命令：

```bash
docker compose up -d
docker compose ps
docker compose logs -f backend
docker compose exec backend <project-command>
docker compose exec frontend-admin <project-command>
docker compose stop
docker compose down
```

同时说明：

- 修改后端和前端源码是否立即生效；
- 新增依赖后在容器内执行什么命令、何时需要重建镜像；
- 如何运行 lint、类型检查、单元测试、集成测试和构建；
- 如何进入容器、查看日志、检查端口和健康状态；
- worker、scheduler、WebSocket 等进程如何重启。

`<project-command>` 只是编写模板时的标记，交付到目标项目文档前必须替换为真实命令，不能原样保留。

### 5. 数据库与持久化

- 说明初始化、迁移、种子、备份和恢复命令；
- 标明数据库卷名称和数据保留行为；
- `docker compose down` 与 `docker compose down -v` 的区别必须写清楚；
- 删除数据卷、重建数据库或执行不可逆迁移前必须获得明确授权；
- 测试数据和开发账号不得包含生产数据或真实个人信息。

### 6. 重建与清理

按影响分级：

| 操作 | 适用场景 | 数据影响 |
| --- | --- | --- |
| 重启服务 | 配置或进程异常 | 不删除数据 |
| 重建指定镜像 | Dockerfile、系统扩展变化 | 通常保留命名卷 |
| 重装应用依赖卷 | lockfile 或依赖损坏 | 删除依赖缓存，不删除数据库 |
| 删除数据库卷 | 确认要全量重置本地数据 | 不可恢复，必须显著警告 |

给出精确的项目命令，禁止把删除卷作为普通故障排查第一步。

### 7. 常见故障

至少覆盖：

- 端口占用和端口覆盖；
- 镜像架构不兼容；
- 文件权限或 UID/GID；
- macOS/Windows 文件监听与卷性能；
- 数据库“已启动但未就绪”；
- 宿主机与容器访问地址混用；
- 源码已更新但容器使用旧镜像/旧依赖卷；
- Compose 项目名不同导致连接到另一组容器或数据卷。

每个故障写“症状、检查命令、原因、处理、验证结果”，不要只给破坏性重置命令。

## 验证要求

至少完成并如实报告：

1. `docker compose config` 能解析当前配置；
2. 开发镜像能构建，或明确未构建原因；
3. 必要服务达到 healthy/running；
4. 本地源码修改能在容器内可见，热更新按技术栈生效；
5. 后端健康检查或最小 API 可访问；
6. 每个前端应用至少能启动或构建；
7. 测试命令能在文档指定目录执行；
8. 停止再启动后，有状态数据按文档预期保留。

不能启动完整环境时，至少执行 Compose 解析、Dockerfile/路径静态核对和文档链接检查，并明确未完成运行验证。
