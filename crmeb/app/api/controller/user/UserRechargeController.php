<?php

namespace app\api\controller\user;

use app\models\system\SystemGroupData;
use app\models\user\UserRecharge;
use app\Request;
use crmeb\services\GroupDataService;
use crmeb\services\SystemConfigService;
use crmeb\services\UtilService;

/**
 * 充值类
 * Class UserRechargeController
 * @package app\api\controller\user
 */
class UserRechargeController
{

    /**
     * 小程序充值
     *
     * @param Request $request
     * @return mixed
     */
    public function routine(Request $request)
    {
        list($price, $recharId, $type) = UtilService::postMore([
            [['price', 'f'], 0],
            [['rechar_id', 'd'], 0],
            ['type', 0]
        ], $request, true);
        if (!$price || $price <= 0) return app('json')->fail('参数错误');
        $storeMinRecharge = sys_config('store_user_min_recharge');
        if ($price < $storeMinRecharge) return app('json')->fail('充值金额不能低于' . $storeMinRecharge);
        switch ((int)$type) {
            case 0: //支付充值余额
                $paid_price = 0;
                if ($recharId) {
                    $data = SystemGroupData::getDateValue($recharId);
                    if ($data === false) {
                        return app('json')->fail('您选择的充值方式已下架!');
                    } else {
                        $paid_price = $data['give_money'] ?? 0;
                    }
                }
                $rechargeOrder = UserRecharge::addRecharge($request->uid(), $price, 'routine', $paid_price);
                if (!$rechargeOrder) return app('json')->fail('充值订单生成失败!');
                try {
                    return app('json')->successful(UserRecharge::jsPay($rechargeOrder));
                } catch (\Exception $e) {
                    return app('json')->fail($e->getMessage());
                }
                break;
            case 1: //佣金转入余额
                if (UserRecharge::importNowMoney($request->uid(), $price))
                    return app('json')->successful('转入余额成功');
                else
                    return app('json')->fail(UserRecharge::getErrorInfo());
                break;
            default:
                return app('json')->fail('缺少参数');
                break;
        }
    }

    /**
     * 公众号充值
     *
     * @param Request $request
     * @return mixed
     */
    public function wechat(Request $request)
    {
        list($price, $recharId, $from, $type) = UtilService::postMore([
            [['price', 'f'], 0],
            [['rechar_id', 'd'], 0],
            ['from', 'weixin'],
            ['type', 0]
        ], $request, true);
        if (!$price || $price <= 0) return app('json')->fail('参数错误');
        $storeMinRecharge = sys_config('store_user_min_recharge');
        if ($price < $storeMinRecharge) return app('json')->fail('充值金额不能低于' . $storeMinRecharge);
        switch ((int)$type) {
            case 0: //支付充值余额
                $paid_price = 0;
                if ($recharId) {
                    $data = SystemGroupData::getDateValue($recharId);
                    if ($data === false) {
                        return app('json')->fail('您选择的充值方式已下架!');
                    } else {
                        $paid_price = $data['give_money'] ?? 0;
                    }
                }
                $rechargeOrder = UserRecharge::addRecharge($request->uid(), $price, 'weixin', $paid_price);
                if (!$rechargeOrder) return app('json')->fail('充值订单生成失败!');
                try {
                    if ($from == 'weixinh5') {
                        $recharge = UserRecharge::wxH5Pay($rechargeOrder);
                    } else {
                        $recharge = UserRecharge::wxPay($rechargeOrder);
                    }
                } catch (\Exception $e) {
                    return app('json')->fail($e->getMessage());
                }
                return app('json')->successful(['type' => $from, 'data' => $recharge]);
                break;
            case 1: //佣金转入余额
                if (UserRecharge::importNowMoney($request->uid(), $price))
                    return app('json')->successful('转入余额成功');
                else
                    return app('json')->fail(UserRecharge::getErrorInfo());
                break;
            default:
                return app('json')->fail('缺少参数');
                break;
        }
    }

    /**
     * 充值额度选择
     * @return mixed
     */
    public function index()
    {
        $rechargeQuota = sys_data('user_recharge_quota') ?? [];
        $data['recharge_quota'] = $rechargeQuota;
        $recharge_attention = sys_config('recharge_attention');
        $recharge_attention = explode("\n", $recharge_attention);
        $data['recharge_attention'] = $recharge_attention;
        return app('json')->successful($data);
    }
}