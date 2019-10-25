<?php
namespace app\api\controller\user;

use app\models\user\UserRecharge;
use app\Request;
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
        list($price,$type) = UtilService::postMore([['price',0],['type',0]], $request, true);
        if(!$price || $price <=0) return app('json')->fail('参数错误');
        $storeMinRecharge = SystemConfigService::get('store_user_min_recharge');
        if($price < $storeMinRecharge) return app('json')->fail('充值金额不能低于'.$storeMinRecharge);
        switch ((int)$type){
            case 0: //支付充值余额
                $rechargeOrder = UserRecharge::addRecharge($request->uid(),$price,'routine');
                if(!$rechargeOrder) return app('json')->fail('充值订单生成失败!');
                try{
                    return app('json')->successful(UserRecharge::jsPay($rechargeOrder));
                }catch (\Exception $e){
                    return app('json')->fail($e->getMessage());
                }
                break;
            case 1: //佣金转入余额
                if(UserRecharge::importNowMoney($request->uid(),$price))
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
        list($price, $from,$type) = UtilService::postMore([['price',0], ['from','weixin'],['type',0]], $request, true);
        if(!$price || $price <=0) return app('json')->fail('参数错误');
        $storeMinRecharge = SystemConfigService::get('store_user_min_recharge');
        if($price < $storeMinRecharge) return app('json')->fail('充值金额不能低于'.$storeMinRecharge);
        switch ((int)$type){
            case 0: //支付充值余额
                $rechargeOrder = UserRecharge::addRecharge($request->uid(),$price,'weixin');
                if(!$rechargeOrder) return app('json')->fail('充值订单生成失败!');
                try{
                    if($from == 'weixinh5'){
                        $recharge = UserRecharge::wxH5Pay($rechargeOrder);
                    }else{
                        $recharge = UserRecharge::wxPay($rechargeOrder);
                    }
                }catch (\Exception $e){
                    return app('json')->fail($e->getMessage());
                }
                return app('json')->successful(['type'=>$from, 'data'=>$recharge]);
                break;
            case 1: //佣金转入余额
                if(UserRecharge::importNowMoney($request->uid(),$price))
                    return app('json')->successful('转入余额成功');
                else
                    return app('json')->fail(UserRecharge::getErrorInfo());
                break;
            default:
                return app('json')->fail('缺少参数');
                break;
        }
    }
}