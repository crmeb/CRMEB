# 事件调用规范与系统事件表（Event）

> 本文是 `crmeb-dev` 技能的子文档，完整二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。内容基于 v8.1 实际代码整理（`backend/app/event.php` + `modules/` 各模块 `event.php`）。

**权威规范见 `.crmeb/rules/事件规范.md`**（事件调用强制规范 + 全量事件名清单 + 参数格式）。本文不复制正文，仅保留以下高频速查：

## 核心要求（强约束）

- 统一使用**字符串 key** 调用事件（如 `event('OrderPaySuccess', [$orderId])`），**禁止类常量**
- 服务层事件名不带 `Listener` 后缀；监听器类名保留 `Listener` 后缀
- 新增模块事件在模块 `event.php` 中注册，由 `App::boot()` 加载
