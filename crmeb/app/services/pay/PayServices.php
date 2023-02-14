<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\pay;

use crmeb\exceptions\ApiException;
use crmeb\services\pay\Pay;

/**
 * 支付统一入口
 * Class PayServices
 * @package app\services\pay
 */
class PayServices
{
    //微信支付类型
    const WEIXIN_PAY = 'weixin';

    //余额支付
    const YUE_PAY = 'yue';

    //线下支付
    const OFFLINE_PAY = 'offline';

    //支付宝
    const ALIAPY_PAY = 'alipay';

    //通联支付
    const ALLIN_PAY = 'allinpay';

    //好友代付
    const FRIEND = 'friend';

    //支付方式
    const PAY_TYPE = [
        PayServices::WEIXIN_PAY => '微信支付',
        PayServices::YUE_PAY => '余额支付',
        PayServices::OFFLINE_PAY => '线下支付',
        PayServices::ALIAPY_PAY => '支付宝',
        PayServices::FRIEND => '好友代付',
        PayServices::ALLIN_PAY => '通联支付'
    ];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param string $key
     * @param $value
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/16
     */
    public function setOption(string $key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @param array $value
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/16
     */
    public function setOptions(array $value)
    {
        $this->options = $value;
        return $this;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/16
     */
    protected function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * 发起支付
     * @param string $payType
     * @param string $openid
     * @param string $orderId
     * @param string $price
     * @param string $successAction
     * @param string $body
     * @return array|string
     */
    public function pay(string $payType, string $openid, string $orderId, string $price, string $successAction, string $body, bool $isCode = false)
    {
        try {

            //这些全都是微信支付
            if (in_array($payType, ['routine', 'weixinh5', 'weixin', 'pc', 'store'])) {
                $payType = 'wechat_pay';
                //判断是否使用v3
                if (sys_config('pay_wechat_type') == 1) {
                    $payType = 'v3_wechat_pay';
                }
            }

            if ($payType == 'alipay') {
                $payType = 'ali_pay';
            }


            $options = [];
            if (self::ALLIN_PAY === $payType) {
                $options['returl'] = $this->getOption('returl');
                if ($options['returl']) {
                    $options['returl'] = str_replace('http://', 'https://', $options['returl']);
                }
                $options['is_wechat'] = $this->getOption('is_wechat', false);
                $options['appid'] = sys_config('routine_appId');
                $payType = 'allin_pay';
            }

            /** @var Pay $pay */
            $pay = app()->make(Pay::class, [$payType]);


            return $pay->create($orderId, $price, $successAction, $body, '', ['openid' => $openid, 'isCode' => $isCode, 'pay_new_weixin_open' => (bool)sys_config('pay_new_weixin_open')] + $options);

        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'api unauthorized rid') !== false) {
                throw new ApiException('请在微信支付配置中将小程序商户号选择改为商户号绑定');
            }
            throw new ApiException($e->getMessage());
        }
    }
}
