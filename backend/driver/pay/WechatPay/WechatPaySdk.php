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

namespace driver\pay\WechatPay;

use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use crmeb\services\easywechat\Application;
use crmeb\services\wechat\Support;
use EasyWeChat\Kernel\Support\Str;
use EasyWeChat\Kernel\Support\Xml;
use EasyWeChat\Pay\Application as PayApplication;
use EasyWeChat\Pay\Message;
use Nyholm\Psr7\Response as Psr7Response;
use think\facade\Db;
use think\facade\Event;

/**
 * 微信支付 V2 SDK（WechatPay 驱动包内置）
 * 整合原 modules/channel/wechat_mini/sdk/MiniProgramService 与
 * modules/channel/wechat_mp/sdk/WechatService 中的支付能力，
 * 供本驱动包 Driver.php 调用，避免 driver 层反向依赖 modules/channel。
 *
 * 通过 $scene 参数区分使用场景：
 * - mini：小程序（routine_appId / routine_appsecret）
 * - mp：公众号（仅使用 wechat_appid）
 * - app：APP（仅使用 wechat_app_appid）
 *
     * 场景隔离用于区分应用身份，所有支付成功通知仍执行同一签名与业务校验。
 *
 * ⚠️ 与 driver/pay/V3WechatPay/WechatV3PaySdk.php 的关系：
 * - 那是 V3 驱动专用补充 SDK，仅提供 minipay 通道（小程序新版支付）；
 * - 本类是 V2 协议完整 SDK，提供 24 个方法覆盖 mini/mp/app 三场景；
 * - 两者协议不同、职责不同，不是同源副本，不需同步修改。
 */
class WechatPaySdk
{
    /**
     * 微信支付渠道标识
     * 与 app\services\pay\PayServices::WEIXIN_PAY 保持一致，driver 层不可反向依赖 app 层
     * 故在此声明本地常量，Notify 事件按字符串路由
     */
    const WEIXIN_PAY_CHANNEL_KEY = 'weixin';

    /**
     * 按场景隔离的 Application 实例
     * @var array<string,Application>
     */
    protected static $instances = [];

    /** @var array<string,string> 场景对应的配置指纹，不保存明文密钥 */
    protected static array $instanceHashes = [];

    /**
     * 从 eb_pay_channel 读取本驱动支付插件配置
     *
     * 架构豁免说明：支付驱动插件化后配置统一存储于 eb_pay_channel.config_fields
     * （app\services\driver\pay\PayChannelServices 负责读写），本类位于 driver 第三方
     * 驱动底座，driver 层无 DAO 体系，故此处 Db 直查为合规做法（同本类 getPemPath 模式），
     * 禁止"修复"为 app 层 DAO
     * @return array{status:int, config:array<string,string>}
     */
    protected static function channelConfig(): array
    {
        try {
            $channel = Db::name('pay_channel')->where('channel_key', self::WEIXIN_PAY_CHANNEL_KEY)->where('is_del', 0)->find();
        } catch (\Throwable $e) {
            return ['status' => 0, 'config' => []];
        }
        $config = [];
        if ($channel && !empty($channel['config_fields'])) {
            // Db 直查返回 text 列原始 JSON 字符串，需解码（模型 cast 在 driver 层不生效）
            $configFields = is_string($channel['config_fields']) ? json_decode($channel['config_fields'], true) : $channel['config_fields'];
            if (is_array($configFields)) {
                foreach ($configFields as $field) {
                    if (isset($field['field'])) {
                        $config[$field['field']] = $field['value'] ?? '';
                    }
                }
            }
        }
        return [
            'status' => (int)($channel['status'] ?? 0),
            'config' => $config,
        ];
    }

    /**
     * 读取支付配置
     * @param string $scene 场景：mini(小程序) / mp(公众号) / app(APP)
     * @return array
     */
    public static function options(string $scene = 'mp'): array
    {
        // 支付插件配置（微信支付已插件化，统一存储于 eb_pay_channel.config_fields）。
        // 公众号和 APP 仅使用 AppID；仅小程序新版支付需要小程序 AppSecret。
        // site_url 属系统公用配置（站点信息），允许 driver 直接 sys_config 读取
        $channelConfig = self::channelConfig();
        $payment = $channelConfig['config'];
        $siteUrl = sys_config('site_url');

        // 按场景读取渠道配置
        if ($scene === 'mini') {
            // 小程序场景
            $wechat = [
                'site_url' => $siteUrl,
                'routine_appId' => $payment['routine_appId'] ?? '',
                'routine_appsecret' => $payment['routine_appsecret'] ?? '',
            ];
            $appId = trim($wechat['routine_appId'] ?? '');
            $appsecret = trim($wechat['routine_appsecret'] ?? '');
            if ($appId === '') {
                throw new ApiException('微信支付V2小程序需要配置小程序AppID');
            }

            $config = [
                'mini_program' => [
                    'app_id' => $appId,
                    'secret' => $appsecret,
                ],
            ];
            // 小程序商户号：新版用 pay_new_weixin_mchid，旧版用 pay_weixin_mchid
            $merchantId = !empty($payment['pay_new_weixin_open'])
                ? trim($payment['pay_new_weixin_mchid'] ?? '')
                : trim($payment['pay_weixin_mchid'] ?? '');
            $config['payment'] = [
                'app_id' => $appId,
                'merchant_id' => $merchantId,
                'key' => trim($payment['pay_weixin_key'] ?? ''),
                'cert_path' => self::getPemPath('pay_weixin_client_cert'),
                'key_path' => self::getPemPath('pay_weixin_client_key'),
                'notify_url' => trim($siteUrl) . '/api/pay/notify/weixin',
            ];
            if (empty($payment['pay_new_weixin_open'])) $config['payment'] = self::withProviderConfig($config['payment'], $payment);
            return $config;
        }

        // 公众号 / APP 支付只需要各自 AppID，AppSecret 属于应用能力，不参与 V2 下单签名。
        $wechat = [
            'wechat_appid' => $payment['wechat_appid'] ?? '',
            'wechat_app_appid' => $payment['wechat_app_appid'] ?? '',
        ];
        if ($scene === 'app') {
            $appId = trim($wechat['wechat_app_appid'] ?? '');
        } else {
            $appId = trim($wechat['wechat_appid'] ?? '');
        }
        if ($appId === '' && $scene !== 'notify') {
            $label = $scene === 'app' ? 'APP应用' : '微信公众号';
            throw new ApiException('微信支付V2' . $label . '需要配置AppID');
        }

        $config = [
            'app_id' => $appId,
            // 保留空 secret 键以兼容 EasyWeChat Application 配置结构；支付流程不会使用它。
            'secret' => '',
            'guzzle' => [
                'timeout' => 10.0,
                'verify' => true,
            ],
        ];

        // payment 配置（渠道启停由 eb_pay_channel.status 控制，插件化后不再读取 pay_weixin_open）
        if ((int)($channelConfig['status'] ?? 0) === 1 || $scene === 'notify') {
            $config['payment'] = [
                'app_id' => $appId,
                'merchant_id' => trim($payment['pay_weixin_mchid'] ?? ''),
                'key' => trim($payment['pay_weixin_key'] ?? ''),
                'cert_path' => self::getPemPath('pay_weixin_client_cert'),
                'key_path' => self::getPemPath('pay_weixin_client_key'),
                'notify_url' => trim($siteUrl) . '/api/pay/notify/weixin',
            ];
            // 服务商模式子商户号
            $config['payment'] = self::withProviderConfig($config['payment'], $payment);
        }
        return $config;
    }

