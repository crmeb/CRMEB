# 驱动插件开发

驱动（`backend/driver/`）是三层架构中的第三方服务底座（支付、短信、上传、邮件、物流、电子面单、发票、打印、机器人、商品采集），与业务模块插件（`backend/modules/`）并列。清单发现、canonical ID、升级包与生命周期验证等共用约定见 [manifest-and-lifecycle.md](manifest-and-lifecycle.md)，本文只描述驱动专属内容。

## 清单与安装

纯配置型驱动不需要自定义 Installer。`ManifestDriverInstaller` 使用清单元数据将其接入统一生命周期和驱动字典；清单必须包含 `driver` 节点，且 `driver_key` 必填。

服务商类代码放在 `driver/<category>/<name>/`，目录包含 `Driver.php` 与 `plugin.json`，UI 配置通过 `driver.config_fields` 描述。服务商凭据属于运行时数据，绝不能写成清单常量。类名和能力约定由现有管理器决定，应复制同类别中相近驱动的结构。

## Driver 实现契约

Driver 命名空间 `driver\{ext}\{DriverName}`，类名默认 `Driver`，继承 `crmeb` 层对应基类：

| 驱动类型 | 基类/接口 | 关键方法 |
|---------|----------|---------|
| pay | `crmeb\services\pay\BasePay` + `PayInterface` | `create()`/`refund()`/`queryRefund()`/`handleNotify()` |
| sms | `crmeb\services\sms\BaseSms` | `send()` 等发送与模板方法 |
| email/bot/upload/logistics/express/invoice/printer/copyproduct | 各自 `crmeb\services\{ext}` 基类 | 按能力实现 |

字段定义、方法签名与回调流程以各 `backend/driver/{ext}/DEVELOPMENT.md` 及同类别现有驱动为准。支付回调规范：验证签名 → 提取订单号 → `event('OrderPaySuccess', [$orderNo])`（字符串事件 key）→ 返回 `success`/`fail`。

## 驱动后台管理

新增驱动类型需同步提供 app 层管理代码（参考 `express` 类别）：控制器 `app/adminapi/controller/driver/{ext}/`（继承 `AuthController`）、路由统一写在 `app/adminapi/route/driver.php`（按驱动扩展名分组）、服务层 `app/services/driver/{ext}/`（继承 `BaseService`）、DAO 与 Model 位于 `app/dao|model/driver/{ext}/`。

## 驱动类别

当前支持上传的驱动类别包括 pay、sms、upload、express、logistics、invoice、email、bot、copyproduct 和 printer。新增驱动类别时必须同时实现对应的发现和管理器路径，不能只创建目录。
