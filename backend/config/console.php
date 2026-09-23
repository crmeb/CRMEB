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
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
$coreCommands = [
    'workerman' => \app\command\Workerman::class,
    'timer' => \app\command\Timer::class,
    'util' => \app\command\Util::class,
    'npm' => \crmeb\command\Npm::class,
    'make:module' => \crmeb\command\MakeModuleCommand::class,
    'refund:sync' => \app\command\RefundSync::class,
    'pay:recover' => \app\command\PaymentRecover::class,
    'pay:clear-cache' => \app\command\ClearPayCache::class,
    'notice:clear' => \app\command\NoticeCache::class,
    'event:check' => \app\command\EventCheck::class,
    'arch:check' => \app\command\ArchCheck::class,
    'docs:check' => \app\command\DocsCheck::class,
    'registry:check' => \app\command\RegistryCheck::class,
    'channel:menu-check' => \app\command\ChannelMenuCheck::class,
    'plugin:required-check' => \app\command\RequiredPluginCheck::class,
    'plugin:manifest-check' => \app\command\PluginManifestCheck::class,
];

return [
    // 执行用户（Windows下无效）
    'user' => null,
    // 指令定义
    'commands' => array_merge(
        $coreCommands,
        \crmeb\plugin\PluginCommandDiscovery::discover(dirname(__DIR__))
    ),
];
