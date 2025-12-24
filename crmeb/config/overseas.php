<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

use think\facade\Env;

return [
    /**
     * Overseas Lite 模式：
     * - 面向海外（默认英文/UTC）
     * - 适合小团队（~20人）后台：收敛菜单与模块，禁用过量营销/分销/直播/微信生态/低代码等
     */
    'enabled' => (bool)Env::get('app.overseas_mode', 0),

    // 后台菜单（导航）允许的路由前缀（注意：这里是不带 /admin 前缀的 menu_path）
    'admin_menu_allow_prefixes' => [
        '/index',
        '/product',
        '/order',
        '/user',
        '/cms',
        '/setting/system_config',
        '/setting/freight',
        '/setting/system_admin',
        '/setting/system_role',
        '/setting/system_menus',
        '/setting/auth',
        '/system/maintain',
        '/file',
        '/export',
        '/statistic',
    ],

    // 禁用的后台路由文件（避免注册路由，直接 404）
    'admin_route_files_disabled' => [
        'agent.php',
        'app.php',
        'crud.php',
        'diy.php',
        'live.php',
        'marketing.php',
        'merchant.php',
        'serve.php',
    ],

    // unique_auth 前端权限标识的禁用前缀（用于收敛权限点）
    'deny_unique_auth_prefixes' => [
        'admin-agent',
        'agent-',
        'admin-marketing',
        'marketing-',
        'admin-wechat',
        'admin-routine',
        'app-',
        'live-',
        'merchant-',
        'crud-',
        'system-crud',
        'system-out-interface',
        'system-wechat',
    ],

    // 菜单英文映射（key 使用不带 /admin 前缀的 menu_path；支持前缀匹配，优先最长匹配）
    'admin_menu_title_map' => [
        '/index' => 'Dashboard',
        '/product' => 'Catalog',
        '/product/product_list' => 'Products',
        '/product/product_classify' => 'Categories',
        '/product/product_reply' => 'Reviews',
        '/order' => 'Orders',
        '/order/list' => 'Order List',
        '/user' => 'Customers',
        '/user/list' => 'Customer List',
        '/cms' => 'Content',
        '/cms/article/index' => 'Articles',
        '/cms/article_category/index' => 'Article Categories',
        '/setting' => 'Settings',
        '/setting/system_config' => 'Store Settings',
        '/setting/freight' => 'Shipping',
        '/setting/freight/express/index' => 'Carriers',
        '/setting/freight/shipping_templates/list' => 'Shipping Templates',
        '/setting/system_admin/index' => 'Staff',
        '/setting/system_role/index' => 'Roles',
        '/setting/system_menus/index' => 'Permissions',
        '/setting/auth/list' => 'Team & Access',
        '/system' => 'Maintenance',
        '/system/maintain' => 'Maintenance',
        '/system/maintain/system_log/index' => 'System Logs',
        '/system/maintain/clear/index' => 'Clear Cache',
        '/file' => 'Files',
        '/export' => 'Export',
        '/statistic' => 'Reports',
    ],

    // 海外支付渠道配置（用于收敛/展示海外可用支付方式）
    'payment_channels' => [
        // 允许的支付方式（与 config/pay.php 的 payType key 对齐）
        'allow' => [
            'stripe',
            'paypal',
        ],
        // 支付方式展示名称（可按需覆盖）
        'labels' => [
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
        ],
    ],
];
