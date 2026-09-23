# 活动营销监听器目录（已迁移）

> **本目录已清空**：原营销类监听器已按三层架构迁移至 `modules/` 扩展层，由各模块的 `event.php` 自注册。

## 监听器迁移对照

| 原监听器 | 现位置 | 注册事件 key |
|----------|--------|--------------|
| BargainListener | `modules/trade/bargain/listener/BargainListener.php` | `Bargain` |
| PinkListener | `modules/trade/combination/listener/PinkListener.php` | `Pink` |
| LotteryListener | `modules/marketing/lottery/listener/`（多个监听器） | `Lottery` 等 |
| SignListener | `modules/marketing/signin/listener/SignRemindNoticeListener.php` | `Sign` |
| IntegralOrderListener | 已删除（死注册，全项目无触发点） | —— |
| QueueStartListener | 已删除（死注册，全项目无触发点），`app/listener/queue/` 目录同步清空 | —— |
| DistributionListener（分销） | `modules/distribution/listener/DistributionListener.php` | 见 `modules/distribution/event.php` |

## 命名规范

- 事件 key **不带** `Listener` 后缀：`event('Bargain', [...])`
- 监听器**类名保留** `Listener` 后缀：`BargainListener`

---

**版本**: v8.1
**迁移说明**: 2026-08-18 P5 事件规范收口
