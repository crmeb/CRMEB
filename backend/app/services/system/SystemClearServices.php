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

namespace app\services\system;


use app\services\product\CenterProductServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\module\ModuleInstaller;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\trade\product\ProductTypeRegistry;
use think\facade\Config;
use think\facade\Db;

/**
 * 清除数据
 * Class SystemClearServices
 * 本类为系统数据清理工具，Db::execute 用于执行 TRUNCATE/DELETE 等清理 SQL，
 * 不属于业务数据操作，保留直接调用。
 * @package app\services\system
 */
class SystemClearServices extends BaseService
{
    /**
     * 清除表数据（跳过当前库不存在的表，兼容插件未安装/表已迁移场景）
     * @param string|array $table_name
     * @param bool $status true=TRUNCATE，false=DELETE
     */
    public function clearData($table_name, bool $status)
    {
        $prefix = config('database.connections.' . config('database.default'))['prefix'];
        $clearData = is_string($table_name) ? [$table_name] : $table_name;
        $existing = $this->listExistingTables($prefix);
        foreach ($clearData as $name) {
            if (!isset($existing[$name])) {
                continue;
            }
            if ($status) {
                Db::execute('TRUNCATE TABLE `' . $prefix . $name . '`');
            } else {
                Db::execute('DELETE FROM `' . $prefix . $name . '`');
            }
        }
    }

