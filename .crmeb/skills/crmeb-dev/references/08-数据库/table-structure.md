# 数据库表结构说明（Table Structure）

> 本文是 `crmeb-dev` 技能的子文档，完整二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。表结构以已检入的安装 SQL 和 Installer 为基准；需要实库核对时默认按 `backend/.env` 从宿主机连接，不自动进入 Docker。默认数据库名 `crmeb`，前缀 `eb_`。
>
> **结构来源说明**：首版核心表结构以 `backend/public/install/crmeb_table.sql` 为准，模块扩展表以对应 `Installer.php::runMigrate()` 为准；全新安装无需补跑历史升级 SQL。

## 一、核心设计思想：中台表 + 扩展表

v8.1 采用**中台统一 + 扩展分离**的数据模型：

```
eb_product_center（商品中台：所有产品类型的通用字段）
   └── 扩展表 eb_pt_{type} / eb_pt_{type}_sku（产品类型独有字段，1:1 关联 product_id）

eb_order_center（订单中台：所有订单的统一状态/金额/履约）
   ├── 扩展表 eb_pt_{type}_order（产品类型订单独有字段，1:1 关联 order_no/source_id）
   └── eb_order_item（订单商品明细，1:N 关联 order_no）

eb_pay_channel（支付渠道：支付方式动态注册表，驱动配置存 config_fields JSON）
```

**数据源唯一原则**：`store_id`、`shipping_type`、`verify_code` 只存中台表实体列；`status_code` 从中台表读取；业务状态由扩展订单表实时推导（`resolveStatusCode()`）。

## 二、商品域核心表

### 2.1 eb_product_center —— 商品中台表（所有产品类型通用）

| 字段分组 | 字段 | 说明 |
|---------|------|------|
| 标识 | `id` / `product_type` / `name` / `intro` | product_type 如 normal/virtual/cardkey/hotel/ticket |
| 图片素材 | `image` / `slider_image` / `video_link` / `recommend_list` / `recommend_image` | 主图/轮播/视频/推荐位 |
| 价格库存 | `price` / `original_price` / `vip_price` / `stock` / `sales` / `virtual_sales` / `views` | 注意新列名 original_price（原 ot_price）、virtual_sales（原 ficti） |
| 分类排序 | `cate_id` / `sort_order` / `unit_name` / `spec_type` | spec_type=1 多规格 |
| 履约配送 | `delivery_methods`(json) / `freight_config`(json) / `service_region`(json) / `service_address` | 配送方式/运费模板/服务区域 |
| 限购 | `is_limit` / `limit_type` / `limit_num` / `min_qty` | 限购开关/类型/数量/起购 |
| 营销 | `is_gift` / `gift_price` / `give_integral` / `give_coupon_ids` | 赠品/赠送积分/赠送优惠券 |
| 展示 | `is_show` / `is_del` / `is_sub` | 上下架/删除 |
| 会员 | `is_vip` / `vip_product` | 会员专享 |
| 预售 | `presale` / `presale_start_time` / `presale_end_time` | 预售周期 |
| 有效期 | `valid_type` / `valid_start_time` / `valid_end_time` / `valid_days` | 0 长期/1 固定区间/2 天数 |
| 自定义 | `custom_form`(text) / `params_list`(text) / `protection_list` / `label_list` | 自定义表单/参数/保障/标签 |
| 时间 | `add_time` / `update_time` | int 时间戳 |

### 2.2 产品类型扩展表（各类型自建，由 Installer 创建）

各产品类型的扩展表由各自 `Installer::runMigrate()` 创建，命名约定 `pt_{type}` / `pt_{type}_sku` / `pt_{type}_order`：

| 产品类型 | 扩展表 | 说明 |
|---------|--------|------|
| normal | `pt_normal` / `pt_normal_sku` / `pt_normal_order` | 实物商品（SKU + 订单扩展） |
| virtual | `pt_virtual` / `pt_virtual_sku` / `pt_virtual_order` | 虚拟商品（网盘 SKU + 订单扩展） |
| cardkey | `pt_cardkey` / `pt_cardkey_stock` / `pt_cardkey_order` | 卡密商品（卡密库 + 订单扩展） |

> 扩展表由各产品类型插件安装时创建，卸载时由 `cleanData()` + `rollbackMigrate()` 清理。新增产品类型需在 Installer 中定义建表 SQL。

