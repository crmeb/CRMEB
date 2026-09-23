# AdminAPI 路由测试规则

> 本文是 `crmeb-dev` 技能的子文档，完整二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。用于校验路由与控制器方法的对应关系，新增/修改路由后自检。

**路由与接口映射验证由 `.crmeb/agents/crmeb-route-validator.md` 的流程承担**（校验控制器类/方法存在性、路由格式规范性、方法命名规范）。

## 使用方式

新增/修改路由后，直接委派 `crmeb-route-validator` 子智能体执行全量路由扫描验证，输出测试报告。
