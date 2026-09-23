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
declare (strict_types=1);

namespace app\services\pay;


use crmeb\exceptions\ApiException;

/**
 * 充值支付服务
 * 支付代码中不做平台判断，平台由调用方通过 $platform 参数传入
 * Class RechargeServices
 * @package app\services\pay
 */
class RechargeServices
{
     /**
      * @var PayServices
      */
    protected $pay;

    /**
     * RechargeServices constructor.
     * @param PayServices $pay
     */
    public function __construct(PayServices $pay)
    {
        $this->pay = $pay;
    }

    /**
     * 发起充值支付
     * @param mixed $recharge 充值订单数据（model 实例或数组，支持 ArrayAccess）
     * @param string $platform 当前平台标识（由调用方传入，如 routine/weixinh5/h5/app/pc）
     * @param string $openid 用户 openid（需要时由调用方传入）
     * @return array
     */
    public function recharge($recharge, string $platform = '', string $openid = ''): array
    {
        if (!$recharge) {
            throw new ApiException('订单不存在');
        }
        if ($recharge['paid'] == 1) {
            throw new ApiException('订单已支付');
        }
        $payType = $recharge['recharge_type'];
        $platform = $platform ?: \app\services\PlatformDetector::detect();

        // 校验支付方式是否为已启用渠道（从 eb_pay_channel 配置表动态读取，禁止硬编码）
        $payTypes = PayServices::getPayType();
        if (!isset($payTypes[$payType])) {
            throw new ApiException('不支持该类型方式');
        }

        // 发起支付
        $options = PayCredentialService::getCredentials((int)$recharge['uid'], $payType);
        $options['platform'] = $platform;
        $options['payer_uid'] = (int)$recharge['uid'];
        if ($openid) {
            $options['openid'] = $openid;
        }
        $res = $this->pay->pay($payType, $recharge['order_id'], $recharge['price'], 'user_recharge', '用户充值', $options);
        if (is_array($res) && isset($res['mweb_url'])) {
            $res['h5_url'] = $res['mweb_url'];
        }

        // 从 plugin.json 动态获取支付返回类型（传入平台标识，不做平台判断）
        $payStatus = $this->pay->getPayStatusType($payType, $platform);

        // 通联支付在微信场景下需要额外返回 pay_url
        $payUrl = '';
        if ($payType === PayServices::ALLIN_PAY && $platform === 'wechat_mp') {
            $payUrl = $this->pay->getChannelUrl(PayServices::ALLIN_PAY, 'h5_union_pay');
        }

        return ['pay_url' => $payUrl, 'jsConfig' => $res, 'pay_key' => md5($recharge['order_id']), 'order_id' => $recharge['order_id'], 'pay_type' => strtoupper($payStatus)];
    }

}