    /**
     * 仅允许被 SystemClearDataController 指定方法调用
     */
    protected function assertCalledFromClearDataController(string $function): void
    {
        $expectedClass = \app\adminapi\controller\system\SystemClearDataController::class;
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 16) as $frame) {
            if (($frame['class'] ?? '') === $expectedClass && ($frame['function'] ?? '') === $function) {
                return;
            }
        }
        throw new AdminException('禁止绕过入口直接调用清除数据能力');
    }

    /**
     * 清除临时附件与二维码表
     */
    public function clearTempData(): void
    {
        $this->assertCalledFromClearDataController('userTemp');
        /** @var SystemAttachmentServices $attachmentServices */
        $attachmentServices = app()->make(SystemAttachmentServices::class);
        $attachmentServices->clearTemporaryAttachments();
        $this->clearData(['qrcode'], true);
    }

    /**
     * 清除回收站商品（含各产品类型扩展表广播）
     */
    public function clearRecycleProduct(): void
    {
        $this->assertCalledFromClearDataController('recycleProduct');
        /** @var CenterProductServices $product */
        $product = app()->make(CenterProductServices::class);
        $product->recycleProduct(0);
    }

    /**
     * 清除商品分类
     */
    public function clearCategoryData(): void
    {
        $this->assertCalledFromClearDataController('categoryData');
        $this->clearData(['store_category'], true);
    }

    /**
     * 清除订单及相关履约/售后/支付流水（不含用户主档与商品主数据）
     */
    public function clearOrderData(): void
    {
        $this->assertCalledFromClearDataController('orderData');
        $prefix = (string)config('database.connections.' . config('database.default') . '.prefix');
        $existing = $this->listExistingTables($prefix);
        $tables = array_merge(
            $this->discoverIndirectUserTables($prefix, $existing),
            [
                'cart',
                'store_cart',
                'store_pink',
                'store_visit',
                'member_order',
                'member_order_status',
                'store_integral_order',
                'store_integral_order_status',
                'order_center',
                'order_economize',
                'order_invoice',
                'order_item',
                'order_log',
                'store_order_invoice',
                'after_sale',
                'after_sale_item',
                'pay_record',
                'refund_effect',
                'refund_order',
                'offline_receipt',
                'offline_receipt_status',
                'capital_flow',
                'printer_task',
                'express_dump_log',
                'sms_record',
            ]
        );
        /** @var ProductTypeRegistry $registry */
        $registry = app()->make(ProductTypeRegistry::class);
        foreach ($registry->all() as $typeKey) {
            $tables[] = 'pt_' . $typeKey . '_order';
        }
        foreach ($existing as $table => $_) {
            if (preg_match('/^pt_.+_order$/', $table)) {
                $tables[] = $table;
            }
        }
        $tables = array_values(array_unique(array_filter(
            $tables,
            fn(string $table): bool => isset($existing[$table]) && !$this->isUserClearProtectedTable($table)
        )));
        $this->clearData($tables, true);
        $this->releaseProductTypeOrderBindings($prefix, $existing);
    }

    /**
     * 清除客服数据
     */
    public function clearKefuData(): void
    {
        $this->assertCalledFromClearDataController('kefuData');
        $this->clearData([
            'store_service',
            'store_service_log',
            'store_service_record',
            'store_service_feedback',
            'store_service_speechcraft',
            'kefu_ai_session',
            'kefu_ai_reply_log',
        ], true);
        $this->delDirAndFile('./public/uploads/store/service');
    }

    /**
     * 清除微信相关运营数据
     */
    public function clearWechatData(): void
    {
        $this->assertCalledFromClearDataController('wechatData');
        $this->clearData([
            'cache',
            'wechat_key',
            'wechat_media',
            'wechat_message',
            'wechat_news_category',
            'wechat_qrcode',
            'wechat_qrcode_cate',
            'wechat_qrcode_record',
            'wechat_reply',
            'wechat_user',
        ], true);
        $this->delDirAndFile('./public/uploads/wechat');
    }

    /**
     * 清除全部附件记录与本地 uploads
     */
    public function clearAttachmentData(): void
    {
        $this->assertCalledFromClearDataController('attachmentData');
        $this->clearData([
            'system_attachment',
            'system_attachment_category',
        ], true);
        $this->delDirAndFile('./public/uploads/');
    }

    /**
     * 清除文章与分类
     */
    public function clearArticleData(): void
    {
        $this->assertCalledFromClearDataController('articleData');
        $this->clearData([
            'article_category',
            'article',
            'article_content',
        ], true);
    }

    /**
     * 清除系统运行记录
     */
    public function clearSystemLogData(): void
    {
        $this->assertCalledFromClearDataController('systemData');
        $this->clearData([
            'system_log',
            'notice_log',
            'notice_read',
            'upgrade_log',
            'module_install_log',
            'express_dump_log',
            'sms_record',
            'cache',
        ], true);
    }

    /**
     * 清除商城业务数据（用户/订单/商品/内容/客服等，保留系统配置与后台账号体系）
     */
    public function clearStoreData(): void
    {
        $this->assertCalledFromClearDataController('storeData');
        $prefix = (string)config('database.connections.' . config('database.default') . '.prefix');
        $existing = $this->listExistingTables($prefix);
        $tables = [
            // 用户
            'user', 'user_address', 'user_bill', 'user_brokerage_frozen', 'user_cancel', 'user_enter',
            'user_friends', 'user_group', 'user_invoice', 'user_label', 'user_label_relation', 'user_level',
            'user_member', 'user_money', 'user_recharge', 'user_search', 'user_sign', 'user_spread', 'user_visit',
            // 订单/售后/支付流水
            'cart', 'store_cart', 'order_center', 'order_economize', 'order_invoice', 'order_item', 'order_log',
            'store_order_invoice', 'store_pink', 'store_visit', 'after_sale', 'after_sale_item', 'pay_record',
            'refund_effect', 'refund_order', 'offline_receipt', 'offline_receipt_status', 'member_order',
            'member_order_status', 'store_integral_order', 'store_integral_order_status', 'capital_flow',
            'printer_task', 'express_dump_log', 'sms_record', 'qrcode',
            // 商品
            'product_center', 'store_category', 'store_product_attr_value', 'store_product_cate',
            'store_product_coupon', 'store_product_description', 'store_product_label', 'store_product_label_cate',
            'store_product_log', 'store_product_param', 'store_product_protection', 'store_product_relation',
            'store_product_reply', 'store_product_rule', 'store_product_virtual', 'store_coupon_issue',
            'store_coupon_issue_user', 'store_coupon_product', 'store_coupon_user',
            // 活动/会员卡
            'store_advance', 'store_bargain', 'store_bargain_user', 'store_bargain_user_help',
            'store_combination', 'store_seckill', 'store_integral', 'luck_lottery', 'luck_lottery_record',
            'luck_prize', 'member_card', 'member_card_batch', 'distribution_level_task',
            'distribution_level_task_record', 'division_agent_apply',
            // 内容/客服/门店/微信用户侧
            'article', 'article_category', 'article_content', 'auxiliary', 'category', 'live_anchor',
            'live_goods', 'live_room', 'live_room_goods', 'store_service', 'store_service_feedback',
            'store_service_log', 'store_service_record', 'store_service_speechcraft', 'kefu_ai_session',
            'kefu_ai_reply_log', 'system_store', 'system_store_staff', 'delivery_service', 'wechat_user',
            'wechat_message', 'wechat_qrcode_record', 'system_log', 'cache',
        ];
        /** @var ProductTypeRegistry $registry */
        $registry = app()->make(ProductTypeRegistry::class);
        foreach ($registry->all() as $typeKey) {
            $tables[] = 'pt_' . $typeKey;
            $tables[] = 'pt_' . $typeKey . '_sku';
            $tables[] = 'pt_' . $typeKey . '_stock';
            $tables[] = 'pt_' . $typeKey . '_order';
        }
        foreach ($existing as $table => $_) {
            if (preg_match('/^pt_/', $table)) {
                $tables[] = $table;
            }
        }
        $this->clearData(array_values(array_unique($tables)), true);
        $this->delDirAndFile('./public/uploads/store/comment');
        $this->delDirAndFile('./public/uploads/store/service');
    }

    /**
     * 清除用户相关业务数据
     * - 直接关联：含 uid/user_id/openid/unionid/spread_uid/to_uid 的表
     * - 间接关联：订单/售后/支付等附属表、pt_*_order、客服会话等
     * - 保护名单：商品/活动主数据、系统配置、运营配置等不会被清
     */
    public function clearUserRelevantData(): void
    {
        $this->assertCalledFromClearDataController('userRelevantData');
        $prefix = (string)config('database.connections.' . config('database.default') . '.prefix');
        $existing = $this->listExistingTables($prefix);
        $tables = array_merge(
            $this->discoverDirectUserTables($prefix, $existing),
            $this->discoverIndirectUserTables($prefix, $existing),
            $this->userClearExtraTables()
        );

        // 已注册 + 库内残留的产品类型扩展订单表
        /** @var ProductTypeRegistry $registry */
        $registry = app()->make(ProductTypeRegistry::class);
        foreach ($registry->all() as $typeKey) {
            $tables[] = 'pt_' . $typeKey . '_order';
        }
        foreach ($existing as $table => $_) {
            if (preg_match('/^pt_.+_order$/', $table)) {
                $tables[] = $table;
            }
        }

        $tables = array_values(array_unique(array_filter(
            $tables,
            fn(string $table): bool => isset($existing[$table]) && !$this->isUserClearProtectedTable($table)
        )));

        $this->clearData($tables, true);
        $this->releaseProductTypeOrderBindings($prefix, $existing);
        $this->delDirAndFile('./public/uploads/store/comment');
        $this->delDirAndFile('./public/uploads/store/service');
    }

    /**
     * 直接关联用户的表（按身份字段发现）
     * @param array<string, string> $existing
     * @return list<string>
     */
    protected function discoverDirectUserTables(string $prefix, array $existing): array
    {
        $database = (string)config('database.connections.' . config('database.default') . '.database');
        $rows = Db::query(
            'SELECT DISTINCT table_name FROM information_schema.columns
             WHERE table_schema = ? AND table_name LIKE ? AND column_name IN (?,?,?,?,?,?)',
            [$database, $prefix . '%', 'uid', 'user_id', 'openid', 'unionid', 'spread_uid', 'to_uid']
        );
        $tables = [];
        $prefixLen = strlen($prefix);
        foreach ($rows as $row) {
            $full = (string)($row['table_name'] ?? $row['TABLE_NAME'] ?? '');
            if ($full === '' || !str_starts_with($full, $prefix)) {
                continue;
            }
            $name = substr($full, $prefixLen);
            if (isset($existing[$name])) {
                $tables[] = $name;
            }
        }
        return $tables;
    }

    /**
     * 间接关联用户业务的表（订单/售后/支付等附属，不含商品主数据）
     * @param array<string, string> $existing
     * @return list<string>
     */
    protected function discoverIndirectUserTables(string $prefix, array $existing): array
    {
        $database = (string)config('database.connections.' . config('database.default') . '.database');
        $rows = Db::query(
            'SELECT DISTINCT table_name FROM information_schema.columns
             WHERE table_schema = ? AND table_name LIKE ? AND column_name IN (?,?,?,?,?)',
            [$database, $prefix . '%', 'order_no', 'order_id', 'after_sale_no', 'refund_order_no', 'oid']
        );
        $tables = [];
        $prefixLen = strlen($prefix);
        foreach ($rows as $row) {
            $full = (string)($row['table_name'] ?? $row['TABLE_NAME'] ?? '');
            if ($full === '' || !str_starts_with($full, $prefix)) {
                continue;
            }
            $name = substr($full, $prefixLen);
            if (!isset($existing[$name]) || $this->isUserClearProtectedTable($name)) {
                continue;
            }
            // 仅 product_id 关联的商品/活动主数据已在保护名单；这里再排除纯库存扩展表
            if (preg_match('/^pt_.+_(sku|stock)$/', $name) || preg_match('/^pt_[a-z0-9]+$/', $name)) {
                continue;
            }
            $tables[] = $name;
        }

        // 售后明细等无 order_no/uid、但必然挂在用户售后下的表
        foreach (['after_sale_item', 'refund_effect', 'pay_record', 'printer_task', 'express_dump_log'] as $name) {
            if (isset($existing[$name])) {
                $tables[] = $name;
            }
        }
        return $tables;
    }

    /**
     * 无通用身份/订单字段、但仍属用户侧业务的补充表
     * @return list<string>
     */
    protected function userClearExtraTables(): array
    {
        return [
            'user_group',
            'user_label',
            'user_brokerage_frozen',
            'member_card',
            'member_card_batch',
            'qrcode',
            'store_pink',
            'store_integral_order',
            'store_integral_order_status',
            'store_order_invoice',
            'luck_lottery_record',
            'distribution_level_task_record',
            'division_agent_apply',
            'store_service',
            'store_service_feedback',
            'store_service_log',
            'store_service_record',
            'kefu_ai_session',
            'kefu_ai_reply_log',
        ];
    }

    /**
     * 清除用户数据时不可动的表（商品/活动主数据、系统与运营配置）
     */
    protected function isUserClearProtectedTable(string $table): bool
    {
        static $exact = null;
        if ($exact === null) {
            $exact = array_flip([
                // 商品与规格主数据
                'product_center',
                'store_category',
                'store_product_attr_value',
                'store_product_cate',
                'store_product_coupon',
                'store_product_description',
                'store_product_label',
                'store_product_label_cate',
                'store_product_param',
                'store_product_protection',
                'store_product_rule',
                'store_coupon_issue',
                'store_coupon_product',
                // 活动主数据（参与记录另清）
                'store_activity',
                'store_bargain',
                'store_combination',
                'store_seckill',
                'store_seckill_time',
                'activity_seckill_sku',
                'store_integral',
                'luck_lottery',
                'luck_prize',
                // 会员档位/等级配置
                'member_ship',
                'member_right',
                'system_user_level',
                // 渠道码配置（扫码记录会清）
                'wechat_qrcode',
                'wechat_qrcode_cate',
                'wechat_key',
                'wechat_reply',
                'wechat_media',
                'wechat_news_category',
                // 支付/通知/驱动配置
                'pay_channel',
                'pay_channel_platform',
                'notice',
                'notice_channel',
                'notice_config',
                'notice_template',
                'driver',
                'printer_channel',
                // 内容与装修
                'article',
                'article_category',
                'article_content',
                'diy',
                'theme',
                'theme_download',
                'theme_module',
                'page_categroy',
                'page_link',
                'agreement',
                'cache',
                'category',
                'auxiliary',
                // 直播/小程序运营配置
                'live_anchor',
                'live_goods',
                'live_room',
                'live_room_goods',
                'routine_scheme',
            ]);
        }
        if (isset($exact[$table])) {
            return true;
        }
        // 系统表（店员账号例外，随用户侧清理）
        if (str_starts_with($table, 'system_') && $table !== 'system_store_staff') {
            return true;
        }
        // 产品类型主表/SKU/库存表：pt_normal、pt_virtual_sku、pt_cardkey_stock 等
        if (preg_match('/^pt_[a-z0-9]+$/', $table) || preg_match('/^pt_.+_(sku|stock)$/', $table)) {
            return true;
        }
        return false;
    }

    /**
     * 释放产品类型库存上挂着的用户订单占用（不删库存本体）
     * @param array<string, string> $existing
     */
    protected function releaseProductTypeOrderBindings(string $prefix, array $existing): void
    {
        foreach ($existing as $table => $full) {
            if (!preg_match('/^pt_.+_stock$/', $table)) {
                continue;
            }
            $cols = array_column(Db::query('SHOW COLUMNS FROM `' . $full . '`'), 'Field');
            if (!in_array('order_no', $cols, true)) {
                continue;
            }
            Db::execute('UPDATE `' . $full . '` SET `order_no` = \'\' WHERE `order_no` <> \'\'');
        }
    }

    /**
     * 当前库已存在的业务表名（不含前缀）=> 全名
     * @return array<string, string>
     */
    protected function listExistingTables(string $prefix): array
    {
        $database = (string)config('database.connections.' . config('database.default') . '.database');
        $rows = Db::query(
            'SELECT table_name FROM information_schema.tables WHERE table_schema = ? AND table_name LIKE ?',
            [$database, $prefix . '%']
        );
        $map = [];
        $prefixLen = strlen($prefix);
        foreach ($rows as $row) {
            $full = (string)($row['table_name'] ?? $row['TABLE_NAME'] ?? '');
            if ($full === '' || !str_starts_with($full, $prefix)) {
                continue;
            }
            $map[substr($full, $prefixLen)] = $full;
        }
        return $map;
    }

    /**
     * 递归删除文件,只能删除 public/uploads下的文件
     * @param $dirName
     * @param bool $subdir
     */
    public function delDirAndFile(string $dirName, $subdir = true)
    {
        if (strstr($dirName, 'public/uploads') === false) {
            return true;
        }
        if ($handle = @opendir("$dirName")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item"))
                        $this->delDirAndFile("$dirName/$item", false);
                    else
                        @unlink("$dirName/$item");
                }
            }
            closedir($handle);
            if (!$subdir) @rmdir($dirName);
        }
    }

    /**
     * 替换域名
     * @param string $url 新域名
     * @param string $siteUrl 要被替换的原域名；为空时取系统 site_url
     * @return mixed
     */
    public function replaceSiteUrl(string $url, string $siteUrl = '')
    {
        $this->assertCalledFromClearDataController('replaceSiteUrl');
        $siteUrl = $siteUrl !== '' ? $siteUrl : (string)sys_config('site_url');
        if ($siteUrl === '' || $url === '') {
            throw new AdminException('原域名和新域名不能为空');
        }
        if ($siteUrl === $url) {
            throw new AdminException('原域名与新域名不能相同');
        }
        // 解析站点 URL 的协议
        $siteUrlScheme = parse_url($siteUrl)['scheme'] ?? '';
        if ($siteUrlScheme === '') {
            throw new AdminException('原域名不合法');
        }
        // 将站点 URL 中的协议替换为 JSON 格式
        $siteUrlJson = str_replace($siteUrlScheme . '://', $siteUrlScheme . ':\\\/\\\/', $siteUrl);

        // 获取当前 URL 的协议
        $urlScheme = parse_url($url)['scheme'] ?? '';
        if ($urlScheme === '') {
            throw new AdminException('新域名不合法');
        }
        // 将当前 URL 中的协议替换为 JSON 格式
        $urlJson = str_replace($urlScheme . '://', $urlScheme . ':\\\/\\\/', $url);
        // 获取数据库表前缀
        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');

        // 构建 SQL 语句数组
        $sql = [
            "UPDATE `{$prefix}distribution_level` SET `image` = replace(`image` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}agreement` SET `content` = replace(content ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article` SET `image_input` = replace(`image_input` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article_category` SET `image` = replace(`image` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article_content` SET `content` = replace(`content` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}cache` SET `result` = replace(result ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}delivery_service` SET `avatar` = replace(`avatar` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}division_agent_apply` SET `images` = replace(images ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}diy` SET `value` = replace(value ,'{$siteUrlJson}','{$urlJson}'),`default_value` = replace(default_value ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}diy` SET `value` = replace(value ,'{$siteUrl}','{$url}'),`default_value` = replace(default_value ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}live_anchor` SET `cover_img` = replace(`cover_img` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}live_goods` SET `cover_img` = replace(`cover_img` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}live_room` SET `cover_img` = replace(`cover_img` ,'{$siteUrl}','{$url}'),`share_img` = replace(`share_img` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}luck_lottery` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`content` = replace(content ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}luck_prize` SET `image` = replace(image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}member_card_batch` SET `qrcode` = replace(qrcode ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}member_right` SET `image` = replace(image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}out_interface` SET `return_params` = replace(return_params ,'{$siteUrl}','{$url}'),`request_example` = replace(request_example ,'{$siteUrl}','{$url}'),`return_example` = replace(return_example ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}qrcode` SET `url` = replace(url ,'{$siteUrl}','{$url}'),`qrcode_url` = replace(qrcode_url ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_bargain` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_category` SET `pic` = replace(`pic` ,'{$siteUrl}','{$url}'),`big_pic` = replace(`big_pic` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_combination` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_integral` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_integral_order` SET `image` = replace(`image` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_pink` SET `avatar` = replace(`avatar` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}product_center` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`slider_image` = replace(slider_image ,'{$siteUrlJson}','{$urlJson}'),`recommend_image` = replace(recommend_image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_product_description` SET `description`= replace(description,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_product_reply` SET `avatar` = replace(avatar ,'{$siteUrl}','{$url}'),`pics` = replace(pics,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_seckill` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_service` SET `avatar` = replace(avatar ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_service_log` SET `msn` = replace(msn ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_service_record` SET `avatar` = replace(avatar ,'{$siteUrl}','{$url}'),`message` = replace(message,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}system_admin` SET `head_pic` = replace(head_pic ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_attachment` SET `att_dir` = replace(att_dir ,'{$siteUrl}','{$url}'),`satt_dir` = replace(satt_dir ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_config` SET `value` = replace(value ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_config` SET `value` = replace(value ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}system_group_data` SET `value` = replace(value ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}system_store` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`oblong_image` = replace(oblong_image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_store_staff` SET `avatar` = replace(avatar ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_user_level` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`icon` = replace(icon ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}user` SET `avatar` = replace(avatar ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}wechat_qrcode` SET `image` = replace(image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}wechat_user` SET `headimgurl` = replace(headimgurl ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}theme` SET `home_data` = replace(home_data ,'{$siteUrlJson}','{$urlJson}'),`detail_data` = replace(detail_data ,'{$siteUrlJson}','{$urlJson}'),`user_data` = replace(user_data ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}theme` SET `home_default_data` = replace(home_default_data ,'{$siteUrlJson}','{$urlJson}'),`detail_default_data` = replace(detail_default_data ,'{$siteUrlJson}','{$urlJson}'),`user_default_data` = replace(user_default_data ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}theme` SET `home_data` = replace(home_data ,'{$siteUrl}','{$url}'),`detail_data` = replace(detail_data ,'{$siteUrl}','{$url}'),`user_data` = replace(user_data ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}theme` SET `home_default_data` = replace(home_default_data ,'{$siteUrl}','{$url}'),`detail_default_data` = replace(detail_default_data ,'{$siteUrl}','{$url}'),`user_default_data` = replace(user_default_data ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}theme` SET `home_image` = replace(home_image ,'{$siteUrl}','{$url}'),`category_image` = replace(category_image ,'{$siteUrl}','{$url}'),`detail_image` = replace(detail_image ,'{$siteUrl}','{$url}'),`user_image` = replace(user_image ,'{$siteUrl}','{$url}'),`home_default_image` = replace(home_default_image ,'{$siteUrl}','{$url}'),`category_default_image` = replace(category_default_image ,'{$siteUrl}','{$url}'),`detail_default_image` = replace(detail_default_image ,'{$siteUrl}','{$url}'),`user_default_image` = replace(user_default_image ,'{$siteUrl}','{$url}')",
        ];

        // 执行 SQL 语句
        return $this->transaction(function () use ($sql) {
            try {
                foreach ($sql as $item) {
                    Db::execute($item);
                }
            } catch (\Throwable $e) {
                throw new AdminException('替换失败,失败原因:{:msg}', ['msg' => $e->getMessage()]);
            }
        });
    }

    /**
     * 恢复出厂：按当前安装包 SQL 重建核心表并回灌种子，再安装默认插件；
     * 仅保留传入的超级管理员，并把原 system_config 覆盖回去。
     *
     * 业务门禁（登录密码 / app_debug / 超管）在 Controller；此处只允许被
     * SystemClearDataController::factoryReset 调用。
     *
     * @return list<string>
     */
    public function factoryReset(int $adminId): array
    {
        $this->assertCalledFromClearDataController('factoryReset');

        @set_time_limit(0);
        $steps = [];
        $prefix = (string)config('database.connections.' . config('database.default') . '.prefix');
        $helper = app()->make(InstallSqlHelper::class);

        $admin = Db::name('system_admin')->where('id', $adminId)->where('is_del', 0)->find();
        if (!$admin) {
            throw new AdminException('管理员不存在');
        }
        $admin = is_array($admin) ? $admin : $admin->toArray();
        $configSnapshot = Db::name('system_config')->column('value', 'menu_name');
        $steps[] = '已快照超级管理员与系统配置';

        $siteUrl = (string)sys_config('site_url');
        $parts = parse_url($siteUrl) ?: [];
        $scheme = (string)($parts['scheme'] ?? 'http');
        $host = (string)($parts['host'] ?? '');
        if ($host !== '' && !empty($parts['port'])) {
            $host .= ':' . $parts['port'];
        }
        if ($host === '') {
            $host = (string)request()->host();
            $scheme = request()->isSsl() ? 'https' : 'http';
        }

        $tableStatements = $helper->getTableStatements();
        $dataGroups = $helper->getDataGroups();
        if ($tableStatements === [] || $dataGroups === []) {
            throw new AdminException('安装 SQL 不完整，请检查 public/install/crmeb_table.sql 与 install/data');
        }

        // 先卸掉当前前缀下全部表，避免插件扩展表残留旧数据
        $existing = $this->listExistingTables($prefix);
        Db::execute('SET FOREIGN_KEY_CHECKS=0');
        foreach ($existing as $full) {
            Db::execute('DROP TABLE IF EXISTS `' . $full . '`');
        }
        Db::execute('SET FOREIGN_KEY_CHECKS=1');
        $steps[] = '已删除旧表 ' . count($existing) . ' 张';

        $database = (string)config('database.connections.' . config('database.default') . '.database');
        foreach ($tableStatements as $statement) {
            $sql = trim($statement);
            preg_match('/CREATE TABLE(?: IF NOT EXISTS)? `eb_([^`]+)`/i', $sql, $matches);
            $fullTableName = $prefix . ($matches[1] ?? '');
            $sql = $helper->prepareSql($sql, $prefix, $scheme, $host);
            try {
                Db::execute($sql);
            } catch (\Throwable $e) {
                $check = Db::query(
                    'SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1',
                    [$database, $fullTableName]
                );
                if (empty($check)) {
                    throw new AdminException('创建表失败[{:table}]: {:msg}', [
                        'table' => $fullTableName,
                        'msg' => $e->getMessage(),
                    ]);
                }
            }
        }
        $steps[] = '已按最新 crmeb_table.sql 重建表结构 ' . count($tableStatements) . ' 张';

        foreach ($dataGroups as $group) {
            foreach ($group['statements'] as $statement) {
                $sql = $helper->prepareSql($statement, $prefix, $scheme, $host);
                try {
                    Db::execute($sql);
                } catch (\Throwable $e) {
                    throw new AdminException('写入默认数据失败[{:table}]: {:msg}', [
                        'table' => $group['table'],
                        'msg' => $e->getMessage(),
                    ]);
                }
            }
        }
        $steps[] = '已回灌安装默认数据 ' . count($dataGroups) . ' 组';

        /** @var ModuleInstaller $moduleInstaller */
        $moduleInstaller = app()->make(ModuleInstaller::class);
        $moduleInstaller->installRequired();
        $moduleInstaller->installDefault();
        $moduleInstaller->installBundledDrivers();
        $steps[] = '已安装默认插件与驱动';

        // 只保留当前超管（固定 id=1）
        Db::name('system_admin')->whereRaw('1=1')->delete();
        $adminColumns = array_column(Db::query('SHOW COLUMNS FROM `' . $prefix . 'system_admin`'), 'Field');
        $adminRow = array_intersect_key($admin, array_flip($adminColumns));
        $adminRow['id'] = 1;
        if (in_array('roles', $adminColumns, true)) {
            $adminRow['roles'] = '1';
        }
        if (in_array('level', $adminColumns, true)) {
            $adminRow['level'] = 0;
        }
        if (in_array('status', $adminColumns, true)) {
            $adminRow['status'] = 1;
        }
        if (in_array('is_del', $adminColumns, true)) {
            $adminRow['is_del'] = 0;
        }
        Db::name('system_admin')->insert($adminRow);
        $steps[] = '已写回超级管理员';

        foreach ($configSnapshot as $menuName => $value) {
            Db::name('system_config')->where('menu_name', (string)$menuName)->update(['value' => $value]);
        }
        $steps[] = '已覆盖回原系统配置';

        CacheService::clear();
        $this->delDirAndFile('./public/uploads/store/comment');
        $this->delDirAndFile('./public/uploads/store/service');
        $steps[] = '已清理缓存与用户侧临时上传';

        $this->writeFactoryResetLog($steps);
        return $steps;
    }

    /**
     * @param list<string> $steps
     */
    protected function writeFactoryResetLog(array $steps): void
    {
        $line = date('Y-m-d H:i:s') . ' factory_reset ' . implode(' | ', $steps) . PHP_EOL;
        @file_put_contents(runtime_path() . 'factory_reset.log', $line, FILE_APPEND);
    }

}