**eb_pt_normal** —— 实物商品扩展（1:1 `product_id`）：
`id`、`product_id`(uniq)、`weight`、`volume`、`presale`、`add_time`、`update_time`

**eb_pt_normal_sku** —— 实物 SKU（1:N `product_id`）：
`id`、`product_id`、`suk`（规格组合键）、`price`、`original_price`、`cost`、`vip_price`、`stock`、`bar_code`、`weight`、`volume`、`unit_name`、`image`、`unique_code`、`brokerage`（一级佣金）、`brokerage_two`（二级佣金）、`is_default_select`、`is_show`、`create_time`、`update_time`

### 2.3 表关系

```
eb_product_center (1) ──(product_id)── (N) eb_pt_normal_sku
        │ 1:1
        └──(product_id)── eb_pt_normal
```

## 三、订单域核心表

### 3.1 eb_order_center —— 订单中台表（所有订单统一入口）

| 字段分组 | 字段 | 说明 |
|---------|------|------|
| 标识 | `id` / `order_no`(uniq) / `source_type` / `source_id` / `uid` | source_type 如 normal_order，source_id 指向扩展表 id |
| 类型 | `order_category` / `product_type` / `activity_type` / `activity_id` | 分类/产品类型/交易模式 |
| 履约 | `store_id` / `verify_code` / `shipping_type` / `detail_route` | 门店ID/核销码/1快递2自提/详情路由 |
| 状态 | `status_code`(默认 pending_payment) / `pay_status` / `refund_status` | 中台状态机 |
| 金额 | `total_price` / `pay_price` / `pay_postage` / `refund_price` / `pay_type` / `coupon_price` / `deduction_price` / `use_integral_num` / `member_discount` / `vip_discount` / `gift_price` / `total_num` | 全部金额字段 |
| 展示 | `title` / `image` / `subtitle` / `remark` / `mark` | mark=买家留言、remark=管理员备注 |
| JSON 扩展 | `extra_info`(json) / `custom_form`(json) / `invoice`(json) | 通用扩展存储 |
| 时间轴 | `add_time` / `pay_time` / `delivery_time` / `take_time` / `refund_time` / `cancel_time` | int 时间戳 |
| 软删 | `is_del` | |

### 3.2 eb_order_item —— 订单商品明细（1:N `order_no`）

`id`、`order_no`、`product_id`、`product_type`、`cart_num`、`price`、`total_price`、`snapshot`(text, 下单快照)、`add_time`

### 3.3 eb_pt_normal_order —— 实物订单扩展（1:1 `order_no`/`order_item_id`）

`id`、`order_no`、`order_item_id`、`product_id`、`sku_id`、`uid`、`is_cancel`、`paid`、`pay_price`、`total_price`、`status`（扩展状态，如 pending_payment）、`has_installation`、`activity_type`、`delivery_name`、`delivery_id`、`delivery_type`、`store_id`、`verify_code`、`shipping_type`、`user_address`、`real_name`、`user_phone`、`refund_status`、`refund_price`、`add_time`、`update_time`

### 3.4 表关系

```
eb_order_center (1) ──(order_no)── (N) eb_order_item
        │ 1:1 (source_type=normal_order, source_id=扩展表id)
        └── eb_pt_normal_order
```

> 详情页 `contact` 字段优先读 `eb_order_center.extra_info.contact`，回退扩展表 `real_name`/`user_phone`/`user_address`。

## 四、支付渠道表（eb_pay_channel）

驱动化支付方式注册表（`channel_key` 唯一）：`id`、`channel_key`、`channel_name`、`platform`、`channel_type`(payment=支付方式/feature=支付功能)、`icon`、`supported_platforms`（支持的端，从 info.php 同步）、`enabled_platforms`（已开启的端）、`config_key_prefix`、`config_fields`(text, 驱动配置 JSON)、`sort`、`status`、`is_del`、`add_time`、`update_time`（建表 SQL 见 `backend/public/install/crmeb_table.sql` 的「eb_pay_channel：支付方式字典表」段）

**规则**：支付方式/配送方式/发票类型等可枚举数据一律从 `eb_pay_channel` 等配置表动态读取，禁止硬编码；驱动配置只存 `config_fields`，禁止写 `eb_system_config`。

## 五、其他域速览

