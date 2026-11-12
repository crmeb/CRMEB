# 驱动分册 · 支付（pay）

核对日期：2026-11-06。先读 [00-总览](00-总览-类型路由与边界.md) 第 3 节（驱动通用机制：四名字、安装、scan、config_fields），本篇只写 **pay 专属**内容。与 00 重复的部分只给索引，不展开。细节与代码冲突时以代码为准。

领域归属：第三方协议层 → `backend/driver/pay/{Dir}/`；见 [domain-selection.md](../domain-selection.md)。

---

## 1. 开工前必须告诉用户

支付是 10 类驱动里**不能纯插件闭环**的类型。以下三处在核心，仅交付 `driver/pay/` 目录做不完，开工前说明并确认处理方式：

1. **渠道行**：后台「支付方式」读 `eb_pay_channel`。`ManifestDriverInstaller` 只写 `eb_driver`，**不会**自动插入渠道行；`PayChannelServices` 也无「新增渠道」接口。插件不能直接改系统表 DDL。可选：用户同意改安装 SQL `backend/public/install/data/pay_channel_data.sql`；或在驱动包内提供 `Installer.php` 在 `runMigrate()` 幂等 INSERT（需用户授权写系统表数据，仍不走 app 改代码）。
2. **服务端分支**：`app/services/pay/PayServices.php` 的 `pay()` / `refund()` 对 `weixin`、`weixin_v3`、`alipay`、`allinpay` 有特殊参数。新 `channel_key` 若需 openid 规则、退款号规则，可能要改这里（零侵入边界例外，须用户同意）。
3. **收银台**：`frontend/uni-app/src/components/payment/index.vue` 按 `channel_key` `switch`，无 `default`。新渠道须补分支，否则 `{ status, payInfo }` 返回了也调不起。

只写驱动时：交付驱动目录 + 交付说明列出上述待办。

---

## 2. 文件清单与参考实现

```text
backend/driver/pay/{Dir}/
├── plugin.json
├── Driver.php          # namespace driver\pay\{Dir}; class Driver extends BasePay implements PayInterface
├── Installer.php       # 可选：插 eb_pay_channel 行、自有表
└── extend/             # 可选 SDK，命名空间 driver\pay\{Dir}\extend\...
```

| 参考 | 用途 |
| --- | --- |
| `driver/pay/AliPay/` | 最小两文件 |
| `driver/pay/AllinPay/` | SDK + `PayChannelConfig` 读配置 |
| `driver/pay/V3WechatPay/` | 多端、服务商、转账 |

现役映射：

| 目录 | driver_key | channel_key（回调 `:type`） |
| --- | --- | --- |
| AliPay | ali_pay | alipay |
| WechatPay | wechat_pay | weixin |
| V3WechatPay | v3_wechat_pay | weixin_v3（别名 `v3wechat`） |
| AllinPay | allin_pay | allinpay（别名 `allin*` 前缀） |

目录名 `{Dir}` 必须等于 `Str::studly(driver_key)`（见 00 总览 §3.2）。

---

## 3. 注册与配置（pay 特有，摘要）

- **安装期**：`plugin.json` → `ManifestDriverInstaller` → `eb_driver` 一行（`driver_type=pay`）。新驱动默认 `status=0`，后台须启用。  
- **运行时**：`DriverRegistry::scan('pay')` **只读表**，不扫目录。  
- **配置值**：`PayChannelConfig::get($channelKey)` 读 `eb_pay_channel.config_fields`（field→value）。禁止写 `system_config`。  
- **配置结构**：`PayChannelServices::getDetail()` 以驱动清单 `config_fields` 为 schema，库内只存 value（升级改 `show_if` 不必迁 JSON）。

`initialize($config)` 在 `pay()`/`refund()` 路径下 **`$config` 为空**；四个现役驱动都在 `initialize` 里调 `PayChannelConfig::get()`，不要依赖构造注入的配置。

---

## 4. plugin.json（driver 子对象）

顶层 `key` 与 `driver.driver_key` 保持一致（见 00）。pay 额外字段（**不进 eb_driver**，`PayServices` 运行时读清单）：

| 字段 | 作用 |
| --- | --- |
| `channel_key` | 业务支付方式 ID；`eb_pay_channel.channel_key`、notify URL、退款路由 |
| `platform` | `wechat` / `alipay` / `allinpay` 等 |
| `capabilities` | `pay`、`refund`、`notify`、`merchant_pay`、`merchant_pay_new`、`query_transfer`、`transfer_notify` |
| `pay_type_map` | 端 → 供应商 pay_type（`wechat_mini`、`h5`、`app`、`pc`…） |
| `pay_status_type` | 返回前端 `status`，可字符串或按端对象 |
| `supported_platforms` | 端中文名 |
| `required_credentials` | 如 `{"wechat_mini":["openid"]}` → `PayCredentialService` 调 channel `getOpenid` |
| `default_options` | 发起支付并入 `$options` 的字段名 |
| `pay_urls` | 固定跳转 URL（通联等） |

