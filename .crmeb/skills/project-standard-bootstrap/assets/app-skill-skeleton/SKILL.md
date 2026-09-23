---
name: {{SKILL_NAME}}
description: {{APP_ID}} 应用的目录说明与核心功能开发文档，供 AI 在修改 {{APP_PATH}} 前快速了解该端架构、入口链路与验证命令
appPath: {{APP_PATH}}
directoryGuide: references/项目目录说明.md
coreFeatures:
  - references/核心功能开发.md
---

# {{APP_ID}} 应用开发技能

本技能覆盖 `{{APP_PATH}}`（运行时：{{RUNTIME}}）。只描述该端事实，不混入其他应用。

## 读取顺序

1. [项目目录说明](references/项目目录说明.md)：目录树、逐目录职责、关键入口、请求与数据链路、依赖边界。
2. [核心功能开发](references/核心功能开发.md)：核心功能的入口、路由或 API、业务层、数据模型、扩展点、测试与验证命令。

## 使用要求

- 两份文档必须从当前代码、路由、manifest 和测试核对，禁止照搬模板或旧文档。
- 修改 `{{APP_PATH}}` 后同步更新受影响条目；事实变化未同步即视为未完成。
- 与 `{{APP_PATH}}/README.md`、`help/docs/applications/{{APP_ID}}.md` 事实保持一致，三者不得互相矛盾。
- 需要更细的专题时，在本技能 `references/` 下按主题新增文件，并在此处登记。