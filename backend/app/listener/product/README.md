# 商品事件监听器目录

## 概述

商品生命周期事件采用**单一事件 + 类型分发**模式：所有商品相关业务统一触发 `Product` 事件，由 `ProductListener` 按 `$type` 分发处理。

## 事件注册（app/event.php）

```php
'Product' => [\app\listener\product\ProductListener::class],
```

## 事件类型（type）分发清单

| type | 触发时机 | 处理方法 |
|------|---------|---------|
| `create` | 商品创建成功后 | `handleCreate` |
| `update` | 商品更新成功后 | `handleUpdate` |
| `delete` | 商品删除（移动回收站）后 | `handleDelete` |
| `status_change` | 商品上架/下架状态变更时 | `handleStatusChange` |
| `stock_change` | 商品库存变动时 | `handleStockChange` |
| `stock_warning` | 商品库存低于预警值时 | `handleStockWarning` |
| `price_change` | 商品价格变动时 | `handlePriceChange` |
| `visit` | 用户访问商品详情页时 | `handleVisit` |
| `collect` | 用户收藏商品时 | `handleCollect` |
| `collect_cancel` | 用户取消收藏时 | `handleCollectCancel` |
| `reply` | 商品收到评价时 | `handleReply` |
| `reply_reply` | 评价被回复时 | `handleReplyReply` |

## 触发方式

在业务代码中使用 `event()` 函数触发，第一个参数为类型，第二个为业务数据：

```php
// 商品创建
event('Product', ['create', $data]);

// 商品价格变动（CenterProductServices 更新时价格变化）
event('Product', ['price_change', [$id, $skuId, $oldPrice, $newPrice, 'update']]);

// 商品访问（详情页）
event('Product', ['visit', [$id, $uid, date('Y-m-d H:i:s'), &$data]]);
```

## 商品事件触发流程

```
商品创建 → Product(create)
    ↓
商品上架/下架 → Product(status_change)
    ↓
商品更新 → Product(update) / Product(price_change)（价格变化时）
    ↓
商品库存变动 → Product(stock_change) → Product(stock_warning)（低于预警时）
    ↓
用户访问商品 → UserVisit + Product(visit)
    ↓
用户收藏/取消 → Product(collect) / Product(collect_cancel)
    ↓
用户评价/回复 → Product(reply) / Product(reply_reply)
    ↓
商品删除 → Product(delete)
```

## 二开扩展指南

### 扩展新类型

1. 在 `ProductListener::handle()` 的 `switch ($type)` 中新增 `case` 分支与处理方法；
2. 在业务触发方调用 `event('Product', ['your_type', $data])`。

### 模块层监听商品事件

产品类型模块（`modules/product/{type}/event.php`）可注册自己的监听器（如 `ProductDetailExtend`），无需修改本目录文件。

## 命名规范

- 事件 key 不带 `Listener` 后缀：`event('Product', [...])`
- 监听器类名保留 `Listener` 后缀：`ProductListener`

---

**版本**: v8.1
**最后更新**: 2026-08-18（P5 事件规范收口，对齐实际实现）
