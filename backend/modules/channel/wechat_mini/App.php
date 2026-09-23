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

use app\services\user\UserWechatAuthServices;
use think\App as ThinkApp;

/**
 * 微信小程序端渠道
 *
 * 端能力：code2Session 登录 + 订阅消息通知 + URL Scheme
 * 通过 modules/channel/wechat_mini/event.php 监听业务事件，
 * notify/ 子目录实现 NotifyChannelInterface 注册通知能力。
 *
 * @package modules\channel\wechat_mini
 */
class App
{
    /**
     * 端渠道名称
     * @var string
     */
    protected static string $name = '微信小程序';

    /**
     * 对应客户端请求头 Form-type 的值
     * @var string
     */
    protected static string $formType = 'routine';

    /**
     * 检测当前请求是否属于本端
     * 直接比较 Form-type header，不依赖 Request 方法
     * @return bool
     */
    public static function detect(): bool
    {
        return strtolower(app('request')->header('Form-type', '')) === static::$formType;
    }

    /**
     * 获取本端对应的 Form-type 值
     * @return string
     */
    public static function getFormType(): string
    {
        return static::$formType;
    }

    /**
     * 获取端渠道名称
     * @return string
     */
    public static function getName(): string
    {
        return static::$name;
    }

    /**
     * 注册本端渠道
     * @param ThinkApp $app
     * @return void
     */
    public static function register(ThinkApp $app): void
    {
    }

    /**
     * 启动本端渠道
     * @param ThinkApp $app
     * @return void
     */
    public static function boot(ThinkApp $app): void
    {
        $eventFile = __DIR__ . '/event.php';
        if (is_file($eventFile)) {
            $event = include $eventFile;
            if (is_array($event)) {
                $app->loadEvent($event);
            }
        }
    }

    /**
     * 获取支付所需的 openid
     * PayCredentialService 根据 plugin.json 声明的 required_credentials 自动调用
     * @param int $uid 用户ID
     * @return string
     */
    public static function getOpenid(int $uid): string
    {
        return app()->make(UserWechatAuthServices::class)->uidToOpenid($uid, 'routine');
    }
}
