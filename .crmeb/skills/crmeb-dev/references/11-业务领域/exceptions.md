---
alwaysApply: false
description: 系统异常类参考手册 - 各自定义异常类（AdminException/ApiException/AuthException/ApiStatusException/CrudException/PayException/UploadException/BusinessException/OAuthException 等）的命名空间、适用场景、典型用途与使用示例，以及全局异常处理入口。异常选型与是否落日志的强制规则见 .crmeb/rules/日志与异常规范.md
---
# CRMEB v8 系统异常类说明文档

---

## 一、概述

本文档整理了 **CRMEB v8** 系统中所有自定义异常类的定义、用途和区别，为二次开发提供参考。

**异常基类说明**：
- 所有自定义异常类都继承自 `RuntimeException`
- 支持数组格式的错误信息（格式：`[错误码, 错误消息]`）
- 支持语言包国际化（通过 `getLang()` 函数）
- 支持字符串占位符替换（`$replace` 参数）

---

## 二、异常类列表

### 2.1 AdminException - 管理员操作异常

| 属性 | 说明 |
|------|------|
| **命名空间** | `crmeb\exceptions\AdminException` |
| **适用场景** | 后台管理相关操作错误 |
| **典型用途** | 数据不存在、订单状态错误、权限不足等 |

**代码位置**：[exceptions/AdminException.php](../../../../../backend/crmeb/exceptions/AdminException.php)

**使用示例**：
```php
use crmeb\exceptions\AdminException;

throw new AdminException('数据不存在');
throw new AdminException('订单未支付');
throw new AdminException('已退完支付金额，不能再退款了');
throw new AdminException(['400', '自定义错误信息']);
```

**实际应用场景**：
- 后台充值退款操作
- 管理员订单处理
- 数据查询验证
- 业务状态校验

---

### 2.2 ApiException - API应用异常

| 属性 | 说明 |
|------|------|
| **命名空间** | `crmeb\exceptions\ApiException` |
| **适用场景** | API接口通用错误 |
| **典型用途** | 参数错误、用户不存在、业务逻辑错误等 |

**代码位置**：[exceptions/ApiException.php](../../../../../backend/crmeb/exceptions/ApiException.php)

**使用示例**：
```php
use crmeb\exceptions\ApiException;

throw new ApiException('参数错误');
throw new ApiException('转入金额不能大于可提现佣金');
throw new ApiException('修改失败');
throw new ApiException('写入余额充值失败');
throw new ApiException('用户不存在');
```

**实际应用场景**：
- 用户端API接口参数验证
- 用户余额/佣金操作验证
- 业务数据操作失败
- 用户信息查询

---

### 2.3 ApiStatusException - API状态异常（扩展版）

| 属性 | 说明 |
|------|------|
| **命名空间** | `crmeb\exceptions\ApiStatusException` |
| **适用场景** | 需要返回额外数据的API错误 |
| **典型用途** | 需要携带业务数据的错误响应 |

**代码位置**：[exceptions/ApiStatusException.php](../../../../../backend/crmeb/exceptions/ApiStatusException.php)

**特有属性**：
- `$apiStatus` - 自定义状态码
- `$apiData` - 附加的业务数据

**特有方法**：
- `getApiStatus()` - 获取API状态码
- `getApiData()` - 获取附加数据

**使用示例**：
```php
use crmeb\exceptions\ApiStatusException;

throw new ApiStatusException(400, '业务错误', ['order_id' => 123, 'amount' => 100]);
throw new ApiStatusException('status_code', '错误消息', ['data' => '额外数据']);
```

**与 ApiException 的区别**：
- ApiException 仅返回错误消息和错误码
- ApiStatusException 可以携带额外的业务数据
- 适用于需要返回详细错误信息的场景

---

### 2.4 AuthException - 认证异常

| 属性 | 说明 |
|------|------|
| **命名空间** | `crmeb\exceptions\AuthException` |
| **适用场景** | 用户认证、授权相关错误 |
| **典型用途** | 登录失败、用户不存在、权限不足等 |