`capabilities` 影响业务分支（如 `merchant_pay_new` 与抽奖转账）。未声明则 `hasCapability()` 为 false。

模板见下（与现役结构一致）：

```json
{
    "key": "your_pay",
    "name": "某某支付",
    "type": "driver",
    "path": "driver/pay/YourPay",
    "version": "1.0.0",
    "driver": {
        "driver_key": "your_pay",
        "channel_key": "yourpay",
        "platform": "yourpay",
        "capabilities": ["pay", "refund", "notify"],
        "config_fields": [
            {"field": "yourpay_mch_id", "label": "商户号", "type": "text", "required": true, "group": "商户"}
        ]
    }
}
```

---

## 5. Driver.php（PayInterface 六方法）

```php
protected function initialize(array $config)
{
    $this->payment = PayChannelConfig::get('yourpay');
}

public function create(...) { $this->authSetPayType($options); /* 调第三方 */ }
public function merchantPay(...) { return false; }
public function refund(...) { /* 注意 PayServices 传入的是退款单号还是 trade_no */ }
public function queryRefund(...) { /* 对齐 normalizeRefundResult 期望结构 */ }
public function handleNotify(string $type = '')
{
    // 验签失败 → response('fail', 500) 等非 2xx
    Event::until('Notify', [[
        'attach' => $attach,
        'out_trade_no' => $outTradeNo,
        'transaction_id' => $transactionId,
    ], 'yourpay']);
}
```

纪律：

