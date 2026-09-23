# 项目契约与标准命令

`.crmeb/project.json` 是人和 AI 的机器可读入口，用于描述项目组成和命令映射。manifest、lockfile、路由、Compose 等仍是具体事实真源；项目契约负责把这些入口串起来并由检查脚本防漂移。

项目进入 `active` 前必须已经根据当前后端运行环境生成或完善根 `docker-compose.yml`，并设置 `docker.enabled: true`。`docker.enabled: false` 只允许存在于初始化过程的 `draft` 契约或明确的“仅审计”结果中。

## 必备结构

```json
{
  "schemaVersion": 1,
  "status": "active",
  "project": {
    "name": "example",
    "type": "fullstack",
    "versionSource": "backend/VERSION"
  },
  "directories": {
    "backend": "backend",
    "frontend": "frontend",
    "docs": "help/docs",
    "process": "tests",
    "tests": "tests",
    "upgrades": "upgrades"
  },
  "applications": [],
  "services": [],
  "docker": {
    "enabled": true,
    "composeFile": "docker-compose.yml",
    "configRoot": "docker"
  },
  "documentation": {
    "required": ["README.md", "help/docs/development.md", "help/docs/docker-development.md"]
  },
  "commands": {
    "doctor": "node scripts/docker-project.mjs doctor",
    "setup": "node scripts/docker-project.mjs setup",
    "dev": "node scripts/docker-project.mjs dev",
    "restart": "node scripts/docker-project.mjs restart",
    "stop": "node scripts/docker-project.mjs stop",
    "down": "node scripts/docker-project.mjs down",
    "status": "node scripts/docker-project.mjs status",
    "logs": "node scripts/docker-project.mjs logs",
    "shell": "node scripts/docker-project.mjs shell",
    "check": "node scripts/check-project.mjs",
    "test": "node scripts/test-project.mjs",
    "smoke": "node scripts/smoke-project.mjs",
    "build": "node scripts/build-project.mjs",
    "resetData": "node scripts/docker-project.mjs reset-data",
    "upgradePlan": "node scripts/upgrade.mjs plan",
    "upgradePackage": "node scripts/upgrade.mjs package",
    "upgradeVerify": "node scripts/upgrade.mjs verify"
  }
}
```

示例只展示映射方式，必须替换为目标项目真实存在的命令。根 `run.sh` / `run.ps1` 调用通用执行器，再由执行器读取这里的底层命令，禁止把命令反向映射到 `run.sh` 造成递归。`status: draft` 表示仍需人工或 AI 补全；只有命令存在且通过验证后才能改为 `active`。

`directories` 记录知识目录归属：`docs` 是当前事实，`process` 是方案、评审与验收等过程文档目录，`tests` 是测试索引与编排入口。过程文档与测试索引统一在 `tests/`，因此 `process` 缺省即为 `tests`；确有独立过程文档目录的项目才单独声明，审计与检查脚本必须读取该字段，不得硬编码目录名。

## Applications

每个可独立运行或构建的应用记录：

- `id`：稳定标识；
- `kind`：backend、admin、web、mobile、worker 等；
- `path`：相对仓库根路径；
- `runtime`：运行时与版本来源文件；
- `devCommand`、`testCommand`、`buildCommand`；
- `healthcheck` 或成功信号；
- `dependsOn`：数据库、缓存或其他应用 ID。

## Services

数据库、缓存、消息队列、对象存储、搜索等服务记录：

- 服务 ID、用途、实现和版本来源；
- 容器内端口与宿主机默认端口；
- 健康检查；
- 数据卷和持久化策略；
- 是否为开发必需；
- 配置变量名，不记录真实秘密。

## 标准命令语义

| 命令 | 统一语义 |
| --- | --- |
| `doctor` | 只读检查运行时、Docker、端口、配置和依赖，不修改系统 |
| `setup` | 创建本地配置并安装依赖；破坏性初始化必须拆成单独命令 |
| `dev` | 启动完整本地开发环境，重复执行应安全 |
| `restart` | 重启全部或指定服务，不删除数据 |
| `stop` | 停止开发环境但保留数据和依赖缓存 |
| `down` | 删除本地容器和网络但保留具名数据卷 |
| `status` | 展示应用、服务、端口和健康状态 |
| `logs` | 查看或跟随日志，可选择具体服务 |
| `shell` | 进入明确指定的开发容器 |
| `check` | 格式、lint、类型、架构、文档和配置静态门禁 |
| `test` | 执行项目默认测试集合，并说明外部依赖 |
| `smoke` | 对运行中的关键页面、API 和后台进程做最小可用验证 |
| `build` | 从干净依赖生成所有发布产物 |
| `upgrade-*` | 规划、生成和验证升级包；不得隐式发布 |

破坏性操作使用明显名称，例如 `reset-data`、`delete-volumes`，要求交互确认或显式 `--yes`。不得让 `setup`、`dev`、`stop` 默认清库。

## 防漂移

- 契约中的路径必须存在；可选应用需显式标注 `enabled: false`。
- 命令必须能从仓库根执行，或通过脚本稳定切换目录。
- 端口和版本应引用配置来源；环境可覆盖时记录变量名。
- CI 和 README 应调用同一标准命令，不维护另一套命令清单。
- 修改应用、服务或命令时，同一变更更新项目契约并运行检查脚本。
