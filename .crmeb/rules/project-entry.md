---
alwaysApply: true
description: CRMEB 项目统一入口，每次处理项目任务前加载，按任务选择规则和技能。
---

处理本仓库任务前读取仓库根目录的 `.crmeb/AGENTS.md`，按其索引加载适用规则、技能和各端约定。原生技能工具不可用时直接读取真源 SKILL.md。所有正文仅维护在 `.crmeb/`，工具目录仅为兼容入口。
