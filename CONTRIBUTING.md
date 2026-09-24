# 参与开发（CONTRIBUTING）

感谢参与 CRMEB v8。本文面向人工贡献者与 AI 辅助开发者，只做流程导航；**强制规范都在被引用的真源文件里，以真源为准**。

## 一、环境准备

1. 按 [README.md](README.md)「快速开始」使用宿主机 PHP、MySQL 和 Redis 启动后端；明确选择容器环境时再使用 `./run.sh install`，也可用 VS Code / Cursor / Trae 的 Dev Containers 打开仓库。
2. 前端：管理端 `frontend/admin`（`npm run dev`，端口 1619）、移动端 `frontend/uni-app`（`npm run dev:h5`），启动方式见 README 对应小节。
3. 安装完成后默认后台账号 `admin` / `crmeb.com`。

## 二、分支与版本

- 主开发分支 `v8.0.dev`；功能分支从它切出，PR 回它。
- 正式发布以 Release / Tag 为准；当前为首版开发阶段，插件清单与 Installer 版本统一 `1.0.0`（详见 [.crmeb/rules/架构规范.md](.crmeb/rules/架构规范.md)「当前发布阶段」）。

## 三、动手前必读

| 任务 | 先读 |
| --- | --- |
| 任何后端改动 | [.crmeb/rules/架构规范.md](.crmeb/rules/架构规范.md)（三层依赖铁律）+ 对应技能 |
| 管理端页面 / 样式 | [.crmeb/skills/crmeb-admin-dev/SKILL.md](.crmeb/skills/crmeb-admin-dev/SKILL.md) 与 [.crmeb/skills/crmeb-admin-ui/SKILL.md](.crmeb/skills/crmeb-admin-ui/SKILL.md) |
| 移动端页面 | [.crmeb/skills/crmeb-uniapp-dev/SKILL.md](.crmeb/skills/crmeb-uniapp-dev/SKILL.md) |
| 插件 / 模块二开 | [.crmeb/rules/插件开发规范.md](.crmeb/rules/插件开发规范.md)（零侵入红线） |
| 涉及接口契约变更 | [.crmeb/rules/前端规范.md](.crmeb/rules/前端规范.md)（全链路同步清单） |

完整规则、技能与子智能体索引见 [.crmeb/AGENTS.md](.crmeb/AGENTS.md)；使用 AI 工具开发时，让 AI 先读取它。

## 四、架构铁律速记

依赖方向单向不可逆：`modules → app → crmeb → driver`；跨模块通信走核心契约或字符串事件；插件二开零侵入。完整定义与判定标准见 [.crmeb/rules/架构规范.md](.crmeb/rules/架构规范.md)。

提交前在 `backend/` 运行机器校验（CI 同款）：

```bash
php think arch:check    # 依赖铁律 / 跨模块互引 / 事件 key / 残留 use
```

## 五、快速创建合规模块

```bash
php think make:module demo --label=演示模块 --type=service
```

生成 plugin.json、App、event、Installer 与分层目录的合规骨架，再往里填业务逻辑即可；完整流程见 [.crmeb/skills/crmeb-plugin-dev/SKILL.md](.crmeb/skills/crmeb-plugin-dev/SKILL.md)。

## 六、提交规范（摘要）

格式 `type(作用域): 中文描述`，类型前缀 `feat / fix / docs / refactor / chore / style`；一次 commit 只承载一个主题。**完整强制条款见 [.crmeb/rules/提交规范.md](.crmeb/rules/提交规范.md)**（含禁止入库清单与密钥处置）。

## 七、PR 前检查清单

- [ ] `php think arch:check` 严重违规为 0
- [ ] `php think event:check` 无新增孤儿事件
- [ ] 涉及插件清单变更时 `php think plugin:manifest-check` 通过
- [ ] 接口契约变更已同步两端 `api` 目录、菜单权限与安装 SQL（核对清单见 [.crmeb/rules/前端规范.md](.crmeb/rules/前端规范.md)）
- [ ] 数据表变更已同步 `backend/public/install/crmeb_table.sql`（基础数据见 `backend/public/install/data/`）
- [ ] 提交信息符合上述格式、按主题分批
