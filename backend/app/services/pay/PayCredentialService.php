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

namespace app\services\pay;

use app\services\PlatformDetector;

/**
 * 支付凭证服务
 *
 * 根据 plugin.json 的 required_credentials（按平台区分）获取支付所需凭证。
 *
 * plugin.json 声明示例（driver 节点内；h5/app/pc 不声明即不需要凭证）：
 *   "required_credentials": {
 *       "wechat_mini": ["openid"],
 *       "wechat_mp":   ["openid"]
 *   }
 *
 * channel 模块实现示例：
 *   // modules/channel/wechat_mini/App.php
 *   public static function getOpenid(int $uid): string { ... }
 *
 * 控制器只需调用 getCredentials()，无需判断哪个支付方式在哪个平台需要什么参数。
 */
class PayCredentialService
{
    /**
     * 获取支付所需的凭证
     * @param int $uid 用户ID
     * @param string $channelKey 支付方式标识
     * @return array 凭证数组（如 ['openid' => 'xxx']）
     */
    public static function getCredentials(int $uid, string $channelKey): array
    {
        $platform = PlatformDetector::detect();

        // 从 plugin.json 读取该支付方式在当前平台声明的所需凭证
        $payServices = app()->make(PayServices::class);
        $channelInfo = $payServices->getChannelInfoByKey($channelKey);
        $required = $channelInfo['required_credentials'][$platform] ?? [];

        if (!$required) {
            return [];
        }

        // 当前平台的 channel App 类（从平台注册表获取，已含渠道启用白名单过滤）
        $platforms = PlatformDetector::getRegisteredPlatforms();
        if (!isset($platforms[$platform])) {
            return [];
        }
        $channelClass = $platforms[$platform]['class'];

        $credentials = [];
        foreach ($required as $cred) {
            // 凭证名 → 方法名：openid → getOpenid
            $method = 'get' . str_replace('_', '', ucfirst($cred));
            if (method_exists($channelClass, $method)) {
                $value = $channelClass::$method($uid);
                if ($value) {
                    $credentials[$cred] = $value;
                }
            }
        }

        return $credentials;
    }
}