| 域 | 表（前缀 eb_） | 说明 |
|----|---------------|------|
| 用户 | `user`、`user_address`、`user_bill`（资金流水）、`user_money`、`user_brokerage`（佣金）、`user_brokerage_frozen`、`user_extract`（提现）、`user_level`、`user_member`（会员）、`user_recharge`、`user_sign`（签到）、`user_spread`（分销关系）、`user_friends`、`user_group`、`user_label`、`user_label_relation`、`user_visit`、`user_cancel`、`user_enter`、`user_invoice`、`user_search` | 用户及资金/等级/分销 |
| 通知 | `notice`（站内信）、`notice_channel`、`notice_config`、`notice_log`、`notice_read`、`notice_template` | 消息通知（新架构） |
| 驱动 | `pay_channel`、`sms_driver`、`email_driver`、`bot_driver`、`upload_driver`、`upload_storage`、`express_driver`、`logistics_driver`、`invoice_driver`、`printer_channel`、`copy_driver` | 各驱动配置表，均含 config_fields JSON |
| 营销 | `store_activity`、`store_bargain`（砍价）、`store_combination`（拼团）、`store_seckill`、`store_integral`（积分商城）、`store_coupon_issue`、`store_pink`（拼团记录）、`luck_lottery`、`luck_prize`、`spread_apply`、`division_agent` | 交易模式/营销玩法 |
| 系统 | `system_admin`、`system_config`、`system_config_tab`、`system_menus`（菜单）、`system_role`、`system_route`、`system_route_cate`、`system_store`、`system_store_staff`、`system_group`、`system_group_data`、`system_crontab`（定时任务，原 system_timer）、`system_log`、`system_crud`、`system_crud_list`、`system_crud_data`、`system_event`、`system_event_data`、`system_file`、`system_pem`、`system_ticket`、`theme`、`upgrade_log`、`system_user_level`、`system_sign_reward` | 后台配置/菜单/权限/定时任务 |
| 内容 | `article`、`article_category`、`article_content`、`category`、`agreement`、`auxiliary`、`cache`、`capital_flow`、`diy`、`express`、`express_company`、`lang_code`、`lang_type`、`lang_country`、`qrcode`、`routine_scheme`、`delivery_service`、`shipping_templates`、`shipping_templates_free`、`shipping_templates_no_delivery`、`shipping_templates_region` | 文章/装修/快递/运费模板/多语言 |
| 线下收银 | `offline_receipt`、`offline_receipt_status` | 线下收银记录（收款单，原 `other_order` 更名） |
| 微信 | `wechat_user`、`wechat_key`、`wechat_media`、`wechat_message`、`wechat_news_category`、`wechat_qrcode`、`wechat_reply` | 公众号/小程序 |

## 六、废弃旧表（勿用于新二开）

- `eb_store_product`、`eb_store_product_attr`、`eb_store_product_attr_value`、`eb_store_product_attr_result` → 由 `eb_product_center` + `eb_pt_{type}_sku` 替代
- `eb_store_order`（当前为 `eb_store_order1` 中间态）、`eb_store_order_cart_info`、`eb_store_order_refund`、`eb_store_order_invoice`、`eb_store_order_status`、`eb_store_order_economize` → 由 `eb_order_center` + `eb_pt_{type}_order` 替代
- `eb_store_cart` → 由 `eb_cart` 替代
- 其他 `eb_store_*`（商品扩展信息类）逐步清理

**判断标准**：新增表走 `eb_product_center`/`eb_order_center` + `pt_` 扩展表体系；看到 `eb_store_*` 旧表用于新功能即视为违规。

## 七、表结构查询方法

```bash
# 默认：从 backend/.env 读取实际主机、端口、用户和数据库
mysql -h <hostname> -P <port> -u <username> -p <database> -e "DESCRIBE eb_order_center;"
mysql -h <hostname> -P <port> -u <username> -p <database> -e "SHOW CREATE TABLE eb_order_center;"

# 只有已明确选择根 Compose 时，宿主机才使用映射端口 33061
mysql -h 127.0.0.1 -P 33061 -u <username> -p <database> -e "SHOW TABLES;"
```

- 项目 MCP `crmeb_mysql`：`get_schema_info` 查结构、`get_table_sample` 抽样、`execute_sql` 执行
- 核心表结构改动同步到 `backend/public/install/crmeb_table.sql`；模块自有表由对应 `Installer.php::runMigrate()` 创建