    /**
     * 服务商签名使用服务商应用，支付者与调起参数使用对应子应用。
     * @param array $config 支付配置
     * @param array $payment 渠道配置
     * @return array
     */
    protected static function withProviderConfig(array $config, array $payment): array
    {
        if (empty($payment['mer_type'])) return $config;
        $spAppid = trim((string)($payment['sp_appid'] ?? ''));
        $subMchid = trim((string)($payment['pay_sub_merchant_id'] ?? ''));
        if ($spAppid === '' || $subMchid === '') throw new ApiException('服务商支付需要配置服务商AppID和子商户号');
        $config['sub_appid'] = $config['app_id'];
        $config['app_id'] = $spAppid;
        $config['sub_mch_id'] = $subMchid;
        return $config;
    }

    /**
     * 获取支付证书路径
     *
     * 架构豁免说明：本类位于 driver 第三方驱动底座，依赖铁律禁止调用 app 层
     * （SystemPemServices/SystemPemDao 均在 app 层），driver 层无 DAO 体系，
     * 故此处 Db 直查为合规做法（同 crmeb\services\AliPayService::getPemPath 模式），
     * 禁止"修复"为 app 层 DAO
     * @param string $name 证书配置名
     * @return string
     */
    public static function getPemPath(string $name): string
    {
        // 支付渠道配置优先，保证 V2 使用自己的证书；system_pem 仅兼容旧版应用配置。
        $channelConfig = self::channelConfig();
        $path = self::resolveConfiguredPemPath((string)($channelConfig['config'][$name] ?? ''));
        if ($path !== '') {
            return $path;
        }

        // 兼容旧版 system_pem 证书配置。
        $info = Db::name('system_pem')->where('name', $name)->find();
        if ($info && !empty($info['path'])) {
            $path = root_path('runtime/pem') . $info['path'] . '.pem';
            if (!file_exists($path)) {
                // 如果 runtime/pem 文件夹不存在，创建文件夹
                if (!file_exists(root_path('runtime/pem'))) {
                    mkdir(root_path('runtime/pem'), 0777, true);
                }
                file_put_contents($path, $info['content'] ?? '');
            }
            if ($path) return $path;
        }
        return '';
    }

    /**
     * 解析支付渠道配置中的证书路径。
     * @param string $path
     * @return string
     */
    protected static function resolveConfiguredPemPath(string $path): string
    {
        if ($path === '') {
            return '';
        }
        if (strstr($path, 'http://') || strstr($path, 'https://')) {
            $path = parse_url($path)['path'] ?? '';
        }
        $path = str_replace('\\', '/', trim($path));
        if ($path === '') {
            return '';
        }
        $rootPath = str_replace('\\', '/', rtrim(root_path(), '/\\')) . '/';
        $normalizedPath = str_replace('\\', '/', $path);
        if (str_starts_with($normalizedPath, $rootPath) && is_file($path) && is_readable($path)) {
            return $path;
        }
        $relativePath = ltrim($path, '/');
        if (str_starts_with($relativePath, 'uploads/')) {
            $relativePath = substr($relativePath, strlen('uploads/'));
        }
        $runtimePath = root_path('runtime/pem') . $relativePath;
        if (is_file($runtimePath) && is_readable($runtimePath)) {
            return $runtimePath;
        }
        $publicPath = public_path('uploads') . $relativePath;
        if (is_file($publicPath) && is_readable($publicPath)) {
            return $publicPath;
        }
        return '';
    }

    /**
     * 获取 Application 实例（按场景缓存）
     * @param string $scene 场景
     * @param bool $cache 是否强制刷新缓存
     * @return Application
     */
    public static function application(string $scene = 'mp', bool $cache = false): Application
    {
        $config = self::channelConfig();
        $certificates = [];
        foreach (['pay_weixin_client_cert', 'pay_weixin_client_key'] as $name) {
            $path = self::resolveConfiguredPemPath((string)($config['config'][$name] ?? ''));
            $certificates[$name] = $path !== '' ? hash_file('sha256', $path) : '';
        }
        $hash = hash('sha256', serialize([$config, $certificates]));
        if (!isset(self::$instances[$scene]) || $cache === true
            || (self::$instanceHashes[$scene] ?? '') !== $hash) {
            self::$instances[$scene] = new Application(self::options($scene));
        }
        self::$instanceHashes[$scene] = $hash;
        return self::$instances[$scene];
    }

