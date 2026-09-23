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
namespace crmeb\services\easywechat\orderShipping;

use crmeb\services\easywechat\Application;
use crmeb\services\pay\PayChannelConfig;
use crmeb\exceptions\PayException;
use EasyWeChat\Kernel\Exceptions\HttpException;

class MiniOrderService
{
    /**
     * @var Application
     */
    protected static array $instances = [];

    /**
     * @param array $config
     * @return array[]
     *
     * @date 2023/05/09
     * @author yyw
     */
    protected static function options(array $config = [])
    {
        $wechat = sys_config_more(['routine_appId', 'routine_appsecret', 'wechat_token', 'wechat_encodingaeskey']);
        // 支付配置统一存储于 eb_pay_channel.config_fields，不再读取 system_config
        $payment = PayChannelConfig::get($config['channel_key'] ?? 'weixin');
        $appid = trim((string)($payment['routine_appId'] ?? $wechat['routine_appId'] ?? ''));
        $secret = trim((string)($payment['routine_appsecret'] ?? $wechat['routine_appsecret'] ?? ''));
        $miniShop = isset($config['mode']) ? $config['mode'] === 'mini_shop' : !empty($payment['pay_new_weixin_open']);
        $mchid = trim((string)($payment[$miniShop ? 'pay_new_weixin_mchid' : 'pay_weixin_mchid'] ?? ''));
        $subMchid = !$miniShop && !empty($payment['mer_type']) ? trim((string)($payment['pay_sub_merchant_id'] ?? '')) : '';
        if ($appid === '' || $secret === '' || $mchid === ''
            || (!empty($config['app_id']) && $config['app_id'] !== $appid)
            || (!empty($config['mch_id']) && $config['mch_id'] !== $mchid)
            || (isset($config['sub_mch_id']) && $config['sub_mch_id'] !== $subMchid)) {
            throw new PayException('小程序发货配置与原支付应用或商户不一致，请恢复原配置');
        }
        return [
            'mini_program' => [
                'app_id' => $appid,
                'secret' => $secret,
                'merchant_id' => $subMchid ?: $mchid,
            ]
        ];
    }

    /**
     * 初始化
     * @param bool $cache
     * @return Application
     */
    protected static function application($cache = false, array $context = [])
    {
        $options = self::options($context);
        $key = hash('sha256', json_encode($options, JSON_THROW_ON_ERROR));
        if (!isset(self::$instances[$key]) || $cache === true) self::$instances[$key] = new Application($options);
        return self::$instances[$key];
    }

    /**
     * 按原支付身份构建发货客户端。
     * @param array $context 原支付身份
     * @return OrderClient
     */
    protected static function order(array $context = []): OrderClient
    {
        $config = self::options($context);
        $miniProgram = self::application(false, $context)->getMiniProgramApp();
        return new OrderClient(
            $miniProgram->getClient(),
            (string) ($config['mini_program']['app_id'] ?? ''),
            (string) ($config['mini_program']['merchant_id'] ?? '')
        );
    }


    /**
     * 上传订单
     * @param string $out_trade_no 订单号(商城订单好)
     * @param int $logistics_type 物流模式，发货方式枚举值：1、实体物流配送采用快递公司进行实体物流配送形式 2、同城配送 3、虚拟商品，虚拟商品，例如话费充值，点卡等，无实体配送形式 4、用户自提
     * @param array $shipping_list 物流信息列表，发货物流单列表，支持统一发货（单个物流单）和分拆发货（多个物流单）两种模式，多重性: [1, 10]
     * @param string $payer_openid 支付者，支付者信息
     * @param int $delivery_mode 发货模式，发货模式枚举值：1、UNIFIED_DELIVERY（统一发货）2、SPLIT_DELIVERY（分拆发货） 示例值: UNIFIED_DELIVERY
     * @param bool $is_all_delivered 分拆发货模式时必填，用于标识分拆发货模式下是否已全部发货完成，只有全部发货完成的情况下才会向用户推送发货完成通知。示例值: true/false
     * @return array
     *
     * @throws HttpException
     * @date 2023/05/09
     * @author yyw
     */
    public static function shippingByTradeNo(string $out_trade_no, int $logistics_type, array $shipping_list, string $payer_openid, string $path, int $delivery_mode = 1, bool $is_all_delivered = true, array $context = [])
    {
        return self::order($context)->shippingByTradeNo($out_trade_no, $logistics_type, $shipping_list, $payer_openid, $path, $delivery_mode, $is_all_delivered);
    }

    /**
     * 获取小程序发货管理订单列表
     * @param array $params
     * @return array
     * @throws HttpException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/8/14
     */
    public static function shippingOrderList($params = [], array $context = [])
    {
        return self::order($context)->shippingOrderList($params);
    }

    /**
     * 合单
     * @param string $out_trade_no
     * @param int $logistics_type
     * @param array $sub_orders
     * @param string $payer_openid
     * @param int $delivery_mode
     * @param bool $is_all_delivered
     * @return array
     * @throws HttpException
     *
     * @date 2023/05/10
     * @author yyw
     */
    public static function combinedShippingByTradeNo(string $out_trade_no, int $logistics_type, array $sub_orders, string $payer_openid, int $delivery_mode = 2, bool $is_all_delivered = false)
    {
        return self::order()->combinedShippingByTradeNo($out_trade_no, $logistics_type, $sub_orders, $payer_openid, $delivery_mode, $is_all_delivered);
    }

    /**
     * 签收通知
     * @param string $merchant_trade_no
     * @param string $received_time
     * @return array
     *
     * @date 2023/05/09
     * @author yyw
     */
    public static function notifyConfirmByTradeNo(string $merchant_trade_no, string $received_time, array $context = [])
    {
        return self::order($context)->notifyConfirmByTradeNo($merchant_trade_no, $received_time);
    }

    /**
     * 判断是否开通
     * @return bool
     * @throws HttpException
     *
     * @date 2023/05/17
     * @author yyw
     */
    public static function isManaged()
    {
        return self::order()->checkManaged();
    }


    /**
     * 设置小修跳转路径
     * @param $path
     * @return array
     * @throws HttpException
     *
     * @date 2023/05/10
     * @author yyw
     */
    public static function setMesJumpPathAndCheck($path)
    {
        return self::order()->setMesJumpPathAndCheck($path);
    }


}
