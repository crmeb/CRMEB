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
namespace app\api\controller\v1\user;

use app\Request;
use app\services\pay\PayServices;
use app\services\user\UserRechargeServices;

/**
 * 充值类
 * Class UserRechargeController
 * @package app\api\controller\user
 */
class UserRechargeController
{
    protected $services = NUll;

    /**
     * UserRechargeController constructor.
     * @param UserRechargeServices $services
     */
    public function __construct(UserRechargeServices $services)
    {
        $this->services = $services;
    }

    /**
     * 用户充值
     * @param Request $request
     * @return mixed
     */
    public function recharge(Request $request)
    {
        [$price, $recharId, $type, $from] = $request->postMore([
            ['price', 0],
            ['rechar_id', 0],
            ['type', 0],
            ['from', 'weixin']
        ], true);
        if (!$price || $price <= 0) return app('json')->fail(410122);
        if (!in_array($type, [0, 1])) return app('json')->fail(410123);
        if (!in_array($from, [PayServices::WEIXIN_PAY, 'weixinh5', 'routine', PayServices::ALIAPY_PAY])) return app('json')->fail(410123);
        $storeMinRecharge = sys_config('store_user_min_recharge');
        if ($price < $storeMinRecharge) return app('json')->fail(410124, null, ['money' => $storeMinRecharge]);
        $uid = (int)$request->uid();
        $re = $this->services->recharge($uid, $price, $recharId, $type, $from);
        if ($re) {
            unset($re['msg']);
            return app('json')->success(410125, $re);
        }
        return app('json')->fail(410126);
    }

    /**
     * 小程序充值
     * @param Request $request
     * @return mixed
     */
    public function routine(Request $request)
    {
        list($price, $recharId, $type) = $request->postMore([['price', 0], ['rechar_id', 0], ['type', 0]], true);
        if (!$price || $price <= 0) return app('json')->fail(410122);
        $storeMinRecharge = sys_config('store_user_min_recharge');
        if ($price < $storeMinRecharge) return app('json')->fail(410124, null, ['money' => $storeMinRecharge]);
        $from = 'routine';
        $uid = (int)$request->uid();
        $re = $this->services->recharge($uid, $price, $recharId, $type, $from);
        if ($re) {
            unset($re['msg']);
            return app('json')->success(410125, $re['data']);
        }
        return app('json')->fail(410126);
    }

    /**
     * 公众号充值
     * @param Request $request
     * @return mixed
     */
    public function wechat(Request $request)
    {
        list($price, $recharId, $from, $type) = $request->postMore([['price', 0], ['rechar_id', 0], ['from', 'weixin'], ['type', 0]], true);
        if (!$price || $price <= 0) return app('json')->fail(410122);
        $storeMinRecharge = sys_config('store_user_min_recharge');
        if ($price < $storeMinRecharge) return app('json')->fail(410124, null, ['money' => $storeMinRecharge]);
        $uid = (int)$request->uid();
        $re = $this->services->recharge($uid, $price, $recharId, $type, $from);
        if ($re) {
            unset($re['msg']);
            return app('json')->success(410125, $re);
        }
        return app('json')->fail(410126);
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
        return app('json')->success($data);
    }
}