    /**
     * 获取支付服务
     * @param string $scene 场景
     * @return PayApplication
     */
    public static function paymentService(string $scene = 'mp')
    {
        return self::application($scene)->getPaymentApp();
    }

    /**
     * V2 XML 接口统一请求(xml 选项自动以 v2 key 签名)
     * @param string $scene
     * @param string $uri
     * @param array $data
     * @param bool $withCerts 双向证书(退款/企业付款需要)
     * @return array
     */
    protected static function xmlRequest(string $scene, string $uri, array $data, bool $withCerts = false): array
    {
        $enterprise = in_array($uri, ['mmpaymkttransfers/promotion/transfers', 'mmpaymkttransfers/gettransferinfo'], true);
        if ($enterprise && !$withCerts) throw new ApiException('企业付款接口必须使用双向证书');
        $app = self::paymentService($scene);
        $config = $app->getConfig();
        // 对齐 4.x Payment BaseClient：所有 V2 请求注入商户号（6.x 仅自动补 nonce_str 与签名，不补 mch_id）
        // 使用安全回调过滤空值/null，避免 PHP 8.2 下 strlen(null) 触发 deprecation
        $data = array_filter(array_merge([
            $uri === 'mmpaymkttransfers/promotion/transfers' ? 'mchid' : 'mch_id' => (string) $config->get('mch_id'),
            'sub_mch_id' => $config->get('sub_mch_id'),
            'sub_appid' => $config->get('sub_appid'),
        ], $data), static fn ($v) => $v !== '' && $v !== null);

        $options = ['xml' => $data];
        if ($withCerts) {
            $options['local_cert'] = (string) $config->get('certificate');
            $options['local_pk'] = (string) $config->get('private_key');
        }
        $res = $app->getClient()->post($uri, $options)->toArray(false);

        if (!is_array($res)) {
            throw new ApiException('微信支付返回格式不正确');
        }
        // 旧企业付款/查询协议没有响应签名；仅这两条双向 TLS 接口按请求身份核对。
        if ($enterprise && ($res['result_code'] ?? '') === 'SUCCESS') {
            $merchant = (string)($res['mchid'] ?? $res['mch_id'] ?? '');
            $appid = (string)($res['mch_appid'] ?? $res['appid'] ?? '');
            if ($merchant !== (string)$config->get('mch_id') || $appid !== (string)($data['mch_appid'] ?? $data['appid'] ?? '')
                || ($res['partner_trade_no'] ?? '') !== ($data['partner_trade_no'] ?? '')) {
                throw new ApiException('企业付款响应与原商户、应用或单号不一致');
            }
        }
        if (!$enterprise && ($res['return_code'] ?? '') === 'SUCCESS'
            && (!is_string($res['sign'] ?? null) || $res['sign'] === ''
                || !hash_equals(self::v2Sign($res, (string)$config->get('v2_secret_key')), $res['sign']))) {
            throw new ApiException('微信支付响应签名校验失败');
        }
        return $res;
    }

    /**
     * V2 MD5 签名(APP 支付参数用)
     * @param array $params
     * @param string $key
     * @return string
     */
    protected static function v2Sign(array $params, string $key): string
    {
        unset($params['sign']);
        $params = array_filter($params, static fn ($v) => $v !== '' && $v !== null);
        ksort($params);

        $parts = [];
        foreach ($params as $name => $value) {
            if (!is_scalar($value)) {
                throw new ApiException('微信支付签名字段格式不正确');
            }
            $parts[] = $name . '=' . $value;
        }
        $content = implode('&', $parts) . '&key=' . $key;
        $type = $params['sign_type'] ?? 'MD5';
        if (!in_array($type, ['MD5', 'HMAC-SHA256'], true) || $key === '') {
            throw new ApiException('微信支付签名算法或密钥不正确');
        }
        return strtoupper($type === 'HMAC-SHA256' ? hash_hmac('sha256', $content, $key) : md5($content));
    }

    /**
     * 生成支付订单对象（小程序场景）
     * @param string|null $openid
     * @param string $out_trade_no
     * @param mixed $total_fee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array
     */
    protected static function paymentOrderMini($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $total_fee = bcmul($total_fee, 100, 0);
        $order = compact('openid', 'out_trade_no', 'total_fee', 'attach', 'body', 'detail', 'trade_type')
            + array_intersect_key($options, array_flip(['time_expire', 'expire_time']));
        if ($order['detail'] === '') unset($order['detail']);
        return $order;
    }

    /**
     * 生成支付订单对象（公众号场景）
     * @param string|null $openid
     * @param string $out_trade_no
     * @param mixed $total_fee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array
     */
    protected static function paymentOrderMp($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $total_fee = bcmul($total_fee, 100, 0);
        $order = compact('out_trade_no', 'total_fee', 'attach', 'body', 'detail', 'trade_type')
            + array_intersect_key($options, array_flip(['time_expire', 'expire_time']));
        if (!is_null($openid)) $order['openid'] = $openid;
        if ($order['detail'] === '') unset($order['detail']);
        return $order;
    }

