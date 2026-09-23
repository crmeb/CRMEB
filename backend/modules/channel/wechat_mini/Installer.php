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

namespace modules\channel\wechat_mini;

use crmeb\module\BaseModuleInstaller;
use think\facade\Db;

/**
 * 微信小程序端渠道安装器
 *
 * channel 为容器目录，小程序端作为其下独立可安装/卸载的端渠道。
 * getKey 形如 channel/wechat_mini，由 ModuleInstaller 扫描 modules/channel/*\/Installer.php 注册。
 *
 * 后台菜单结构：
 * - 一级菜单「小程序」（挂载到系统"应用"菜单下, icon=el-icon-Cellphone, sort=50）
 * - 二级子菜单4个：小程序配置 / 小程序下载 / 小程序上传（CI） / 小程序链接（外链管理）
 * - 每个子菜单下含对应的 auth_type=3 接口权限条目，共13条
 * - 营销 > 直播管理（小程序直播功能，含主播/商品/直播间管理）
 *
 * 数据存储说明：
 * - 共享身份表：wechat_user（公众号与小程序的微信身份映射）；本安装器会确保其存在。
 * - 自有 2 类表：routine_scheme（小程序外链/URL Scheme 记录）、直播相关 4 张表（live_anchor/live_goods/live_room/live_room_goods）。
 * - 3 个 system_config_tab 配置分类（routine 小程序配置 / routine_config 小程序配置 / routine_encoding 消息推送配置），
 *   其中父分类「小程序配置」（eng_title=routine）不再预置在核心安装 SQL，由本安装器创建。
 * - 12 条 system_config 配置项（routine_appId ~ routine_encodingaeskey + get_avatar/order_shipping_open/recharge_switch）。
 * - 小程序登录、订阅消息能力依赖核心配置、eb_user 和共享身份表，不依赖 wechat_mp 模块。
 * - 后台菜单共 1(一级) + 4(二级) + 13(三级接口权限) + 直播菜单(1父+3子+18接口权限) = 40 条记录。
 */
class Installer extends BaseModuleInstaller
{
    public function getKey(): string  { return 'channel/wechat_mini'; }
    public function getName(): string { return '微信小程序'; }
    public function getType(): string { return 'channel'; }

    // ============== 小程序相关 system_config 配置键列表 ==============

    /**
     * 小程序配置项键名
     * 用于 seed 写入和 cleanData 清理
     */
    private const ROUTINE_CONFIG_KEYS = [
        'routine_appId',
        'routine_appsecret',
        'routine_name',
        'routine_contact_type',
        'routine_auth_type',
        'routine_api',
        'routine_token',
        'routine_encode',
        'routine_encodingaeskey',
        'get_avatar',
        'order_shipping_open',
        'recharge_switch',
    ];

