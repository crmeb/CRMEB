# 模块领域选择

根据业务契约发生的变化确定归属，不要根据页面名称判断。

| 领域 | 目录 | 适用场景 | 核心扩展机制 |
|---|---|---|---|
| 产品类型 | `modules/product/<type>` | 定义卖什么：SKU 形态、库存、配送/履约、商品详情、售后规则 | `ProductTypeRegistry`、`OrderTypeRegistry`、`AfterSaleRuleRegistry` |
| 交易模式 | `modules/trade/<mode>` | 定义怎么卖：活动校验、活动价、限额、拼团/砍价生命周期 | `TradeModeRegistry`；部分场景使用 `OrderTypeRegistry` |
| 营销 | `modules/marketing/<name>` | 观察用户/订单行为发放奖励，或运行不改变成交契约的独立活动 | 事件 |
| 渠道 | `modules/channel/<client>` | 定义用户来源、认证身份、渠道 SDK/API 和渠道通知 | 渠道检测及代理/注册事件 |
| 分销 | `modules/distribution` | 分销关系、等级、事业部、佣金快照和结算 | 事件及带白名单的 Service 代理 |

第三方支付、短信、上传、邮件、电子面单、打印、发票、机器人、物流或商品采集服务商属于 `backend/driver/`，使用[驱动插件](driver-plugin.md)方式接入，不属于业务模块。

## 判断方法

### 产品类型与交易模式

- 改变实物/数字商品形态、库存维度、配送、使用核销或产品专属退款资格时，属于产品类型。
- 改变已有产品的价格、购买资格或限额时，属于交易模式。
- 两个维度互相正交。兼容性由产品 Handler 声明，并在需要的选品和下单校验中强制执行。

### 交易模式与营销

- 订单管线需要调用 Handler 完成交易校验或计价时，属于 trade。
- 移除功能后，订单创建/支付语义不受影响，只会停止奖励或观察行为时，属于 marketing。
- 只有参与记录、奖品记录而不创建交易订单的活动属于 marketing。

### 渠道与通用 app Service

- 渠道专属 openid、小程序码、OAuth、订阅通知或平台 SDK 行为属于渠道模块。
- 共享用户/订单行为属于 `app/`，通过核心契约或代理事件请求渠道能力。
- 渠道之间不得互相引用，`app/` 也不得直接引用渠道模块。

## 相近参考模块

参考当前源码，不使用归档计划：

- 产品类型：多维库存参考 `product/ticket`，自动数字履约参考 `product/cardkey`，物流配送参考 `product/normal`。
- 交易模式：活动计价/校验参考 `trade/seckill`，生命周期事件参考 `trade/combination`，独立订单 Adapter 参考 `trade/integral`。
- 营销：事件驱动奖励和 admin 管理参考 `marketing/lottery`，小型观察者参考 `marketing/newuser`。
- 渠道：SDK、代理和监听器参考 `channel/wechat_mini`、`channel/wechat_mp`。
- 扁平领域：大型独立模块参考 `agent`。

选择生命周期和持久化需求最接近、结构最小的参考模块；不要仅因 UI 相似就复制复杂模块。
