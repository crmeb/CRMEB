# CRMEB AI 开发入口

开始处理本仓库任务前，读取仓库根目录的 [.crmeb/AGENTS.md](.crmeb/AGENTS.md)，按其索引加载相关规则和技能。

所有规则、技能、子智能体及各端约定只维护在 `.crmeb/`；本文件及工具目录只是发现入口。即使原生技能面板未注册，也应直接读取 `.crmeb/skills/<技能名>/SKILL.md`。
