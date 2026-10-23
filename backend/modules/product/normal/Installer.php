<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal;

use crmeb\module\BaseModuleInstaller;
use think\facade\Db;

/**
 * 实物商品产品类型安装器
 */
class Installer extends BaseModuleInstaller
{
    public function getKey(): string
    {
        return 'product_normal';
    }

    public function getName(): string
    {
        return '实物商品';
    }

    public function getType(): string
    {
        return 'product_type';
    }

    /**
     * 建表
     * 实物商品扩展表 + 订单表
     * 注：中心通用表（product/order_center/order_item/order_log/cart）由全局迁移创建，这里只建实物专属表
     */
    protected function runMigrate(): void
    {
        // 1. 部署前端 UMD 扩展资源
        $this->deployFrontendAssets();

        // 2. 确保中心商品主表存在（由 modules/product/migrate/ 定义）
        $this->ensureCenterTables();

        // 3. 实物商品专属表
        if (!$this->hasTable('pt_normal')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('pt_normal')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                `product_id` int NOT NULL DEFAULT 0 COMMENT '关联商品主表ID',
                `weight` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '重量(kg)',
                `volume` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '体积(m³)',
                `add_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
                `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='实物商品扩展表（仅含实物专属字段，通用字段见 eb_product_center）';");
        }

        // 实物商品 SKU 表（多规格库存/价格载体，与 eb_product_center 通过 product_id 关联）
        if (!$this->hasTable('pt_normal_sku')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('pt_normal_sku')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
                `product_id` int unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
                `suk` varchar(128) NOT NULL DEFAULT '' COMMENT 'SKU规格值JSON',
                `price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '售价',
                `original_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT 'SKU划线价',
                `cost` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '成本价',
                `vip_price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '会员价',
                `stock` int unsigned NOT NULL DEFAULT '0' COMMENT '库存',
                `bar_code` varchar(15) NOT NULL DEFAULT '' COMMENT '条码',
                `bar_code_number` varchar(15) NOT NULL DEFAULT '' COMMENT '商品编码',
                `weight` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单SKU重量（kg）',
                `volume` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单SKU体积（m³）',
                `unit_name` varchar(32) NOT NULL DEFAULT '' COMMENT 'SKU单位（SKU级实际单位，如瓶/箱/盒）',
                `image` varchar(128) NOT NULL DEFAULT '' COMMENT 'SKU图片',
                `unique_code` char(8) NOT NULL DEFAULT '' COMMENT '唯一码',
                `brokerage` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '一级返佣',
                `brokerage_two` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '二级返佣',
                `is_default_select` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否默认选中',
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示SKU',
                PRIMARY KEY (`id`),
                KEY `product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='实物商品SKU表';");
        }

        // 唯一码统一使用 8 位短码，安装时顺便把旧表结构拉齐。
        $this->ensureNormalSkuColumns();

        if (!$this->hasTable('pt_normal_order')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('pt_normal_order')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '关联eb_order_center.order_no（订单号）',
                `order_item_id` int NOT NULL DEFAULT 0 COMMENT '关联订单项ID',
                `product_id` int NOT NULL DEFAULT 0 COMMENT '关联商品主表ID',
                `sku_id` int NOT NULL DEFAULT 0 COMMENT '关联SKU表ID（无SKU类型为0）',
                `uid` int NOT NULL DEFAULT 0 COMMENT '用户ID',
                `is_cancel` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否取消（0否/1是）',
                `paid` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否支付（0否/1是）',
                `pay_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实付金额',
                `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品总价',
                `status` varchar(32) NOT NULL DEFAULT 'pending_payment' COMMENT '业务状态',
                `has_installation` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否需要上门安装（0否/1是）',
                `activity_type` varchar(32) NOT NULL DEFAULT '' COMMENT '活动类型（seckill/combination/bargain/advance），普通订单留空',
                `delivery_name` varchar(64) NOT NULL DEFAULT '' COMMENT '快递公司',
                `delivery_id` varchar(64) NOT NULL DEFAULT '' COMMENT '快递单号',
                `delivery_type` varchar(32) NOT NULL DEFAULT '' COMMENT '配送方式',
                `user_address` varchar(200) NOT NULL DEFAULT '' COMMENT '收货地址',
                `real_name` varchar(32) NOT NULL DEFAULT '' COMMENT '收货人',
                `user_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '联系电话',
                `refund_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '退款状态',
                `refund_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
                `add_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
                `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
                PRIMARY KEY (`id`),
                KEY `idx_order_no` (`order_no`),
                KEY `idx_order_item_id` (`order_item_id`),
                KEY `idx_sku_id` (`sku_id`),
                KEY `idx_uid` (`uid`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='实物商品订单扩展表';");
        }
    }

    /**
     * 确保中心通用表存在（product/order_center/order_item/order_log/cart）
     * 由 modules/product/migrate/Version20260628CreateCenterTables 定义，
     * 此处直接在实物模块安装时补齐，避免依赖全局迁移。
     */
    protected function ensureCenterTables(): void
    {
        if (!$this->hasTable('product_center')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('product_center')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID（主键）',
                `product_type` varchar(32) NOT NULL DEFAULT 'normal' COMMENT '产品类型标识（normal/ticket/hotel/appoint等）',
                `name` varchar(128) NOT NULL DEFAULT '' COMMENT '商品名称',
                `intro` varchar(256) NOT NULL DEFAULT '' COMMENT '商品简介',
                `keyword` varchar(256) NOT NULL DEFAULT '' COMMENT '搜索关键词',
                `image` varchar(256) NOT NULL DEFAULT '' COMMENT '主图',
                `slider_image` varchar(2000) NOT NULL DEFAULT '' COMMENT '轮播图（JSON格式）',
                `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '展示价格（冗余字段）',
                `original_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '划线价（冗余字段）',
                `stock` int NOT NULL DEFAULT '0' COMMENT '总库存（冗余字段）',
                `sales` int NOT NULL DEFAULT '0' COMMENT '销量（冗余字段，累加已支付订单数）',
                `virtual_sales` int NOT NULL DEFAULT '0' COMMENT '虚拟销量',
                `views` int NOT NULL DEFAULT '0' COMMENT '浏览量（冗余字段）',
                `cate_id` int NOT NULL DEFAULT '0' COMMENT '分类ID（可选，实物必填）',
                `sort_order` int NOT NULL DEFAULT '0' COMMENT '排序',
                `unit_name` varchar(32) NOT NULL DEFAULT '' COMMENT '默认单位',
                `spec_type` tinyint NOT NULL DEFAULT '0' COMMENT '规格类型（0单规格/1多规格）',
                `delivery_methods` json DEFAULT NULL COMMENT '交付方式(express/pickup/door_service/check_in/digital)',
                `freight_config` json DEFAULT NULL COMMENT '运费配置JSON：express节点(type=free/fixed/template,postage,temp_id) + door_service节点(type=free/fixed,postage)',
                `is_limit` tinyint NOT NULL DEFAULT '0' COMMENT '是否开启限购(0否/1是)',
                `limit_type` tinyint NOT NULL DEFAULT '0' COMMENT '限购类型(1单次/2永久)',
                `limit_num` int NOT NULL DEFAULT '0' COMMENT '限购数量',
                `min_qty` int NOT NULL DEFAULT '1' COMMENT '起购数量',
                `is_gift` tinyint NOT NULL DEFAULT '0' COMMENT '是否支持送礼(0不支持/1支持)',
                `gift_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '送礼价格',
                `give_integral` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '赠送积分',
                `give_coupon_ids` varchar(500) NOT NULL DEFAULT '' COMMENT '赠送优惠券ID(逗号分隔)',
                `presale` tinyint NOT NULL DEFAULT '0' COMMENT '是否预售(0否/1是)',
                `presale_start_time` int NOT NULL DEFAULT '0' COMMENT '预售开始时间',
                `presale_end_time` int NOT NULL DEFAULT '0' COMMENT '预售结束时间',
                `presale_day` int NOT NULL DEFAULT '1' COMMENT '预售发货时间(预售活动结束后N天内发货)',
                `valid_type` tinyint NOT NULL DEFAULT '0' COMMENT '有效期类型(0长期/1固定时段/2购买后N天)',
                `valid_start_time` int NOT NULL DEFAULT '0' COMMENT '有效期开始时间',
                `valid_end_time` int NOT NULL DEFAULT '0' COMMENT '有效期结束时间',
                `valid_days` int NOT NULL DEFAULT '0' COMMENT '购买后有效天数',
                `service_region` json COMMENT '服务区域JSON',
                `service_address` varchar(500) NOT NULL DEFAULT '' COMMENT '服务地址',
                `is_show` tinyint NOT NULL DEFAULT '0' COMMENT '是否上架(0否/1是)',
                `is_del` tinyint NOT NULL DEFAULT '0' COMMENT '是否删除（0否/1是）',
                `is_sub` tinyint NOT NULL DEFAULT '0' COMMENT '是否开启分销（0否/1是）',
                `is_vip` tinyint NOT NULL DEFAULT '0' COMMENT '是否开启会员价（0否/1是）',
                `vip_product` tinyint NOT NULL DEFAULT '0' COMMENT '会员专属（0不限制/1仅会员可见可买/2所有人可见仅会员可买）',
                `add_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
                `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
                PRIMARY KEY (`id`),
                KEY `idx_product_type` (`product_type`),
                KEY `idx_cate_id` (`cate_id`),
                KEY `idx_is_show` (`is_show`),
                KEY `idx_is_del` (`is_del`),
                KEY `idx_sort_order` (`sort_order`),
                FULLTEXT KEY `ft_name_keyword` (`name`, `keyword`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='商品中台主表';");
        }

        // 兼容旧版建表：如果 product_center 表已存在但缺少 is_show 等列，补齐
        $this->ensureProductCenterColumns();

        if (!$this->hasTable('order_item')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('order_item')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '关联order_center.order_no',
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
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='订单商品快照表';");
        }

        if (!$this->hasTable('order_log')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('order_log')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号',
                `uid` int NOT NULL DEFAULT '0' COMMENT '操作用户ID',
                `operator_id` int NOT NULL DEFAULT '0' COMMENT '操作人ID',
                `operator_type` varchar(16) NOT NULL DEFAULT '' COMMENT '操作人类型',
                `from_status` varchar(32) NOT NULL DEFAULT '' COMMENT '变更前状态',
                `to_status` varchar(32) NOT NULL DEFAULT '' COMMENT '变更后状态',
                `content` varchar(256) NOT NULL DEFAULT '' COMMENT '操作说明',
                `add_time` int NOT NULL DEFAULT '0' COMMENT '操作时间',
                PRIMARY KEY (`id`),
                KEY `idx_order_no` (`order_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='订单状态日志表';");
        }

        if (!$this->hasTable('cart')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('cart')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
                `product_id` int NOT NULL DEFAULT '0' COMMENT '商品ID',
                `product_type` varchar(32) NOT NULL DEFAULT '' COMMENT '产品类型',
                `cart_num` int NOT NULL DEFAULT '1' COMMENT '数量',
                `is_pay` tinyint NOT NULL DEFAULT '0' COMMENT '0未购买 1已购买',
                `is_del` tinyint NOT NULL DEFAULT '0' COMMENT '是否删除',
                `is_new` tinyint NOT NULL DEFAULT '0' COMMENT '是否立即购买',
                `add_time` int NOT NULL DEFAULT '0' COMMENT '加车时间',
                PRIMARY KEY (`id`),
                KEY `idx_uid` (`uid`),
                KEY `idx_product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='购物车表';");
        }
    }

    /**
     * 确保 product_center 表包含 is_show 等必要列
     * 兼容旧版建表（仅 CREATE TABLE IF NOT EXISTS，不会给已有表补列）
     */
    protected function ensureProductCenterColumns(): void
    {
        $tableName = $this->table('product_center');
        $columns = [
            'is_show' => "tinyint NOT NULL DEFAULT '0' COMMENT '是否上架（0否/1是）'",
            'is_sub' => "tinyint NOT NULL DEFAULT '0' COMMENT '是否开启分销（0否/1是）'",
            'is_vip' => "tinyint NOT NULL DEFAULT '0' COMMENT '是否开启会员价（0否/1是）'",
            'vip_product' => "tinyint NOT NULL DEFAULT '0' COMMENT '会员专属（0不限制/1仅会员可见可买/2所有人可见仅会员可买）'",
            'presale_day' => "int NOT NULL DEFAULT '1' COMMENT '预售发货时间（预售活动结束后N天内发货）'",
        ];
        foreach ($columns as $column => $definition) {
            $this->ensureColumn($tableName, $column, $definition);
        }
    }

    /**
     * 确保普通商品 SKU 的唯一码长度与保存逻辑一致。
     */
    protected function ensureNormalSkuColumns(): void
    {
        $tableName = $this->table('pt_normal_sku');
        try {
            $database = config('database.connections.mysql.database');
            $result = Db::query(
                "SELECT data_type, character_maximum_length FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1",
                [$database, $tableName, 'unique_code']
            );
            if (empty($result)) {
                return;
            }
            $column = $result[0];
            $currentLength = (int)($column['character_maximum_length'] ?? 0);
            if ($currentLength !== 8) {
                $lengthResult = Db::query(
                    "SELECT COALESCE(MAX(CHAR_LENGTH(`unique_code`)), 0) AS max_length FROM `{$tableName}` WHERE `unique_code` <> ''"
                );
                $maxLength = (int)($lengthResult[0]['max_length'] ?? 0);
                if ($maxLength <= 8) {
                    $this->exec("ALTER TABLE `{$tableName}` MODIFY COLUMN `unique_code` char(8) NOT NULL DEFAULT '' COMMENT '唯一码'");
                }
            }
        } catch (\Throwable $e) {
            // 兼容只读/非 MySQL 安装环境，列修复失败不阻断其他模块初始化。
        }
    }

    /**
     * 检查并补齐单列
     * 列不存在时执行 ALTER TABLE ADD COLUMN
     */
    protected function ensureColumn(string $table, string $column, string $definition): void
    {
        try {
            $database = config('database.connections.mysql.database');
            $result = \think\facade\Db::query(
                "SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1",
                [$database, $table, $column]
            );
            if (empty($result)) {
                $this->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
            }
        } catch (\Throwable $e) {
            // 静默忽略，不影响安装流程
        }
    }

    /**
     * 回滚建表
     */
    protected function rollbackMigrate(): void
    {
        $this->removeFrontendAssets();
        $this->exec("DROP TABLE IF EXISTS `{$this->table('pt_normal_order')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('pt_normal_sku')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('pt_normal')}`;");
    }

    /**
     * 获取普通商品插件的通知初值。
     * @return array
     */
    protected function getNoticeDefinitions(): array
    {
        return [
            [
                'mark' => 'order_create',
                'type' => 1,
                'name' => '下单成功',
                'scene' => '订单提交后提醒用户付款',
                'variables' => [
                    'order_id' => '订单号',
                    'pay_price' => '应付金额',
                    'store_name' => '商品',
                    'user_phone' => '联系电话',
                    'order_time' => '订单时间',
                    'order_type' => '订单类型'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '下单成功',
                        'content' => '订单{order_id}已提交，商品{store_name}，应付{pay_price}元。',
                        'status' => 1
                    ],
                    'is_sms' => [
                        'title' => '下单成功',
                        'content' => '订单{order_id}已提交，商品{store_name}，应付{pay_price}元。',
                        'extra' => [
                            'sms_id' => '520268'
                        ]
                    ],
                    'is_wechat' => [
                        'title' => '新订单通知',
                        'content' => "订单类型\n{{short_thing6.DATA}}\n订单编号\n{{character_string1.DATA}}\n订单时间\n{{time2.DATA}}",
                        'extra' => [
                            'tempkey' => '48089',
                            'tempid' => '',
                            'key_list' => [
                                'short_thing6' => 'order_id',
                                'character_string1' => 'order_id',
                                'time2' => 'order_time'
                            ]
                        ]
                    ],
                    'is_routine' => [
                        'title' => '支付成功',
                        'content' => "订单编号\n{{character_string2.DATA}}\n订单金额\n{{amount13.DATA}}",
                        'extra' => [
                            'tempkey' => '585',
                            'tempid' => '',
                            'key_list' => [
                                'character_string2' => 'order_id',
                                'amount13' => 'pay_price'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'mark' => 'order_pay_success',
                'type' => 1,
                'name' => '支付成功',
                'scene' => '支付成功后通知用户',
                'variables' => [
                    'order_id' => '订单号',
                    'pay_price' => '支付金额',
                    'total_num' => '商品数量',
                    'user_phone' => '联系电话',
                    'link' => '订单链接',
                    'store_name' => '商品',
                    'order_time' => '订单时间',
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '支付成功',
                        'content' => '订单{order_id}已支付成功，金额{pay_price}元。',
                        'status' => 1
                    ],
                    'is_sms' => [
                        'title' => '支付成功',
                        'content' => '订单{order_id}已支付成功，金额{pay_price}元。',
                        'extra' => [
                            'sms_id' => '520268'
                        ]
                    ],
                    'is_wechat' => [
                        'title' => '支付成功',
                        'content' => "订单号\n{{character_string2.DATA}}\n下单时间\n{{time4.DATA}}\n商品名称\n{{thing3.DATA}}\n支付金额\n{{amount5.DATA}}",
                        'extra' => [
                            'tempkey' => '43216',
                            'tempid' => '',
                            'key_list' => [
                                'character_string2' => 'order_id',
                                'time4' => 'order_time',
                                'thing3' => 'store_name',
                                'amount5' => 'pay_price'
                            ]
                        ]
                    ],
                    'is_routine' => [
                        'title' => '支付成功',
                        'content' => '付款单号{{character_string1.DATA}}\n付款金额{{amount2.DATA}}',
                        'extra' => [
                            'tempkey' => '1927',
                            'tempid' => '',
                            'key_list' => [
                                'character_string1' => 'order_id',
                                'amount2' => 'pay_price'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'mark' => 'order_pay_success',
                'type' => 2,
                'name' => '新订单支付客服提醒',
                'scene' => '支付成功后提醒平台跟进发货',
                'variables' => [
                    'order_id' => '订单号',
                    'pay_price' => '支付金额',
                    'total_num' => '数量'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '新订单待发货',
                        'content' => '订单{order_id}已支付{pay_price}元，请跟进发货。',
                        'status' => 2
                    ]
                ]
            ],
            [
                'mark' => 'order_postage_success',
                'type' => 1,
                'name' => '发货提醒',
                'scene' => '快递单号确定后通知用户',
                'variables' => [
                    'order_id' => '订单号',
                    'store_name' => '商品',
                    'delivery_name' => '快递公司',
                    'delivery_id' => '快递单号',
                    'nickname' => '收货人',
                    'user_phone' => '电话',
                    'link' => '链接'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '发货提醒',
                        'content' => '订单{order_id}已由{delivery_name}发货，单号{delivery_id}。',
                        'status' => 1
                    ],
                    'is_sms' => [
                        'title' => '发货提醒',
                        'content' => '亲爱的用户{nickname}，您的商品{store_name}，订单号{order_id}已发货，请注意查收。',
                        'extra' => [
                            'sms_id' => '574815'
                        ]
                    ],
                    'is_wechat' => [
                        'title' => '发货提醒',
                        'content' => '订单编号{{character_string2.DATA}}\n商品名称{{thing4.DATA}}\n快递公司{{thing13.DATA}}\n快递单号{{character_string14.DATA}}',
                        'extra' => [
                            'tempkey' => '42984',
                            'tempid' => '',
                            'key_list' => [
                                'character_string2' => 'order_id',
                                'thing4' => 'store_name',
                                'thing13' => 'delivery_name',
                                'character_string14' => 'delivery_id'
                            ]
                        ]
                    ],
                    'is_routine' => [
                        'title' => '发货提醒',
                        'content' => '快递单号{{character_string2.DATA}}\n快递公司{{thing1.DATA}}\n商品{{thing5.DATA}}',
                        'extra' => [
                            'tempkey' => '1458',
                            'tempid' => '',
                            'key_list' => [
                                'character_string2' => 'delivery_id',
                                'thing1' => 'delivery_name',
                                'thing5' => 'store_name'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'mark' => 'order_deliver_success',
                'type' => 1,
                'name' => '送货提醒',
                'scene' => '配送订单出库后通知用户',
                'variables' => [
                    'order_id' => '订单号',
                    'store_name' => '商品',
                    'delivery_name' => '配送员',
                    'delivery_id' => '配送编号',
                    'nickname' => '收货人',
                    'user_phone' => '电话',
                    'link' => '链接'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '送货提醒',
                        'content' => '订单{order_id}正在配送，配送员{delivery_name}。',
                        'status' => 1
                    ]
                ]
            ],
            [
                'mark' => 'order_take',
                'type' => 1,
                'name' => '确认收货',
                'scene' => '确认收货后通知用户',
                'variables' => [
                    'order_id' => '订单号',
                    'store_name' => '商品',
                    'pay_price' => '金额',
                    'user_phone' => '电话',
                    'link' => '链接'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '确认收货',
                        'content' => '订单{order_id}已确认收货，感谢您的光临。',
                        'status' => 1
                    ],
                    'is_sms' => [
                        'title' => '确认收货',
                        'content' => '订单{order_id}已确认收货。',
                        'extra' => [
                            'sms_id' => '520271'
                        ]
                    ],
                    'is_wechat' => [
                        'title' => '确认收货',
                        'content' => '订单编号{{character_string2.DATA}}\n商品名称{{thing4.DATA}}\n订单金额{{amount9.DATA}}',
                        'extra' => [
                            'tempkey' => '42985',
                            'tempid' => '',
                            'key_list' => [
                                'character_string2' => 'order_id',
                                'thing4' => 'store_name',
                                'amount9' => 'pay_price'
                            ]
                        ]
                    ],
                    'is_routine' => [
                        'title' => '确认收货',
                        'content' => '订单编号{{character_string1.DATA}}\订单商品{{thing2.DATA}}',
                        'extra' => [
                            'tempkey' => '1481',
                            'tempid' => '',
                            'key_list' => [
                                'character_string1' => 'order_id',
                                'thing2' => 'store_name'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'mark' => 'order_take',
                'type' => 2,
                'name' => '确认收货客服提醒',
                'scene' => '收货后提醒平台订单完成',
                'variables' => [
                    'order_id' => '订单号',
                    'store_name' => '商品',
                    'pay_price' => '金额'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '订单已收货',
                        'content' => '订单{order_id}已确认收货。',
                        'status' => 2
                    ]
                ]
            ],
            [
                'mark' => 'order_refund',
                'type' => 1,
                'name' => '退款成功',
                'scene' => '退款到账后通知用户',
                'variables' => [
                    'order_id' => '订单号',
                    'pay_price' => '订单金额',
                    'refund_price' => '实退金额',
                    'store_name' => '商品',
                    'user_phone' => '电话',
                    'refund_time' => '退款时间',
                    'refund_status' => '退款状态',
                    'link' => '链接'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '退款成功',
                        'content' => '订单{order_id}已退款{refund_price}元。',
                        'status' => 1
                    ],
                    'is_sms' => [
                        'title' => '退款成功',
                        'content' => '订单{order_id}已退款{refund_price}元。',
                        'extra' => [
                            'sms_id' => '979010'
                        ]
                    ],
                    'is_wechat' => [
                        'title' => '退款成功',
                        'content' => '订单编号{{character_string1.DATA}}\商品名称{{thing2.DATA}}\n退款金额{{amount3.DATA}}',
                        'extra' => [
                            'tempkey' => '46622',
                            'tempid' => '',
                            'key_list' => [
                                'character_string1' => 'order_id',
                                'thing2' => 'store_name',
                                'amount3' => 'refund_price'
                            ]
                        ]
                    ],
                    'is_routine' => [
                        'title' => '退款成功',
                        'content' => '订单编号{{character_string1.DATA}}\n退款金额{{amount2.DATA}}',
                        'extra' => [
                            'tempkey' => '1451',
                            'tempid' => '',
                            'key_list' => [
                                'character_string1' => 'order_id',
                                'amount2' => 'refund_price'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'mark' => 'order_refund_create',
                'type' => 1,
                'name' => '退款申请已提交',
                'scene' => '退款申请提交后回执用户',
                'variables' => [
                    'order_id' => '订单号',
                    'pay_price' => '金额',
                    'refund_price' => '申请金额'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '退款申请已提交',
                        'content' => '订单{order_id}的退款申请已提交，申请金额{refund_price}元。',
                        'status' => 1
                    ]
                ]
            ],
            [
                'mark' => 'order_refund_create',
                'type' => 2,
                'name' => '用户申请退款客服提醒',
                'scene' => '申请退款后提醒平台审核',
                'variables' => [
                    'order_id' => '订单号',
                    'pay_price' => '金额',
                    'refund_price' => '申请金额'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '新退款申请',
                        'content' => '订单{order_id}申请退款{refund_price}元，请审核。',
                        'status' => 2
                    ]
                ]
            ],
            [
                'mark' => 'send_order_refund_no_status',
                'type' => 1,
                'name' => '退款失败',
                'scene' => '拒绝退款申请后通知用户原因',
                'variables' => [
                    'order_id' => '订单号',
                    'pay_price' => '金额',
                    'refund_price' => '退款金额',
                    'refuse_reason' => '原因',
                    'store_name' => '商品',
                    'user_phone' => '电话',
                    'link' => '链接'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '退款失败',
                        'content' => '订单{order_id}退款申请未通过：{refuse_reason}。',
                        'status' => 1
                    ],
                    'is_sms' => [
                        'title' => '退款失败',
                        'content' => '您的订单{order_id}退款申请已被拒绝，有疑问可咨询商城客服。',
                        'extra' => [
                            'sms_id' => '979001'
                        ]
                    ],
                    'is_wechat' => [
                        'title' => '退款失败',
                        'content' => '订单编号{{character_string1.DATA}}\n商品名称{{thing2.DATA}}\n退款金额{{amount3.DATA}}\n驳回原因{{thing4.DATA}}',
                        'extra' => [
                            'tempkey' => '46623',
                            'tempid' => '',
                            'key_list' => [
                                'character_string1' => 'order_id',
                                'thing2' => 'store_name',
                                'amount3' => 'refund_price',
                                'thing4' => 'refuse_reason'
                            ]
                        ]
                    ],
                    'is_routine' => [
                        'title' => '退款失败',
                        'content' => '订单编号{{character_string7.DATA}}\n商品名称{{thing10.DATA}}\n退款金额{{amount3.DATA}}\退款原因{{thing11.DATA}}',
                        'extra' => [
                            'tempkey' => '1451',
                            'tempid' => '',
                            'key_list' => [
                                'character_string7' => 'order_id',
                                'thing10' => 'store_name',
                                'amount3' => 'refund_price',
                                'thing11' => 'refuse_reason'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'mark' => 'price_revision',
                'type' => 1,
                'name' => '订单改价',
                'scene' => '订单改价后提醒用户重新支付',
                'variables' => [
                    'order_id' => '订单号',
                    'pay_price' => '新金额',
                    'change_price' => '差额',
                    'user_phone' => '电话'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '订单改价',
                        'content' => '订单{order_id}价格调整，应付{pay_price}元。',
                        'status' => 1
                    ],
                    'is_sms' => [
                        'title' => '订单改价',
                        'content' => '您的订单{order_id}，实际支付金额已被修改为{pay_price}元。',
                        'extra' => [
                            'sms_id' => '528288'
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * 将商品迁移和数据导出权限挂到实物商品（列表）菜单下。
     * 权限记录沿用原 ID，已有角色授权不受影响。
     * @return int
     */
    protected function seed(): int
    {
        $noticeCount = app()->make(\app\services\message\pipeline\NoticeSeedServices::class)->seed($this->getOwnerPluginId(), $this->getNoticeDefinitions());
        $normalRootId = (int)Db::table($this->table('system_menus'))
            ->where('unique_auth', 'admin-normal-product')
            ->where('auth_type', 1)
            ->where('is_del', 0)
            ->value('id');
        $normalMenuId = (int)Db::table($this->table('system_menus'))
            ->where('unique_auth', 'admin-normal-product-list')
            ->where('auth_type', 1)
            ->where('is_del', 0)
            ->value('id');
        if (!$normalRootId || !$normalMenuId) {
            return $noticeCount;
        }

        $productRootId = $this->getMenuPidByKey('core:admin-product');
        $exportMenuId = (int)Db::table($this->table('system_menus'))
            ->where('unique_auth', 'product-export')
            ->value('id');
        $crawlMenuId = (int)Db::table($this->table('system_menus'))
            ->where('unique_auth', 'product-copy')
            ->value('id');
        $normalPath = implode('/', array_filter([$productRootId, $normalRootId]));
        $listPath = $normalPath . '/' . $normalMenuId;
        $count = (int)Db::table($this->table('system_menus'))
            ->where('id', $normalRootId)
            ->update([
                'pid' => $productRootId,
                'path' => (string)$productRootId,
                'menu_path' => '/product/normal',
            ]);
        $count += (int)Db::table($this->table('system_menus'))
            ->where('id', $normalMenuId)
            ->update([
                'pid' => $normalRootId,
                'path' => $normalPath,
                'menu_path' => '/product/normal/list',
            ]);
        $statisticMenuId = (int)Db::table($this->table('system_menus'))
            ->where('unique_auth', 'admin-normal-statistics')
            ->where('auth_type', 1)
            ->where('is_del', 0)
            ->value('id');
        if ($statisticMenuId) {
            $count += (int)Db::table($this->table('system_menus'))
                ->where('id', $statisticMenuId)
                ->update([
                    'pid' => $normalRootId,
                    'path' => $normalPath,
                    'menu_path' => '/product/normal/statistics',
                ]);
        }

        $count += (int)Db::table($this->table('system_menus'))
            ->where('unique_auth', 'product-export')
            ->update([
                'pid' => $normalMenuId,
                'path' => $listPath,
            ]);

        if ($exportMenuId) {
            $count += (int)Db::table($this->table('system_menus'))
                ->where('unique_auth', 'export-product_list')
                ->update([
                    'pid' => $exportMenuId,
                    'path' => $listPath . '/' . $exportMenuId,
                ]);
        }

        if ($crawlMenuId) {
            $count += (int)Db::table($this->table('system_menus'))
                ->where('id', $crawlMenuId)
                ->update([
                    'pid' => $normalMenuId,
                    'path' => $listPath,
                ]);
            $count += (int)Db::table($this->table('system_menus'))
                ->where('pid', $crawlMenuId)
                ->update([
                    'path' => $listPath . '/' . $crawlMenuId,
                ]);
        }

        return $count + $noticeCount;
    }

    /**
     * 存量站点菜单结构迁移（幂等，安装与启用前执行）。
     * 兼容旧叶子菜单、旧三级菜单和核心旧商品统计菜单，最终统一为：
     * 商品 → 实物商品 → 商品管理 / 商品统计。
     * @return int
     */
    protected function migrateMenuStructure(): int
    {
        $menus = Db::table($this->table('system_menus'));
        $ownerScope = function ($query) {
            if ($this->supportsOwnershipColumn('system_menus')) {
                $query->whereIn('owner_plugin_id', ['', $this->getOwnerPluginId()]);
            }
            return $query;
        };
        $count = 0;

        // 1. 保留旧父菜单 ID，或把当前叶子菜单转成商品管理，避免角色授权丢失。
        $listMenu = (clone $menus)
            ->where('unique_auth', 'admin-normal-product-list')
            ->where('auth_type', 1)
            ->where('is_del', 0);
        $listMenu = $ownerScope($listMenu)->find();
        $dirMenu = (clone $menus)
            ->where('menu_name', '实物商品')
            ->where('unique_auth', 'admin-normal-product')
            ->where('auth_type', 1)
            ->where('is_del', 0);
        $dirMenu = $ownerScope($dirMenu)->find();

        if ($dirMenu) {
            $count += (int)(clone $menus)->where('id', $dirMenu['id'])->update([
                'pid' => $this->getMenuPidByKey('core:admin-product'),
                'menu_name' => '实物商品',
                'controller' => 'normal',
                'action' => 'index',
                'api_url' => '',
                'menu_path' => '/product/normal',
                'unique_auth' => 'admin-normal-product',
            ]);
        }
        if ($listMenu) {
            $count += (int)(clone $menus)->where('id', $listMenu['id'])->update([
                'pid' => $dirMenu ? (int)$dirMenu['id'] : (int)($listMenu['pid'] ?? 0),
                'menu_name' => '商品管理',
                'menu_path' => '/product/normal/list',
                'controller' => 'normalProduct',
                'action' => 'index',
                'unique_auth' => 'admin-normal-product-list',
            ]);
        }

        // 2. 旧的独立“添加商品”菜单不再展示，添加入口保留为隐藏权限。
        $count += (int)$ownerScope((clone $menus)
            ->where('menu_name', '添加商品')
            ->where('unique_auth', 'admin-normal-product-add')
            ->where('auth_type', 1)
            ->where('is_del', 0))->delete();

        // 3. 旧插件统计菜单原地改造成“商品统计”，核心旧菜单保留但隐藏。
        $statisticsMenu = $ownerScope((clone $menus)
            ->whereIn('menu_name', ['统计看板', '商品统计'])
            ->where('unique_auth', 'admin-normal-statistics')
            ->where('auth_type', 1)
            ->where('is_del', 0))->find();
        if ($statisticsMenu) {
            $count += (int)(clone $menus)->where('id', $statisticsMenu['id'])->update([
                'pid' => $dirMenu ? (int)$dirMenu['id'] : (int)($statisticsMenu['pid'] ?? 0),
                'menu_name' => '商品统计',
                'controller' => 'normalStatistics',
                'action' => 'index',
                'api_url' => 'normal/statistic',
                'menu_path' => '/product/normal/statistics',
                'unique_auth' => 'admin-normal-statistics',
            ]);
        }
        if ($this->supportsOwnershipColumn('system_menus')) {
            $count += (int)(clone $menus)
                ->where('menu_path', '/statistic/product')
                ->where('auth_type', 1)
                ->where('owner_plugin_id', '')
                ->update(['is_show' => 0, 'is_show_path' => 0]);
        } else {
            $count += (int)(clone $menus)
                ->where('menu_path', '/statistic/product')
                ->where('auth_type', 1)
                ->update(['is_show' => 0, 'is_show_path' => 0]);
        }

        // 4. 「订单管理」改名「实物订单」并挂到「订单」一级菜单下。
        $orderMenuQuery = (clone $menus)
            ->where('menu_name', '订单管理')
            ->where('unique_auth', 'admin-normal-order-list')
            ->where('auth_type', 1)
            ->where('is_del', 0);
        $orderMenu = $ownerScope($orderMenuQuery)->find();
        if ($orderMenu) {
            $count += (int)(clone $menus)->where('id', $orderMenu['id'])->update([
                'menu_name' => '实物订单',
                'pid' => $this->getMenuPidByKey('core:admin-order'),
                'icon' => 'CRMEB-102-dingdan-xianxing',
            ]);
        }

        // 「实物订单」已是订单一级菜单下的子菜单，统一使用 CRMEB 订单图标
        $count += (int)$ownerScope((clone $menus)
            ->where('menu_name', '实物订单')
            ->where('unique_auth', 'admin-normal-order-list')
            ->where('auth_type', 1)
            ->where('is_del', 0))->update(['icon' => 'CRMEB-102-dingdan-xianxing']);

        // 5. 「创建实物商品」权限项补充 unique_auth，使其能被新配置按旧身份认领。
        $count += (int)$ownerScope((clone $menus)
            ->where('menu_name', '创建实物商品')
            ->where('api_url', 'normal/product')
            ->where('methods', 'POST')
            ->where('auth_type', 3)
            ->where('unique_auth', ''))->update(['unique_auth' => 'admin-normal-product-add']);

        return $count;
    }

    /**
     * 后台菜单配置
     * 结构：商品 → 实物商品 → 商品管理 / 商品统计；订单 → 实物订单
     */
    protected function getMenuConfig(): array
    {
        return [
            // 实物商品父菜单
            [
                'key' => 'normal-root',
                'parent_key' => 'core:admin-product',
                'menu_name' => '实物商品',
                'controller' => 'normal',
                'action' => 'index',
                'module' => 'admin',
                'api_url' => '',
                'menu_path' => '/product/normal',
                'unique_auth' => 'admin-normal-product',
                'mark' => '实物商品管理',
                'icon' => 'CRMEB-566-shiwu-xianxing',
                'sort' => 10,
                'is_show' => 1,
                'is_show_path' => 1,
                'access' => 1,
                'auth_type' => 1,
            ],
            // 商品管理
            [
                'key' => 'normal-product-list',
                'pid' => '@实物商品',
                'menu_name' => '商品管理',
                'controller' => 'normalProduct',
                'action' => 'index',
                'module' => 'admin',
                'api_url' => 'normal/product/list',
                'menu_path' => '/product/normal/list',
                'unique_auth' => 'admin-normal-product-list',
                'mark' => '实物商品列表',
                'icon' => '',
                'sort' => 1,
                'is_show' => 1,
                'is_show_path' => 1,
                'access' => 1,
                'auth_type' => 1,
            ],
            // 商品统计
            [
                'key' => 'normal-statistics',
                'pid' => '@实物商品',
                'menu_name' => '商品统计',
                'controller' => 'normalStatistics',
                'action' => 'index',
                'module' => 'admin',
                'api_url' => 'normal/statistic',
                'menu_path' => '/product/normal/statistics',
                'unique_auth' => 'admin-normal-statistics',
                'mark' => '实物商品统计',
                'icon' => '',
                'sort' => 2,
                'is_show' => 1,
                'is_show_path' => 1,
                'access' => 1,
                'auth_type' => 1,
            ],
            // 商品统计接口权限
            [
                'key' => 'normal-statistics-basic',
                'pid' => '@商品统计',
                'menu_name' => '查询实物商品基础统计',
                'controller' => 'normalStatistics',
                'action' => 'getBasic',
                'module' => 'admin',
                'api_url' => 'normal/statistic/get_basic',
                'methods' => 'GET',
                'unique_auth' => 'admin-normal-statistics-basic',
                'is_show' => 1,
                'is_show_path' => 1,
                'access' => 1,
                'auth_type' => 2,
            ],
            [
                'key' => 'normal-statistics-trend',
                'pid' => '@商品统计',
                'menu_name' => '查询实物商品趋势统计',
                'controller' => 'normalStatistics',
                'action' => 'getTrend',
                'module' => 'admin',
                'api_url' => 'normal/statistic/get_trend',
                'methods' => 'GET',
                'unique_auth' => 'admin-normal-statistics-trend',
                'is_show' => 1,
                'is_show_path' => 1,
                'access' => 1,
                'auth_type' => 2,
            ],
            [
                'key' => 'normal-statistics-ranking',
                'pid' => '@商品统计',
                'menu_name' => '查询实物商品排行统计',
                'controller' => 'normalStatistics',
                'action' => 'getProductRanking',
                'module' => 'admin',
                'api_url' => 'normal/statistic/get_product_ranking',
                'methods' => 'GET',
                'unique_auth' => 'admin-normal-statistics-ranking',
                'is_show' => 1,
                'is_show_path' => 1,
                'access' => 1,
                'auth_type' => 2,
            ],
            [
                'key' => 'normal-statistics-excel',
                'pid' => '@商品统计',
                'menu_name' => '导出实物商品统计',
                'controller' => 'normalStatistics',
                'action' => 'getExcel',
                'module' => 'admin',
                'api_url' => 'normal/statistic/get_excel',
                'methods' => 'GET',
                'unique_auth' => 'admin-normal-statistics-excel',
                'is_show' => 1,
                'is_show_path' => 1,
                'access' => 1,
                'auth_type' => 2,
            ],
            // ============ 商品管理隐藏权限菜单（挂在商品管理下） ============
            // 实物商品各类型数量（隐藏，仅权限认证用）
            [
                'pid' => '@商品管理',
                'menu_name' => '实物商品各类型数量',
                'controller' => 'normalProduct',
                'action' => 'typeHeader',
                'module' => 'admin',
                'api_url' => 'normal/product/type_header',
                'methods' => 'GET',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 创建实物商品（隐藏，仅权限认证用；列表页"添加商品"按钮与添加页路由共用此权限）
            [
                'pid' => '@商品管理',
                'menu_name' => '创建实物商品',
                'controller' => 'normalProduct',
                'action' => 'save',
                'module' => 'admin',
                'api_url' => 'normal/product',
                'methods' => 'POST',
                'unique_auth' => 'admin-normal-product-add',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 更新实物商品（隐藏，仅权限认证用）
            [
                'pid' => '@商品管理',
                'menu_name' => '更新实物商品',
                'controller' => 'normalProduct',
                'action' => 'update',
                'module' => 'admin',
                'api_url' => 'normal/product/:id',
                'methods' => 'PUT',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 实物商品上下架（隐藏，仅权限认证用）
            [
                'pid' => '@商品管理',
                'menu_name' => '实物商品上下架',
                'controller' => 'normalProduct',
                'action' => 'setStatus',
                'module' => 'admin',
                'api_url' => 'normal/product/status/:id/:status',
                'methods' => 'PUT',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 删除实物商品（隐藏，仅权限认证用）
            [
                'pid' => '@商品管理',
                'menu_name' => '删除实物商品',
                'controller' => 'normalProduct',
                'action' => 'delete',
                'module' => 'admin',
                'api_url' => 'normal/product/:id',
                'methods' => 'DELETE',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 实物商品详情（隐藏，仅权限认证用）
            [
                'pid' => '@商品管理',
                'menu_name' => '实物商品详情',
                'controller' => 'normalProduct',
                'action' => 'read',
                'module' => 'admin',
                'api_url' => 'normal/product/:id',
                'methods' => 'GET',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 批量删除实物商品（隐藏，仅权限认证用）
            [
                'pid' => '@商品管理',
                'menu_name' => '批量删除实物商品',
                'controller' => 'normalProduct',
                'action' => 'batchDelete',
                'module' => 'admin',
                'api_url' => 'normal/product/batch_delete',
                'methods' => 'POST',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 批量恢复实物商品（隐藏，仅权限认证用）
            [
                'pid' => '@商品管理',
                'menu_name' => '批量恢复实物商品',
                'controller' => 'normalProduct',
                'action' => 'batchRecover',
                'module' => 'admin',
                'api_url' => 'normal/product/batch_recover',
                'methods' => 'POST',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 批量上下架实物商品（隐藏，仅权限认证用）
            [
                'pid' => '@商品管理',
                'menu_name' => '批量上下架实物商品',
                'controller' => 'normalProduct',
                'action' => 'batchSetStatus',
                'module' => 'admin',
                'api_url' => 'normal/product/batch_status',
                'methods' => 'PUT',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 批量设置实物商品（隐藏，仅权限认证用）
            [
                'pid' => '@商品管理',
                'menu_name' => '批量设置实物商品',
                'controller' => 'normalProduct',
                'action' => 'batchSetting',
                'module' => 'admin',
                'api_url' => 'normal/product/batch_setting',
                'methods' => 'POST',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 彻底删除实物商品（隐藏，仅权限认证用）
            [
                'pid' => '@商品管理',
                'menu_name' => '彻底删除实物商品',
                'controller' => 'normalProduct',
                'action' => 'fullDel',
                'module' => 'admin',
                'api_url' => 'normal/product/full_del/:id',
                'methods' => 'DELETE',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 实物订单（挂在"订单"下）
            [
                'parent_key' => 'core:admin-order',
                'menu_name' => '实物订单',
                'controller' => 'normalOrder',
                'action' => 'index',
                'module' => 'admin',
                'api_url' => 'normal/order/list',
                'menu_path' => '/product/normal/order/list',
                'unique_auth' => 'admin-normal-order-list',
                'mark' => '实物订单列表',
                'icon' => 'CRMEB-102-dingdan-xianxing',
                // 订单菜单排序区间：订单中台 140，售后订单 60，预留中间位置给其他商品订单。
                'sort' => 100,
                'is_show' => 1,
                'is_show_path' => 1,
                'access' => 1,
                'auth_type' => 1,
            ],
            // 实物商品订单详情（隐藏，仅权限认证用）
            [
                'pid' => '@实物订单',
                'menu_name' => '实物商品订单详情',
                'controller' => 'normalOrder',
                'action' => 'read',
                'module' => 'admin',
                'api_url' => 'normal/order/:order_no',
                'methods' => 'GET',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
            // 实物商品订单发货（隐藏，仅权限认证用）
            [
                'pid' => '@实物订单',
                'menu_name' => '实物商品订单发货',
                'controller' => 'normalOrder',
                'action' => 'shipment',
                'module' => 'admin',
                'api_url' => 'normal/order/shipment',
                'methods' => 'POST',
                'is_show' => 1,
                'access' => 1,
                'auth_type' => 3,
            ],
        ];
    }

    /**
     * 重写安装方法：安装前先做存量菜单结构迁移（幂等），
     * 已安装时也执行 runCustom（迁移修复）
     */
    public function install(): array
    {
        $this->migrateMenuStructure();
        $result = parent::install();
        if ($result['status'] === 'success' || $result['status'] === 'skipped') {
            $this->runCustom();
        }
        return $result;
    }

    protected function runCustom(): void
    {
        // 已安装插件再次执行安装时，基类会直接返回 skipped；仍需补跑菜单和种子，
        // 让旧版叶子菜单能够幂等升级为「实物商品 → 商品管理 / 商品统计」结构。
        $this->migrateMenuStructure();
        $this->insertMenus();
        $this->seed();
        $this->seedCrontab();

        $db = \think\facade\Db::table($this->table('system_menus'));
        // 迁移：清理旧的单一菜单条目（menu_name=实物商品, controller=normalProduct）
        // 这些旧条目的 pid=1，没有子菜单，是早期版本残留
        $oldMenu = (clone $db)
            ->alias('main')
            ->where('main.menu_name', '实物商品')
            ->where('main.controller', 'normalProduct')
            ->where('main.unique_auth', 'admin-normal-product-list')
            ->when($this->supportsOwnershipColumn('system_menus'), function ($query) {
                $query->whereIn('main.owner_plugin_id', ['', $this->getOwnerPluginId()]);
            })
            ->whereNotExists(function ($query) {
                $query->table($this->table('system_menus'))
                    ->whereRaw('pid = main.id');
            })
            ->find();
        if ($oldMenu) {
            (clone $db)->delete($oldMenu['id']);
        }

        // 修复父菜单 menu_path 为空导致 el-submenu index 冲突
        // 兼容极旧版本残留：当前结构下该行已由 migrateMenuStructure() 改造为叶子菜单
        (clone $db)
            ->where('menu_name', '实物商品')
            ->where('controller', 'normalProduct')
            ->where('menu_path', '')
            ->update(['menu_path' => '/product/normal/list']);

    }

    /**
     * 启用时回调：存量菜单结构迁移 + 重插菜单 + 部署前端资源
     */
    public function onEnable(): void
    {
        $this->migrateMenuStructure();
        parent::onEnable();
        $this->deployFrontendAssets();
    }

    /**
     * 禁用时回调：隐藏菜单
     */
    public function onDisable(): void
    {
        parent::onDisable();
    }

    /**
     * 部署前端 UMD 扩展资源到 public/admin/static/extensions/normal/
     */
    protected function deployFrontendAssets(): void
    {
        $sourceDir = __DIR__ . '/frontend/dist/';
        $targetDir = public_path('admin/static/extensions/normal/');
        if (!is_dir($sourceDir)) {
            return;
        }
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        foreach (['app.umd.min.js', 'app.umd.css'] as $file) {
            $source = $sourceDir . $file;
            $target = $targetDir . $file;
            if (is_file($source) && !is_file($target)) {
                copy($source, $target);
            }
        }
    }

    /**
     * 移除已部署的前端资源
     */
    protected function removeFrontendAssets(): void
    {
        $targetDir = public_path('admin/static/extensions/normal/');
        if (!is_dir($targetDir)) {
            return;
        }
        foreach (['app.umd.min.js', 'app.umd.css'] as $file) {
            $target = $targetDir . $file;
            if (is_file($target)) {
                unlink($target);
            }
        }
        if (empty(glob($targetDir . '*'))) {
            rmdir($targetDir);
        }
    }

    /**
     * 预置定时任务：eb_system_crontab 插入 mark=advanceOff 的预售到期下架记录（幂等）
     *
     * 预售为普通商品能力，任务执行体由 App::register 注册的 NormalAdvanceOffTask 承接；
     * 系统安装 SQL 已含该种子，此处兜底「卸载后重装」场景。
     *
     * @return int 新增记录数
     */
    private function seedCrontab(): int
    {
        if (Db::table($this->table('system_crontab'))->where('mark', 'advanceOff')->where('is_del', 0)->count() > 0) {
            return 0;
        }
        Db::table($this->table('system_crontab'))->insert([
            'name'                => '预售商品到期自动下架',
            'mark'                => 'advanceOff',
            'content'             => '每隔5分钟执行预售商品到期下架',
            'type'                => 2,
            'minute'              => 5,
            'timeStr'             => '0 */5 * * * *',
            'next_execution_time' => time(),
            'is_open'             => 1,
            'add_time'            => time(),
            'update_time'         => time(),
        ]);
        return 1;
    }

    /**
     * 清理业务数据
     */
    protected function cleanData(): int
    {
        $count = app()->make(\app\services\message\pipeline\NoticeSeedServices::class)->clean($this->getOwnerPluginId());
        $count += Db::table($this->table('system_crontab'))->where('mark', 'advanceOff')->delete();
        $count += \think\facade\Db::table($this->table('pt_normal_order'))->whereRaw('1=1')->delete();
        $count += \think\facade\Db::table($this->table('pt_normal_sku'))->whereRaw('1=1')->delete();
        $count += \think\facade\Db::table($this->table('pt_normal'))->whereRaw('1=1')->delete();
        return $count;
    }
}
