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


use app\model\user\UserRecharge;
use app\services\user\UserRechargeServices;
use app\services\wechat\WechatUserServices;
use crmeb\exceptions\ApiException;

/**
 *
 * Class RechargeServices
 * @package app\services\pay
 */
class RechargeServices
{
    protected $pay;

    /**
     * RechargeServices constructor.
     * @param PayServices $pay
     */
    public function __construct(PayServices $pay)
    {
        $this->pay = $pay;
    }

    public function recharge(UserRecharge $recharge)
    {
        if (!$recharge) {
            throw new ApiException(410173);
        }
        if ($recharge['paid'] == 1) {
            throw new ApiException(410174);
        }
        $userType = '';
        switch ($recharge['recharge_type']) {
            case 'weixin':
            case 'weixinh5':
                $userType = 'wechat';
                break;
            case 'routine':
                $userType = 'routine';
                break;
            case PayServices::ALIAPY_PAY:
                $userType = PayServices::ALIAPY_PAY;
                break;
        }

        $userType = get_pay_type($userType);

        if (!$userType) {
            throw new ApiException(410278);
        }
        /** @var WechatUserServices $wechatUser */
        $wechatUser = app()->make(WechatUserServices::class);
        $openid = $wechatUser->uidToOpenid((int)$recharge['uid'], $userType);
        if (in_array($recharge['recharge_type'], ['weixin', 'routine']) && !request()->isApp()) {
            if (!$openid) {
                throw new ApiException(410275);
            }
        } else {
            $openid = '';
        }

        $res = $this->pay->pay($recharge['recharge_type'], $openid, $recharge['order_id'], $recharge['price'], 'user_recharge', '用户充值');

        if ($userType === PayServices::ALLIN_PAY) {
            $res['pay_type'] = $userType;
        }

        return $res;
    }

}
