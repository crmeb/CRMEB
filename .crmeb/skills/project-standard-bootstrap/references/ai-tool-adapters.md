# AI 工具适配

## 单一真源原则

所有项目规则正文维护在 `.crmeb/AGENTS.md`、`.crmeb/rules/` 和 `.crmeb/skills/`。工具专属文件只负责发现和导入，不复制长期规则，避免 Codex、Claude、Cursor、Copilot 和 Gemini 得到不同结论。

## 默认适配矩阵

| 工具 | 仓库入口 | 适配方式 |
| --- | --- | --- |
| Codex 及 AGENTS 兼容工具 | `AGENTS.md` | Markdown 薄入口指向 `.crmeb/AGENTS.md` |
| Claude Code | `CLAUDE.md` | 使用 `@.crmeb/AGENTS.md` 导入真源 |
| Cursor | `.cursor/rules/project-standard.mdc` | `alwaysApply: true`，引用 `.crmeb/AGENTS.md` |
| GitHub Copilot | `.github/copilot-instructions.md` | 仓库级指令要求先读取 `.crmeb/AGENTS.md` |
| Gemini CLI | `GEMINI.md` | 使用 `@./.crmeb/AGENTS.md` 导入真源 |
| 通用抓取或其他工具 | `llms.txt` | 只给项目入口和 `.crmeb` 真源路径 |

这些路径依据各工具官方发现机制设置：[Codex AGENTS.md](https://learn.chatgpt.com/docs/agent-configuration/agents-md)、[Claude Code memory](https://code.claude.com/docs/en/memory)、[Cursor Rules](https://docs.cursor.com/context/rules)、[GitHub Copilot instructions](https://docs.github.com/en/copilot/how-tos/configure-custom-instructions-in-your-ide/add-repository-instructions-in-your-ide)、[Gemini CLI context](https://google-gemini.github.io/gemini-cli/docs/cli/gemini-md.html)。

## 生成要求

- 初始化时创建缺失入口，不覆盖项目已有工具指令。
- 已有入口包含项目专属内容时，保留内容并增加指向 `.crmeb` 的引导；冲突条款需人工或 AI 语义合并。
- 默认使用入库薄文件而不是软链接，保证 Windows 和压缩包场景可用；项目明确采用软链接时验证 Git 与目标工具能正确发现。
- 工具专属入口保持简短，避免每次会话加载完整规则库；具体规则由 `.crmeb/AGENTS.md` 按任务路由。
- 子目录需要特殊约定时，优先使用工具支持的作用域机制或子目录入口，但正文仍链接到 `.crmeb/scopes/`。

## 验证

1. 静态检查所有入口存在且包含 `.crmeb/AGENTS.md`。
2. Codex/AGENTS 工具确认根入口生效。
3. Claude Code 用 `/context` 检查 `CLAUDE.md` 和导入文件。
4. Gemini CLI 用 `/memory show` 或 `/memory list` 检查 `GEMINI.md`。
5. Cursor 在 Rules 设置或上下文中确认项目规则生效。
6. Copilot 用仓库上下文发起一次规则相关问题，确认读取仓库级指令。

若无法实际启动某工具，只能报告“入口静态有效”，不得声称已验证该工具发现成功。

