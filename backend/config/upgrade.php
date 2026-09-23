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

/**
 * 升级配置
 * 版本列表按从小到大排列，升级时会按顺序执行
 */
return [
    // 最低版本要求 (只有达到此版本才能使用跨版本在线升级功能)
    // 因为跨版本在线升级功能是在 v6.0.0 版本开发的，低于此版本的用户无法使用
    'min_version' => [
        'version' => 'CRMEB-BZ v6.0.0',
        'code' => 600,
        'message' => '跨版本在线升级功能需要 v6.0.0 及以上版本才能使用，请先手动升级到 v6.0.0 版本'
    ],

    // 版本列表 (按版本从小到大排列)
    // version: 版本名称
    // code: 版本代码 (数字，用于比较)
    // file: 升级脚本文件名 (相对于 upgrade/versions/ 目录)
    // description: 版本描述
    'versions' => [
        [
            'version' => 'CRMEB-BZ v6.0.1',
            'code' => 601,
            'file' => 'v6.0.1.php',
            'description' => '性能优化与问题修复版本'
        ],
    ],

    // 升级脚本目录
    'upgrade_path' => app()->getRootPath() . 'upgrade' . DIRECTORY_SEPARATOR . 'versions' . DIRECTORY_SEPARATOR,

    // 平台信息
    'platform' => 'CRMEB',

    // APP认证信息 (可从.version文件读取覆盖)
    'app_id' => 'ze7x9rxsv09l6pvsyo',
    'app_key' => 'fuF7U9zaybLa5gageVQzxtxQMFnvU2OI',

    // 远程升级服务器配置
    'remote' => [
        'login_url' => 'https://upgrade.crmeb.net/api/login',
        'upgrade_url' => 'https://upgrade.crmeb.net/api/upgrade/list',
        'upgrade_current_url' => 'https://upgrade.crmeb.net/api/upgrade/current_list',
        'agreement_url' => 'https://upgrade.crmeb.net/api/upgrade/agreement',
        'package_download_url' => 'https://upgrade.crmeb.net/api/upgrade/download',
        'upgrade_status_url' => 'https://upgrade.crmeb.net/api/upgrade/status',
        'upgrade_log_url' => 'https://upgrade.crmeb.net/api/upgrade/log',
    ],

    // 备份配置
    'backup' => [
        'database' => true,  // 是否备份数据库
        'project' => true,   // 是否备份项目文件
        'path' => app()->getRootPath() . 'backup' . DIRECTORY_SEPARATOR,
    ],

    // 忽略的目录 (备份时)
    'ignore_dirs' => ['.', '..', '.git', '.idea', 'runtime', 'backup', 'upgrade'],

    // 忽略的文件扩展名
    'ignore_extensions' => ['zip', 'gz', 'log'],
];
