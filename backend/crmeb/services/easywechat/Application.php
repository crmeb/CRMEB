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

namespace crmeb\services\easywechat;

use crmeb\services\easywechat\miniPayment\WeChatClient;
use crmeb\services\easywechat\miniScheme\ProgramScheme;
use crmeb\services\easywechat\oauth2\wechat\WechatOauth;
use crmeb\services\easywechat\Open3rd\ProgramOpen3rd;
use crmeb\services\easywechat\subscribe\ProgramSubscribe;
use crmeb\services\easywechat\wechatTemplate\ProgramTemplate;
use crmeb\services\easywechat\wechatlive\ProgramWechatLive;
use EasyWeChat\MiniApp\Application as MiniProgramApplication;
use EasyWeChat\OfficialAccount\Application as OfficialAccountApplication;
use EasyWeChat\OpenPlatform\Application as OpenPlatformApplication;
use EasyWeChat\Pay\Application as PayApplication;
use Psr\SimpleCache\CacheInterface;

/**
 * 微信应用工厂(EasyWeChat 6.x)
 *
 * 6.x 不再使用 Pimple 容器,本类只负责按传入配置懒加载各模块 Application,
 * 并统一注入 ThinkPHP 的 PSR-16 缓存,使 access_token / ticket 与项目缓存共用。
 * 自定义业务客户端(订阅消息、直播、scheme 码等)通过显式 getter 获取。
 *
 * @property-read \crmeb\services\easywechat\miniPayment\WeChatClient $minipay 小程序合单支付
 * @property-read \crmeb\services\easywechat\subscribe\ProgramSubscribe $now_notice 小程序订阅消息
 * @property-read \crmeb\services\easywechat\wechatlive\ProgramWechatLive $wechat_live 小程序直播
 * @property-read \crmeb\services\easywechat\miniScheme\ProgramScheme $mini_scheme 小程序 scheme 码
 * @property-read \crmeb\services\easywechat\wechatTemplate\ProgramTemplate $new_notice 公众号模板消息
 * @property-read \crmeb\services\easywechat\oauth2\wechat\WechatOauth $oauth2 微信网页授权
 */
class Application
{
    protected array $config;

    protected ?OfficialAccountApplication $officialAccountApp = null;
    protected ?MiniProgramApplication $miniProgramApp = null;
    protected ?PayApplication $paymentApp = null;
    protected ?OpenPlatformApplication $openPlatformApp = null;

    protected ?WeChatClient $miniPayClient = null;
    protected ?ProgramSubscribe $subscribeClient = null;
    protected ?ProgramWechatLive $liveClient = null;
    protected ?ProgramScheme $schemeClient = null;
    protected ?ProgramTemplate $templateClient = null;
    protected ?WechatOauth $oauth2Client = null;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 获取公众号 Application
     */
    public function getOfficialAccountApp(): OfficialAccountApplication
    {
        if ($this->officialAccountApp === null) {
            $config = [
                'app_id' => $this->config['app_id'] ?? $this->config['wechat']['appid'] ?? '',
                'secret' => $this->config['secret'] ?? $this->config['wechat']['appsecret'] ?? '',
                'token' => $this->config['token'] ?? $this->config['wechat']['token'] ?? '',
                'aes_key' => $this->config['aes_key'] ?? $this->config['wechat']['encodingaeskey'] ?? '',
                // 使用 stable_token 获取 access_token,避免旧接口 cgi-bin/token 在多实例/多环境同 appid 下互相顶号导致 40001
                'use_stable_access_token' => true,
            ];
            $this->officialAccountApp = $this->withCache(new OfficialAccountApplication($config));
        }

        return $this->officialAccountApp;
    }

    /**
     * 获取小程序 Application
     */
    public function getMiniProgramApp(): MiniProgramApplication
    {
        if ($this->miniProgramApp === null) {
            $config = [
                'app_id' => $this->config['mini_program']['app_id'] ?? '',
                'secret' => $this->config['mini_program']['secret'] ?? '',
                'token' => $this->config['mini_program']['token'] ?? '',
                'aes_key' => $this->config['mini_program']['aes_key'] ?? '',
                // 使用 stable_token 获取 access_token,避免旧接口 cgi-bin/token 在多实例/多环境同 appid 下互相顶号导致 40001
                'use_stable_access_token' => true,
            ];
            $this->miniProgramApp = $this->withCache(new MiniProgramApplication($config));
        }

        return $this->miniProgramApp;
    }

