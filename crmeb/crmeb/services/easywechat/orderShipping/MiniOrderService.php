<?php

namespace crmeb\services\easywechat\orderShipping;

use crmeb\services\easywechat\Application;
use crmeb\services\SystemConfigService;
use EasyWeChat\Core\Exceptions\HttpException;

class MiniOrderService
{
    /**
     * @var Application
     */
    protected static $instance;

    /**
     * @param array $config
     * @return array[]
     *
     * @date 2023/05/09
     * @author yyw
     */
    protected static function options(array $config = [])
    {
        $payment = SystemConfigService::more(['routine_appId', 'routine_appsecret', 'pay_weixin_mchid', 'pay_new_weixin_open', 'pay_new_weixin_mchid', 'wechat_token', 'wechat_encodingaeskey']);
        return [
            'mini_program' => [
                'app_id' => $payment['routine_appId'] ?? '',
                'secret' => $payment['routine_appsecret'] ?? '',
                'merchant_id' => empty($payment['pay_new_weixin_open']) ? trim($payment['pay_weixin_mchid']) : trim($payment['pay_new_weixin_mchid']),
            ]
        ];
    }

    /**
     * 初始化
     * @param bool $cache
     * @return Application
     */
    protected static function application($cache = false)
    {
        (self::$instance === null || $cache === true) && (self::$instance = new Application(self::options()));
        return self::$instance;
    }

    protected static function order()
    {
        return self::application()->order_ship;
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
    public static function shippingByTradeNo(string $out_trade_no, int $logistics_type, array $shipping_list, string $payer_openid, string $path, int $delivery_mode = 1, bool $is_all_delivered = true)
    {
        return self::order()->shippingByTradeNo($out_trade_no, $logistics_type, $shipping_list, $payer_openid, $path, $delivery_mode, $is_all_delivered);
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
    public static function notifyConfirmByTradeNo(string $merchant_trade_no, string $received_time)
    {
        return self::order()->notifyConfirmByTradeNo($merchant_trade_no, $received_time);
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
