--
-- CRMEB v8 系统表结构
-- 按表名字母顺序排列；每张表前标注中文用途
--

-- eb_after_sale：售后单表
CREATE TABLE IF NOT EXISTS `eb_after_sale` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `after_sale_no` varchar(32) NOT NULL DEFAULT '' COMMENT '售后单号（全局唯一）',
  `order_center_id` bigint NOT NULL DEFAULT '0' COMMENT '关联 eb_order_center.id',
  `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号（冗余，便于查询）',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `order_category` varchar(32) NOT NULL DEFAULT '' COMMENT '订单大类: goods/points/member/recharge',
  `product_type` varchar(32) NOT NULL DEFAULT '' COMMENT '产品类型: normal/virtual/cardkey/coupon',
  `activity_type` varchar(32) NOT NULL DEFAULT '' COMMENT '活动类型: seckill/combination/bargain/integral（空=无活动）',
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT '售后类型: refund_only/return_refund/exchange/resend/repair',
  `status` varchar(32) NOT NULL DEFAULT 'pending_audit' COMMENT '售后状态码: pending_audit/audit_passed/audit_rejected/canceled/refunding/refunded/pending_return/receiving/repairing/resending/completed',
  `before_status` varchar(32) NOT NULL DEFAULT '' COMMENT '售后前中台订单状态码（取消/驳回时恢复）',
  `refund_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '申请退款金额',
  `actual_refund_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际退款金额（协商后）',
  `refund_integral` int NOT NULL DEFAULT '0' COMMENT '退还积分数',
  `return_postage` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退货运费（商家承担部分）',
  `refund_target` varchar(16) NOT NULL DEFAULT 'cash' COMMENT '退款标的物: cash/integral',
  `is_deposit_refund` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否退定金（预售专用）',
  `product_snapshot` json DEFAULT NULL COMMENT '售后商品信息快照（商品名/规格/数量/单价/图片）',
  `cart_ids` json DEFAULT NULL COMMENT '售后关联的购物车明细ID列表（JSON数组，如[3,5]）',
  `refund_num` int NOT NULL DEFAULT '0' COMMENT '总售后数量（明细refund_num之和，便于快速查询）',
  `refund_type` varchar(32) NOT NULL DEFAULT '' COMMENT '退款方式: original/balance（原路退回/退余额）',
  `refund_trade_no` varchar(64) NOT NULL DEFAULT '' COMMENT '退款交易流水号（支付渠道返回）',
  `return_delivery_type` varchar(32) NOT NULL DEFAULT '' COMMENT '退货快递公司',
  `return_delivery_no` varchar(64) NOT NULL DEFAULT '' COMMENT '退货快递单号',
  `return_delivery_images` json DEFAULT NULL COMMENT '退货快递凭证图片',
  `resend_delivery_type` varchar(32) NOT NULL DEFAULT '' COMMENT '补发快递公司',
  `resend_delivery_no` varchar(64) NOT NULL DEFAULT '' COMMENT '补发快递单号',
  `reason` varchar(500) NOT NULL DEFAULT '' COMMENT '用户申请原因',
  `evidence_images` json DEFAULT NULL COMMENT '凭证图片数组',
  `reject_reason` varchar(500) NOT NULL DEFAULT '' COMMENT '商家拒绝原因',
  `audit_uid` int NOT NULL DEFAULT '0' COMMENT '审核人ID',
  `audit_remark` varchar(500) NOT NULL DEFAULT '' COMMENT '审核备注',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '管理员备注',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '软删除',
  `repair_remark` varchar(1000) NOT NULL DEFAULT '' COMMENT '维修备注（维修过程记录）',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '申请时间',
  `audit_time` int NOT NULL DEFAULT '0' COMMENT '审核时间',
  `return_time` int NOT NULL DEFAULT '0' COMMENT '用户退货时间',
  `receive_time` int NOT NULL DEFAULT '0' COMMENT '商家收货时间',
  `refund_time` int NOT NULL DEFAULT '0' COMMENT '退款时间',
  `resend_time` int NOT NULL DEFAULT '0' COMMENT '补发时间',
  `repair_time` int NOT NULL DEFAULT '0' COMMENT '维修开始时间',
  `repair_complete_time` int NOT NULL DEFAULT '0' COMMENT '维修完成时间',
  `complete_time` int NOT NULL DEFAULT '0' COMMENT '完成时间',
  `cancel_time` int NOT NULL DEFAULT '0' COMMENT '取消时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_after_sale_no` (`after_sale_no`),
  KEY `idx_order_center_id` (`order_center_id`),
  KEY `idx_order_no` (`order_no`),
  KEY `idx_uid` (`uid`),
  KEY `idx_type_status` (`type`,`status`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`order_category`),
  KEY `idx_add_time` (`add_time`),
  KEY `idx_order_no_status` (`order_no`,`is_del`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='售后单表';

-- eb_after_sale_item：售后商品明细
CREATE TABLE IF NOT EXISTS `eb_after_sale_item` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `after_sale_id` bigint unsigned NOT NULL COMMENT '售后单ID',
  `order_item_key` varchar(96) NOT NULL COMMENT '订单商品行稳定键',
  `cart_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '订单快照中的cart_id',
  `product_id` bigint unsigned NOT NULL DEFAULT '0',
  `sku_unique` varchar(128) NOT NULL DEFAULT '',
  `product_type` varchar(32) NOT NULL DEFAULT '',
  `activity_type` varchar(32) NOT NULL DEFAULT '',
  `refund_num` int unsigned NOT NULL DEFAULT '0',
  `refund_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `refund_integral` int unsigned NOT NULL DEFAULT '0',
  `snapshot` json DEFAULT NULL COMMENT '申请时固化的商品、活动与权益快照',
  `add_time` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_after_sale_item` (`after_sale_id`,`order_item_key`),
  KEY `idx_after_sale_id` (`after_sale_id`),
  KEY `idx_product_sku` (`product_id`,`sku_unique`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='售后商品明细';

-- eb_agreement：协议表（含插件协议）
CREATE TABLE IF NOT EXISTS `eb_agreement` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '协议类型  1：付费会员协议,2:代理商协议(分销插件),3:隐私协议,4:用户协议,5:注销协议,6:积分协议,7:关于我们,8:分销说明(分销插件)',
  `owner_plugin_id` varchar(96) NOT NULL DEFAULT '' COMMENT '所属插件复合标识，空为系统协议',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '协议名称',
  `content` longtext COMMENT '协议内容',
  `system_prompt` text COMMENT '系统AI提示词（AI写作的角色设定与格式要求）',
  `user_prompt` text COMMENT '用户提示词（AI写作输入框默认提示词）',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序倒序',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '1：显示：0：不显示',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_type_plugin` (`type`,`owner_plugin_id`) USING BTREE,
  KEY `idx_owner_plugin_id` (`owner_plugin_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC COMMENT='协议表（含插件协议）';

-- eb_ai_model：AI模型登记表（系统内置AI能力）
CREATE TABLE IF NOT EXISTS `eb_ai_model` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '模型显示名称',
  `protocol` varchar(32) NOT NULL DEFAULT 'openai_compatible' COMMENT '协议族标识',
  `provider` varchar(50) NOT NULL DEFAULT '' COMMENT '厂商标识（仅展示）',
  `model` varchar(100) NOT NULL DEFAULT '' COMMENT '模型标识',
  `base_url` varchar(255) NOT NULL DEFAULT '' COMMENT '接口地址',
  `full_url` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '完整URL模式：1=请求地址已是完整接口路径，不再补全 /chat/completions',
  `api_key` varchar(255) NOT NULL DEFAULT '' COMMENT '接口密钥',
  `supports_tools` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否支持工具调用',
  `supports_vision` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否支持视觉输入',
  `supports_embedding` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否支持文本向量化',
  `context_window` int unsigned NOT NULL DEFAULT '0' COMMENT '上下文窗口大小',
  `max_tokens` int unsigned NOT NULL DEFAULT '2048' COMMENT '单次最大输出token',
  `temperature` decimal(3,2) NOT NULL DEFAULT '0.70' COMMENT '默认采样温度',
  `is_default` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否系统默认模型',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序倒序',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '1：启用：0：停用',
  `create_time` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_protocol` (`protocol`) USING BTREE,
  KEY `idx_default` (`is_default`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC COMMENT='AI模型登记表（系统内置AI能力）';

-- eb_article：文章管理表
CREATE TABLE IF NOT EXISTS `eb_article` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '文章管理ID',
  `cid` varchar(255) NOT NULL DEFAULT '0' COMMENT '分类id',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '文章标题',
  `author` varchar(255) NOT NULL DEFAULT '' COMMENT '文章作者',
  `image_input` varchar(255) NOT NULL DEFAULT '' COMMENT '文章图片',
  `synopsis` varchar(255) NOT NULL DEFAULT '' COMMENT '文章简介',
  `share_title` varchar(255) NOT NULL DEFAULT '' COMMENT '文章分享标题',
  `share_synopsis` varchar(255) NOT NULL DEFAULT '' COMMENT '文章分享简介',
  `visit` varchar(255) NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `sort` int unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '原文链接',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `add_time` varchar(255) NOT NULL DEFAULT '' COMMENT '添加时间',
  `hide` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员id',
  `mer_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商户id',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '商品关联id',
  `is_hot` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否热门(小程序)',
  `is_banner` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否轮播图(小程序)',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='文章管理表';

-- eb_article_category：文章分类表
CREATE TABLE IF NOT EXISTS `eb_article_category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '文章分类id',
  `pid` int NOT NULL DEFAULT '0' COMMENT '父级ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '文章分类标题',
  `intr` varchar(255) NOT NULL DEFAULT '' COMMENT '文章分类简介',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '文章分类图片',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `sort` int unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_del` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '1删除0未删除',
  `add_time` varchar(255) NOT NULL DEFAULT '' COMMENT '添加时间',
  `hidden` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='文章分类表';

-- eb_article_content：文章内容表
CREATE TABLE IF NOT EXISTS `eb_article_content` (
  `nid` int unsigned NOT NULL DEFAULT '0' COMMENT '文章id',
  `content` longtext COMMENT '文章内容',
  UNIQUE KEY `nid` (`nid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='文章内容表';

-- eb_auxiliary：辅助表
CREATE TABLE IF NOT EXISTS `eb_auxiliary` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `binding_id` int NOT NULL DEFAULT '0' COMMENT '绑定id',
  `relation_id` int NOT NULL DEFAULT '0' COMMENT '关联id',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型0=客服转接辅助，1=商品和分类辅助，2=优惠券和商品辅助',
  `other` varchar(500) NOT NULL DEFAULT '' COMMENT '其他数据为json',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='辅助表';

-- eb_cache：微信缓存表
CREATE TABLE IF NOT EXISTS `eb_cache` (
  `key` varchar(32) NOT NULL DEFAULT '' COMMENT '缓存key',
  `result` text COMMENT '缓存数据',
  `expire_time` int NOT NULL DEFAULT '0' COMMENT '失效时间0=永久',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '缓存时间',
  PRIMARY KEY (`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='微信缓存表';

-- eb_capital_flow：资金流水表
CREATE TABLE IF NOT EXISTS `eb_capital_flow` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `flow_id` varchar(32) NOT NULL DEFAULT '' COMMENT '流水id',
  `order_id` varchar(50) NOT NULL DEFAULT '' COMMENT '关联id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '昵称',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '电话',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '交易金额',
  `trading_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '交易类型',
  `pay_type` varchar(32) NOT NULL DEFAULT '' COMMENT '支付类型',
  `mark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '交易时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='资金流水表';

-- eb_cart：购物车表
CREATE TABLE IF NOT EXISTS `eb_cart` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '商品ID',
  `product_type` varchar(32) NOT NULL DEFAULT '' COMMENT '产品类型',
  `product_attr_unique` varchar(100) NOT NULL DEFAULT '',
  `cart_num` int NOT NULL DEFAULT '1' COMMENT '数量',
  `is_pay` tinyint NOT NULL DEFAULT '0' COMMENT '0未购买 1已购买',
  `is_del` tinyint NOT NULL DEFAULT '0' COMMENT '是否删除',
  `is_new` tinyint NOT NULL DEFAULT '0' COMMENT '是否立即购买',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '加车时间',
  PRIMARY KEY (`id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='购物车表';

-- eb_category：标签分类
CREATE TABLE IF NOT EXISTS `eb_category` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` int NOT NULL DEFAULT '0' COMMENT '上级id',
  `owner_id` int NOT NULL DEFAULT '0' COMMENT '所属人，为全部',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '分类名称',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '分类类型0=标签分类，1=快捷短语分类',
  `other` text COMMENT '其他参数',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `name` (`name`),
  KEY `is_be` (`owner_id`,`type`,`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='标签分类';

-- eb_delivery_service：配送员表
CREATE TABLE IF NOT EXISTS `eb_delivery_service` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '配送员uid',
  `avatar` varchar(250) NOT NULL DEFAULT '' COMMENT '配送员头像',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '配送员名称',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号码',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '0隐藏1显示',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC COMMENT='配送员表';

-- eb_diy：DIY数据表
CREATE TABLE IF NOT EXISTS `eb_diy` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(255) NOT NULL DEFAULT '' COMMENT '版本号',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '页面名称',
  `template_name` varchar(255) NOT NULL DEFAULT '' COMMENT '模板名称',
  `value` longtext COMMENT '页面数据',
  `default_value` longtext COMMENT '默认页面数据',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否使用',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '页面类型',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '显示首页',
  `is_bg_color` tinyint(1) NOT NULL DEFAULT '0' COMMENT '颜色是否选中',
  `is_bg_pic` tinyint(1) NOT NULL DEFAULT '0' COMMENT '背景图是否选中',
  `color_picker` varchar(50) NOT NULL DEFAULT '' COMMENT '背景颜色',
  `bg_pic` varchar(256) NOT NULL DEFAULT '' COMMENT '背景图',
  `bg_tab_val` tinyint(1) NOT NULL DEFAULT '0' COMMENT '背景图图片样式',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `order_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '个人中心订单样式',
  `my_banner_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '个人中心banner是否显示',
  `my_menus_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '个人中心我的服务样式',
  `business_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '个人中心商家管理样式',
  `is_diy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否diy',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT 'diy顶部title',
  `is_pro` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否新版本',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='DIY数据表';

-- eb_driver：统一驱动字典表
CREATE TABLE IF NOT EXISTS `eb_driver` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `driver_type` varchar(32) NOT NULL DEFAULT '' COMMENT '驱动类型：upload/sms/email/bot/logistics/express/invoice/copyproduct/pay/printer',
  `driver_key` varchar(64) NOT NULL DEFAULT '' COMMENT '驱动标识',
  `driver_name` varchar(100) NOT NULL DEFAULT '' COMMENT '显示名称',
  `class_name` varchar(100) NOT NULL DEFAULT 'Driver' COMMENT '驱动类名',
  `namespace` varchar(160) NOT NULL DEFAULT '' COMMENT '驱动命名空间',
  `owner_plugin_id` varchar(96) NOT NULL DEFAULT '' COMMENT '所属驱动插件复合标识',
  `icon` varchar(128) NOT NULL DEFAULT '' COMMENT '图标',
  `description` text COMMENT '描述',
  `is_builtin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否内置：0=扩展 1=内置',
  `capabilities` text COMMENT '能力配置 JSON',
  `config_fields` text COMMENT '配置字段定义 JSON',
  `help_doc` text COMMENT '帮助文档 JSON',
  `domain_template` varchar(255) NOT NULL DEFAULT '' COMMENT '域名模板（upload 专属）',
  `bucket_fields` text COMMENT '存储空间配置字段 JSON（upload 专属）',
  `thumb_watermark_config` text COMMENT '缩略图和水印配置 JSON（upload 专属）',
  `platforms` text COMMENT '支持平台 JSON（copyproduct 专属）',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序（越大越靠前）',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0=关闭 1=启用',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_type_driver` (`driver_type`,`driver_key`),
  KEY `idx_status_sort` (`status`,`sort`),
  KEY `idx_owner_plugin_id` (`owner_plugin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='统一驱动字典表';

-- eb_express：快递公司表
CREATE TABLE IF NOT EXISTS `eb_express` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '快递公司id',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '快递公司简称',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '快递公司全称',
  `partner_id` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要月结账号',
  `partner_key` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要月结密码',
  `net` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要取件网店',
  `check_man` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否填写电子面单承载快递员名',
  `partner_name` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否填写电子面单客户账户名称',
  `is_code` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否填写电子面单承载编号',
  `courier_name` varchar(100) NOT NULL DEFAULT '' COMMENT '承载快递员名',
  `customer_name` varchar(100) NOT NULL DEFAULT '' COMMENT '客户账户名称',
  `code_name` varchar(100) NOT NULL DEFAULT '' COMMENT '电子面单承载编号',
  `account` varchar(100) NOT NULL DEFAULT '' COMMENT '账号',
  `key` varchar(100) NOT NULL DEFAULT '' COMMENT '密码',
  `net_name` varchar(100) NOT NULL DEFAULT '' COMMENT '网点名称',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可用',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `code` (`code`) USING BTREE,
  KEY `is_show` (`is_show`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='快递公司表';

-- eb_express_company：电子面单物流公司管理
CREATE TABLE IF NOT EXISTS `eb_express_company` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `driver_key` varchar(32) NOT NULL DEFAULT '' COMMENT '绑定驱动标识',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '快递公司编码',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '快递公司名称',
  `account` varchar(100) NOT NULL DEFAULT '' COMMENT '月结账号',
  `key` varchar(100) NOT NULL DEFAULT '' COMMENT '月结密钥',
  `net_name` varchar(100) NOT NULL DEFAULT '' COMMENT '网点名称',
  `courier_name` varchar(100) NOT NULL DEFAULT '' COMMENT '揽件人',
  `customer_name` varchar(100) NOT NULL DEFAULT '' COMMENT '客户名',
  `code_name` varchar(100) NOT NULL DEFAULT '' COMMENT '编码名称',
  `siid` varchar(100) NOT NULL DEFAULT '' COMMENT '云打印机编号',
  `temp_id` varchar(100) NOT NULL DEFAULT '' COMMENT '快递模板ID',
  `print_open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启打印',
  `partner_id` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用月结账号',
  `partner_key` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用月结密钥',
  `net` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用网点',
  `check_man` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用揽件人',
  `partner_name` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用客户名',
  `is_code` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用编码',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_driver` (`driver_key`),
  KEY `idx_status_sort` (`status`,`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='电子面单物流公司管理';

-- eb_express_dump_log：电子面单打印记录表
CREATE TABLE IF NOT EXISTS `eb_express_dump_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `order_id` int NOT NULL DEFAULT '0' COMMENT '订单ID',
  `order_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '订单编号',
  `driver_key` varchar(32) NOT NULL DEFAULT '' COMMENT '使用的驱动标识',
  `com` varchar(50) NOT NULL DEFAULT '' COMMENT '快递公司编码',
  `com_name` varchar(100) NOT NULL DEFAULT '' COMMENT '快递公司名称',
  `temp_id` varchar(64) NOT NULL DEFAULT '' COMMENT '使用的面单模板ID',
  `kuaidinum` varchar(64) NOT NULL DEFAULT '' COMMENT '快递单号',
  `label` varchar(500) NOT NULL DEFAULT '' COMMENT '面单图片URL',
  `print_type` varchar(20) NOT NULL DEFAULT '' COMMENT '打印方式：IMAGE=图片 CLOUD=云打印',
  `siid` varchar(64) NOT NULL DEFAULT '' COMMENT '打印机编号',
  `to_name` varchar(50) NOT NULL DEFAULT '' COMMENT '收件人姓名',
  `to_tel` varchar(20) NOT NULL DEFAULT '' COMMENT '收件人电话',
  `to_addr` varchar(255) NOT NULL DEFAULT '' COMMENT '收件人地址',
  `from_name` varchar(50) NOT NULL DEFAULT '' COMMENT '寄件人姓名',
  `from_tel` varchar(20) NOT NULL DEFAULT '' COMMENT '寄件人电话',
  `from_addr` varchar(255) NOT NULL DEFAULT '' COMMENT '寄件人地址',
  `operator_id` int NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `operator_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人姓名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '打印状态：0=失败 1=成功',
  `error_msg` varchar(500) NOT NULL DEFAULT '' COMMENT '错误信息',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_com` (`com`),
  KEY `idx_add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='电子面单打印记录表';

-- eb_express_setting：电子面单业务设置
CREATE TABLE IF NOT EXISTS `eb_express_setting` (
  `id` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '固定记录ID',
  `open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '电子面单打印开关',
  `siid` varchar(100) NOT NULL DEFAULT '' COMMENT '云打印机编号',
  `company_id` varchar(100) NOT NULL DEFAULT '' COMMENT '默认快递公司ID',
  `temp_id` varchar(100) NOT NULL DEFAULT '' COMMENT '默认面单模板ID',
  `com` varchar(50) NOT NULL DEFAULT '' COMMENT '默认快递公司编码',
  `to_name` varchar(50) NOT NULL DEFAULT '' COMMENT '默认寄件人姓名',
  `to_tel` varchar(30) NOT NULL DEFAULT '' COMMENT '默认寄件人电话',
  `to_address` varchar(255) NOT NULL DEFAULT '' COMMENT '默认寄件人地址',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='电子面单业务设置';

-- eb_express_template：电子面单模板表
CREATE TABLE IF NOT EXISTS `eb_express_template` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `driver_key` varchar(32) NOT NULL DEFAULT '' COMMENT '所属驱动标识',
  `com` varchar(50) NOT NULL DEFAULT '' COMMENT '快递公司编码',
  `com_name` varchar(100) NOT NULL DEFAULT '' COMMENT '快递公司名称',
  `temp_id` varchar(64) NOT NULL DEFAULT '' COMMENT '模板ID',
  `temp_name` varchar(128) NOT NULL DEFAULT '' COMMENT '模板名称',
  `width` int NOT NULL DEFAULT '0' COMMENT '模板宽度(mm)',
  `height` int NOT NULL DEFAULT '0' COMMENT '模板高度(mm)',
  `preview_url` varchar(500) NOT NULL DEFAULT '' COMMENT '预览图URL',
  `extra_config` text COMMENT '附加配置JSON',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '启用状态：0=关闭 1=启用',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_com_temp` (`com`,`temp_id`),
  KEY `idx_driver_key` (`driver_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='电子面单模板表';

-- eb_lang_code：语言code表
CREATE TABLE IF NOT EXISTS `eb_lang_code` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type_id` int NOT NULL DEFAULT '0' COMMENT '语言类型',
  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '状态码',
  `remarks` varchar(255) NOT NULL DEFAULT '' COMMENT '备注说明',
  `lang_explain` varchar(255) NOT NULL DEFAULT '' COMMENT '说明',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '服务端1，用户端2',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25231 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='语言code表';

-- eb_lang_country：浏览器语言类型表
CREATE TABLE IF NOT EXISTS `eb_lang_country` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type_id` int NOT NULL DEFAULT '0' COMMENT '管理语言类型',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '国家标识',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '国家名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=351 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='浏览器语言类型表';

-- eb_lang_type：语言类型
CREATE TABLE IF NOT EXISTS `eb_lang_type` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `language_name` varchar(50) NOT NULL DEFAULT '' COMMENT '语言名称',
  `file_name` varchar(20) DEFAULT '' COMMENT '配置文件名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1启用0禁用',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认语言',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='语言类型';

-- eb_member_card：会员卡表
CREATE TABLE IF NOT EXISTS `eb_member_card` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `card_batch_id` int unsigned NOT NULL DEFAULT '0' COMMENT '卡批次id',
  `card_number` varchar(20) NOT NULL DEFAULT '' COMMENT '卡号',
  `card_password` char(12) NOT NULL DEFAULT '' COMMENT '密码',
  `use_uid` int NOT NULL DEFAULT '0' COMMENT '使用用户',
  `use_time` int NOT NULL DEFAULT '0' COMMENT '使用时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '卡状态：0：冻结；1：激活',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`,`card_batch_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC COMMENT='会员卡表';

-- eb_member_card_batch：会员卡批次表
CREATE TABLE IF NOT EXISTS `eb_member_card_batch` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `title` varchar(100) NOT NULL DEFAULT '0' COMMENT '批次名称',
  `total_num` int unsigned NOT NULL DEFAULT '0' COMMENT '生成卡数量',
  `use_start_time` int unsigned NOT NULL DEFAULT '7' COMMENT '体验开始时间',
  `use_end_time` int NOT NULL DEFAULT '0' COMMENT '体验结束时间',
  `use_day` int NOT NULL DEFAULT '0' COMMENT '体验天数',
  `use_num` int unsigned NOT NULL DEFAULT '0' COMMENT '使用',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否生效,控制此批次所有卡0：不生效；1：生效',
  `sort` int unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `qrcode` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码图路径',
  `remark` varchar(512) NOT NULL DEFAULT '' COMMENT '备注',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC COMMENT='会员卡批次表';

-- eb_member_order：会员订单表
CREATE TABLE IF NOT EXISTS `eb_member_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `order_id` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单类型 0=免费领取 1=购买 2=卡密领取',
  `channel_type` varchar(32) NOT NULL DEFAULT '' COMMENT '渠道类型',
  `pay_type` varchar(32) NOT NULL DEFAULT '' COMMENT '支付方式',
  `member_type` varchar(32) NOT NULL DEFAULT '' COMMENT '会员类型',
  `member_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '会员价格',
  `pay_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实付金额',
  `code` varchar(64) NOT NULL DEFAULT '' COMMENT '会员卡密',
  `vip_day` int NOT NULL DEFAULT '0' COMMENT '会员天数',
  `is_permanent` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否永久会员 0=否 1=是',
  `is_free` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否免费领取 0=否 1=是',
  `overdue_time` bigint NOT NULL DEFAULT '0' COMMENT '会员到期时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态',
  `paid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付状态 0=未支付 1=已支付',
  `pay_time` int unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `trade_no` varchar(64) NOT NULL DEFAULT '' COMMENT '第三方交易单号',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '原始金额',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_order_id` (`order_id`) USING BTREE,
  KEY `idx_uid` (`uid`) USING BTREE,
  KEY `idx_paid` (`paid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='会员订单表';

-- eb_member_order_status：会员订单状态变更表
CREATE TABLE IF NOT EXISTS `eb_member_order_status` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `order_id` int unsigned NOT NULL DEFAULT '0' COMMENT '订单id',
  `change_type` varchar(64) NOT NULL DEFAULT '' COMMENT '变更类型',
  `change_message` varchar(500) NOT NULL DEFAULT '' COMMENT '变更消息',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_order_id` (`order_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='会员订单状态变更表';

-- eb_member_right：会员权益
CREATE TABLE IF NOT EXISTS `eb_member_right` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `right_type` varchar(100) NOT NULL DEFAULT '' COMMENT '权益类别',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT ' 权益名称',
  `show_title` varchar(255) NOT NULL DEFAULT '' COMMENT '显示权益名称',
  `image` varchar(200) NOT NULL DEFAULT '' COMMENT '权益图标',
  `explain` varchar(1024) NOT NULL DEFAULT '' COMMENT '权益介绍',
  `number` int NOT NULL DEFAULT '1' COMMENT '规则数字',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序倒序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0:禁用，1：启用',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`,`right_type`) USING BTREE,
  KEY `type` (`right_type`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC COMMENT='会员权益';

-- eb_member_ship：会员类型
CREATE TABLE IF NOT EXISTS `eb_member_ship` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` varchar(20) NOT NULL DEFAULT 'month' COMMENT '会员类别month:月卡会员；quarter:季卡；year:年卡；ever:永久；free:免费',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '会员名称',
  `vip_day` int NOT NULL DEFAULT '0' COMMENT '会员时间(天)',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
  `pre_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '优惠后价格',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序倒序',
  `is_del` int NOT NULL DEFAULT '0' COMMENT '删除',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC COMMENT='会员类型';

-- eb_module_install_log：模块安装日志表
CREATE TABLE IF NOT EXISTS `eb_module_install_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `module_key` varchar(96) NOT NULL DEFAULT '' COMMENT '插件复合标识，如 module:product:normal、driver:pay:wechat_pay',
  `module_name` varchar(64) NOT NULL DEFAULT '' COMMENT '模块名称',
  `module_type` varchar(32) NOT NULL DEFAULT '' COMMENT '模块类型 product_type/activity',
  `version` varchar(32) NOT NULL DEFAULT '1.0.0' COMMENT '模块版本',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1已安装 0已卸载',
  `enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未启用 1已启用',
  `install_time` int NOT NULL DEFAULT '0' COMMENT '安装时间',
  `uninstall_time` int NOT NULL DEFAULT '0' COMMENT '卸载时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_module_key` (`module_key`),
  KEY `idx_status` (`status`),
  KEY `idx_module_type` (`module_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='模块安装日志表';

-- eb_notice：站内信
CREATE TABLE IF NOT EXISTS `eb_notice` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` varchar(32) NOT NULL DEFAULT 'system' COMMENT '类型：system(系统通知)/order(订单通知)/activity(活动通知)/user(用户消息)',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COMMENT '内容',
  `image` varchar(500) NOT NULL DEFAULT '' COMMENT '封面图',
  `link_type` varchar(32) NOT NULL DEFAULT 'none' COMMENT '链接类型：none/page/webview/order/activity',
  `link_url` varchar(500) NOT NULL DEFAULT '' COMMENT '链接地址',
  `target_type` varchar(32) NOT NULL DEFAULT 'all' COMMENT '目标类型：all(全部用户)/user(指定用户)/level(指定等级)',
  `target_ids` text COMMENT '目标ID列表(JSON数组)',
  `module_key` varchar(50) NOT NULL DEFAULT '' COMMENT '关联模块或事件标识',
  `extra_data` json DEFAULT NULL COMMENT '额外数据(JSON)',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态：0草稿 1已发布 2已撤回',
  `send_at` int NOT NULL DEFAULT '0' COMMENT '发送时间',
  `expire_at` int NOT NULL DEFAULT '0' COMMENT '过期时间',
  `total_count` int NOT NULL DEFAULT '0' COMMENT '目标人数',
  `read_count` int NOT NULL DEFAULT '0' COMMENT '已读人数',
  `created_by` int NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `created_at` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `is_del` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_send_at` (`send_at`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='站内信';

-- eb_notice_channel：消息渠道配置表
CREATE TABLE IF NOT EXISTS `eb_notice_channel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '渠道ID',
  `channel_key` varchar(50) NOT NULL COMMENT '渠道标识（is_system/is_sms/is_wechat/is_routine/is_ent_wechat/is_dingtalk/is_email/is_printer）',
  `owner_plugin_id` varchar(160) NOT NULL DEFAULT '' COMMENT '渠道所有者（canonical 插件 ID）',
  `channel_name` varchar(100) NOT NULL COMMENT '渠道名称',
  `adapter_class` varchar(255) NOT NULL DEFAULT '' COMMENT '渠道适配器类名（实现 NoticeChannelInterface，如 app\services\message\channel\SmsChannel）',
  `support_user` tinyint(1) NOT NULL DEFAULT '1' COMMENT '支持用户端消息（0否/1是）',
  `support_platform` tinyint(1) NOT NULL DEFAULT '1' COMMENT '支持平台端消息（0否/1是）',
  `icon` varchar(255) DEFAULT '' COMMENT '图标',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（0禁用/1启用）',
  `config` text COMMENT '渠道配置（JSON格式，如Webhook、API Key等）',
  `sort` int unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `description` varchar(500) DEFAULT '' COMMENT '渠道描述',
  `created_at` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `channel_key` (`channel_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='消息渠道配置表';

-- eb_notice_config：通知配置主表
CREATE TABLE IF NOT EXISTS `eb_notice_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `mark` varchar(50) NOT NULL DEFAULT '' COMMENT '消息标识',
  `owner_plugin_id` varchar(160) NOT NULL DEFAULT '' COMMENT '通知所有者（canonical 插件 ID）',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '通知类型名称',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '通知场景说明',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=用户通知 2=管理员通知',
  `variable` varchar(256) NOT NULL DEFAULT '' COMMENT '可用变量列表',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mark_type` (`mark`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='通知配置主表';

-- eb_notice_log：消息发送记录表
CREATE TABLE IF NOT EXISTS `eb_notice_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `mark` varchar(100) NOT NULL COMMENT '消息标识',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID（0为系统通知）',
  `channel_key` varchar(50) NOT NULL COMMENT '渠道标识',
  `channel_name` varchar(100) DEFAULT '' COMMENT '渠道名称',
  `title` varchar(255) DEFAULT '' COMMENT '消息标题',
  `content` text COMMENT '消息内容',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发送状态（0待发送/1成功/2失败）',
  `error_msg` varchar(500) DEFAULT '' COMMENT '错误信息',
  `send_time` int unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  `created_at` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `mark` (`mark`),
  KEY `uid` (`uid`),
  KEY `channel_key` (`channel_key`),
  KEY `status` (`status`),
  KEY `send_time` (`send_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='消息发送记录表';

-- eb_notice_read：站内信已读记录
CREATE TABLE IF NOT EXISTS `eb_notice_read` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `notice_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '站内信ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `is_read` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否已读：0未读 1已读',
  `read_at` int NOT NULL DEFAULT '0' COMMENT '阅读时间',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '用户是否删除：0否 1是',
  `created_at` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_notice_uid` (`notice_id`,`uid`),
  KEY `idx_uid_read` (`uid`,`is_read`),
  KEY `idx_uid_deleted` (`uid`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='站内信已读记录';

-- eb_notice_template：通知-渠道关联表
CREATE TABLE IF NOT EXISTS `eb_notice_template` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `notification_id` int unsigned NOT NULL COMMENT '通知ID（关联eb_system_notification.id）',
  `channel_key` varchar(50) NOT NULL COMMENT '渠道标识（is_system/is_sms/is_wechat/is_routine/is_ent_wechat/is_dingtalk/is_email/is_printer）',
  `status` tinyint(1) NOT NULL DEFAULT '2' COMMENT '状态（0：不存在，1：开启，2：关闭）',
  `template_title` varchar(200) NOT NULL DEFAULT '' COMMENT '渠道消息标题',
  `template_content` text COMMENT '渠道消息内容模板',
  `extra` text COMMENT '渠道特有配置（JSON格式，如Webhook地址、模板ID等）',
  `created_at` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `notice_template` (`notification_id`,`channel_key`),
  KEY `channel_key` (`channel_key`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='通知-渠道关联表';

-- eb_offline_receipt：其他订单表（线下收银）
CREATE TABLE IF NOT EXISTS `eb_offline_receipt` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `order_id` int unsigned NOT NULL DEFAULT '0' COMMENT '订单id',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型',
  `trade_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '交易类型',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额',
  `paid_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实付金额',
  `refund_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `coupon_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券金额',
  `integral_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '积分抵扣金额',
  `give_integral` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '赠送积分',
  `give_brokerage` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '赠送佣金',
  `give_brokerage_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '赠送佣金金额',
  `paid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付状态',
  `pay_time` int unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `refund_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '退款状态',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='其他订单表（线下收银）';

-- eb_offline_receipt_status：其他订单状态变更表
CREATE TABLE IF NOT EXISTS `eb_offline_receipt_status` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL DEFAULT '0' COMMENT '订单id',
  `change_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '变更类型',
  `change_message` varchar(500) NOT NULL DEFAULT '' COMMENT '变更消息',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='其他订单状态变更表';

-- eb_order_center：订单中台汇总表（统一所有订单类型的订单数据）
CREATE TABLE IF NOT EXISTS `eb_order_center` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号（全局唯一）',
  `source_type` varchar(32) NOT NULL DEFAULT '' COMMENT '来源类型标识（normal_order/integral_order/offline_receipt/user_recharge）',
  `source_id` int NOT NULL DEFAULT '0' COMMENT '源订单表ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `order_category` varchar(32) NOT NULL DEFAULT '' COMMENT '订单大类：goods/points/member/recharge',
  `product_type` varchar(32) NOT NULL DEFAULT '' COMMENT '产品类型（normal/virtual/cardkey/coupon），非商品订单留空',
  `store_id` int NOT NULL DEFAULT '0' COMMENT '门店ID（到店自提必填，不自提默认为0）',
  `verify_code` varchar(32) NOT NULL DEFAULT '' COMMENT '核销码（到店自提/景区门票/酒店等需要核销的场景）',
  `shipping_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '配送方式：1=快递配送 2=到店自提 3=上门服务',
  `activity_type` varchar(32) NOT NULL DEFAULT '' COMMENT '活动类型（seckill/combination/bargain/advance），非活动订单留空',
  `activity_id` int unsigned NOT NULL DEFAULT '0' COMMENT '活动ID',
  `status_code` varchar(32) NOT NULL DEFAULT 'pending_payment' COMMENT '统一状态码：pending_payment/processing/completed/canceled/refunding/refunded',
  `pay_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付状态：0未支付 1已支付 2部分退款 3全额退款',
  `pay_counted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '购买次数已记账：0未记账 1已记账',
  `take_rewards_given` tinyint(1) NOT NULL DEFAULT '0' COMMENT '收货奖励已记账：0未记账 1已记账',
  `refund_reward_accounted` json DEFAULT NULL COMMENT '退款奖励累计已处理目标（含已核对短缺）',
  `refund_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '退款状态：0无退款 1申请中 2已退款',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总金额',
  `pay_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际支付金额',
  `pay_postage` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费（非商品订单为0）',
  `refund_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '已退款金额',
  `pay_type` varchar(32) NOT NULL DEFAULT '' COMMENT '支付方式，值与 eb_pay_channel.channel_key 对齐',
  `coupon_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券抵扣金额',
  `deduction_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '积分抵扣金额',
  `use_integral_num` int NOT NULL DEFAULT '0' COMMENT '使用的积分数',
  `member_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '等级会员优惠金额',
  `vip_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付费会员优惠金额',
  `gift_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '礼品费用',
  `gift_uid` int NOT NULL DEFAULT '0' COMMENT '礼品领取人UID（0=未领取）',
  `total_num` int NOT NULL DEFAULT '0' COMMENT '商品数量总数',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '订单标题摘要',
  `image` varchar(500) NOT NULL DEFAULT '' COMMENT '缩略图',
  `subtitle` varchar(255) NOT NULL DEFAULT '' COMMENT '副标题（SKU等）',
  `extra_info` json DEFAULT NULL COMMENT '扩展信息JSON（contact/优惠明细/履约详情等）',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '管理员备注',
  `mark` text COMMENT '买家留言（用户下单时填写）',
  `custom_form` json DEFAULT NULL COMMENT '自定义表单数据（JSON格式）',
  `invoice` json DEFAULT NULL COMMENT '发票信息（JSON格式）',
  `detail_route` varchar(255) NOT NULL DEFAULT '' COMMENT '详情页跳转路由',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '下单时间',
  `pay_time` int NOT NULL DEFAULT '0' COMMENT '支付时间',
  `delivery_time` int NOT NULL DEFAULT '0' COMMENT '发货/履约时间',
  `take_time` int NOT NULL DEFAULT '0' COMMENT '收货/完成时间',
  `refund_time` int NOT NULL DEFAULT '0' COMMENT '退款时间',
  `cancel_time` int NOT NULL DEFAULT '0' COMMENT '取消时间',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户删除标记（用户端隐藏）',
  `is_system_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '后台删除标记（后台列表隐藏）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  UNIQUE KEY `uk_source_order` (`source_type`,`source_id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_gift_uid` (`gift_uid`),
  KEY `idx_order_category` (`order_category`),
  KEY `idx_source_type` (`source_type`),
  KEY `idx_status_code` (`status_code`),
  KEY `idx_product_type` (`product_type`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_add_time` (`add_time`),
  KEY `idx_pay_time` (`pay_time`),
  KEY `idx_cat_status_time` (`order_category`,`status_code`,`add_time`),
  KEY `idx_uid_status_time` (`uid`,`status_code`,`add_time`),
  KEY `idx_pay_type_status_time` (`pay_type`,`pay_status`,`add_time`),
  KEY `idx_pay_type` (`pay_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='订单中台汇总表（统一所有订单类型的订单数据）';

-- eb_order_economize：用户资金节省表
CREATE TABLE IF NOT EXISTS `eb_order_economize` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_id` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `order_type` tinyint(1) unsigned zerofill NOT NULL DEFAULT '1' COMMENT '配送方式 1=商品订单 ，2=线下订单',
  `pay_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实际支付金额',
  `postage_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '邮费优惠金额',
  `member_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '会员优惠金额',
  `offline_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '线下优惠金额',
  `coupon_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '优惠券优惠金额',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`,`order_id`,`uid`) USING BTREE,
  UNIQUE KEY `order_id_2` (`order_id`,`uid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `pay_price` (`pay_price`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户资金节省表';

-- eb_order_invoice：订单发票表
CREATE TABLE IF NOT EXISTS `eb_order_invoice` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户uid',
  `category` varchar(10) NOT NULL DEFAULT 'order' COMMENT '开票分类 order:订单',
  `order_id` int NOT NULL DEFAULT '0' COMMENT '订单id',
  `invoice_id` int NOT NULL DEFAULT '0' COMMENT '发票id',
  `header_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '抬头类型',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '发票类型',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称（发票抬头）',
  `duty_number` varchar(50) NOT NULL DEFAULT '' COMMENT '税号',
  `drawer_phone` varchar(30) NOT NULL DEFAULT '' COMMENT '开票人手机号',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '开票人邮箱',
  `tell` varchar(30) NOT NULL DEFAULT '' COMMENT '注册电话',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '注册地址',
  `bank` varchar(50) NOT NULL DEFAULT '' COMMENT '开户行',
  `card_number` varchar(50) NOT NULL DEFAULT '' COMMENT '银行卡号',
  `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支付',
  `is_refund` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单是否退款',
  `is_invoice` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开票',
  `invoice_number` varchar(50) NOT NULL DEFAULT '' COMMENT '开票票号',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `invoice_time` int NOT NULL DEFAULT '0' COMMENT '开票时间',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `unique_num` varchar(255) NOT NULL DEFAULT '' COMMENT '唯一值',
  `invoice_num` varchar(255) NOT NULL DEFAULT '' COMMENT '发票号码',
  `invoice_type` varchar(255) NOT NULL DEFAULT '' COMMENT '发票类型',
  `invoice_serial_number` varchar(255) NOT NULL DEFAULT '' COMMENT '发票流水号',
  `red_invoice_num` varchar(255) NOT NULL DEFAULT '' COMMENT '发票红字编码',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='订单发票表';

-- eb_order_item：订单商品快照表
CREATE TABLE IF NOT EXISTS `eb_order_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '关联order_meta.order_no',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '商品ID',
  `product_type` varchar(32) NOT NULL DEFAULT '' COMMENT '产品类型',
  `cart_num` int NOT NULL DEFAULT '1' COMMENT '购买数量',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
  `total_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '小计',
  `snapshot` text NOT NULL COMMENT '商品快照JSON',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_order_no` (`order_no`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='订单商品快照表';

-- eb_order_log：订单状态日志表
CREATE TABLE IF NOT EXISTS `eb_order_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号',
  `uid` int NOT NULL DEFAULT '0' COMMENT '操作用户ID',
  `operator_id` int NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `operator_type` varchar(16) NOT NULL DEFAULT '' COMMENT '操作人类型(user/admin/system)',
  `from_status` varchar(32) NOT NULL DEFAULT '' COMMENT '变更前状态',
  `to_status` varchar(32) NOT NULL DEFAULT '' COMMENT '变更后状态',
  `content` varchar(256) NOT NULL DEFAULT '' COMMENT '操作说明',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `idx_order_no` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='订单状态日志表';

-- eb_page_categroy：页面链接分类
CREATE TABLE IF NOT EXISTS `eb_page_categroy` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` int NOT NULL DEFAULT '0' COMMENT '父类id',
  `type` varchar(50) NOT NULL DEFAULT 'link' COMMENT '类型:link、special、product、product_category、custom',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
  `sort` smallint NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='页面链接分类';

-- eb_page_link：页面链接
CREATE TABLE IF NOT EXISTS `eb_page_link` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cate_id` int NOT NULL DEFAULT '0' COMMENT '分类id',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '分组1:基础2:分销3:个人中心',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '页面名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '页面链接',
  `param` varchar(255) NOT NULL DEFAULT '' COMMENT '参数',
  `example` varchar(255) NOT NULL DEFAULT '' COMMENT '事例',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `sort` smallint NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='页面链接';

-- eb_pay_channel：支付方式字典表
CREATE TABLE IF NOT EXISTS `eb_pay_channel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `channel_key` varchar(32) NOT NULL DEFAULT '' COMMENT '支付标识（唯一）',
  `channel_name` varchar(64) NOT NULL DEFAULT '' COMMENT '显示名称',
  `platform` varchar(32) NOT NULL DEFAULT '' COMMENT '所属平台：wechat/alipay/allinpay/self',
  `channel_type` varchar(32) NOT NULL DEFAULT 'payment' COMMENT '渠道类型：payment=支付方式 feature=支付功能',
  `icon` varchar(128) NOT NULL DEFAULT '' COMMENT '图标 class 或 URL',
  `supported_platforms` varchar(255) NOT NULL DEFAULT '' COMMENT '支持的端：wechat_mini,wechat_mp,h5,app,pc（从 info.php 同步）',
  `enabled_platforms` varchar(255) NOT NULL DEFAULT '' COMMENT '已开启的端（管理员配置）：wechat_mini,wechat_mp,h5,app,pc',
  `config_key_prefix` varchar(64) NOT NULL DEFAULT '' COMMENT '配置项前缀（关联 system_config）',
  `driver` varchar(128) NOT NULL DEFAULT '' COMMENT '驱动类名（支付方式实现类）',
  `config_fields` text COMMENT '配置字段定义 JSON（从 info.php 同步）',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序（越大越靠前）',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '全局状态：0=关闭 1=开启',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除1删除',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_channel_key` (`channel_key`),
  KEY `idx_status_sort` (`status`,`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='支付方式字典表';

-- eb_pay_channel_platform：支付方式-平台关联表
CREATE TABLE IF NOT EXISTS `eb_pay_channel_platform` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `channel_key` varchar(32) NOT NULL DEFAULT '' COMMENT '支付方式标识（关联 eb_pay_channel.channel_key）',
  `platform` varchar(32) NOT NULL DEFAULT '' COMMENT '端标识：wechat_mini/wechat_mp/h5/app/pc',
  `pay_type` varchar(32) NOT NULL DEFAULT '' COMMENT '支付类型：JSAPI/APP/NATIVE/h5/MWEB',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0=关闭 1=开启',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序（越大越靠前）',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_channel_platform` (`channel_key`,`platform`),
  KEY `idx_platform_status` (`platform`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='支付方式-平台关联表';

-- eb_pay_record：微信支付合同及恢复记录
CREATE TABLE IF NOT EXISTS `eb_pay_record` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '原商户订单号',
  `business_type` varchar(32) NOT NULL DEFAULT '' COMMENT '支付业务: product/user_recharge/member/offline_scan/user_extract',
  `channel_key` varchar(32) NOT NULL DEFAULT '' COMMENT '原支付渠道',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '服务端支付金额',
  `context` json DEFAULT NULL COMMENT '原支付身份快照，不含密钥',
  `transaction_id` varchar(64) NOT NULL DEFAULT '' COMMENT '微信支付流水',
  `state` varchar(32) NOT NULL DEFAULT 'prepared' COMMENT '状态: creating/prepared/closing/completed/compensating/compensated/closed',
  `effects` json DEFAULT NULL COMMENT '待执行后置任务',
  `effects_state` varchar(32) NOT NULL DEFAULT '' COMMENT '后置任务状态: pending/completed',
  `effects_token` varchar(32) NOT NULL DEFAULT '' COMMENT '后置任务租约令牌',
  `effects_lock_until` int NOT NULL DEFAULT '0' COMMENT '后置任务租约到期时间',
  `refund` json DEFAULT NULL COMMENT '充值或取消订单退款意图及结果',
  `refund_state` varchar(32) NOT NULL DEFAULT '' COMMENT '补偿退款状态: PENDING/SUBMITTING/UNKNOWN/PROCESSING/SUCCESS/FAILED',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `last_check_time` int NOT NULL DEFAULT '0' COMMENT '最近恢复检查时间',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '软删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_state_update_time` (`state`,`update_time`),
  KEY `idx_effects_state` (`effects_state`),
  KEY `idx_recovery_check` (`is_del`,`last_check_time`,`id`),
  KEY `idx_refund_state` (`refund_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='微信支付合同及恢复记录';

-- eb_printer_channel：打印机渠道表
CREATE TABLE IF NOT EXISTS `eb_printer_channel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `print_name` varchar(255) NOT NULL DEFAULT '' COMMENT '打印机名称',
  `driver` varchar(128) NOT NULL DEFAULT '' COMMENT '驱动名称（YiLianYun/FeiEYun）',
  `printer_id` varchar(128) NOT NULL DEFAULT '' COMMENT '打印机设备ID/SN/终端号',
  `icon` varchar(128) NOT NULL DEFAULT '' COMMENT '图标',
  `config_fields` text COMMENT '配置字段定义 JSON（含值，从 info.php 同步）',
  `times` int NOT NULL DEFAULT '1' COMMENT '打印联数',
  `print_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '打印时机：1支付后，2下单后',
  `print_content` longtext COMMENT '打印内容模板 JSON',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序（越大越靠前）',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0关闭，1开启',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_status_sort` (`status`,`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='打印机渠道表';

-- eb_printer_task：小票设备提交进度表
CREATE TABLE IF NOT EXISTS `eb_printer_task` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `task_key` varchar(64) NOT NULL DEFAULT '' COMMENT '设备提交任务唯一标识',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '业务订单号',
  `channel_id` int unsigned NOT NULL DEFAULT '0' COMMENT '打印机渠道ID',
  `scene` varchar(32) NOT NULL DEFAULT '' COMMENT '打印场景: paid/created/manual',
  `status` varchar(32) NOT NULL DEFAULT 'pending' COMMENT '状态: pending/processing/submitted/failed/uncertain',
  `attempts` int NOT NULL DEFAULT '0' COMMENT '提交尝试次数',
  `lease_token` varchar(32) NOT NULL DEFAULT '' COMMENT '当前提交租约标识',
  `lease_until` int NOT NULL DEFAULT '0' COMMENT '提交租约到期时间',
  `provider_job_id` varchar(128) NOT NULL DEFAULT '' COMMENT '云端接单号',
  `last_error` varchar(500) NOT NULL DEFAULT '' COMMENT '脱敏失败说明',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_task_key` (`task_key`),
  KEY `idx_order_no_scene` (`order_no`,`scene`),
  KEY `idx_status_lease_until` (`status`,`lease_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='小票设备提交进度表';

-- eb_product_center：商品中台主表
CREATE TABLE IF NOT EXISTS `eb_product_center` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID（主键）',
  `product_type` varchar(32) NOT NULL DEFAULT '' COMMENT '产品类型标识（normal/ticket/hotel/appoint等）',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '商品名称',
  `intro` varchar(256) NOT NULL DEFAULT '' COMMENT '商品简介',
  `params_list` text COMMENT '商品参数(JSON)',
  `custom_form` text COMMENT '自定义表单(JSON)',
  `protection_list` varchar(255) DEFAULT '' COMMENT '保障服务ID列表(逗号分隔)',
  `label_list` varchar(255) DEFAULT '' COMMENT '标签ID列表(逗号分隔)',
  `video_link` varchar(500) NOT NULL DEFAULT '' COMMENT '视频链接',
  `keyword` varchar(256) NOT NULL DEFAULT '' COMMENT '搜索关键词',
  `image` varchar(256) NOT NULL DEFAULT '' COMMENT '主图',
  `recommend_list` varchar(1000) DEFAULT '' COMMENT '推荐商品ID列表(逗号分隔)',
  `recommend_image` varchar(256) DEFAULT '' COMMENT '商品推荐图',
  `slider_image` varchar(2000) NOT NULL DEFAULT '' COMMENT '轮播图（JSON格式）',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '展示价格（冗余字段，取最低SKU价/扩展表价格，下单以SKU表/扩展表为准）',
  `original_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '划线价（冗余字段，取最低SKU划线价/扩展表划线价，下单以SKU表/扩展表为准）',
  `vip_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '会员专享价',
  `stock` int NOT NULL DEFAULT '0' COMMENT '总库存（冗余字段，取所有SKU库存之和/扩展表库存，下单以SKU表/扩展表为准）',
  `sales` int NOT NULL DEFAULT '0' COMMENT '销量（冗余字段，累加已支付订单数，权威源在订单数据）',
  `virtual_sales` int NOT NULL DEFAULT '0' COMMENT '虚拟销量（配置字段，管理员手动设置，自身即权威源）',
  `views` int NOT NULL DEFAULT '0' COMMENT '浏览量（冗余字段，累加PV数据，权威源在浏览日志）',
  `cate_id` int NOT NULL DEFAULT '0' COMMENT '分类ID（可选，实物必填）',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT '排序',
  `unit_name` varchar(32) NOT NULL DEFAULT '' COMMENT '默认单位（列表展示用，如件/张/间/次；下单以SKU表unit_name或扩展表unit_name为准）',
  `spec_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '规格类型(0单规格/1多规格)',
  `delivery_methods` json DEFAULT NULL COMMENT '支持的交付方式：express(快递)/pickup(到店自提)/door_service(上门服务)/check_in(到店入住)/digital(数字交付)',
  `freight_config` json NOT NULL DEFAULT (json_object(_utf8mb4'express',json_object(_utf8mb4'type',_utf8mb4'template',_utf8mb4'postage',0,_utf8mb4'temp_id',0),_utf8mb4'door_service',json_object(_utf8mb4'type',_utf8mb4'free',_utf8mb4'postage',0))) COMMENT '运费配置JSON：{"express":{"type":"free|fixed|template","postage":N,"temp_id":N},"door_service":{"type":"free|fixed","postage":N}}',
  `is_limit` tinyint NOT NULL DEFAULT '0' COMMENT '是否开启限购（0否/1是）',
  `limit_type` tinyint NOT NULL DEFAULT '0' COMMENT '限购类型（1单次/2永久）',
  `limit_num` int NOT NULL DEFAULT '0' COMMENT '限购数量',
  `min_qty` int NOT NULL DEFAULT '1' COMMENT '起购数量',
  `is_gift` tinyint NOT NULL DEFAULT '0' COMMENT '是否赠品（0否/1是）',
  `gift_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '赠品价格',
  `give_integral` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '赠送积分',
  `give_coupon_ids` varchar(500) NOT NULL DEFAULT '' COMMENT '赠送优惠券ID（逗号分隔）',
  `is_show` tinyint NOT NULL DEFAULT '0' COMMENT '是否上架（0否/1是）',
  `is_del` tinyint NOT NULL DEFAULT '0' COMMENT '是否删除（0否/1是）',
  `is_sub` tinyint NOT NULL DEFAULT '0' COMMENT '是否开启分销（0否/1是）',
  `is_vip` tinyint NOT NULL DEFAULT '0' COMMENT '是否开启会员价（0否/1是）',
  `vip_product` tinyint NOT NULL DEFAULT '0' COMMENT '会员专属（0不限制/1仅会员可见可买/2所有人可见仅会员可买）',
  `presale` tinyint NOT NULL DEFAULT '0' COMMENT '是否预售（0否/1是）',
  `presale_start_time` int NOT NULL DEFAULT '0' COMMENT '预售开始时间',
  `presale_end_time` int NOT NULL DEFAULT '0' COMMENT '预售结束时间',
  `valid_type` tinyint NOT NULL DEFAULT '0' COMMENT '有效期类型（0长期有效/1固定时段/2购买后N天）',
  `valid_start_time` int NOT NULL DEFAULT '0' COMMENT '有效期开始时间（valid_type=1时有效）',
  `valid_end_time` int NOT NULL DEFAULT '0' COMMENT '有效期结束时间（valid_type=1时有效）',
  `valid_days` int NOT NULL DEFAULT '0' COMMENT '购买后有效天数（valid_type=2时有效）',
  `service_region` json DEFAULT NULL COMMENT '服务区域（上门类商品）：[{"province":"","city":"","district":""}]',
  `service_address` varchar(500) NOT NULL DEFAULT '' COMMENT '服务地址（上门类商品：默认地址模板）',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_product_type` (`product_type`),
  KEY `idx_cate_id` (`cate_id`),
  KEY `idx_is_show` (`is_show`),
  KEY `idx_is_del` (`is_del`),
  KEY `idx_sort_order` (`sort_order`),
  FULLTEXT KEY `ft_name_keyword` (`name`,`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品中台主表';

-- eb_qrcode：微信二维码管理表
CREATE TABLE IF NOT EXISTS `eb_qrcode` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '微信二维码ID',
  `third_type` varchar(32) NOT NULL DEFAULT '' COMMENT '二维码类型',
  `third_id` int unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `ticket` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码参数',
  `expire_seconds` int unsigned NOT NULL DEFAULT '0' COMMENT '二维码有效时间',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `add_time` varchar(255) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '微信访问url',
  `qrcode_url` varchar(255) NOT NULL DEFAULT '' COMMENT '微信二维码url',
  `scan` int unsigned NOT NULL DEFAULT '0' COMMENT '被扫的次数',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '二维码所属平台1=小程序，2=公众号，3=H5',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `third_type` (`third_type`,`third_id`) USING BTREE,
  KEY `ticket` (`ticket`(191)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=COMPACT COMMENT='微信二维码管理表';

-- eb_refund_effect：退款本地副作用幂等日志
CREATE TABLE IF NOT EXISTS `eb_refund_effect` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `refund_order_id` bigint unsigned NOT NULL COMMENT '统一退款单ID',
  `effect_type` varchar(48) NOT NULL COMMENT '本地副作用类型',
  `effect_key` varchar(128) NOT NULL COMMENT '副作用业务唯一键',
  `status` varchar(16) NOT NULL DEFAULT 'processing' COMMENT 'processing/completed/failed',
  `payload` json DEFAULT NULL,
  `last_error` varchar(1000) NOT NULL DEFAULT '',
  `add_time` int unsigned NOT NULL DEFAULT '0',
  `update_time` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_refund_effect` (`refund_order_id`,`effect_type`,`effect_key`),
  KEY `idx_refund_status` (`refund_order_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='退款本地副作用幂等日志';

-- eb_refund_order：统一退款单
CREATE TABLE IF NOT EXISTS `eb_refund_order` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `refund_no` varchar(48) NOT NULL COMMENT '系统退款单号，创建后不可修改',
  `after_sale_id` bigint unsigned NOT NULL COMMENT '售后单ID，一张售后单只允许一个退款意图',
  `order_center_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '订单中台ID',
  `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '本次退款金额',
  `refund_integral` int unsigned NOT NULL DEFAULT '0' COMMENT '本次退还积分',
  `refund_method` varchar(16) NOT NULL DEFAULT 'original' COMMENT 'original/balance',
  `pay_channel` varchar(32) NOT NULL DEFAULT '' COMMENT '原支付渠道',
  `state` varchar(32) NOT NULL DEFAULT 'pending' COMMENT '退款状态机状态',
  `channel_refund_id` varchar(128) NOT NULL DEFAULT '' COMMENT '渠道退款流水或标识',
  `channel_response` json DEFAULT NULL COMMENT '脱敏后的渠道响应',
  `context` json DEFAULT NULL COMMENT '创建退款时固化的上下文',
  `attempts` int unsigned NOT NULL DEFAULT '0' COMMENT '渠道提交/查询尝试次数',
  `next_retry_time` int unsigned NOT NULL DEFAULT '0' COMMENT '下次恢复时间',
  `last_error` varchar(1000) NOT NULL DEFAULT '' COMMENT '最近错误',
  `version` int unsigned NOT NULL DEFAULT '0' COMMENT '乐观锁版本',
  `channel_succeeded_at` int unsigned NOT NULL DEFAULT '0' COMMENT '渠道确认成功时间',
  `completed_at` int unsigned NOT NULL DEFAULT '0' COMMENT '本地效果全部完成时间',
  `add_time` int unsigned NOT NULL DEFAULT '0',
  `update_time` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_refund_no` (`refund_no`),
  UNIQUE KEY `uk_after_sale_id` (`after_sale_id`),
  KEY `idx_state_retry` (`state`,`next_retry_time`),
  KEY `idx_order_state` (`order_center_id`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='统一退款单';

-- eb_shared_table_registry：插件间共享表准入登记表
CREATE TABLE IF NOT EXISTS `eb_shared_table_registry` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `table_name` varchar(64) NOT NULL DEFAULT '' COMMENT '共享表名（不含前缀）',
  `owner_plugin_id` varchar(96) NOT NULL DEFAULT '' COMMENT '占用插件复合标识，如 module:trade:seckill',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '登记时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_table_plugin` (`table_name`,`owner_plugin_id`),
  KEY `idx_table_name` (`table_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='插件间共享表准入登记表';

-- eb_shipping_templates：运费模板表
CREATE TABLE IF NOT EXISTS `eb_shipping_templates` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '模板名称',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '计费方式',
  `appoint` tinyint(1) NOT NULL DEFAULT '0' COMMENT '指定包邮',
  `no_delivery` tinyint(1) NOT NULL DEFAULT '0' COMMENT '指定不送达',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='运费模板表';

-- eb_shipping_templates_free：运费模板指定包邮关联表
CREATE TABLE IF NOT EXISTS `eb_shipping_templates_free` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `province_id` int NOT NULL DEFAULT '0' COMMENT '省ID',
  `temp_id` int NOT NULL DEFAULT '0' COMMENT '模板ID',
  `city_id` int NOT NULL DEFAULT '0' COMMENT '城市ID',
  `number` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '包邮件数',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '包邮金额',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '计费方式',
  `uniqid` varchar(32) NOT NULL DEFAULT '' COMMENT '分组唯一值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='运费模板指定包邮关联表';

-- eb_shipping_templates_no_delivery：运费模板指定不送达表
CREATE TABLE IF NOT EXISTS `eb_shipping_templates_no_delivery` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `province_id` int NOT NULL DEFAULT '0' COMMENT '省ID',
  `temp_id` int NOT NULL DEFAULT '0' COMMENT '模板ID',
  `city_id` int NOT NULL DEFAULT '0' COMMENT '城市ID',
  `uniqid` varchar(32) NOT NULL DEFAULT '' COMMENT '分组唯一值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='运费模板指定不送达表';

-- eb_shipping_templates_region：运费模板指定城市运费表
CREATE TABLE IF NOT EXISTS `eb_shipping_templates_region` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `province_id` int NOT NULL DEFAULT '0' COMMENT '省ID',
  `temp_id` int NOT NULL DEFAULT '0' COMMENT '模板ID',
  `city_id` int NOT NULL DEFAULT '0' COMMENT '城市ID',
  `first` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '首件',
  `first_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '首件运费',
  `continue` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '续件',
  `continue_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '续件运费',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '计费方式',
  `uniqid` varchar(32) NOT NULL DEFAULT '' COMMENT '分组唯一值',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='运费模板指定城市运费表';

-- eb_sms_record：短信发送记录表
CREATE TABLE IF NOT EXISTS `eb_sms_record` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '短信发送记录编号',
  `uid` varchar(255) NOT NULL DEFAULT '' COMMENT '短信平台账号',
  `phone` char(11) NOT NULL DEFAULT '' COMMENT '接受短信的手机号',
  `content` text COMMENT '短信内容',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '发送短信时间',
  `add_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '添加记录ip',
  `template` varchar(255) NOT NULL DEFAULT '' COMMENT '短信模板ID',
  `resultcode` int unsigned NOT NULL DEFAULT '0' COMMENT '状态码 100=成功,130=失败,131=空号,132=停机,133=关机,134=无状态',
  `record_id` int unsigned NOT NULL DEFAULT '0' COMMENT '发送记录id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='短信发送记录表';

-- eb_store_cart：购物车表
CREATE TABLE IF NOT EXISTS `eb_store_cart` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '购物车表ID',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT '类型',
  `product_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `product_attr_unique` varchar(16) NOT NULL DEFAULT '' COMMENT '商品属性',
  `cart_num` smallint unsigned NOT NULL DEFAULT '0' COMMENT '商品数量',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = 未购买 1 = 已购买',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `is_new` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为立即购买',
  `combination_id` int unsigned NOT NULL DEFAULT '0' COMMENT '拼团id',
  `seckill_id` int unsigned NOT NULL DEFAULT '0' COMMENT '秒杀商品ID',
  `bargain_id` int unsigned NOT NULL DEFAULT '0' COMMENT '砍价id',
  `advance_id` int NOT NULL DEFAULT '0' COMMENT '预售商品id',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '购物车商品状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `goods_id` (`product_id`) USING BTREE,
  KEY `uid` (`uid`,`is_pay`) USING BTREE,
  KEY `uid_2` (`uid`,`is_del`) USING BTREE,
  KEY `uid_3` (`uid`,`is_new`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=COMPACT COMMENT='购物车表';

-- eb_store_category：商品分类表
CREATE TABLE IF NOT EXISTS `eb_store_category` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '商品分类表ID',
  `pid` int NOT NULL DEFAULT '0' COMMENT '父id',
  `cate_name` varchar(100) NOT NULL DEFAULT '' COMMENT '分类名称',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `pic` varchar(128) NOT NULL DEFAULT '' COMMENT '图标',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否推荐',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `big_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '分类大图',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE,
  KEY `is_base` (`is_show`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品分类表';

-- eb_store_coupon_issue：优惠券前台领取表
CREATE TABLE IF NOT EXISTS `eb_store_coupon_issue` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cid` int NOT NULL DEFAULT '0' COMMENT '优惠券ID',
  `coupon_title` varchar(255) NOT NULL DEFAULT '' COMMENT '优惠券名称',
  `start_time` int NOT NULL DEFAULT '0' COMMENT '优惠券领取开启时间',
  `end_time` int NOT NULL DEFAULT '0' COMMENT '优惠券领取结束时间',
  `total_count` int NOT NULL DEFAULT '0' COMMENT '优惠券领取数量',
  `remain_count` int NOT NULL DEFAULT '0' COMMENT '优惠券剩余领取数量',
  `receive_limit` int NOT NULL DEFAULT '0' COMMENT '每个人个领取的优惠券数量',
  `is_permanent` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否无限张数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 正常 0 未开启 -1 已无效',
  `is_give_subscribe` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否首次关注赠送 0-否(默认) 1-是',
  `is_full_give` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否满赠0-否(默认) 1-是',
  `full_reduction` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '消费满多少赠送优惠券',
  `is_del` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '优惠券添加时间',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '优惠券名称',
  `integral` int unsigned NOT NULL DEFAULT '0' COMMENT '兑换消耗积分值',
  `coupon_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '兑换的优惠券面值',
  `use_min_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '最低消费多少金额可用优惠券',
  `coupon_time` int unsigned NOT NULL DEFAULT '0' COMMENT '优惠券有效期限（单位：天）',
  `product_id` varchar(2000) NOT NULL DEFAULT '' COMMENT '所属商品id',
  `category_id` varchar(2000) NOT NULL DEFAULT '' COMMENT '分类id',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '优惠券类型 0-通用 1-品类券 2-商品券',
  `receive_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 手动领取，2 新人券，3赠送券，4会员券',
  `start_use_time` int NOT NULL DEFAULT '0' COMMENT '优惠券使用开始时间',
  `end_use_time` int NOT NULL DEFAULT '0' COMMENT '优惠券使用结束时间',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `cid` (`cid`) USING BTREE,
  KEY `start_time` (`start_time`,`end_time`) USING BTREE,
  KEY `remain_count` (`remain_count`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `coupon_time` (`coupon_time`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=COMPACT COMMENT='优惠券前台领取表';

-- eb_store_coupon_issue_user：优惠券前台用户领取记录表
CREATE TABLE IF NOT EXISTS `eb_store_coupon_issue_user` (
  `uid` int NOT NULL DEFAULT '0' COMMENT '领取优惠券用户ID',
  `issue_coupon_id` int NOT NULL DEFAULT '0' COMMENT '优惠券前台领取ID',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '领取时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='优惠券前台用户领取记录表';

-- eb_store_coupon_product：优惠券模板关联列表
CREATE TABLE IF NOT EXISTS `eb_store_coupon_product` (
  `coupon_id` int NOT NULL DEFAULT '0' COMMENT '优惠券模板id',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '商品id',
  `category_id` int NOT NULL DEFAULT '0' COMMENT '分类id',
  KEY `coupon_id` (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='优惠券模板关联列表';

-- eb_store_coupon_user：优惠券发放记录表
CREATE TABLE IF NOT EXISTS `eb_store_coupon_user` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '优惠券发放记录id',
  `cid` int unsigned NOT NULL DEFAULT '0' COMMENT '兑换的项目id',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '优惠券所属用户',
  `coupon_title` varchar(32) NOT NULL DEFAULT '' COMMENT '优惠券名称',
  `coupon_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '优惠券的面值',
  `use_min_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '最低消费多少金额可用优惠券',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '优惠券创建时间',
  `start_time` int NOT NULL DEFAULT '0' COMMENT '优惠券开始时间',
  `end_time` int unsigned NOT NULL DEFAULT '0' COMMENT '优惠券结束时间',
  `use_time` int unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
  `type` varchar(32) NOT NULL DEFAULT 'send' COMMENT '获取方式',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（0：未使用，1：已使用, 2:已过期）',
  `is_fail` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否有效',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `cid` (`cid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `end_time` (`end_time`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `idx_status_use_time_cid` (`status`,`use_time`,`cid`) USING BTREE,
  KEY `is_fail` (`is_fail`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='优惠券发放记录表';

-- eb_store_product_attr_value：商品属性值表
CREATE TABLE IF NOT EXISTS `eb_store_product_attr_value` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `product_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `suk` varchar(128) NOT NULL DEFAULT '' COMMENT '商品属性索引值 (attr_value|attr_value[|....])',
  `stock` int unsigned NOT NULL DEFAULT '0' COMMENT '属性对应的库存',
  `sales` int unsigned NOT NULL DEFAULT '0' COMMENT '销量',
  `price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '属性金额',
  `image` varchar(128) NOT NULL DEFAULT '' COMMENT '图片',
  `unique` char(8) NOT NULL DEFAULT '' COMMENT '唯一值',
  `cost` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '成本价',
  `bar_code` varchar(50) NOT NULL DEFAULT '' COMMENT '商品条码',
  `bar_code_number` varchar(50) NOT NULL DEFAULT '' COMMENT '条形码',
  `ot_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
  `vip_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '会员专享价',
  `weight` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '重量',
  `volume` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '体积',
  `brokerage` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '一级返佣',
  `brokerage_two` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '二级返佣',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '活动类型 0=商品，1=秒杀，2=砍价，3=拼团',
  `quota` int NOT NULL DEFAULT '0' COMMENT '活动限购数量',
  `quota_show` int NOT NULL DEFAULT '0' COMMENT '活动限购数量显示',
  `is_virtual` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否虚拟商品',
  `coupon_id` int NOT NULL DEFAULT '0' COMMENT '优惠券id',
  `disk_info` text COMMENT '虚拟信息内容',
  `is_show` int NOT NULL DEFAULT '1' COMMENT '是否显示',
  `is_default_select` int NOT NULL DEFAULT '0' COMMENT '是否默认规格',
  PRIMARY KEY (`id`),
  KEY `unique` (`unique`,`suk`) USING BTREE,
  KEY `store_id` (`product_id`,`suk`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品属性值表';

-- eb_store_product_cate：商品分类辅助表
CREATE TABLE IF NOT EXISTS `eb_store_product_cate` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '商品id',
  `cate_id` int NOT NULL DEFAULT '0' COMMENT '分类id',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `cate_pid` int NOT NULL DEFAULT '0' COMMENT '一级分类id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品分类辅助表';

-- eb_store_product_coupon：商品关联优惠券表
CREATE TABLE IF NOT EXISTS `eb_store_product_coupon` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '商品id',
  `issue_coupon_id` int NOT NULL DEFAULT '0' COMMENT '优惠劵id',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '优惠券名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品关联优惠券表';

-- eb_store_product_description：商品详情表
CREATE TABLE IF NOT EXISTS `eb_store_product_description` (
  `product_id` int NOT NULL DEFAULT '0' COMMENT '商品ID',
  `activity_type` varchar(32) NOT NULL DEFAULT 'normal' COMMENT '活动类型（normal/integral/seckill/combination/bargain）',
  `description` longtext COMMENT '商品详情',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品类型',
  KEY `product_id` (`product_id`,`type`),
  KEY `idx_product_activity` (`product_id`,`activity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品详情表';

-- eb_store_product_label：商品标签
CREATE TABLE IF NOT EXISTS `eb_store_product_label` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '标签名称',
  `cate_id` int NOT NULL DEFAULT '0' COMMENT '分类id',
  `type` int NOT NULL DEFAULT '0' COMMENT '效果设置0自定义1图片',
  `font_color` varchar(255) NOT NULL DEFAULT '' COMMENT '字体颜色',
  `bg_color` varchar(255) NOT NULL DEFAULT '' COMMENT '背景颜色',
  `border_color` varchar(255) NOT NULL DEFAULT '' COMMENT '边框颜色',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `is_show` int NOT NULL DEFAULT '1' COMMENT '移动端展示',
  `status` int NOT NULL DEFAULT '1' COMMENT '是否开启',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `is_del` int NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品标签';

-- eb_store_product_label_cate：商品标签分类
CREATE TABLE IF NOT EXISTS `eb_store_product_label_cate` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `is_del` int NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品标签分类';

-- eb_store_product_log：商品统计
CREATE TABLE IF NOT EXISTS `eb_store_product_log` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` enum('visit','cart','order','pay','collect','refund') NOT NULL COMMENT '类型',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '商品ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `visit_num` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否浏览',
  `cart_num` int NOT NULL DEFAULT '0' COMMENT '加入购物车数量',
  `order_num` int NOT NULL DEFAULT '0' COMMENT '下单数量',
  `pay_num` int NOT NULL DEFAULT '0' COMMENT '支付数量',
  `pay_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '支付金额',
  `cost_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '商品成本价',
  `pay_uid` int NOT NULL DEFAULT '0' COMMENT '支付用户ID',
  `refund_num` int NOT NULL DEFAULT '0' COMMENT '退款数量',
  `refund_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `collect_num` tinyint(1) NOT NULL DEFAULT '0' COMMENT '收藏',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `idx_add_time_product_id` (`add_time`,`product_id`),
  KEY `idx_product_id_add_time` (`product_id`,`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品统计';

-- eb_store_product_param：商品参数表
CREATE TABLE IF NOT EXISTS `eb_store_product_param` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '参数名称',
  `value` text COMMENT '参数内容',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `is_del` int NOT NULL DEFAULT '0' COMMENT '是否删除',
  `status` int NOT NULL DEFAULT '1' COMMENT '参数状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品参数表';

-- eb_store_product_protection：商品保障表
CREATE TABLE IF NOT EXISTS `eb_store_product_protection` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` varchar(2000) NOT NULL DEFAULT '' COMMENT '内容',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `num` int NOT NULL DEFAULT '0' COMMENT '使用数量',
  `status` int NOT NULL DEFAULT '1' COMMENT '状态',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `is_del` int NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品保障表';

-- eb_store_product_relation：商品点赞和收藏表
CREATE TABLE IF NOT EXISTS `eb_store_product_relation` (
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `product_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT '类型(收藏(collect）、点赞(like))',
  `category` varchar(32) NOT NULL DEFAULT '' COMMENT '某种类型的商品(普通商品、秒杀商品)',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  UNIQUE KEY `uid` (`uid`,`product_id`,`type`,`category`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `category` (`category`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品点赞和收藏表';

-- eb_store_product_reply：评论表
CREATE TABLE IF NOT EXISTS `eb_store_product_reply` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `oid` int NOT NULL DEFAULT '0' COMMENT '订单ID',
  `unique` char(32) NOT NULL DEFAULT '' COMMENT '唯一id',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '商品id',
  `reply_type` varchar(32) NOT NULL DEFAULT 'product' COMMENT '某种商品类型(普通商品、秒杀商品）',
  `product_score` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品分数',
  `service_score` tinyint(1) NOT NULL DEFAULT '0' COMMENT '服务分数',
  `comment` varchar(512) NOT NULL DEFAULT '' COMMENT '评论内容',
  `pics` text COMMENT '评论图片',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '评论时间',
  `merchant_reply_content` varchar(300) NOT NULL DEFAULT '' COMMENT '管理员回复内容',
  `merchant_reply_time` int NOT NULL DEFAULT '0' COMMENT '管理员回复时间',
  `is_del` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '0未删除1已删除',
  `is_reply` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未回复1已回复',
  `nickname` varchar(64) NOT NULL DEFAULT '' COMMENT '用户名称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像',
  `suk` varchar(255) NOT NULL DEFAULT '' COMMENT '规格名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '评论状态',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_id_2` (`oid`,`unique`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `parent_id` (`reply_type`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE,
  KEY `product_score` (`product_score`) USING BTREE,
  KEY `service_score` (`service_score`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='评论表';

-- eb_store_product_rule：商品规则值(规格)表
CREATE TABLE IF NOT EXISTS `eb_store_product_rule` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `rule_name` varchar(32) NOT NULL DEFAULT '' COMMENT '规格名称',
  `rule_value` text COMMENT '规格值',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品规则值(规格)表';

-- eb_store_visit：商品浏览分析表
CREATE TABLE IF NOT EXISTS `eb_store_visit` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '商品ID',
  `product_type` varchar(32) NOT NULL DEFAULT '' COMMENT '商品类型',
  `cate_id` int NOT NULL DEFAULT '0' COMMENT '商品分类ID',
  `type` char(50) NOT NULL DEFAULT '' COMMENT '商品类型',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `count` int NOT NULL DEFAULT '0' COMMENT '访问次数',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '备注描述',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商品浏览分析表';

-- eb_system_admin：后台管理员表
CREATE TABLE IF NOT EXISTS `eb_system_admin` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT COMMENT '后台管理员表ID',
  `account` varchar(32) NOT NULL DEFAULT '' COMMENT '后台管理员账号',
  `head_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '管理员头像',
  `pwd` varchar(100) NOT NULL DEFAULT '' COMMENT '后台管理员密码',
  `real_name` varchar(16) NOT NULL DEFAULT '' COMMENT '后台管理员姓名',
  `roles` varchar(128) NOT NULL DEFAULT '' COMMENT '后台管理员权限(menus_id)',
  `last_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '后台管理员最后一次登录ip',
  `last_time` int unsigned NOT NULL DEFAULT '0' COMMENT '后台管理员最后一次登录时间',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '后台管理员添加时间',
  `login_count` int unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `level` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '后台管理员级别',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '后台管理员状态 1有效0无效',
  `division_id` int NOT NULL DEFAULT '0' COMMENT '事业部id',
  `is_del` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `account` (`account`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='后台管理员表';

-- eb_system_attachment：附件管理表
CREATE TABLE IF NOT EXISTS `eb_system_attachment` (
  `att_id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '附件名称',
  `att_dir` varchar(200) NOT NULL DEFAULT '' COMMENT '附件路径',
  `satt_dir` varchar(200) NOT NULL DEFAULT '' COMMENT '压缩图片路径',
  `att_size` char(30) NOT NULL DEFAULT '' COMMENT '附件大小',
  `att_type` char(30) NOT NULL DEFAULT '' COMMENT '附件类型',
  `pid` int NOT NULL DEFAULT '0' COMMENT '分类ID0编辑器,1商品图片,2拼团图片,3砍价图片,4秒杀图片,5文章图片,6组合数据图',
  `time` int NOT NULL DEFAULT '0' COMMENT '上传时间',
  `image_type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '图片上传类型 1本地 2七牛云 3OSS 4COS ',
  `module_type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '图片上传模块类型 1 后台上传 2 用户生成',
  `real_name` varchar(255) NOT NULL DEFAULT '' COMMENT '原始文件名',
  `scan_token` varchar(64) NOT NULL DEFAULT '' COMMENT '扫码上传能力的SHA256摘要',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型，0图片，1视频',
  PRIMARY KEY (`att_id`) USING BTREE,
  KEY `idx_scan_token` (`scan_token`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='附件管理表';

-- eb_system_attachment_category：附件分类表
CREATE TABLE IF NOT EXISTS `eb_system_attachment_category` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` int NOT NULL DEFAULT '0' COMMENT '父级ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
  `enname` varchar(50) NOT NULL DEFAULT '' COMMENT '分类目录',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型，0图片，1视频',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='附件分类表';

-- eb_system_city：城市表
CREATE TABLE IF NOT EXISTS `eb_system_city` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `city_id` int NOT NULL DEFAULT '0' COMMENT '城市id',
  `level` int NOT NULL DEFAULT '0' COMMENT '省市级别',
  `parent_id` int NOT NULL DEFAULT '0' COMMENT '父级id',
  `area_code` varchar(30) NOT NULL DEFAULT '' COMMENT '区号',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `merger_name` varchar(255) NOT NULL DEFAULT '' COMMENT '合并名称',
  `lng` varchar(50) NOT NULL DEFAULT '' COMMENT '经度',
  `lat` varchar(50) NOT NULL DEFAULT '' COMMENT '纬度',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3975 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='城市表';

-- eb_system_config：配置表
CREATE TABLE IF NOT EXISTS `eb_system_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '配置id',
  `menu_name` varchar(255) NOT NULL DEFAULT '' COMMENT '字段名称',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '类型(文本框,单选按钮...)',
  `input_type` varchar(20) NOT NULL DEFAULT 'input' COMMENT '表单类型',
  `config_tab_id` int unsigned NOT NULL DEFAULT '0' COMMENT '配置分类id',
  `parameter` varchar(255) NOT NULL DEFAULT '' COMMENT '规则 单选框和多选框',
  `upload_type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '上传文件格式1单图2多图3文件',
  `required` varchar(255) NOT NULL DEFAULT '' COMMENT '规则',
  `width` int unsigned NOT NULL DEFAULT '0' COMMENT '多行文本框的宽度',
  `high` int unsigned NOT NULL DEFAULT '0' COMMENT '多行文框的高度',
  `value` varchar(5000) NOT NULL DEFAULT '' COMMENT '默认值',
  `info` varchar(255) NOT NULL DEFAULT '' COMMENT '配置名称',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '配置简介',
  `sort` int unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `level` int NOT NULL DEFAULT '0' COMMENT '配置层级0顶级1次级',
  `link_id` int NOT NULL DEFAULT '0' COMMENT '关联上级配置id',
  `link_value` int NOT NULL DEFAULT '0' COMMENT '关联上级配置的值',
  `owner_plugin_id` varchar(96) NOT NULL DEFAULT '' COMMENT '所属插件复合标识，空为核心配置',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_owner_plugin_id` (`owner_plugin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=491 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='配置表';

-- eb_system_config_tab：配置分类表
CREATE TABLE IF NOT EXISTS `eb_system_config_tab` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '配置分类id',
  `pid` int NOT NULL DEFAULT '0' COMMENT '上级分类id',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '配置分类名称',
  `eng_title` varchar(255) NOT NULL DEFAULT '' COMMENT '配置分类英文名称',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '配置分类状态',
  `info` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '配置分类是否显示',
  `icon` varchar(30) NOT NULL DEFAULT '' COMMENT '图标',
  `type` int NOT NULL DEFAULT '0' COMMENT '配置类型',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `menus_id` int NOT NULL DEFAULT '0' COMMENT '菜单ID',
  `owner_plugin_id` varchar(96) NOT NULL DEFAULT '' COMMENT '所属插件复合标识，空为核心分组',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_owner_plugin_id` (`owner_plugin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='配置分类表';

-- eb_system_crontab：定时器
CREATE TABLE IF NOT EXISTS `eb_system_crontab` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '定时器名称',
  `mark` varchar(255) NOT NULL DEFAULT '' COMMENT '标签',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '说明',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '周期状态 1=每隔多少秒 2=每隔多少分钟 3=每隔多少小时 4=每隔多少天 5=每天几点执行 6=每周周几几点执行 7=每月几号几点执行',
  `month` int NOT NULL DEFAULT '0' COMMENT '月',
  `week` int NOT NULL DEFAULT '0' COMMENT '周',
  `day` int NOT NULL DEFAULT '0' COMMENT '日',
  `hour` int NOT NULL DEFAULT '0' COMMENT '时',
  `minute` int NOT NULL DEFAULT '0' COMMENT '分',
  `second` int NOT NULL DEFAULT '0' COMMENT '秒',
  `last_execution_time` int NOT NULL DEFAULT '0' COMMENT '上次执行时间',
  `next_execution_time` int NOT NULL DEFAULT '0' COMMENT '下次执行时间',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '修改时间',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `is_open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启',
  `customCode` text NOT NULL COMMENT '执行代码',
  `timeStr` varchar(255) NOT NULL DEFAULT '' COMMENT '时间代码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='定时器';

-- eb_system_crud：crud生成记录
CREATE TABLE IF NOT EXISTS `eb_system_crud` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pid` int NOT NULL DEFAULT '0' COMMENT '菜单上级id',
  `name` varchar(200) NOT NULL COMMENT '菜单名称',
  `menu_id` int NOT NULL DEFAULT '0' COMMENT '主菜单id',
  `model_name` varchar(255) NOT NULL DEFAULT '' COMMENT '模块名',
  `table_name` varchar(50) NOT NULL COMMENT '表明',
  `field` text NOT NULL COMMENT '参数内容',
  `menu_ids` varchar(255) NOT NULL DEFAULT '' COMMENT '生成的菜单id',
  `route_ids` varchar(255) NOT NULL DEFAULT '' COMMENT '权限id',
  `make_path` varchar(1000) NOT NULL DEFAULT '' COMMENT '文件路径',
  `table_collation` varchar(100) NOT NULL DEFAULT '' COMMENT '字符集',
  `table_comment` varchar(255) NOT NULL DEFAULT '' COMMENT '表备注',
  `add_time` int NOT NULL COMMENT '添加时间',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='crud生成记录';

-- eb_system_crud_data：crud数据字典
CREATE TABLE IF NOT EXISTS `eb_system_crud_data` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cid` int NOT NULL DEFAULT '0' COMMENT '列表id',
  `pid` int NOT NULL DEFAULT '0' COMMENT '上级id',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `value` text NOT NULL COMMENT '数据内容JSON',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='crud数据字典';

-- eb_system_crud_list：crud数据字典列表
CREATE TABLE IF NOT EXISTS `eb_system_crud_list` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `mark` varchar(255) NOT NULL DEFAULT '' COMMENT '标识',
  `level` int NOT NULL DEFAULT '0' COMMENT '层级0一级，1多级',
  `status` int NOT NULL DEFAULT '1' COMMENT '状态',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='crud数据字典列表';

-- eb_system_file_md5：文件md5
CREATE TABLE IF NOT EXISTS `eb_system_file_md5` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '' COMMENT '文件路径',
  `md5` varchar(255) NOT NULL DEFAULT '' COMMENT '文件md5',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=963 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='文件md5';

-- eb_system_group：组合数据表
CREATE TABLE IF NOT EXISTS `eb_system_group` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '组合数据ID',
  `cate_id` int NOT NULL DEFAULT '0' COMMENT '分类id',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '数据组名称',
  `info` varchar(256) NOT NULL DEFAULT '' COMMENT '数据提示',
  `config_name` varchar(50) NOT NULL DEFAULT '' COMMENT '数据字段',
  `fields` text COMMENT '数据组字段以及类型（json数据）',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `config_name` (`config_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='组合数据表';

-- eb_system_group_data：组合数据详情表
CREATE TABLE IF NOT EXISTS `eb_system_group_data` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '组合数据详情ID',
  `gid` int NOT NULL DEFAULT '0' COMMENT '对应的数据组id',
  `value` text COMMENT '数据组对应的数据值（json数据）',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加数据时间',
  `sort` int NOT NULL DEFAULT '0' COMMENT '数据排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1：开启；2：关闭；）',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1073 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='组合数据详情表';

-- eb_system_log：管理员操作记录表
CREATE TABLE IF NOT EXISTS `eb_system_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员操作记录ID',
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员id',
  `admin_name` varchar(64) NOT NULL DEFAULT '' COMMENT '管理员姓名',
  `path` varchar(128) NOT NULL DEFAULT '' COMMENT '链接',
  `page` varchar(64) NOT NULL DEFAULT '' COMMENT '行为',
  `method` varchar(12) NOT NULL DEFAULT '' COMMENT '访问类型',
  `ip` varchar(16) NOT NULL DEFAULT '' COMMENT '登录IP',
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT '类型',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  `merchant_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商户id',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=595 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='管理员操作记录表';

-- eb_system_menus：菜单表
CREATE TABLE IF NOT EXISTS `eb_system_menus` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `pid` smallint unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `icon` varchar(64) NOT NULL DEFAULT '' COMMENT '图标',
  `menu_name` varchar(32) NOT NULL DEFAULT '' COMMENT '按钮名',
  `module` varchar(32) NOT NULL DEFAULT '' COMMENT '模块名',
  `controller` varchar(64) NOT NULL DEFAULT '' COMMENT '控制器',
  `action` varchar(32) NOT NULL DEFAULT '' COMMENT '方法名',
  `api_url` varchar(100) NOT NULL DEFAULT '' COMMENT 'api接口地址',
  `methods` varchar(10) NOT NULL DEFAULT '' COMMENT '提交方式POST GET PUT DELETE',
  `params` varchar(128) NOT NULL DEFAULT '[]' COMMENT '参数',
  `sort` tinyint NOT NULL DEFAULT '1' COMMENT '排序',
  `is_show` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '是否为隐藏菜单0=隐藏菜单,1=显示菜单',
  `is_show_path` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为隐藏菜单供前台使用',
  `access` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '子管理员是否可用',
  `menu_path` varchar(128) NOT NULL DEFAULT '' COMMENT '路由名称 前端使用',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `auth_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为菜单 1菜单 2功能',
  `header` varchar(50) NOT NULL DEFAULT '' COMMENT '顶部菜单标示',
  `is_header` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否顶部菜单1是0否',
  `unique_auth` varchar(150) NOT NULL DEFAULT '' COMMENT '前台唯一标识',
  `menu_key` varchar(190) NOT NULL DEFAULT '' COMMENT '菜单稳定标识，系统生成且不可修改',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `mark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `owner_plugin_id` varchar(96) NOT NULL DEFAULT '' COMMENT '所属插件复合标识，空为核心菜单',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE,
  KEY `is_show` (`is_show`) USING BTREE,
  KEY `access` (`access`) USING BTREE,
  KEY `idx_owner_plugin_id` (`owner_plugin_id`),
  UNIQUE KEY `uk_menu_key` ((NULLIF(`menu_key`, '')))
) ENGINE=InnoDB AUTO_INCREMENT=4961 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='菜单表';

-- eb_system_pem：证书表
CREATE TABLE IF NOT EXISTS `eb_system_pem` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '证书类型',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '证书名称',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '证书文件名',
  `content` text COMMENT '证书内容',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='证书表';

-- eb_system_role：身份管理表
CREATE TABLE IF NOT EXISTS `eb_system_role` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '身份管理id',
  `role_name` varchar(32) NOT NULL DEFAULT '' COMMENT '身份管理名称',
  `rules` text COMMENT '身份管理权限(menus_id)',
  `level` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '管理员等级',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='身份管理表';

-- eb_system_route：路由规则表
CREATE TABLE IF NOT EXISTS `eb_system_route` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cate_id` int NOT NULL DEFAULT '0' COMMENT '分类',
  `app_name` varchar(20) NOT NULL DEFAULT 'api' COMMENT '应用名',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '路由名称',
  `describe` text NOT NULL COMMENT '功能描述',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '路由路径',
  `method` enum('POST','GET','DELETE','PUT','*') NOT NULL DEFAULT 'GET' COMMENT '路由请求方式',
  `file_path` varchar(255) NOT NULL DEFAULT '' COMMENT '文件路径',
  `action` varchar(255) NOT NULL DEFAULT '' COMMENT '方法名称',
  `query` longtext NOT NULL COMMENT 'get请求参数',
  `header` longtext NOT NULL COMMENT 'header',
  `request` longtext NOT NULL COMMENT '请求数据',
  `request_type` varchar(100) NOT NULL DEFAULT '' COMMENT '请求类型',
  `response` longtext NOT NULL COMMENT '返回数据',
  `request_example` longtext NOT NULL COMMENT '请求示例',
  `response_example` longtext NOT NULL COMMENT '返回示例',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型',
  `add_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `delete_time` timestamp NULL DEFAULT NULL COMMENT '删除字段',
  `error_code` longtext NOT NULL COMMENT '错误码',
  PRIMARY KEY (`id`),
  KEY `path` (`path`)
) ENGINE=InnoDB AUTO_INCREMENT=1698 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='路由规则表';

-- eb_system_route_cate：路由规分类表
CREATE TABLE IF NOT EXISTS `eb_system_route_cate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pid` int NOT NULL DEFAULT '0' COMMENT '上级id',
  `app_name` varchar(20) NOT NULL DEFAULT '' COMMENT '应用名',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `mark` varchar(32) NOT NULL DEFAULT '' COMMENT '分组标识',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `sort` int NOT NULL DEFAULT '0',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `app_name` (`app_name`)
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='路由规分类表';

-- eb_system_store：门店自提
CREATE TABLE IF NOT EXISTS `eb_system_store` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '门店名称',
  `introduction` varchar(1000) NOT NULL DEFAULT '' COMMENT '简介',
  `phone` char(25) NOT NULL DEFAULT '' COMMENT '手机号码',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '省市区',
  `detailed_address` varchar(255) NOT NULL DEFAULT '' COMMENT '详细地址',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '门店logo',
  `oblong_image` varchar(255) NOT NULL DEFAULT '' COMMENT '门店推荐图',
  `latitude` char(25) NOT NULL DEFAULT '' COMMENT '纬度',
  `longitude` char(25) NOT NULL DEFAULT '' COMMENT '经度',
  `valid_time` varchar(100) NOT NULL DEFAULT '' COMMENT '核销有效日期',
  `day_time` varchar(100) NOT NULL DEFAULT '' COMMENT '每日营业开关时间',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='门店自提';

-- eb_system_store_staff：门店店员表
CREATE TABLE IF NOT EXISTS `eb_system_store_staff` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '微信用户id',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '店员头像',
  `store_id` int NOT NULL DEFAULT '0' COMMENT '门店id',
  `staff_name` varchar(64) NOT NULL DEFAULT '' COMMENT '店员名称',
  `phone` char(15) NOT NULL DEFAULT '' COMMENT '手机号码',
  `verify_status` tinyint NOT NULL DEFAULT '0' COMMENT '核销开关',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='门店店员表';

-- eb_system_ticket：小票打印机列表
CREATE TABLE IF NOT EXISTS `eb_system_ticket` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '平台选择，1易联云，2飞鹅云',
  `print_name` varchar(255) NOT NULL DEFAULT '' COMMENT '打印机名称',
  `yly_user_id` varchar(255) NOT NULL DEFAULT '' COMMENT '易联云用户id',
  `yly_app_id` varchar(255) NOT NULL DEFAULT '' COMMENT '易联云应用ID',
  `yly_app_secret` varchar(255) NOT NULL DEFAULT '' COMMENT '易联云应用密钥',
  `yly_sn` varchar(255) NOT NULL DEFAULT '' COMMENT '易联云终端号',
  `fey_user` varchar(255) NOT NULL DEFAULT '' COMMENT '飞鹅云user',
  `fey_ukey` varchar(255) NOT NULL DEFAULT '' COMMENT '飞鹅云ukey',
  `fey_sn` varchar(255) NOT NULL DEFAULT '' COMMENT '飞鹅云SN',
  `times` int NOT NULL DEFAULT '0' COMMENT '打印联数',
  `print_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '打印时机，1支付后，2下单后',
  `print_content` longtext COMMENT '打印内容',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '申请状态，0关闭，1开启',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='小票打印机列表';

-- eb_system_user_level：设置用户等级表
CREATE TABLE IF NOT EXISTS `eb_system_user_level` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `mer_id` int NOT NULL DEFAULT '0' COMMENT '商户id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '会员名称',
  `money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '购买金额',
  `valid_date` int NOT NULL DEFAULT '0' COMMENT '有效时间',
  `is_forever` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为永久会员',
  `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否购买,1=购买,0=不购买',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示 1=显示,0=隐藏',
  `grade` int NOT NULL DEFAULT '0' COMMENT '会员等级',
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '享受折扣',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '会员卡背景',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '会员图标',
  `explain` text COMMENT '说明',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除.1=删除,0=未删除',
  `exp_num` int NOT NULL DEFAULT '0' COMMENT '升级经验',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='设置用户等级表';

-- eb_theme：主题表
CREATE TABLE IF NOT EXISTS `eb_theme` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(255) NOT NULL DEFAULT '' COMMENT '版本号',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '主题名称',
  `info` varchar(255) NOT NULL DEFAULT '' COMMENT '主题简介',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主题类型，0:自建主题，1:广场主题',
  `home_data` longtext COMMENT '首页数据',
  `home_image` varchar(255) NOT NULL DEFAULT '' COMMENT '首页图片',
  `home_data_id` int NOT NULL DEFAULT '0' COMMENT '首页数据来源id',
  `home_data_update_time` int NOT NULL DEFAULT '0' COMMENT '首页数据更新时间',
  `category_data` longtext COMMENT '分类页数据',
  `category_image` varchar(255) NOT NULL DEFAULT '' COMMENT '分类页图片',
  `category_data_id` int NOT NULL DEFAULT '0' COMMENT '分类页数据来源id',
  `category_data_update_time` int NOT NULL DEFAULT '0' COMMENT '分类页数据更新时间',
  `detail_data` longtext COMMENT '商品详情页数据',
  `detail_image` varchar(255) NOT NULL DEFAULT '' COMMENT '商城详情页图片',
  `detail_data_id` int NOT NULL DEFAULT '0' COMMENT '商品详情页数据来源id',
  `detail_data_update_time` int NOT NULL DEFAULT '0' COMMENT '商品详情页数据更新时间',
  `user_data` longtext COMMENT '个人中心数据',
  `user_image` varchar(255) NOT NULL DEFAULT '' COMMENT '个人中心图片',
  `user_data_id` int NOT NULL DEFAULT '0' COMMENT '个人中心数据来源id',
  `user_data_update_time` int NOT NULL DEFAULT '0' COMMENT '个人中心数据更新时间',
  `theme_data` longtext COMMENT '主题风格数据',
  `theme_data_id` int NOT NULL DEFAULT '0' COMMENT '主题风格数据来源id',
  `theme_data_update_time` int NOT NULL DEFAULT '0' COMMENT '主题风格数据更新时间',
  `home_default_data` longtext COMMENT '首页默认数据',
  `home_default_image` varchar(255) NOT NULL DEFAULT '' COMMENT '首页默认图片',
  `category_default_data` longtext COMMENT '分类页默认数据',
  `category_default_image` varchar(255) NOT NULL DEFAULT '' COMMENT '分类页默认图片',
  `detail_default_data` longtext COMMENT '商品详情页默认数据',
  `detail_default_image` varchar(255) NOT NULL DEFAULT '' COMMENT '商城详情页默认图片',
  `user_default_data` longtext COMMENT '个人中心默认数据',
  `user_default_image` varchar(255) NOT NULL DEFAULT '' COMMENT '个人中心默认图片',
  `theme_default_data` longtext COMMENT '主题风格默认数据',
  `page_type` varchar(255) NOT NULL DEFAULT 'theme' COMMENT '页面类型：theme主题，micro微页面',
  `is_use` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否使用',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `up_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='主题表';

-- eb_theme_download：主题下载记录表
CREATE TABLE IF NOT EXISTS `eb_theme_download` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '模板名称',
  `tid` int NOT NULL DEFAULT '0' COMMENT '模板ID',
  `download_time` int NOT NULL DEFAULT '0' COMMENT '下载时间',
  `download_url` varchar(500) NOT NULL DEFAULT '' COMMENT '下载地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='主题下载记录表';

-- eb_theme_module：主题组件表
CREATE TABLE IF NOT EXISTS `eb_theme_module` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT '组件类型',
  `data` longtext COMMENT '组件数据',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='主题组件表';

-- eb_upgrade_log：升级记录
CREATE TABLE IF NOT EXISTS `eb_upgrade_log` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `title` varchar(120) NOT NULL DEFAULT '' COMMENT '标题说明',
  `content` text NOT NULL COMMENT '更新内容',
  `first_version` char(3) NOT NULL DEFAULT '' COMMENT '版本号第一位',
  `second_version` char(3) NOT NULL DEFAULT '' COMMENT '版本号第二位',
  `third_version` char(3) NOT NULL DEFAULT '' COMMENT '版本号第三位',
  `fourth_version` char(3) NOT NULL DEFAULT '' COMMENT '版本号第四位',
  `error_data` text NOT NULL COMMENT '错误内容',
  `upgrade_time` int NOT NULL DEFAULT '0' COMMENT '升级时间',
  `package_link` varchar(255) NOT NULL DEFAULT '' COMMENT '文件备份地址',
  `data_link` varchar(255) NOT NULL DEFAULT '' COMMENT '数据备份地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='升级记录';

-- eb_upload_storage：云储存
CREATE TABLE IF NOT EXISTS `eb_upload_storage` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `access_key` varchar(100) NOT NULL DEFAULT '' COMMENT 'access_key',
  `driver_key` varchar(64) NOT NULL DEFAULT '' COMMENT '存储驱动标识',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=本地存储,2=七牛,3=oss,4=cos',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '空间名',
  `region` varchar(100) NOT NULL DEFAULT '' COMMENT '地域',
  `acl` enum('private','public-read','public-read-write') NOT NULL DEFAULT 'public-read' COMMENT '权限',
  `domain` varchar(100) NOT NULL DEFAULT '' COMMENT '空间域名',
  `cdn` varchar(255) NOT NULL DEFAULT '' COMMENT 'CDN加速域名',
  `cname` varchar(255) NOT NULL DEFAULT '' COMMENT 'CNAME值',
  `is_ssl` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=http,1=https',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `add_time` int NOT NULL COMMENT '添加事件',
  `update_time` int NOT NULL COMMENT '更新事件',
  PRIMARY KEY (`id`),
  KEY `is_delete` (`is_delete`),
  KEY `status` (`status`),
  KEY `idx_driver_key_is_delete_status` (`driver_key`,`is_delete`,`status`),
  KEY `access_key` (`access_key`,`type`,`is_delete`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='云储存';

-- eb_user：用户表
CREATE TABLE IF NOT EXISTS `eb_user` (
  `uid` int unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `account` varchar(32) NOT NULL DEFAULT '' COMMENT '用户账号',
  `pwd` varchar(32) NOT NULL DEFAULT '' COMMENT '用户密码',
  `real_name` varchar(25) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `birthday` int NOT NULL DEFAULT '0' COMMENT '生日',
  `card_id` varchar(20) NOT NULL DEFAULT '' COMMENT '身份证号码',
  `mark` varchar(255) NOT NULL DEFAULT '' COMMENT '用户备注',
  `partner_id` int NOT NULL DEFAULT '0' COMMENT '合伙人id',
  `group_id` int NOT NULL DEFAULT '0' COMMENT '用户分组id',
  `nickname` varchar(60) NOT NULL DEFAULT '' COMMENT '用户昵称',
  `avatar` varchar(256) NOT NULL DEFAULT '' COMMENT '用户头像',
  `phone` char(15) NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(64) NOT NULL DEFAULT '' COMMENT '邮箱',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '添加ip',
  `last_time` int unsigned NOT NULL DEFAULT '0' COMMENT '最后一次登录时间',
  `last_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '最后一次登录ip',
  `now_money` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '用户余额',
  `brokerage_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '佣金金额',
  `integral` int unsigned NOT NULL DEFAULT '0' COMMENT '用户剩余积分',
  `exp` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '会员经验',
  `sign_num` int NOT NULL DEFAULT '0' COMMENT '连续签到天数',
  `sign_remind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '签到提醒状态',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1为正常，0为禁止',
  `level` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `agent_level` int NOT NULL DEFAULT '0' COMMENT '分销等级',
  `spread_open` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有推广资格',
  `spread_uid` int unsigned NOT NULL DEFAULT '0' COMMENT '推广元id',
  `spread_time` int unsigned NOT NULL DEFAULT '0' COMMENT '推广员关联时间',
  `user_type` varchar(32) NOT NULL DEFAULT '' COMMENT '用户类型',
  `is_promoter` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否为推广员',
  `pay_count` int unsigned NOT NULL DEFAULT '0' COMMENT '用户购买次数',
  `spread_count` int NOT NULL DEFAULT '0' COMMENT '下级人数',
  `clean_time` int NOT NULL DEFAULT '0' COMMENT '清理会员时间',
  `addres` varchar(255) NOT NULL DEFAULT '' COMMENT '详细地址',
  `adminid` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员编号 ',
  `login_type` varchar(36) NOT NULL DEFAULT '' COMMENT '用户登陆类型，h5,wechat,routine',
  `record_phone` varchar(11) NOT NULL DEFAULT '0' COMMENT '记录临时电话',
  `is_money_level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '会员来源  0: 购买商品升级   1：花钱购买的会员2: 会员卡领取',
  `is_ever_level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否永久性会员  0: 非永久会员  1：永久会员',
  `overdue_time` bigint NOT NULL DEFAULT '0' COMMENT '会员到期时间',
  `uniqid` varchar(32) NOT NULL DEFAULT '' COMMENT '用户唯一值',
  `division_name` varchar(255) NOT NULL DEFAULT '' COMMENT '事业部/代理商名称',
  `division_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '代理类型：0普通，1事业部，2代理，3员工',
  `division_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '代理状态',
  `is_division` tinyint(1) NOT NULL DEFAULT '0' COMMENT '事业部状态',
  `is_agent` tinyint(1) NOT NULL DEFAULT '0' COMMENT '代理状态',
  `is_staff` tinyint(1) NOT NULL DEFAULT '0' COMMENT '员工状态',
  `division_id` int NOT NULL DEFAULT '0' COMMENT '事业部id',
  `agent_id` int NOT NULL DEFAULT '0' COMMENT '代理商id',
  `staff_id` int NOT NULL DEFAULT '0' COMMENT '员工id',
  `division_percent` int NOT NULL DEFAULT '0' COMMENT '分佣比例',
  `division_change_time` int NOT NULL DEFAULT '0' COMMENT '事业部/代理/员工修改时间',
  `division_end_time` int NOT NULL DEFAULT '0' COMMENT '事业部/代理/员工结束时间',
  `division_invite` int NOT NULL DEFAULT '0' COMMENT '代理商邀请码',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否注销',
  PRIMARY KEY (`uid`) USING BTREE,
  KEY `account` (`account`) USING BTREE,
  KEY `spreaduid` (`spread_uid`) USING BTREE,
  KEY `level` (`level`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `is_promoter` (`is_promoter`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户表';

-- eb_user_address：用户地址表
CREATE TABLE IF NOT EXISTS `eb_user_address` (
  `id` mediumint unsigned NOT NULL AUTO_INCREMENT COMMENT '用户地址id',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `real_name` varchar(32) NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `phone` varchar(16) NOT NULL DEFAULT '' COMMENT '收货人电话',
  `province` varchar(64) NOT NULL DEFAULT '' COMMENT '收货人所在省',
  `city` varchar(64) NOT NULL DEFAULT '' COMMENT '收货人所在市',
  `city_id` int NOT NULL DEFAULT '0' COMMENT '城市id',
  `district` varchar(64) NOT NULL DEFAULT '' COMMENT '收货人所在区',
  `detail` varchar(256) NOT NULL DEFAULT '' COMMENT '收货人详细地址',
  `post_code` int unsigned NOT NULL DEFAULT '0' COMMENT '邮编',
  `longitude` varchar(16) NOT NULL DEFAULT '0' COMMENT '经度',
  `latitude` varchar(16) NOT NULL DEFAULT '0' COMMENT '纬度',
  `is_default` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否默认',
  `is_del` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `is_default` (`is_default`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户地址表';

-- eb_user_bill：用户账单表
CREATE TABLE IF NOT EXISTS `eb_user_bill` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '用户账单id',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `link_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '关联id',
  `pm` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '0 = 支出 1 = 获得',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '账单标题',
  `category` varchar(64) NOT NULL DEFAULT '' COMMENT '明细种类',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT '明细类型',
  `number` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '明细数字',
  `balance` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '剩余',
  `mark` varchar(512) NOT NULL DEFAULT '' COMMENT '备注',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = 带确定 1 = 有效 -1 = 无效',
  `take` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = 未收货 1 = 已收货',
  `frozen_time` int NOT NULL DEFAULT '0' COMMENT '积分冻结时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `openid` (`uid`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `pm` (`pm`) USING BTREE,
  KEY `type` (`category`,`type`,`link_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户账单表';

-- eb_user_brokerage：用户分佣账单表
CREATE TABLE IF NOT EXISTS `eb_user_brokerage` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '用户佣金id',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `link_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '关联id',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT '明细类型',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '账单标题',
  `number` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '明细数字',
  `balance` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '剩余',
  `pm` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '0 = 支出 1 = 获得',
  `mark` varchar(512) NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = 带确定 1 = 有效 -1 = 无效',
  `take` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = 未收货 1 = 已收货',
  `frozen_time` int unsigned NOT NULL DEFAULT '0' COMMENT '冻结到期时间',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `pm` (`pm`) USING BTREE,
  KEY `type` (`type`,`link_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户分佣账单表';

-- eb_user_brokerage_frozen：用户佣金冻结记录表
CREATE TABLE IF NOT EXISTS `eb_user_brokerage_frozen` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户uid',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `uill_id` int NOT NULL DEFAULT '0' COMMENT '关联id',
  `frozen_time` int NOT NULL DEFAULT '0' COMMENT '冻结到期时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否有效',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `order_id` varchar(50) NOT NULL DEFAULT '' COMMENT '订单id',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户佣金冻结记录表';

-- eb_user_cancel：用户注销表
CREATE TABLE IF NOT EXISTS `eb_user_cancel` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户uid',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '用户昵称',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '用户提交申请时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1通过，2拒绝',
  `up_time` int NOT NULL DEFAULT '0' COMMENT '操作时间',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户注销表';

-- eb_user_enter：商户申请表
CREATE TABLE IF NOT EXISTS `eb_user_enter` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '商户申请ID',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `province` varchar(32) NOT NULL DEFAULT '' COMMENT '商户所在省',
  `city` varchar(32) NOT NULL DEFAULT '' COMMENT '商户所在市',
  `district` varchar(32) NOT NULL DEFAULT '' COMMENT '商户所在区',
  `address` varchar(256) NOT NULL DEFAULT '' COMMENT '商户详细地址',
  `merchant_name` varchar(256) NOT NULL DEFAULT '' COMMENT '商户名称',
  `link_user` varchar(32) NOT NULL DEFAULT '' COMMENT '关联用户',
  `link_tel` varchar(16) NOT NULL DEFAULT '' COMMENT '商户电话',
  `charter` varchar(512) NOT NULL DEFAULT '' COMMENT '商户证书',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `apply_time` int unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `success_time` int NOT NULL DEFAULT '0' COMMENT '通过时间',
  `fail_message` varchar(256) NOT NULL DEFAULT '' COMMENT '未通过原因',
  `fail_time` int unsigned NOT NULL DEFAULT '0' COMMENT '未通过时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '-1 审核未通过 0未审核 1审核通过',
  `is_lock` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '0 = 开启 1= 关闭',
  `is_del` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uid` (`uid`) USING BTREE,
  KEY `province` (`province`,`city`,`district`) USING BTREE,
  KEY `is_lock` (`is_lock`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='商户申请表';

-- eb_user_extract：用户提现表
CREATE TABLE IF NOT EXISTS `eb_user_extract` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `wechat_order_id` varchar(32) NOT NULL DEFAULT '' COMMENT '微信订单ID',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `real_name` varchar(64) NOT NULL DEFAULT '' COMMENT '名称',
  `user_name` varchar(64) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `extract_type` varchar(32) NOT NULL DEFAULT 'bank' COMMENT 'bank = 银行卡 alipay = 支付宝wx=微信',
  `bank_code` varchar(32) NOT NULL DEFAULT '0' COMMENT '银行卡',
  `bank_address` varchar(256) NOT NULL DEFAULT '' COMMENT '开户地址',
  `alipay_code` varchar(64) NOT NULL DEFAULT '' COMMENT '支付宝账号',
  `extract_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '提现金额',
  `extract_fee` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '提现手续费',
  `mark` varchar(512) NOT NULL DEFAULT '' COMMENT '备注',
  `balance` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '提现前佣金',
  `fail_msg` varchar(128) NOT NULL DEFAULT '' COMMENT '无效原因',
  `fail_time` int unsigned NOT NULL DEFAULT '0' COMMENT '无效时间',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '-1 未通过 0 审核中 1 已提现',
  `wechat` varchar(50) NOT NULL DEFAULT '' COMMENT '微信号',
  `qrcode_url` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码地址',
  `channel_type` varchar(32) NOT NULL DEFAULT '' COMMENT '提现来源',
  `out_bill_no` varchar(255) NOT NULL DEFAULT '' COMMENT '商户单号',
  `transfer_bill_no` varchar(255) NOT NULL DEFAULT '' COMMENT '微信转账单号',
  `state` varchar(32) NOT NULL DEFAULT '' COMMENT '单据状态',
  `package_info` varchar(2000) NOT NULL DEFAULT '' COMMENT '跳转领取页面的package信息',
  `fail_reason` varchar(255) NOT NULL DEFAULT '' COMMENT '失败原因',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `extract_type` (`extract_type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `openid` (`uid`) USING BTREE,
  KEY `fail_time` (`fail_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户提现表';

-- eb_user_friends：用户好友关系
CREATE TABLE IF NOT EXISTS `eb_user_friends` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户uid',
  `friends_uid` int NOT NULL DEFAULT '0' COMMENT '好友uid',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户好友关系';

-- eb_user_group：用户分组表
CREATE TABLE IF NOT EXISTS `eb_user_group` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_name` varchar(64) NOT NULL DEFAULT '' COMMENT '用户分组名称',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户分组表';

-- eb_user_invoice：用户发票管理表
CREATE TABLE IF NOT EXISTS `eb_user_invoice` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `header_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '抬头类型1:个人2：企业',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '发票类型1：普通2：专用',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称（发票抬头）',
  `duty_number` varchar(50) NOT NULL DEFAULT '' COMMENT '税号',
  `drawer_phone` varchar(30) NOT NULL DEFAULT '' COMMENT '开票人手机号',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '开票人邮箱',
  `tell` varchar(30) NOT NULL DEFAULT '' COMMENT '注册电话',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '注册地址',
  `bank` varchar(50) NOT NULL DEFAULT '' COMMENT '注册开户银行',
  `card_number` varchar(50) NOT NULL DEFAULT '' COMMENT '银行卡号',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC COMMENT='用户发票管理表';

-- eb_user_label：用户标签表
CREATE TABLE IF NOT EXISTS `eb_user_label` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `label_cate` int NOT NULL DEFAULT '0' COMMENT '标签分类',
  `label_name` varchar(255) NOT NULL DEFAULT '' COMMENT '标签名称',
  PRIMARY KEY (`id`),
  KEY `label_cate` (`label_cate`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户标签表';

-- eb_user_label_relation：用户标签关联表
CREATE TABLE IF NOT EXISTS `eb_user_label_relation` (
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `label_id` int NOT NULL DEFAULT '0' COMMENT '标签ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户标签关联表';

-- eb_user_level：用户等级记录表
CREATE TABLE IF NOT EXISTS `eb_user_level` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户uid',
  `level_id` int NOT NULL DEFAULT '0' COMMENT '等级vip',
  `grade` int NOT NULL DEFAULT '0' COMMENT '会员等级',
  `valid_time` int NOT NULL DEFAULT '0' COMMENT '过期时间',
  `is_forever` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否永久',
  `mer_id` int NOT NULL DEFAULT '0' COMMENT '商户id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:禁止,1:正常',
  `mark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `remind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已通知',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除,0=未删除,1=删除',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `discount` int NOT NULL DEFAULT '0' COMMENT '享受折扣',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户等级记录表';

-- eb_user_member：用户会员表
CREATE TABLE IF NOT EXISTS `eb_user_member` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `level_id` int NOT NULL DEFAULT '0' COMMENT '会员等级ID',
  `grade` int NOT NULL DEFAULT '0' COMMENT '等级序号',
  `is_forever` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否永久',
  `overdue_time` int NOT NULL DEFAULT '0' COMMENT '过期时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1有效 0无效',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户会员表';

-- eb_user_money：用户余额账单表
CREATE TABLE IF NOT EXISTS `eb_user_money` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '用户余额id',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `link_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '关联id',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT '明细类型',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '账单标题',
  `number` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '明细数字',
  `balance` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '剩余',
  `pm` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '0 = 支出 1 = 获得',
  `mark` varchar(512) NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = 带确定 1 = 有效 -1 = 无效',
  `add_time` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `pm` (`pm`) USING BTREE,
  KEY `type` (`type`,`link_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户余额账单表';

-- eb_user_recharge：用户充值表
CREATE TABLE IF NOT EXISTS `eb_user_recharge` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '充值用户UID',
  `order_id` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号',
  `trade_no` varchar(100) NOT NULL DEFAULT '' COMMENT '微信订单号',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `give_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '购买赠送金额',
  `recharge_type` varchar(32) NOT NULL DEFAULT '' COMMENT '充值类型',
  `paid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否充值',
  `pay_time` int NOT NULL DEFAULT '0' COMMENT '充值支付时间',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '充值时间',
  `refund_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `channel_type` varchar(255) NOT NULL DEFAULT '' COMMENT '用户访问端标识',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `recharge_type` (`recharge_type`) USING BTREE,
  KEY `paid` (`paid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户充值表';

-- eb_user_search：用户搜索记录表
CREATE TABLE IF NOT EXISTS `eb_user_search` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户uid',
  `keyword` varchar(255) NOT NULL DEFAULT '' COMMENT '搜索关键词',
  `vicword` varchar(1000) NOT NULL DEFAULT '' COMMENT '关键词分词',
  `num` int NOT NULL DEFAULT '1' COMMENT '搜索次数',
  `result` text COMMENT '搜索结果',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户搜索记录表';

-- eb_user_spread：用户推广关系表
CREATE TABLE IF NOT EXISTS `eb_user_spread` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户uid',
  `spread_uid` int NOT NULL DEFAULT '0' COMMENT '推广人uid',
  `spread_time` int NOT NULL DEFAULT '0' COMMENT '推广时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`) USING BTREE,
  KEY `spread_uid` (`spread_uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户推广关系表';

-- eb_user_visit：用户访问表
CREATE TABLE IF NOT EXISTS `eb_user_visit` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户uid',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '访问路径',
  `ip` varchar(255) NOT NULL DEFAULT '' COMMENT '用户ip',
  `stay_time` int NOT NULL DEFAULT '0' COMMENT '页面停留时间(秒)',
  `add_time` int NOT NULL DEFAULT '0' COMMENT '访问时间',
  `channel_type` varchar(255) NOT NULL DEFAULT '' COMMENT '用户访问端标识',
  `province` varchar(255) NOT NULL DEFAULT '' COMMENT '用户省份',
  PRIMARY KEY (`id`),
  KEY `time` (`channel_type`,`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户访问表';