    /**
     * 获取微信支付 Application(V2 通道)
     * 旧配置键映射:merchant_id/key/cert_path/key_path -> mch_id/v2_secret_key/certificate/private_key
     */
    public function getPaymentApp(): PayApplication
    {
        if ($this->paymentApp === null) {
            $payment = $this->config['payment'] ?? [];
            $config = [
                'app_id' => $payment['app_id'] ?? $this->config['app_id'] ?? '',
                'mch_id' => $payment['merchant_id'] ?? $payment['mch_id'] ?? '',
                'v2_secret_key' => $payment['key'] ?? '',
                'certificate' => $payment['cert_path'] ?? '',
                'private_key' => $payment['key_path'] ?? '',
                'notify_url' => $payment['notify_url'] ?? '',
                // 服务商模式子商户号,6.x 会随 V2 签名自动附加
                'sub_mch_id' => $payment['sub_mch_id'] ?? '',
                'http' => [
                    'timeout' => 30.0,
                    'throw' => false,
                ],
            ];
            $this->paymentApp = new PayApplication($config);
        }

        return $this->paymentApp;
    }

    /**
     * 获取开放平台 Application
     */
    public function getOpenPlatformApp(): OpenPlatformApplication
    {
        if ($this->openPlatformApp === null) {
            $config = [
                'app_id' => $this->config['app_id'] ?? '',
                'secret' => $this->config['secret'] ?? '',
                'token' => $this->config['token'] ?? '',
                'aes_key' => $this->config['aes_key'] ?? '',
            ];
            $this->openPlatformApp = $this->withCache(new OpenPlatformApplication($config));
        }

        return $this->openPlatformApp;
    }

    /**
     * 小程序合单支付客户端(shop/pay)
     */
    public function miniPay(): WeChatClient
    {
        if ($this->miniPayClient === null) {
            $merchantId = $this->config['payment']['merchant_id'] ?? $this->config['payment']['mch_id'] ?? '';
            $this->miniPayClient = new WeChatClient(
                $this->getMiniProgramApp()->getClient(),
                (string) $merchantId
            );
        }

        return $this->miniPayClient;
    }

    /**
     * 小程序订阅消息客户端
     */
    public function subscribe(): ProgramSubscribe
    {
        return $this->subscribeClient ??= new ProgramSubscribe($this->getMiniProgramApp()->getClient());
    }

    /**
     * 小程序直播客户端
     */
    public function live(): ProgramWechatLive
    {
        return $this->liveClient ??= new ProgramWechatLive($this->getMiniProgramApp()->getClient());
    }

    /**
     * 小程序 scheme 码/链接客户端
     */
    public function scheme(): ProgramScheme
    {
        return $this->schemeClient ??= new ProgramScheme($this->getMiniProgramApp()->getClient());
    }

    /**
     * 公众号模板消息客户端
     */
    public function template(): ProgramTemplate
    {
        return $this->templateClient ??= new ProgramTemplate($this->getOfficialAccountApp()->getClient());
    }

    /**
     * 微信网页授权客户端(appid 按请求场景选择:APP / 公众号 H5)
     */
    public function oauth2(): WechatOauth
    {
        if ($this->oauth2Client === null) {
            $wechat = sys_config_more(['wechat_appid', 'wechat_app_appid', 'wechat_app_appsecret', 'wechat_appsecret']);
            $request = app('request');
            if ($request->isApp()) {
                $appId = trim($wechat['wechat_app_appid'] ?? '');
                $appSecret = trim($wechat['wechat_app_appsecret'] ?? '');
            } else {
                $appId = trim($wechat['wechat_appid'] ?? '');
                $appSecret = trim($wechat['wechat_appsecret'] ?? '');
            }
            $this->oauth2Client = new WechatOauth($appId, $appSecret);
        }

        return $this->oauth2Client;
    }

    /**
     * 第三方平台代开发客户端(需开放平台已授权的小程序)
     */
    public function open3rd(string $authorizerAppId = '', string $authorizerRefreshToken = ''): ProgramOpen3rd
    {
        return new ProgramOpen3rd($this->getOpenPlatformApp(), $authorizerAppId, $authorizerRefreshToken);
    }

    /**
     * 统一注入项目缓存(PSR-16),access_token 等凭证走 ThinkPHP 缓存
     */
    protected function withCache(OfficialAccountApplication|MiniProgramApplication|OpenPlatformApplication $app): OfficialAccountApplication|MiniProgramApplication|OpenPlatformApplication
    {
        try {
            $cache = app('cache');
            if ($cache instanceof CacheInterface) {
                $app->setCache($cache);
            }
        } catch (\Throwable $e) {
            // 缓存不可用时回退 6.x 默认文件缓存,不影响业务
        }

        return $app;
    }

    /**
     * 魔术访问:兼容旧 Pimple 时代的属性名(minipay/now_notice/wechat_live 等)
     */
    public function __get(string $name)
    {
        return match ($name) {
            'minipay' => $this->miniPay(),
            'now_notice' => $this->subscribe(),
            'wechat_live' => $this->live(),
            'mini_scheme' => $this->scheme(),
            'new_notice' => $this->template(),
            'oauth2' => $this->oauth2(),
            default => throw new \RuntimeException("Service '{$name}' not found in Application."),
        };
    }
}