    /**
     * 建表
     * 小程序自有表及共享身份表从 v6 迁移，hasTable 幂等跳过；新部署时则创建。
     */
    protected function runMigrate(): void
    {
        // 微信渠道身份映射表由小程序和公众号共同使用，确保仅安装小程序时也能完成登录。
        if (!$this->hasTable('wechat_user')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('wechat_user')}` (
                `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
                `uid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
                `unionid` varchar(30) NOT NULL DEFAULT '' COMMENT '微信开放平台唯一标识',
                `openid` varchar(255) NOT NULL DEFAULT '' COMMENT '微信渠道openid',
                `nickname` varchar(64) NOT NULL DEFAULT '' COMMENT '微信昵称',
                `headimgurl` varchar(256) NOT NULL DEFAULT '' COMMENT '微信头像',
                `sex` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '性别',
                `city` varchar(64) NOT NULL DEFAULT '' COMMENT '城市',
                `language` varchar(64) NOT NULL DEFAULT '' COMMENT '语言',
                `province` varchar(64) NOT NULL DEFAULT '' COMMENT '省份',
                `country` varchar(64) NOT NULL DEFAULT '' COMMENT '国家',
                `remark` varchar(256) NOT NULL DEFAULT '' COMMENT '备注',
                `groupid` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分组ID',
                `tagid_list` varchar(256) NOT NULL DEFAULT '' COMMENT '标签ID列表',
                `subscribe` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否订阅',
                `subscribe_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '关注时间',
                `add_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间',
                `second` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '二级推荐人',
                `user_type` varchar(32) NOT NULL DEFAULT 'wechat' COMMENT '用户类型',
                `is_complete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '信息是否完善',
                `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否注销',
                PRIMARY KEY (`id`),
                UNIQUE KEY `openid` (`openid`,`uid`) USING BTREE,
                KEY `groupid` (`groupid`) USING BTREE,
                KEY `subscribe_time` (`subscribe_time`) USING BTREE,
                KEY `add_time` (`add_time`) USING BTREE,
                KEY `subscribe` (`subscribe`) USING BTREE,
                KEY `unionid` (`unionid`) USING BTREE,
                KEY `uid` (`uid`) USING BTREE
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='微信渠道用户身份表';");
        }

        // 小程序外链表
        if (!$this->hasTable('routine_scheme')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('routine_scheme')}` (
                `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
                `title` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
                `path` varchar(255) NOT NULL DEFAULT '' COMMENT '小程序页面地址',
                `url` varchar(255) NOT NULL DEFAULT '' COMMENT '生成链接地址',
                `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
                `expire_type` int(11) NOT NULL DEFAULT '-1' COMMENT '到期类型',
                `expire_interval` int(11) NOT NULL DEFAULT '0' COMMENT '到期天数',
                `expire_time` int(11) NOT NULL DEFAULT '0' COMMENT '到期时间',
                `is_del` int(11) NOT NULL DEFAULT '0' COMMENT '是否删除',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='小程序外链';");
        }

        // 直播主播表
        if (!$this->hasTable('live_anchor')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('live_anchor')}` (
                `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
                `name` varchar(50) NOT NULL DEFAULT '' COMMENT '主播名称',
                `cover_img` varchar(255) NOT NULL DEFAULT '' COMMENT '主播图像',
                `wechat` varchar(50) NOT NULL DEFAULT '' COMMENT '主播微信号',
                `phone` varchar(32) NOT NULL DEFAULT '' COMMENT '手机号',
                `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
                `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
                `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='直播主播表';");
        }

        // 直播商品表
        if (!$this->hasTable('live_goods')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('live_goods')}` (
                `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
                `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '微信商品ID',
                `audit_id` int(10) NOT NULL DEFAULT '0' COMMENT '审核ID',
                `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品id',
                `name` varchar(30) NOT NULL DEFAULT '' COMMENT '商品名称',
                `cover_img` varchar(255) NOT NULL DEFAULT '' COMMENT '商品图片链接',
                `url` varchar(255) NOT NULL DEFAULT '' COMMENT '商品小程序链接',
                `price_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '价格类型（1:一口价; 2:价格区间; 3:折扣价）',
                `cost_price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '成本价',
                `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '一口价/最低价',
                `price2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '最高价',
                `audit_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态（0：未审核，1：审核中，2:审核通过，3审核失败）',
                `third_part_tag` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1、2：表示是为 API 添加商品，否则是直播控制台添加的商品',
                `sort` smallint(5) NOT NULL DEFAULT '0' COMMENT '排序',
                `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
                `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
                `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='直播商品表';");
        }

        // 直播间表
        if (!$this->hasTable('live_room')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('live_room')}` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
                `room_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '直播间 id',
                `name` varchar(32) NOT NULL DEFAULT '' COMMENT '直播间名字',
                `cover_img` varchar(255) NOT NULL DEFAULT '' COMMENT '背景图',
                `share_img` varchar(255) NOT NULL DEFAULT '' COMMENT '分享图',
                `start_time` int(10) NOT NULL DEFAULT '0' COMMENT '直播计划开始时间',
                `end_time` int(10) NOT NULL DEFAULT '0' COMMENT '直播计划结束时间',
                `anchor_name` varchar(50) NOT NULL DEFAULT '' COMMENT '主播昵称',
                `anchor_wechat` varchar(50) NOT NULL DEFAULT '' COMMENT '主播微信号',
                `phone` varchar(32) NOT NULL DEFAULT '' COMMENT '主播手机号',
                `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '直播间类型 【1: 推流，0：手机直播】',
                `screen_type` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '横屏、竖屏 【1：横屏，0：竖屏】',
                `close_like` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否关闭点赞',
                `close_goods` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否关闭货架',
                `close_comment` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否关闭评论',
                `error_msg` varchar(255) NOT NULL DEFAULT '' COMMENT '未通过原因',
                `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态0=未审核1=微信审核2=审核通过-1=审核未通过',
                `live_status` smallint(5) UNSIGNED NOT NULL DEFAULT '102' COMMENT '直播状态101：直播中，102：未开始，103已结束，104禁播，105：暂停，106：异常，107：已过期',
                `mark` varchar(512) NOT NULL DEFAULT '' COMMENT '备注',
                `replay_status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回放状态',
                `sort` smallint(5) NOT NULL DEFAULT '0' COMMENT '排序',
                `is_show` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否显示',
                `is_del` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否删除',
                `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
                PRIMARY KEY (`id`,`phone`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='直播间表' ROW_FORMAT=DYNAMIC;");
        }

        // 直播间导入商品表
        if (!$this->hasTable('live_room_goods')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('live_room_goods')}` (
                `live_room_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '直播间id',
                `live_goods_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品id',
                KEY `broadcast_room_id` (`live_room_id`,`live_goods_id`) USING BTREE
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='直播间导入商品表' ROW_FORMAT=DYNAMIC;");
        }
    }

    /**
     * 回滚建表
     */
    protected function rollbackMigrate(): void
    {
        $this->exec("DROP TABLE IF EXISTS `{$this->table('routine_scheme')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('live_room_goods')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('live_room')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('live_goods')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('live_anchor')}`;");
    }

    /**
     * 获取小程序插件的通知渠道定义。
     * @return array
     */
    protected function getNoticeChannelDefinitions(): array
    {
        return [
            [
                'channel_key' => 'is_routine',
                'channel_name' => '微信小程序',
                'adapter_class' => \modules\channel\wechat_mini\service\RoutineTemplateListService::class,
                'support_user' => 1,
                'support_platform' => 0,
                'sort' => 7
            ],
        ];
    }

    /**
     * 种子数据
     *
     * 写入小程序相关 system_config 配置项和配置分类。
     * 幂等逻辑：已存在的条目跳过不写入。
     *
     * 写入内容：
     * - 3 个配置分类（config_tab）：routine（小程序配置，父分类）、routine_config（小程序配置）、routine_encoding（消息推送配置）
     * - 12 条配置项（system_config）：routine_appId ~ routine_encodingaeskey + get_avatar/order_shipping_open/recharge_switch
     *
     * @return int 实际写入的条目数
     */
    protected function seed(): int
    {
        $count = 0;
        $count += app()->make(\app\services\message\pipeline\NoticeSeedServices::class)->seedChannels($this->getOwnerPluginId(), $this->getNoticeChannelDefinitions());

        // 0. 确保父配置分类「小程序配置」存在（核心 SQL 不再预置，由本安装器创建）
        // menus_id 动态绑定一级菜单「小程序」，避免硬编码菜单 id
        $menuId = (int) Db::table($this->table('system_menus'))
            ->where('menu_name', '小程序')
            ->where('menu_path', '/app/routine/download')
            ->where('is_del', 0)
            ->value('id');

        $tabRoutine = $this->ensureConfigTab('routine', [
            'pid'       => 78,
            'title'     => '小程序配置',
            'eng_title' => 'routine',
            'status'    => 1,
            'info'      => 0,
            'icon'      => 'logo-android',
            'type'      => 3,
            'sort'      => 0,
            'menus_id'  => $menuId ?: 0,
        ]);
        if ($tabRoutine['created']) {
            $count++;
        }

        // 回填「小程序配置」菜单路径：insertMenus 阶段 routine 分类尚未创建，
        // menu_path 为占位 /setting/system_config/0，此处更新为真实分类 id
        if ($tabRoutine['id'] && $menuId) {
            Db::table($this->table('system_menus'))
                ->where('menu_name', '小程序配置')
                ->where('pid', $menuId)
                ->update(['menu_path' => '/setting/system_config/' . $tabRoutine['id']]);
        }

        // 1. 确保配置分类 tab 存在（挂载在父分类「小程序配置」下）
        $tabRoutineConfig = $this->ensureConfigTab('routine_config', [
            'pid'       => $tabRoutine['id'],
            'title'     => '小程序配置',
            'eng_title' => 'routine_config',
            'status'    => 1,
            'info'      => 0,
            'icon'      => '',
            'type'      => 3,
            'sort'      => 0,
            'menus_id'  => $menuId ?: 0,
        ]);
        if ($tabRoutineConfig['created']) {
            $count++;
        }

        $tabRoutineEncoding = $this->ensureConfigTab('routine_encoding', [
            'pid'       => $tabRoutine['id'],
            'title'     => '消息推送配置',
            'eng_title' => 'routine_encoding',
            'status'    => 1,
            'info'      => 0,
            'icon'      => '',
            'type'      => 3,
            'sort'      => 0,
            'menus_id'  => $menuId ?: 0,
        ]);
        if ($tabRoutineEncoding['created']) {
            $count++;
        }

        // 2. 写入配置项
        $configs = [
            // --- 小程序配置分组（tab: routine_config） ---
            [
                'menu_name'     => 'routine_appId',
                'config_tab_id' => $tabRoutineConfig['id'],
                'type'          => 'text',
                'input_type'    => 'input',
                'parameter'     => '',
                'upload_type'   => 0,
                'required'      => '',
                'width'         => 100,
                'high'          => 0,
                'value'         => '""',
                'info'          => 'appId',
                'desc'          => '小程序appID',
                'sort'          => 100,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],
            [
                'menu_name'     => 'routine_appsecret',
                'config_tab_id' => $tabRoutineConfig['id'],
                'type'          => 'text',
                'input_type'    => 'input',
                'parameter'     => '',
                'upload_type'   => 0,
                'required'      => '',
                'width'         => 100,
                'high'          => 0,
                'value'         => '""',
                'info'          => 'AppSecret',
                'desc'          => '小程序AppSecret',
                'sort'          => 99,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],
            [
                'menu_name'     => 'routine_name',
                'config_tab_id' => $tabRoutineConfig['id'],
                'type'          => 'text',
                'input_type'    => 'input',
                'parameter'     => '',
                'upload_type'   => 0,
                'required'      => '',
                'width'         => 100,
                'high'          => 0,
                'value'         => '""',
                'info'          => '小程序名称',
                'desc'          => '小程序名称',
                'sort'          => 98,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],
            [
                'menu_name'     => 'routine_contact_type',
                'config_tab_id' => $tabRoutineConfig['id'],
                'type'          => 'radio',
                'input_type'    => 'input',
                'parameter'     => "0=>跟随系统\n1=>小程序客服",
                'upload_type'   => 1,
                'required'      => '',
                'width'         => 0,
                'high'          => 0,
                'value'         => '"0"',
                'info'          => '客服类型',
                'desc'          => '跟随系统：跟随系统使用默认客服、电话或者跳转链接；小程序客服：需要在小程序后台配置客服用户；',
                'sort'          => 97,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],
            [
                'menu_name'     => 'routine_auth_type',
                'config_tab_id' => $tabRoutineConfig['id'],
                'type'          => 'checkbox',
                'input_type'    => 'input',
                'parameter'     => "1=>微信授权\n2=>手动填写",
                'upload_type'   => 1,
                'required'      => '',
                'width'         => 0,
                'high'          => 0,
                'value'         => '["1","2"]',
                'info'          => '手机号获取方式',
                'desc'          => '小程序获取手机号的方式，微信授权和手机号验证码',
                'sort'          => 90,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],

            // --- 消息推送配置分组（tab: routine_encoding） ---
            [
                'menu_name'     => 'routine_api',
                'config_tab_id' => $tabRoutineEncoding['id'],
                'type'          => 'text',
                'input_type'    => 'input',
                'parameter'     => '',
                'upload_type'   => 1,
                'required'      => '',
                'width'         => 100,
                'high'          => 0,
                'value'         => '"/api/wechat/miniServe"',
                'info'          => '接口地址',
                'desc'          => '配置小程序消息推送使用的接口地址，直接复制输入框内容（此项系统生成，无法修改）',
                'sort'          => 0,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],
            [
                'menu_name'     => 'routine_token',
                'config_tab_id' => $tabRoutineEncoding['id'],
                'type'          => 'text',
                'input_type'    => 'input',
                'parameter'     => '',
                'upload_type'   => 1,
                'required'      => '',
                'width'         => 100,
                'high'          => 0,
                'value'         => '""',
                'info'          => '小程序验证TOKEN',
                'desc'          => '小程序验证TOKEN',
                'sort'          => 0,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],
            [
                'menu_name'     => 'routine_encode',
                'config_tab_id' => $tabRoutineEncoding['id'],
                'type'          => 'radio',
                'input_type'    => 'input',
                'parameter'     => "0=>明文模式\n1=>兼容模式\n2=>安全模式",
                'upload_type'   => 1,
                'required'      => '',
                'width'         => 0,
                'high'          => 0,
                'value'         => '"0"',
                'info'          => '消息加解密方式',
                'desc'          => '小程序消息推送中的消息加密方式，暂时仅支持明文模式',
                'sort'          => 0,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],
            [
                'menu_name'     => 'routine_encodingaeskey',
                'config_tab_id' => $tabRoutineEncoding['id'],
                'type'          => 'text',
                'input_type'    => 'input',
                'parameter'     => '',
                'upload_type'   => 1,
                'required'      => '',
                'width'         => 0,
                'high'          => 0,
                'value'         => '""',
                'info'          => 'EncodingAESKey',
                'desc'          => '消息加密密钥由43位字符组成，字符范围为A-Z,a-z,0-9',
                'sort'          => 0,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],

            // --- 小程序端功能配置（原核心 SQL 预置，随父分类一并迁移） ---
            [
                'menu_name'     => 'get_avatar',
                'config_tab_id' => $tabRoutineConfig['id'],
                'type'          => 'radio',
                'input_type'    => 'input',
                'parameter'     => "1=>开启\n0=>关闭",
                'upload_type'   => 1,
                'required'      => '',
                'width'         => 0,
                'high'          => 0,
                'value'         => '"1"',
                'info'          => '强制获取昵称头像',
                'desc'          => '是否在小程序用户授权之后，弹窗获取用户的昵称和头像',
                'sort'          => 89,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],
            [
                'menu_name'     => 'order_shipping_open',
                'config_tab_id' => $tabRoutineConfig['id'],
                'type'          => 'radio',
                'input_type'    => 'input',
                'parameter'     => "1=>开启\n0=>关闭",
                'upload_type'   => 1,
                'required'      => '',
                'width'         => 0,
                'high'          => 0,
                'value'         => '"0"',
                'info'          => '发货信息管理',
                'desc'          => '小程序有订单发货管理时，请打开此开关，否则会导致订单资金冻结',
                'sort'          => 0,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],
            [
                'menu_name'     => 'recharge_switch',
                'config_tab_id' => $tabRoutineConfig['id'],
                'type'          => 'radio',
                'input_type'    => 'input',
                'parameter'     => "1=>开启\n0=>关闭",
                'upload_type'   => 1,
                'required'      => '',
                'width'         => 0,
                'high'          => 0,
                'value'         => '"0"',
                'info'          => '小程序充值开关',
                'desc'          => '仅小程序端的充值开关，小程序提交审核前,需要关闭此功能',
                'sort'          => 88,
                'status'        => 1,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ],
        ];

        foreach ($configs as $config) {
            $exists = Db::table($this->table('system_config'))
                ->where('menu_name', $config['menu_name'])
                ->find();
            if ($exists) {
                continue;
            }

            // 合并默认值
            $defaults = [
                'parameter'   => '',
                'upload_type' => 1,
                'required'    => '',
                'width'       => 0,
                'high'        => 0,
                'status'      => 1,
                'level'       => 0,
                'link_id'     => 0,
                'link_value'  => 0,
            ];

            Db::table($this->table('system_config'))->insert(array_merge($defaults, $config));
            $count++;
        }

        // DIY 页面链接：确保"直播链接"分类与页面链接存在（幂等），供 DIY 链接选择器使用
        $liveCateId = $this->ensurePageLinkCategory(['商城页面', '营销链接', '直播链接']);
        $count += $this->seedPageLinks($liveCateId, [
            ['name' => '直播列表', 'url' => '/pages/columnGoods/live_list/index'],
        ]);

        return $count;
    }

    /**
     * 确保配置分类存在，不存在则创建
     * @param string $engTitle 英文标识
     * @param array $data 分类数据
     * @return array ['id' => int, 'created' => bool]
     */
    private function ensureConfigTab(string $engTitle, array $data): array
    {
        $existing = Db::table($this->table('system_config_tab'))
            ->where('eng_title', $engTitle)
            ->find();
        if ($existing) {
            return ['id' => (int)$existing['id'], 'created' => false];
        }
        $id = Db::table($this->table('system_config_tab'))->insertGetId($data);
        return ['id' => (int)$id, 'created' => true];
    }

    /**
     * 获取小程序配置父分类（eng_title=routine）的 id
     *
     * 用于拼接「小程序配置」菜单的 menu_path（/setting/system_config/{id}）。
     * 首次安装时 insertMenus 阶段 routine 分类尚未创建（核心 SQL 不再预置），
     * 返回 0 作为占位，由 seed() 创建分类后回填真实路径。
     * @return int
     */
    protected function getRoutineTabId(): int
    {
        $tab = Db::table($this->table('system_config_tab'))
            ->where('eng_title', 'routine')
            ->find();
        return $tab ? (int)$tab['id'] : 0;
    }

    /**
     * 后台菜单配置
     *
     * 完整菜单树：
     *
     * 应用 (pid=0, 系统已有)
     * └── 小程序 (pid=应用, menu_path=/app/routine/download)
     *     ├── 小程序配置 (auth=1, menu_path=/setting/system_config/{routine_tab_id}, 系统配置页)
     *     ├── 小程序下载 (auth=1, menu_path=/app/routine/download, 默认页)
     *     │   ├── 查看小程序信息 (auth=3, GET app/routine/info)
     *     │   ├── 下载小程序模版 (auth=3, POST app/routine/download)
     *     │   └── 一键同步订阅消息 (auth=3, GET app/routine/syncSubscribe)
     *     ├── 小程序上传 (auth=1, menu_path=/app/routine/ci_upload, CI自动化上传)
     *     │   ├── 获取运行环境 (auth=3, GET app/routine/ci/environment)
     *     │   ├── 获取安装指南 (auth=3, GET app/routine/ci/guide)
     *     │   ├── 获取上传配置 (auth=3, GET app/routine/ci/config)
     *     │   ├── 保存上传密钥 (auth=3, POST app/routine/ci/private_key)
     *     │   ├── 上传小程序代码 (auth=3, POST app/routine/ci/upload)
     *     │   └── 获取预览二维码 (auth=3, POST app/routine/ci/preview)
     *     └── 小程序链接 (auth=1, menu_path=/app/routine/link, 外链管理)
     *         ├── 外链列表 (auth=3, GET app/routine/scheme_list)
     *         ├── 外链添加表单 (auth=3, GET app/routine/scheme_form/<id>)
     *         ├── 外链保存 (auth=3, POST app/routine/scheme_save/<id>)
     *         └── 外链删除 (auth=3, DELETE app/routine/scheme_del/<id>)
     *
     * 注意：子菜单排序 sort=0 为同组优先，auth_type=3 接口权限条目统一 sort=0
     */
    protected function getMenuConfig(): array
    {
        return [
            // ========== 一级：小程序（挂载到"应用"下） ==========
            [
                'parent_key' => 'core:admin-app',
                'menu_name'  => '小程序',
                'controller' => 'routine',
                'action'     => 'index',
                'api_url'    => '/adminapi/app/routine/info',
                'menu_path'  => '/app/routine/download',
                'icon'       => 'CRMEB-202-xiaochengxu-xianxing',
                'sort'       => 50,
                'is_show'    => 1,
                'access'     => 1,
                'auth_type'  => 1,
                'header'     => 'app',
                'is_header'  => 1,
                'unique_auth' => 'channel-wechat-mini',
                'mark'       => '小程序',
            ],

            // ========== 二级：小程序下载（子菜单 - 默认页） ==========
            [
                'pid'         => '@小程序',
                'menu_name'   => '小程序下载',
                'icon'        => '',
                'controller'  => 'routine',
                'action'      => 'download',
                'api_url'     => '/adminapi/app/routine/info',
                'menu_path'   => '/app/routine/download',
                'sort'        => 1,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 1,
                'header'      => 'app',
                'unique_auth' => 'routine-download',
                'mark'        => '小程序下载',
            ],

            // ---------- 三级接口权限：小程序下载 ----------
            [
                'pid'         => '@小程序下载',
                'menu_name'   => '查看小程序信息',
                'api_url'     => 'app/routine/info',
                'methods'     => 'GET',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-info',
                'mark'        => '查看小程序信息',
            ],
            [
                'pid'         => '@小程序下载',
                'menu_name'   => '下载小程序模版',
                'api_url'     => 'app/routine/download',
                'methods'     => 'POST',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-download',
                'mark'        => '下载小程序模版',
            ],
            [
                'pid'         => '@小程序下载',
                'menu_name'   => '一键同步订阅消息',
                'api_url'     => 'app/routine/syncSubscribe',
                'methods'     => 'GET',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-sync-subscribe',
                'mark'        => '一键同步订阅消息',
            ],

            // ========== 二级：小程序上传（子菜单 - CI自动化上传） ==========
            [
                'pid'         => '@小程序',
                'menu_name'   => '小程序上传',
                'icon'        => '',
                'controller'  => 'routine.ci',
                'action'      => 'index',
                'api_url'     => '/adminapi/app/routine/ci/environment',
                'menu_path'   => '/app/routine/ci_upload',
                'sort'        => 2,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 1,
                'header'      => 'app',
                'unique_auth' => 'routine-ci-upload',
                'mark'        => '小程序上传',
            ],

            // ---------- 三级接口权限：小程序上传 ----------
            [
                'pid'         => '@小程序上传',
                'menu_name'   => '获取运行环境',
                'api_url'     => 'app/routine/ci/environment',
                'methods'     => 'GET',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-ci-environment',
                'mark'        => '获取运行环境',
            ],
            [
                'pid'         => '@小程序上传',
                'menu_name'   => '获取安装指南',
                'api_url'     => 'app/routine/ci/guide',
                'methods'     => 'GET',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-ci-guide',
                'mark'        => '获取安装指南',
            ],
            [
                'pid'         => '@小程序上传',
                'menu_name'   => '获取上传配置',
                'api_url'     => 'app/routine/ci/config',
                'methods'     => 'GET',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-ci-config',
                'mark'        => '获取上传配置',
            ],
            [
                'pid'         => '@小程序上传',
                'menu_name'   => '保存上传密钥',
                'api_url'     => 'app/routine/ci/private_key',
                'methods'     => 'POST',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-ci-private-key',
                'mark'        => '保存上传密钥',
            ],
            [
                'pid'         => '@小程序上传',
                'menu_name'   => '上传小程序代码',
                'api_url'     => 'app/routine/ci/upload',
                'methods'     => 'POST',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-ci-upload',
                'mark'        => '上传小程序代码',
            ],
            [
                'pid'         => '@小程序上传',
                'menu_name'   => '获取预览二维码',
                'api_url'     => 'app/routine/ci/preview',
                'methods'     => 'POST',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-ci-preview',
                'mark'        => '获取预览二维码',
            ],

            // ========== 二级：小程序链接（子菜单 - 外链管理） ==========
            [
                'pid'         => '@小程序',
                'menu_name'   => '小程序链接',
                'icon'        => '',
                'controller'  => 'routineScheme',
                'action'      => 'schemeList',
                'api_url'     => '/adminapi/app/routine/scheme_list',
                'menu_path'   => '/app/routine/link',
                'sort'        => 3,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 1,
                'header'      => 'app',
                'unique_auth' => 'routine-link',
                'mark'        => '小程序链接',
            ],

            // ---------- 三级接口权限：小程序链接 ----------
            [
                'pid'         => '@小程序链接',
                'menu_name'   => '外链列表',
                'api_url'     => 'app/routine/scheme_list',
                'methods'     => 'GET',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-scheme-list',
                'mark'        => '外链列表',
            ],
            [
                'pid'         => '@小程序链接',
                'menu_name'   => '外链添加表单',
                'api_url'     => 'app/routine/scheme_form/<id>',
                'methods'     => 'GET',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-scheme-form',
                'mark'        => '外链添加表单',
            ],
            [
                'pid'         => '@小程序链接',
                'menu_name'   => '外链保存',
                'api_url'     => 'app/routine/scheme_save/<id>',
                'methods'     => 'POST',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-scheme-save',
                'mark'        => '外链保存',
            ],
            [
                'pid'         => '@小程序链接',
                'menu_name'   => '外链删除',
                'api_url'     => 'app/routine/scheme_del/<id>',
                'methods'     => 'DELETE',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 3,
                'unique_auth' => 'app-routine-scheme-del',
                'mark'        => '外链删除',
            ],

            // ========== 二级：小程序配置（指向系统配置页 routine 分类） ==========
            [
                'pid'         => '@小程序',
                'menu_name'   => '小程序配置',
                'icon'        => '',
                'menu_path'   => '/setting/system_config/' . $this->getRoutineTabId(),
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 1,
                'unique_auth' => 'setting-system-config',
                'mark'        => '小程序配置',
            ],

            // ========== 营销 > 直播管理（小程序直播功能，非独立插件） ==========
            [
                'parent_key' => 'core:admin-marketing',
                'menu_name'  => '直播管理',
                'controller' => 'live',
                'action'     => 'index',
                'api_url'    => 'live/room/list',
                'menu_path'  => '/marketing/live/room',
                'icon'       => 'CRMEB-226-zhibo-xianxing',
                'sort'       => 70,
                'is_show'    => 1,
                'access'     => 1,
                'auth_type'  => 1,
            ],
            // 营销 > 直播管理 > 主播管理
            [
                'pid'        => '@直播管理',
                'menu_name'  => '主播管理',
                'icon'       => '',
                'controller' => 'live',
                'action'     => 'anchor',
                'api_url'    => 'live/anchor/list',
                'menu_path'  => '/marketing/live/anchor',
                'is_show'    => 1,
                'sort'       => 1,
                'access'     => 1,
                'auth_type'  => 1,
            ],
            // 营销 > 直播管理 > 直播商品
            [
                'pid'        => '@直播管理',
                'menu_name'  => '直播商品',
                'icon'       => '',
                'controller' => 'live',
                'action'     => 'goods',
                'api_url'    => 'live/goods/list',
                'menu_path'  => '/marketing/live/goods',
                'is_show'    => 1,
                'sort'       => 2,
                'access'     => 1,
                'auth_type'  => 1,
            ],
            // 营销 > 直播管理 > 直播间管理
            [
                'pid'        => '@直播管理',
                'menu_name'  => '直播间管理',
                'icon'       => '',
                'controller' => 'live',
                'action'     => 'room',
                'api_url'    => 'live/room/list',
                'menu_path'  => '/marketing/live/room',
                'is_show'    => 1,
                'sort'       => 3,
                'access'     => 1,
                'auth_type'  => 1,
            ],
            // ============== 直播隐藏权限菜单 ==============
            [
                'pid'        => '@直播管理',
                'menu_name'  => '添加修改主播表单',
                'controller' => 'live',
                'action'     => 'anchorAdd',
                'api_url'    => 'live/anchor/add/<id>',
                'methods'    => 'GET',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '保存主播数据',
                'controller' => 'live',
                'action'     => 'anchorSave',
                'api_url'    => 'live/anchor/save',
                'methods'    => 'POST',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '删除主播',
                'controller' => 'live',
                'action'     => 'anchorDel',
                'api_url'    => 'live/anchor/del/<id>',
                'methods'    => 'DELETE',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '设置主播是否显示',
                'controller' => 'live',
                'action'     => 'anchorShow',
                'api_url'    => 'live/anchor/set_show/<id>/<is_show>',
                'methods'    => 'GET',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '生成直播商品',
                'controller' => 'live',
                'action'     => 'goodsCreate',
                'api_url'    => 'live/goods/create',
                'methods'    => 'POST',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '添加修改直播商品',
                'controller' => 'live',
                'action'     => 'goodsAdd',
                'api_url'    => 'live/goods/add',
                'methods'    => 'POST',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '直播商品详情',
                'controller' => 'live',
                'action'     => 'goodsDetail',
                'api_url'    => 'live/goods/detail/<id>',
                'methods'    => 'GET',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '直播商品重新审核',
                'controller' => 'live',
                'action'     => 'goodsAudit',
                'api_url'    => 'live/goods/audit/<id>',
                'methods'    => 'GET',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '直播商品撤回审核',
                'controller' => 'live',
                'action'     => 'goodsResestAudit',
                'api_url'    => 'live/goods/resestAudit/<id>',
                'methods'    => 'GET',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '删除直播商品',
                'controller' => 'live',
                'action'     => 'goodsDel',
                'api_url'    => 'live/goods/del/<id>',
                'methods'    => 'DELETE',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '设置直播商品是否显示',
                'controller' => 'live',
                'action'     => 'goodsShow',
                'api_url'    => 'live/goods/set_show/<id>/<is_show>',
                'methods'    => 'GET',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '同步直播商品状态',
                'controller' => 'live',
                'action'     => 'goodsSync',
                'api_url'    => 'live/goods/syncGoods',
                'methods'    => 'GET',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '添加直播间',
                'controller' => 'live',
                'action'     => 'roomAdd',
                'api_url'    => 'live/room/add',
                'methods'    => 'POST',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '直播间详情',
                'controller' => 'live',
                'action'     => 'roomDetail',
                'api_url'    => 'live/room/detail/<id>',
                'methods'    => 'GET',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '直播间添加商品',
                'controller' => 'live',
                'action'     => 'roomAddGoods',
                'api_url'    => 'live/room/add_goods',
                'methods'    => 'POST',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '删除直播间',
                'controller' => 'live',
                'action'     => 'roomDel',
                'api_url'    => 'live/room/del/<id>',
                'methods'    => 'DELETE',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '设置直播间是否显示',
                'controller' => 'live',
                'action'     => 'roomShow',
                'api_url'    => 'live/room/set_show/<id>/<is_show>',
                'methods'    => 'GET',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@直播管理',
                'menu_name'  => '同步直播间状态',
                'controller' => 'live',
                'action'     => 'roomSync',
                'api_url'    => 'live/room/syncRoom',
                'methods'    => 'GET',
                'auth_type'  => 3,
                'is_show'    => 1,
                'access'     => 1,
            ],
        ];
    }

    /**
     * 模块自有表：小程序外链与直播相关表
     * @return array 表名列表（不含前缀）
     */
    protected function getOwnedTables(): array
    {
        return ['routine_scheme', 'live_room_goods', 'live_room', 'live_goods', 'live_anchor'];
    }

    /**
     * 清理业务数据（卸载时调用）
     *
     * 清理：
     * - 小程序外链记录（routine_scheme 表数据）
     * - 小程序相关 system_config 配置项（11 条）
     * - 本安装器创建的 3 个配置分类（routine_encoding / routine_config / routine，先删子分类再删父分类）
     *
     * @return int
     */
    protected function cleanData(): int
    {
        $count = app()->make(\app\services\message\pipeline\NoticeSeedServices::class)->clean($this->getOwnerPluginId());

        // 清理自有表数据（表不存在时 cleanTable 自动跳过）
        $count += $this->cleanTable('routine_scheme');
        $count += $this->cleanTable('live_room_goods');
        $count += $this->cleanTable('live_room');
        $count += $this->cleanTable('live_goods');
        $count += $this->cleanTable('live_anchor');

        // 清理小程序 system_config 配置项
        $count += Db::table($this->table('system_config'))
            ->whereIn('menu_name', self::ROUTINE_CONFIG_KEYS)
            ->delete();

        // 删除本安装器创建的配置分类（先子后父，父分类 routine 由本安装器创建，随模块卸载一并清理）
        $count += Db::table($this->table('system_config_tab'))
            ->whereIn('eng_title', ['routine_encoding', 'routine_config', 'routine'])
            ->delete();

        // 清理 DIY 页面链接与独有分类（"直播链接"）
        $count += $this->cleanPageLinks(['直播链接'], [
            '/pages/columnGoods/live_list/index',
        ]);

        return $count;
    }
}
