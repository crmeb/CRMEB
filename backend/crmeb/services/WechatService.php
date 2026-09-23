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

namespace crmeb\services;

use crmeb\services\easywechat\Application;
use crmeb\services\pay\PayChannelConfig;
use EasyWeChat\Kernel\HttpClient\Response;
use EasyWeChat\MiniApp\Application as MiniProgramApplication;
use EasyWeChat\OfficialAccount\Application as OfficialAccountApplication;
use EasyWeChat\Pay\Application as PayApplication;

/**
 * 微信服务封装
 * 提供小程序、公众号、支付等微信相关服务的统一入口
 * @package crmeb\services
 */
class WechatService
{
    /**
     * @var Application
     */
    protected static $instance;

    /**
     * 获取微信应用配置
     * @return array
     */
    public static function options(): array
    {
        $wechat = sys_config_more([
            'wechat_app_appsecret',
            'wechat_app_appid',
            'site_url',
            'routine_appId',
            'routine_appsecret',
            'routine_token',
            'routine_encodingaeskey',
            'wechat_appid',
            'wechat_appsecret',
            'wechat_token',
            'wechat_encodingaeskey',
        ]);

        // 支付配置统一存储于 eb_pay_channel.config_fields，不再读取 system_config
        $payment = PayChannelConfig::get('weixin');

        if (request()->isApp()) {
            $appId = trim($wechat['wechat_app_appid'] ?? '');
            $appsecret = trim($wechat['wechat_app_appsecret'] ?? '');
        } elseif (request()->isWechat()) {
            $appId = trim($wechat['wechat_appid'] ?? '');
            $appsecret = trim($wechat['wechat_appsecret'] ?? '');
        } else {
            $appId = trim($wechat['routine_appId'] ?? '');
            $appsecret = trim($wechat['routine_appsecret'] ?? '');
        }

        return [
            'token' => trim($wechat['wechat_token'] ?? $wechat['routine_token'] ?? ''),
            'aes_key' => trim($wechat['wechat_encodingaeskey'] ?? $wechat['routine_encodingaeskey'] ?? ''),
            'app_id' => $appId,
            'secret' => $appsecret,
            'mini_program' => [
                'app_id' => trim($wechat['routine_appId'] ?? ''),
                'secret' => trim($wechat['routine_appsecret'] ?? ''),
                'token' => trim($wechat['routine_token'] ?? ''),
                'aes_key' => trim($wechat['routine_encodingaeskey'] ?? ''),
            ],
            'payment' => [
                'app_id' => $appId,
                'merchant_id' => empty($payment['pay_new_weixin_open'])
                    ? trim($payment['pay_weixin_mchid'] ?? '')
                    : trim($payment['pay_new_weixin_mchid'] ?? ''),
                'key' => trim($payment['pay_weixin_key'] ?? ''),
                'cert_path' => '',
                'key_path' => '',
                'notify_url' => trim($wechat['site_url'] ?? '') . '/api/pay/notify/weixin',
            ],
        ];
    }

    /**
     * 获取应用实例
     * @param bool $refresh 是否刷新实例
     * @return Application
     */
    public static function application(bool $refresh = false): Application
    {
        if (self::$instance === null || $refresh) {
            self::$instance = new Application(self::options());
        }
        return self::$instance;
    }

    /**
     * 获取小程序应用
     * @return MiniProgramApplication
     */
    public static function miniProgram(): MiniProgramApplication
    {
        return self::application()->getMiniProgramApp();
    }

    /**
     * 获取小程序二维码（不限量）
     * @param string $scene 场景值
     * @param string|null $page 页面路径
     * @param int $width 二维码宽度
     * @param bool $autoColor 自动配置线条颜色
     * @param array $lineColor 线条颜色
     * @return Response 二进制响应,调用方 ->saveAs() 落盘或 ->getContent() 取内容
     */
    public static function appCodeUnlimit(
        string $scene,
        string $page = null,
        int $width = 430,
        bool $autoColor = false,
        array $lineColor = ['r' => 0, 'g' => 0, 'b' => 0]
    ) {
        return self::miniProgram()->getClient()->postJson('wxa/getwxacodeunlimit', array_filter([
            'scene' => $scene,
            'page' => $page,
            'width' => $width,
            'auto_color' => $autoColor,
            'line_color' => $lineColor,
        ], static fn ($value) => $value !== null && $value !== ''));
    }

    /**
     * 获取公众号应用
     * @return OfficialAccountApplication
     */
    public static function officialAccount(): OfficialAccountApplication
    {
        return self::application()->getOfficialAccountApp();
    }

    /**
     * 获取支付应用（V2 通道）
     * @return PayApplication
     */
    public static function payment(): PayApplication
    {
        return self::application()->getPaymentApp();
    }
}