    /**
     * 统一下单补齐 V2 必填字段（对齐 4.x Payment Order Client::unify）
     * @param string $scene
     * @param array $order
     * @param string $tradeType
     * @return array
     */
    protected static function withUnifyFields(string $scene, array $order, string $tradeType): array
    {
        $config = self::paymentService($scene)->getConfig();
        unset($order['expire_time']);
        $order['appid'] = (string) $config->get('app_id');
        if ($config->get('sub_appid') && isset($order['openid'])) {
            $order['sub_openid'] = $order['openid'];
            unset($order['openid']);
        }
        $order['notify_url'] = (string) $config->get('notify_url');
        if (empty($order['spbill_create_ip'])) {
            $order['spbill_create_ip'] = ($tradeType === 'NATIVE') ? self::serverIp() : request()->ip();
        }
        if ($tradeType === 'MWEB') {
            $order['scene_info'] = json_encode(['h5_info' => [
                'type' => 'Wap', 'wap_url' => sys_config('site_url'), 'wap_name' => sys_config('site_name'),
            ]], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }
        return $order;
    }

    /**
     * 按支付身份、金额、场景隔离预支付缓存。
     * @param string $scene 场景
     * @param string $orderNo 订单号
     * @param mixed $openid 支付者
     * @param mixed $amount 金额
     * @param string $mode 支付模式
     * @return string
     */
    protected static function prepayCacheKey(string $scene, string $orderNo, $openid, $amount, string $mode): string
    {
        $config = self::paymentService($scene)->getConfig();
        $identity = [$scene, $openid, (string)$amount, $mode, $config->get('app_id'),
            $config->get('sub_appid'), $config->get('mch_id'), $config->get('sub_mch_id')];
        return 'pay:v2:prepay:' . $orderNo . ':' . hash('sha256', json_encode($identity, JSON_THROW_ON_ERROR));
    }

    /**
     * 获取服务器出口 IP（对齐 4.x Support\get_server_ip，NATIVE 下单场景需要）
     * @return string
     */
    protected static function serverIp(): string
    {
        if (!empty($_SERVER['SERVER_ADDR'])) {
            $ip = $_SERVER['SERVER_ADDR'];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            $ip = gethostbyname($_SERVER['SERVER_NAME']);
        } else {
            $ip = gethostbyname(gethostname());
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ?: '127.0.0.1';
    }

    /**
     * 统一下单 - 小程序场景
     * 返回 prepay_id 字符串（与原 MiniProgramService::paymentPrepare 一致）
     * @param string $scene
     * @param string|null $openid
     * @param string $out_trade_no
     * @param mixed $total_fee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return string
     */
    public static function paymentPrepare(string $scene, $openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $key = self::prepayCacheKey($scene, $out_trade_no, $openid, $total_fee, $trade_type);
        $result = CacheService::get($key);
        if ($result) {
            return $result;
        }

        if ($scene === 'mini') {
            // 小程序：缓存 prepay_id 字符串
            $order = self::paymentOrderMini($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
            $order = self::withUnifyFields('mini', $order, $trade_type);
            $result = self::xmlRequest('mini', 'pay/unifiedorder', $order);
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                CacheService::set($key, $result['prepay_id'], 7000);
                return $result['prepay_id'];
            } else {
                if ($result['return_code'] == 'FAIL') {
                    exception('微信支付错误返回：' . $result['return_msg']);
                } else if (isset($result['err_code'])) {
                    exception('微信支付错误返回：' . $result['err_code_des']);
                } else {
                    exception('没有获取微信支付的预支付ID，请重新发起支付!');
                }
                exit;
            }
        }

        // 公众号 / APP：缓存整个 result 数组
        $order = self::paymentOrderMp($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
        $order = self::withUnifyFields($scene, $order, $trade_type);
        $result = self::xmlRequest($scene, 'pay/unifiedorder', $order);
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            CacheService::set($key, $result, 7000);
            return $result;
        } else {
            if ($result['return_code'] == 'FAIL') {
                exception('微信支付错误返回：' . $result['return_msg']);
            } else if (isset($result['err_code'])) {
                exception('微信支付错误返回：' . $result['err_code_des']);
            } else {
                exception('没有获取微信支付的预支付ID，请重新发起支付!');
            }
            exit;
        }
    }

    /**
     * 统一下单 - 新版小程序支付（minipay 通道）
     * @param string $scene
     * @param string|null $openid
     * @param string $out_trade_no
     * @param mixed $total_fee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param array $options
     * @return array
     */
    public static function newPaymentPrepare(string $scene, $openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $options = [])
    {
        $key = self::prepayCacheKey($scene, $out_trade_no, $openid, $total_fee, 'mini_shop');
        $result = CacheService::get($key);
        if ($result) {
            return $result;
        }

        if ($scene === 'mini') {
            // 小程序：缓存 payment_params
            $application = self::application('mini');
            if (trim((string)($application->getConfig()['mini_program']['secret'] ?? '')) === '') {
                throw new ApiException('微信支付V2新小程序支付需要配置小程序AppSecret');
            }
            $order = self::paymentOrderMini($openid, $out_trade_no, $total_fee, $attach, $body, $detail, 'JSAPI', $options);
            $result = $application->minipay->createorder($order);
            if ($result['errcode'] === 0) {
                CacheService::set($key, $result['payment_params'], 7000);
                return $result['payment_params'];
            } else {
                exception('微信支付错误返回：' . '[' . $result['errcode'] . ']' . $result['errmsg']);
                exit;
            }
        }

        // 公众号：缓存整个 result（含 return_code/result_code）
        $order = self::paymentOrderMp($openid, $out_trade_no, $total_fee, $attach, $body, $detail, 'JSAPI', $options);
        $result = self::application('mp')->minipay->createorder($order);
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            CacheService::set($key, $result, 7000);
            return $result;
        } else {
            if ($result['return_code'] == 'FAIL') {
                exception('微信支付错误返回：' . $result['return_msg']);
            } else if (isset($result['err_code'])) {
                exception('微信支付错误返回：' . $result['err_code_des']);
            } else {
                exception('没有获取微信支付的预支付ID，请重新发起支付!');
            }
            exit;
        }
    }

    /**
     * 获得 jsSdk 支付参数
     * @param string $scene
     * @param string|null $openid
     * @param string $out_trade_no
     * @param mixed $total_fee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array|string
     */
    public static function jsPay(string $scene, $openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $prepare = self::paymentPrepare($scene, $openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
        // 小程序场景 prepare 为 prepay_id 字符串；公众号场景 prepare 为 result 数组
        $prepayId = is_array($prepare) ? $prepare['prepay_id'] : $prepare;
        $app = self::paymentService($scene);
        return $app->getUtils()->buildSdkConfig($prepayId, (string) ($app->getConfig()->get('sub_appid') ?: $app->getConfig()->get('app_id')), 'MD5');
    }

    /**
     * 获得 jsSdk 支付参数 - 新版小程序支付
     * @param string $scene
     * @param string|null $openid
     * @param string $out_trade_no
     * @param mixed $total_fee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param array $options
     * @return array
     */
    public static function newJsPay(string $scene, $openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $options = [])
    {
        if ($scene === 'mini') {
            // 小程序：newPaymentPrepare 返回 payment_params，直接使用
            $config = self::newPaymentPrepare('mini', $openid, $out_trade_no, $total_fee, $attach, $body, $detail, $options);
            $config['timestamp'] = $config['timeStamp'];
            unset($config['timeStamp']);
            return $config;
        }
        // 公众号：newPaymentPrepare 返回 result，取 prepay_id
        $prepare = self::newPaymentPrepare('mp', $openid, $out_trade_no, $total_fee, $attach, $body, $detail, $options);
        $app = self::paymentService('mp');
        return $app->getUtils()->buildSdkConfig($prepare['prepay_id'], (string) $app->getConfig()->get('app_id'), 'MD5');
    }

    /**
     * 获得 App 支付参数
     * @param string $scene
     * @param string|null $openid
     * @param string $out_trade_no
     * @param mixed $total_fee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array|string
     */
    public static function appPay(string $scene, $openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'APP', $options = [])
    {
        $prepare = self::paymentPrepare($scene, $openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
        $prepayId = is_array($prepare) ? $prepare['prepay_id'] : $prepare;

        // V2 APP 支付参数(MD5 签名,保持与 4.x jssdk->appConfig 相同形状)
        $app = self::paymentService($scene);
        $params = [
            'appid' => (string) ($app->getConfig()->get('sub_appid') ?: $app->getConfig()->get('app_id')),
            'partnerid' => (string) ($app->getConfig()->get('sub_mch_id') ?: $app->getConfig()->get('mch_id')),
            'prepayid' => $prepayId,
            'package' => 'Sign=WXPay',
            'noncestr' => Str::random(16),
            'timestamp' => (string) time(),
        ];
        $params['sign'] = self::v2Sign($params, (string) $app->getConfig()->get('v2_secret_key'));

        return $params;
    }

    /**
     * 获得 native 支付参数（公众号场景专用）
     * @param string $scene
     * @param string|null $openid
     * @param string $out_trade_no
     * @param mixed $total_fee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array
     */
    public static function nativePay(string $scene, $openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'NATIVE', $options = [])
    {
        $data = self::paymentPrepare($scene, $openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
        $res = [];
        if ($data) {
            // paymentPrepare 在 mp 场景返回 result 数组
            $prepayId = is_array($data) ? ($data['code_url'] ?? '') : $data;
            $res['code_url'] = is_array($data) ? $prepayId : '';
            $res['invalid'] = time() + 60;
            // logo 由 Driver::create 从注入的 options 填充，SDK 自身不读取系统配置
            $res['logo'] = '';
        }
        return $res;
    }

    /**
     * 使用商户订单号退款
     * @param string $scene
     * @param string $orderNo
     * @param string $refundNo
     * @param float $totalFee
     * @param float|null $refundFee
     * @param string|null $opUserId
     * @param string $refundReason
     * @param string $type
     * @param string $refundAccount
     * @return array
     */
    public static function refund(string $scene, $orderNo, $refundNo, $totalFee, $refundFee = null, $opUserId = null, $refundReason = '', $type = 'out_trade_no', $refundAccount = 'REFUND_SOURCE_UNSETTLED_FUNDS')
    {
        $totalFee = floatval($totalFee);
        $refundFee = floatval($refundFee);
        $data = [
            'out_refund_no' => $refundNo,
            'total_fee' => (int)$totalFee,
            'refund_fee' => (int)$refundFee,
            'reason' => $refundReason,
            'refund_account' => $refundAccount,
        ];
        if ($type == 'out_trade_no') {
            $data['out_trade_no'] = $orderNo;
        } else {
            $data['transaction_id'] = $orderNo;
        }
        // 对齐 4.x Payment Refund Client：退款请求注入 appid
        $config = self::paymentService($scene)->getConfig();
        $data['appid'] = (string)$config->get('app_id');
        // 退款结果通知与支付通知共用入口，不能仅靠分钟级查单推进订单状态。
        $notifyUrl = trim((string)$config->get('notify_url'));
        if ($notifyUrl !== '') {
            $data['notify_url'] = $notifyUrl;
        }
        return self::xmlRequest($scene, 'secapi/pay/refund', $data, true);
    }

    /**
     * 使用商户订单号退款 - 小程序新版（minipay 通道）
     * @param string $scene
     * @param string $orderNo
     * @param float $totalFee
     * @param float|null $refundFee
     * @param array $opt
     * @return mixed
     */
    public static function miniRefund(string $scene, $orderNo, $totalFee, $refundFee = null, array $opt = [])
    {
        $totalFee = floatval($totalFee);
        $refundFee = floatval($refundFee);

        $order = [
            'openid' => $opt['open_id'] ?? '',
            'trade_no' => $opt['order_id'] ?? '',
            'transaction_id' => $opt['trade_no'] ?? '',
            'refund_no' => $opt['refund_id'] ?? $opt['refund_no'] ?? '',
            'total_amount' => $totalFee,
            'refund_amount' => $refundFee,
        ];
        return self::application($scene)->minipay->refundorder($order);
    }

    /**
     * 查询并核对新版支付订单。
     * @param string $orderNo 原订单号
     * @param array $context 原支付信息
     * @return array
     */
    public static function getMiniOrder(string $orderNo, array $context): array
    {
        $config = self::options('mini')['payment'] ?? [];
        if (($context['app_id'] ?? '') !== ($config['app_id'] ?? '')
            || ($context['mch_id'] ?? '') !== ($config['merchant_id'] ?? '')) {
            throw new ApiException('原新版支付商户或应用配置已变化');
        }
        $result = self::application('mini')->minipay->getorder($orderNo);
        $order = $result['order'] ?? [];
        if ((int)($result['errcode'] ?? -1) !== 0 || ($order['trade_no'] ?? '') !== $orderNo
            || ($order['mchid'] ?? '') !== ($context['mch_id'] ?? '')) {
            throw new ApiException('新版支付原订单查询或归属校验失败');
        }
        return $order;
    }

    /**
     * 查询新版支付固定退款号。
     * @param string $orderNo 原订单号
     * @param string $refundNo 固定退款号
     * @param array $context 原支付信息
     * @return array
     */
    public static function queryMiniRefund(string $orderNo, string $refundNo, array $context): array
    {
        $order = self::getMiniOrder($orderNo, $context);
        foreach ($order['refund_list'] ?? [] as $refund) {
            if (($refund['refund_no'] ?? '') === $refundNo) {
                return ['status' => strtoupper((string)($refund['result'] ?? 'PROCESSING')),
                    'refund_id' => $refund['refund_id'] ?? '', 'out_refund_no' => $refundNo];
            }
        }
        return ['status' => 'UNKNOWN'];
    }

    /**
     * 查询普通支付订单。
     * @param string $scene 原支付场景
     * @param string $orderNo 原订单号
     * @return array
     */
    public static function queryOrder(string $scene, string $orderNo): array
    {
        return self::xmlRequest($scene, 'pay/orderquery', ['appid' => self::paymentService($scene)->getConfig()->get('app_id'),
            'out_trade_no' => $orderNo]);
    }

    /**
     * 关闭普通支付订单。
     * @param string $scene 原支付场景
     * @param string $orderNo 原订单号
     * @return array
     */
    public static function closeOrder(string $scene, string $orderNo): array
    {
        return self::xmlRequest($scene, 'pay/closeorder', ['appid' => self::paymentService($scene)->getConfig()->get('app_id'),
            'out_trade_no' => $orderNo]);
    }

    /**
     * 根据订单号退款（带异常包装）
     * @param string $scene
     * @param string $orderNo
     * @param array $opt
     * @return bool
     */
    public static function payOrderRefund(string $scene, $orderNo, array $opt)
    {
        if (!isset($opt['pay_price'])) throw new AdminException('缺少pay_price');
        // 证书配置校验（插件化后从 eb_pay_channel.config_fields 读取）
        $channelConfig = self::channelConfig();
        if (($channelConfig['config']['pay_weixin_client_key'] ?? '') === '' || ($channelConfig['config']['pay_weixin_client_cert'] ?? '') === '') {
            throw new AdminException('请配置支付证书');
        }
        $totalFee = floatval(bcmul($opt['pay_price'], 100, 0));
        $refundFee = isset($opt['refund_price']) ? floatval(bcmul($opt['refund_price'], 100, 0)) : null;
        $refundReason = $opt['desc'] ?? '';
        $refundNo = $opt['refund_id'] ?? $orderNo;
        $opUserId = $opt['op_user_id'] ?? null;
        $type = $opt['type'] ?? 'out_trade_no';
        /*仅针对老资金流商户使用
        REFUND_SOURCE_UNSETTLED_FUNDS---未结算资金退款（默认使用未结算资金退款）
        REFUND_SOURCE_RECHARGE_FUNDS---可用余额退款*/
        $refundAccount = $opt['refund_account'] ?? 'REFUND_SOURCE_UNSETTLED_FUNDS';
        try {
            $res = self::refund($scene, $orderNo, $refundNo, $totalFee, $refundFee, $opUserId, $refundReason, $type, $refundAccount);
            if ($res['return_code'] == 'FAIL') throw new AdminException('退款失败:{:msg}', ['msg' => $res['return_msg']]);
            if (isset($res['err_code'])) throw new AdminException('退款失败:{:msg}', ['msg' => $res['err_code_des']]);
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
        return true;
    }

    /**
     * 企业付款到零钱（公众号场景专用）
     * @param string $scene
     * @param string $openid openid
     * @param string $orderId 订单号
     * @param string $amount 金额
     * @param string $desc 说明
     */
    public static function merchantPay(string $scene, string $openid, string $orderId, string $amount, string $desc)
    {
        $identity = self::options($scene)['payment'] ?? [];
        $options = self::options('notify');
        if (!empty($identity['sub_mch_id'])) throw new ApiException('旧企业付款不支持子商户身份，请使用已开通的直连转账渠道');
        if (!isset($options['payment']['cert_path'])) {
            throw new ApiException('企业微信支付到零钱需要支付证书，检测到您没有上传');
        }
        if (!$options['payment']['cert_path']) {
            throw new ApiException('企业微信支付到零钱需要支付证书，检测到您没有上传');
        }
        $merchantPayData = [
            'mch_appid' => $identity['app_id'] ?? '',
            'mchid' => self::paymentService('notify')->getConfig()->get('mch_id'),
            'partner_trade_no' => $orderId, //随机字符串作为订单号，跟红包和支付一个概念。
            'openid' => $openid, //收款人的openid
            'check_name' => 'NO_CHECK',  //文档中有三种校验实名的方法 NO_CHECK OPTION_CHECK FORCE_CHECK
            'amount' => (int)bcmul($amount, '100', 0),  //单位为分
            'desc' => $desc,
            'spbill_create_ip' => request()->ip(),  //发起交易的IP地址
        ];
        $result = self::xmlRequest('notify', 'mmpaymkttransfers/promotion/transfers', $merchantPayData, true);
        if (($result['return_code'] ?? '') === 'SUCCESS' && ($result['result_code'] ?? '') === 'SUCCESS' && !empty($result['payment_no'])) {
            return $result;
        } else {
            throw new ApiException($result['err_code_des'] ?? '企业付款到零钱失败，请稍后再试');
        }
    }

    /**
     * 旧企业付款超时或处理中时按固定单号查询，不换号重付。
     * @param string $orderNo 原付款单号
     * @param array $context 原付款身份
     * @return array
     */
    public static function queryEnterpriseTransfer(string $orderNo, array $context): array
    {
        $config = self::options('notify')['payment'] ?? [];
        $payment = self::channelConfig()['config'];
        $appField = ($context['platform'] ?? '') === 'wechat_mini' ? 'routine_appId' : (($context['platform'] ?? '') === 'app' ? 'wechat_app_appid' : 'wechat_appid');
        if (!empty($payment['mer_type']) || ($config['merchant_id'] ?? '') !== ($context['mch_id'] ?? '')
            || trim((string)($payment[$appField] ?? '')) !== ($context['app_id'] ?? '')) throw new ApiException('原企业付款商户或应用已变化');
        $result = self::xmlRequest('notify', 'mmpaymkttransfers/gettransferinfo', [
            'appid' => $context['app_id'], 'partner_trade_no' => $orderNo,
        ], true);
        if (($result['return_code'] ?? '') !== 'SUCCESS' || ($result['result_code'] ?? '') !== 'SUCCESS') throw new ApiException('企业付款查单尚未确认，请稍后重试');
        return $result;
    }

    /**
     * 微信 V2 支付及退款通知入口
     *
     * 架构说明：driver 层不可反向依赖 app 层（PayNotifyServices/PayServices 均在 app 层），
     * 故统一通过 Event::until('Notify', ...) 将回调路由交给 app 层监听器处理。
     * NotifyListener 监听器内部按 attach 字段分发到 PayNotifyServices::wechat{Attach} 方法，
     * 与原 Hook 调用路径等价（监听器实现见 app/listener/pay/NotifyListener.php）。
     *
     * V2 回调为 XML 推送,应答也必须是 XML(6.x Server 的默认 JSON 应答不适用)。
     *
     * @param string $scene 场景：notify / mini / mp / app
     * @return \think\Response
     */
    public static function handleNotify(string $scene = 'mp')
    {
        $app = self::paymentService($scene);
        $app->setRequestFromSymfonyRequest(Support::symfonyRequest());
        $server = $app->getServer();

        $server->with(function (Message $message, \Closure $next) use ($scene) {
            $reply = static function (string $code, string $msg = 'OK') {
                return new Psr7Response(200, ['Content-Type' => 'text/xml'], Xml::build([
                    'return_code' => $code,
                    'return_msg' => $msg,
                ]));
            };

            // EasyWeChat 已解密 req_info；退款通知没有普通支付签名，须查验签名结果后分发。
            $original = Xml::parse($message->getOriginalContents());
            if (isset($original['req_info'])) {
                try {
                    $notify = self::verifyRefundNotification($scene, $original, $message->toArray());
                    $result = Event::until('Notify', [$notify, self::WEIXIN_PAY_CHANNEL_KEY]);
                    return $result === true ? $reply('SUCCESS') : $reply('FAIL', '处理失败');
                } catch (\Throwable $e) {
                    \think\facade\Log::error('[WechatV2Refund] 通知处理失败 type=' . get_class($e));
                    return $reply('FAIL', '退款结果校验或处理失败');
                }
            }

            // 校验 V2 回调签名（4.x Payment Notify Handler::validate 行为，6.x Server 不校验）
            $sign = $message['sign'] ?? '';
            try {
                $valid = is_string($sign) && $sign !== '' && hash_equals(
                    self::v2Sign($message->toArray(), (string)self::paymentService($scene)->getConfig()->get('v2_secret_key')), $sign);
            } catch (\Throwable $e) {
                $valid = false;
            }
            if (!$valid) {
                return $reply('FAIL', 'sign error');
            }

            $data = $message->toArray();
            $data['payment_mode'] = 'v2';
            $data['amount'] = ['total' => isset($data['total_fee']) && ctype_digit((string)$data['total_fee'])
                ? (int)$data['total_fee'] : null, 'currency' => $data['fee_type'] ?? 'CNY'];
            $data['mchid'] = $data['mch_id'] ?? '';
            $data['sub_mchid'] = $data['sub_mch_id'] ?? '';
            $data['openid'] = $data['sub_openid'] ?? $data['openid'] ?? '';

            if ($scene === 'mini') {
                // 小程序场景：原 MiniProgramService::handleNotify 逻辑
                // return_code=FAIL 的通知没有 result_code 字段,统一 ?? 容错
                if (($message['return_code'] ?? '') === 'SUCCESS' && ($message['result_code'] ?? '') === 'SUCCESS' && isset($message['out_trade_no'])) {
                    if (isset($message['attach']) && $message['attach']) {
                        $result = Event::until('Notify', [$data, self::WEIXIN_PAY_CHANNEL_KEY]);
                        return $result === true ? $reply('SUCCESS') : $reply('FAIL', '处理失败');
                    }
                }
                return $reply('SUCCESS');
            }

            // 公众号场景：原 WechatService::handleNotify 逻辑
            if (($message['return_code'] ?? '') === 'SUCCESS' && ($message['result_code'] ?? '') === 'SUCCESS') {
                $result = Event::until('Notify', [$data, self::WEIXIN_PAY_CHANNEL_KEY]);
                return $result === true ? $reply('SUCCESS') : $reply('FAIL', '处理失败');
            }
            return $reply('SUCCESS');
        });

        try {
            return Support::toThinkResponse($server->serve());
        } catch (\Throwable $e) {
            \think\facade\Log::error('[WechatV2] 通知处理失败 type=' . get_class($e));
            return Support::toThinkResponse(new Psr7Response(500, ['Content-Type' => 'text/xml'], Xml::build([
                'return_code' => 'FAIL', 'return_msg' => '处理失败',
            ])));
        }
    }

    /**
     * 核对加密退款通知与同一退款号的已验签查询结果，防止无认证 XML 提前推进退款。
     * @param string $scene 支付场景
     * @param array $original 原始 XML 外层字段
     * @param array $refund 已由 EasyWeChat 解密的退款字段
     * @return array 统一退款成功通知
     */
    protected static function verifyRefundNotification(string $scene, array $original, array $refund): array
    {
        $config = self::paymentService($scene)->getConfig();
        $merchantId = (string)$config->get('mch_id');
        $subMerchantId = (string)$config->get('sub_mch_id');
        if (($original['return_code'] ?? '') !== 'SUCCESS' || empty($original['req_info'])
            || $merchantId === '' || ($original['mch_id'] ?? '') !== $merchantId
            || !is_string($original['appid'] ?? null) || $original['appid'] === ''
            || ($subMerchantId !== '' && $original['appid'] !== (string)$config->get('app_id'))
            || (isset($original['sub_mch_id']) && $original['sub_mch_id'] !== $subMerchantId)
            || ($refund['refund_status'] ?? '') !== 'SUCCESS') {
            throw new ApiException('V2退款通知的商户或状态不一致');
        }
        foreach (['out_trade_no', 'transaction_id', 'out_refund_no', 'refund_id'] as $field) {
            if (!is_string($refund[$field] ?? null) || $refund[$field] === '') {
                throw new ApiException('V2退款通知缺少必要单号');
            }
        }
        foreach (['total_fee', 'refund_fee'] as $field) {
            if (!is_string($refund[$field] ?? null) || !preg_match('/^\d{1,10}$/D', $refund[$field])) {
                throw new ApiException('V2退款通知金额无效');
            }
        }
        // ECB 加密不提供完整性认证，必须用验签查单结果确认订单、交易、退款号与金额。
        $query = self::xmlRequest($scene, 'pay/refundquery', [
            'appid' => $original['appid'],
            'out_refund_no' => $refund['out_refund_no'],
            // 共用通知入口的公众号子应用不一定是原支付应用，查询按子商户与固定退款号定位。
            'sub_appid' => null,
        ]);
        if (($query['return_code'] ?? '') !== 'SUCCESS' || ($query['result_code'] ?? '') !== 'SUCCESS'
            || ($query['mch_id'] ?? '') !== $merchantId || ($query['appid'] ?? '') !== $original['appid']
            || ($query['sub_mch_id'] ?? '') !== $subMerchantId
            || ($query['fee_type'] ?? 'CNY') !== 'CNY'
            || ($query['out_trade_no'] ?? '') !== $refund['out_trade_no']
            || ($query['transaction_id'] ?? '') !== $refund['transaction_id']
            || (string)($query['total_fee'] ?? '') !== $refund['total_fee']) {
            throw new ApiException('V2退款查询与通知身份或订单不一致');
        }
        // 查询可能包含多笔退款，按固定退款号定位，不能直接取第零笔。
        foreach ($query as $field => $value) {
            if (!preg_match('/^out_refund_no_(\d+)$/D', (string)$field, $match) || $value !== $refund['out_refund_no']) {
                continue;
            }
            $index = $match[1];
            if (($query['refund_status_' . $index] ?? '') !== 'SUCCESS'
                || ($query['refund_id_' . $index] ?? '') !== $refund['refund_id']
                || (string)($query['refund_fee_' . $index] ?? '') !== $refund['refund_fee']) {
                throw new ApiException('V2退款尚未确认成功或退款金额不一致');
            }
            return [
                'event_type' => 'REFUND.SUCCESS',
                'payment_mode' => 'v2',
                'refund_status' => 'SUCCESS',
                'out_trade_no' => $refund['out_trade_no'],
                'transaction_id' => $refund['transaction_id'],
                'out_refund_no' => $refund['out_refund_no'],
                'refund_id' => $refund['refund_id'],
                'mchid' => $merchantId,
                'sub_mchid' => $subMerchantId,
                'appid' => $query['appid'],
                'sub_appid' => $query['sub_appid'] ?? '',
                'amount' => ['total' => (int)$refund['total_fee'], 'refund' => (int)$refund['refund_fee'], 'currency' => 'CNY'],
            ];
        }
        throw new ApiException('V2退款查询未找到对应退款号');
    }

    /**
     * 查询退款订单（公众号场景专用）
     * @param string $scene
     * @param string $outTradeNo
     * @param string $type 查询字段：out_trade_no / transaction_id / out_refund_no / refund_id
     * @return array
     */
    public static function queryRefund(string $scene, string $outTradeNo, string $type = 'out_trade_no')
    {
        // 对齐 4.x Payment Refund Client::query：查询请求注入 appid
        $data = ['appid' => (string) self::paymentService($scene)->getConfig()->get('app_id')];
        // 系统固定退款号使用 out_refund_no，不能作为微信生成的 refund_id 传入。
        if (in_array($type, ['out_trade_no', 'transaction_id', 'out_refund_no'], true)) {
            $data[$type] = $outTradeNo;
        } else {
            $data['refund_id'] = $outTradeNo;
        }
        return self::xmlRequest($scene, 'pay/refundquery', $data);
    }
}