- 不 `use app\` / `use modules\`；不改订单表。  
- 成功入账只走 `Notify` 事件 → `NotifyListener`。  
- 回调路由已存在：`Route::any('pay/notify/:type', ...)`，`resolveChannelKeyByType()` 支持别名（`v3wechat`→`weixin_v3`，`allin*`→`allinpay`）。

---

## 6. 三条链路（逐步）

### 6.1 发起支付

1. 用户端提交 → `OrderPayServices`（按 `channel_key` switch 准备 openid 等）。  
2. `PayServices::pay()` → `resolveDriverName()`（可读 `version_switch`）→ `resolvePayType()`（优先 `eb_pay_channel_platform`）。  
3. `app()->make(Pay::class, [$driverName])` → `Driver::create()`。  
4. 返回 `{ status, payInfo }` → uni-app `payment/index.vue`（**按 channel_key 分支**，不是只看 status）。

### 6.2 异步回调

1. `PayController::notify` → `PayServices::handleNotify($type)`。  
2. 解析 channel → 实例化 Driver → `handleNotify()`。  
3. 驱动验签 → `Event::until('Notify', ...)`。  
4. 应答：失败 **非 2xx**；成功按协议返回。

### 6.3 退款

`PayServices::refund()` 对微信/支付宝/通联有不同参数整理；微信 V3 校验支付与退款 `channel_key` 一致。上层建议 `submitRefund()` + `queryRefundStatus()`：**UNKNOWN 不能换号重提**。

分销提现等「付款到零钱」走 `merchantPay`，需声明 capability 且可能涉及核心分支评估。

---

## 7. eb_pay_channel 渠道行（Installer 思路）

支付方式页列表来自 **已有** `eb_pay_channel` 行。仅装驱动不会出现新支付方式。

驱动包可选 `Installer.php`：

- `getKey()` → `driver:pay:{driver_key}`  
- `getType()` → `driver`  
- `runMigrate()`：若不存在则 INSERT 一行（`channel_key`、`channel_name`、`driver`=**目录名** `YourPay`、`config_fields` 空数组或默认 schema）  
- `rollbackMigrate()`：按 `channel_key` 删除（仅卸载驱动时，生产慎用）

`driver` 列存**目录名**，不是 FQCN。骨架参照 `backend/driver/sms/yunxin/Installer.php`。

---

## 8. 前端与凭证

三层对齐缺一会「后台能配、端上调不起」：

1. `supported_platforms` + `eb_pay_channel_platform` 各端开关与 `pay_type`  
2. `pay_type_map` 默认 pay_type  
3. `pay_status_type` → 接口 `status`；收银台仍要 **switch(channel_key)** 分支

`PayCredentialService::getCredentials($uid, $channelKey)` 读 `required_credentials`，反射调用对应 **channel** 模块 `App::getOpenid($uid)`（见 [02-模块-channel](02-模块-channel-用户渠道.md)）。

---

## 9. 逐步落地（新 pay 驱动）

1. 定 `driver_key`、`channel_key`、目录名 `Str::studly(driver_key)`。  
2. 复制 `AliPay` 或 `AllinPay` 改 namespace。  
3. 写 `plugin.json`（六方法能力 + config_fields）。  
4. 实现 `Driver` 六方法 + `PayChannelConfig::get`。  
5. （可选）`Installer` 插 `eb_pay_channel` 行。  
6. 后台安装驱动 → 启用 → 填配置 → `php think pay:clear-cache`。  
7. 与用户确认 core 三处（§1）处理方案。  
8. 沙箱：拉起支付 → 回调 → 退款 → `queryRefund`。  
9. `php think plugin:manifest-check`；打包 `driver/pay/{Dir}/` → `.plugin-packages/`。

---

## 10. 验收

**静态**

- [ ] `php -l`；`plugin:manifest-check`  
- [ ] 四名字对齐；无 app/modules use；无真实密钥  
- [ ] `handleNotify` 验签失败非 2xx；成功只 `Notify` 一次  

**联调（隔离库 + 沙箱）**

- [ ] `DriverRegistry::scan('pay')` 含 `driver_key` 且 `status=1`  
- [ ] `eb_pay_channel` 有行；支付方式页可保存  
- [ ] 各端拉起；重复回调不重复入账  
- [ ] 退款与 `queryRefund` 终态正确  
- [ ] notify URL：`{site_url}/api/pay/notify/{channel_key}`  

---

## 11. 排障（精选）

| 现象 | 处理 |
| --- | --- |
| scan 看不到驱动 | 未安装/未启用/owner_plugin_id 过滤 |
| 支付方式页没有 | 缺 `eb_pay_channel` 行 |
| `Driver not supported` | 目录名与 studly(driver_key) |
| 有 payInfo 端上无反应 | 补 `payment/index.vue` switch |
| 回调 200 但订单未付 | 未走 Notify；out_trade_no 不一致 |
| 改配置不生效 | `php think pay:clear-cache` |
| 退款渠道不一致 | 用支付记录里的 channel_key |

---

## 12. 打包与禁止事项

- 交付物：`backend/driver/pay/{Dir}/` 整目录 zip 到 `.plugin-packages/`（见 [插件开发规范 §7](../../../../rules/插件开发规范.md)）。  
- 禁止：驱动内改订单/退款状态；密钥入库/入 git；支付配置写 `system_config`。  
- 安全：[安全红线](../../../../rules/安全红线.md) 支付验签与幂等。

---

## 13. 与 00 总览的分工（避免重复阅读）

| 主题 | 读哪里 |
| --- | --- |
| 四名字、studly、ManifestDriverInstaller、scan 不写库 | [00-总览 §3](00-总览-类型路由与边界.md) |
| config_fields 的 type/show_if/password | [00-总览 §3.5](00-总览-类型路由与边界.md) |
| plugin:manifest-check、安装 API、arch:check | [00-总览 §5](00-总览-类型路由与边界.md) |
| pay 渠道行、PayServices 分支、收银台 switch | **本篇 §1、§6、§8** |
| 排障 | 本篇 §11 |

---

## 14. PayChannelConfig 例外说明

`crmeb/services/pay/PayChannelConfig.php` 在 crmeb 层直查 `pay_channel` 表，属于架构允许的例外（类注释明确禁止「改成 app DAO」）。驱动读配置**只走此类**，不要在驱动内 `Db::name('pay_channel')` 复制第二套逻辑。

字段合并：`config_fields` 为空时，部分路径会回读 `driver/pay/{Dir}/plugin.json` 的字段定义作 schema（全新安装未保存过配置时），与 `PayChannelServices::mergeConfigFields` 后台行为一致。

---

## 15. capabilities 与业务调用示例

声明在 `plugin.json` → 运行时 `DriverRegistry` 解码 → `PayServices::hasCapability($platform, $cap)`。

| capability | 典型调用方 |
| --- | --- |
| `pay` / `refund` / `notify` | 基础支付闭环 |
| `merchant_pay` / `merchant_pay_new` | 分销提现、抽奖转账等 |
| `query_transfer` / `transfer_notify` | 转账查询与回调 |

未声明 `merchant_pay_new` 时，业务不会走 V3 转账新接口——这不是驱动 bug，是清单能力未开放。

---

## 16. 静态命令（pay 相关）

```bash
cd backend
php -l driver/pay/YourPay/Driver.php
php think plugin:manifest-check
php think pay:clear-cache          # 改 eb_pay_channel 后
php -r 'require "vendor/autoload.php"; (new think\App(getcwd()."/"))->initialize();
  foreach (crmeb\services\driver\DriverRegistry::scan("pay") as $k=>$d)
    echo $k, " status=", $d["status"], PHP_EOL;'
```

---

## 17. 小结

支付二开稳态 = **安装写 eb_driver** + **PayChannelConfig 自取配置** + **补 eb_pay_channel 行与收银台分支** + **Notify 闭环**。驱动只负责第三方协议，业务结果由既有 `NotifyListener` 完成。