**代码位置**：[exceptions/AuthException.php](../../../../../backend/crmeb/exceptions/AuthException.php)

**使用示例**：
```php
use crmeb\exceptions\AuthException;

throw new AuthException('没有此用户');
throw new AuthException('账号或密码错误');
throw new AuthException('新增用户失败');
throw new AuthException('授权失败');
```

**实际应用场景**：
- 用户登录验证
- 微信授权操作
- 客服系统登录
- 用户注册验证

---

### 2.5 CrudException - CRUD操作异常

| 属性 | 说明 |
|------|------|
| **命名空间** | `crmeb\exceptions\CrudException` |
| **适用场景** | 数据库增删改查操作错误 |
| **典型用途** | 数据创建、读取、更新、删除失败 |

**代码位置**：[exceptions/CrudException.php](../../../../../backend/crmeb/exceptions/CrudException.php)

**使用示例**：
```php
use crmeb\exceptions\CrudException;

throw new CrudException('数据插入失败');
throw new CrudException('数据更新失败');
throw new CrudException('数据删除失败');
throw new CrudException('数据查询失败');
```

**与 ApiException 的区别**：
- CrudException 专注于数据库操作错误
- ApiException 用于更广泛的业务逻辑错误
- CrudException 更适合数据层错误处理

---

### 2.6 PayException - 支付异常

| 属性 | 说明 |
|------|------|
| **命名空间** | `crmeb\exceptions\PayException` |
| **适用场景** | 支付流程相关错误 |
| **典型用途** | 订单支付、退款、支付参数错误等 |

**代码位置**：[exceptions/PayException.php](../../../../../backend/crmeb/exceptions/PayException.php)

**使用示例**：
```php
use crmeb\exceptions\PayException;

throw new PayException('您支付的订单不存在');
throw new PayException('暂不支持其他类型订单支付');
throw new PayException('缺少openid');
throw new PayException('支付签名验证失败');
```

**实际应用场景**：
- 订单支付验证
- 支付参数校验
- 微信支付操作
- 支付回调处理

---

### 2.7 UploadException - 上传异常

| 属性 | 说明 |
|------|------|
| **命名空间** | `crmeb\exceptions\UploadException` |
| **适用场景** | 文件上传相关错误 |
| **典型用途** | 上传失败、文件类型错误、文件大小超限等 |

**代码位置**：[exceptions/UploadException.php](../../../../../backend/crmeb/exceptions/UploadException.php)

**使用示例**：
```php
use crmeb\exceptions\UploadException;

throw new UploadException('文件上传失败');
throw new UploadException('不支持的文件类型');
throw new UploadException('文件大小超过限制');
throw new UploadException('文件不存在');
```

**与 ApiException 的区别**：
- UploadException 专注于文件上传场景
- ApiException 用于一般业务逻辑错误
- UploadException 携带更明确的业务语义

---

## 三、异常对比表

| 异常类 | 作用域 | 携带额外数据 | 典型场景 |
|--------|--------|--------------|----------|
| **AdminException** | 后台管理 | 否 | 数据不存在、订单状态错误 |
| **ApiException** | API接口 | 否 | 参数错误、业务逻辑错误 |
| **ApiStatusException** | API接口 | 是 | 需要返回详细错误信息 |
| **AuthException** | 认证授权 | 否 | 登录失败、权限不足 |
| **CrudException** | 数据库操作 | 否 | 数据增删改查失败 |
| **PayException** | 支付流程 | 否 | 订单支付、退款错误 |
| **UploadException** | 文件上传 | 否 | 上传失败、类型错误 |

---

## 四、使用规范

### 4.1 选择合适的异常类型

```
用户认证/授权相关     → AuthException
后台管理操作相关      → AdminException
API接口参数/业务错误  → ApiException 或 ApiStatusException
支付流程相关          → PayException
文件上传相关          → UploadException
数据库操作相关        → CrudException
```

### 4.2 异常抛出方式

**方式一：直接抛出错误消息**
```php
throw new ApiException('错误信息');
```

**方式二：数组格式（错误码+消息）**
```php
throw new ApiException([400, '错误信息']);
throw new ApiException(['error_code', '错误信息']);
```

**方式三：携带替换参数**
```php
throw new ApiException('用户 {name} 不存在', ['name' => $userName]);
```

**方式四：ApiStatusException 携带额外数据**
```php
throw new ApiStatusException(400, '业务错误', ['order_id' => 123]);
```

---

## 五、全局异常处理

系统通过全局异常处理器统一捕获这些异常，并根据异常类型返回不同的HTTP响应。

**异常处理配置位置**（当前现状）：
- 三端 API 公共基类：`app/http/BaseApiExceptionHandle.php`（`abstract class BaseApiExceptionHandle extends Handle`，收口统一的异常日志格式与 JSON 渲染流程）
- 各端子类：`app/api/ApiExceptionHandle.php`、`app/adminapi/AdminApiExceptionHandle.php`（继承 `BaseApiExceptionHandle`，各端仅覆写 `operatorId()`/`renderCustom()` 等差异扩展点）
- 全局默认兜底：`app/ExceptionHandle.php`
- 注意：不存在 `config/exception.php` 与 `app/kefuapi/KefuApiExceptionHandle.php`、`app/outapi/OutApiExceptionHandle.php`（旧版描述已失效）

**关键渲染分支**（`BaseApiExceptionHandle::render()`）：

```php
// AuthException / AdminException / ApiException / ValidateException
// → app('json')->make($e->getCode() ?: 400, $message)
// → 与 return app('json')->fail() 完全等价的 {status: 400, msg} JSON
```

且上述四个异常类均在 `ignoreReport` 列表中，**不会写 error 日志**；其余异常（如普通 `\Exception`）走兜底分支 `app('json')->fail($message)` 并记录 error 日志。

**异常响应格式**：
```json
{
    "status": 400,
    "msg": "错误信息"
}
```

---

## 六、异常与 JSON 响应的选择规则（throw vs fail vs success）

> **权威规范见** [.crmeb/rules/日志与异常规范.md](../../../../rules/日志与异常规范.md)（异常类选型对照表 + throw/fail/success 分层规则 + 日志写法与排障入口）与 [.crmeb/rules/接口与响应规范.md](../../../../rules/接口与响应规范.md)（响应体 `{status, msg, data}` 与 `Json` 唯一出口）。**本文不复制规范正文**，本节仅保留速记住的口径：
>
> - **成功** → Controller 出口 `return app('json')->success(...)`（Service 层禁止 `return success/fail`）
> - **Controller 可预判的正常失败分支** → `return app('json')->fail(...)`
> - **Service/Dao 深层规则违反、事务内需回滚** → `throw new AdminException`（后台）/ `ApiException`（用户端）
> - **需落日志排查的系统错误** → 抛普通 `\Exception`（`AdminException` 等在 `ignoreReport` 中不写日志）
>
> 三者在 HTTP 层最终都渲染为 `{status: 400, msg}`，区别在执行流（正常返回 vs 中断冒泡）、所处层级与日志行为，完整判定与示例以规则文件为准。

---

**版本**: v8.1
**创建日期**: 2026-06-03
**最后更新**: 2026-09-10
**适用范围**: CRMEB v8
**文档位置**: [.crmeb/skills/crmeb-dev/references/11-业务领域/exceptions.md](exceptions.md)
**v8.1 变更**: 新增第六章"异常与 JSON 响应的选择规则"（throw vs fail vs success 使用场景）；修正第五章全局异常处理器路径（现为 `app/http/BaseApiExceptionHandle.php` 公共基类 + api/adminapi 子类，kefuapi/outapi 处理器已不存在）；响应格式修正为 `{status, msg}`
