<?php
namespace app\ebapi\controller;

use app\ebapi\model\store\StoreCouponIssue;
use app\ebapi\model\store\StoreCouponUser;
use service\JsonService;

/**
 * 小程序优惠券api接口
 * Class CouponsApi
 * @package app\ebapi\controller
 *
 */
class CouponsApi extends AuthController
{
    /**
     * 获取用户优惠券
     * @return \think\response\Json
     */
    public function get_use_coupons($types='')
    {
        switch ($types){
            case 0:case '':
                $list= StoreCouponUser::getUserAllCoupon($this->userInfo['uid']);
                break;
            case 1:
                $list=StoreCouponUser::getUserValidCoupon($this->userInfo['uid']);
                break;
            case 2:
                $list=StoreCouponUser::getUserAlreadyUsedCoupon($this->userInfo['uid']);
                break;
            default:
                $list=StoreCouponUser::getUserBeOverdueCoupon($this->userInfo['uid']);
                break;
        }
        foreach ($list as &$v){
            $v['add_time'] = date('Y/m/d',$v['add_time']);
            $v['end_time'] = date('Y/m/d',$v['end_time']);
        }
        return JsonService::successful($list);
    }
    /**
     * 获取用户优惠券
     * @return \think\response\Json
     */
    public function get_use_coupon(){

        return JsonService::successful('',StoreCouponUser::getUserAllCoupon($this->userInfo['uid']));
    }

    /**
     * 获取可以使用的优惠券
     * @param int $totalPrice
     * @return \think\response\Json
     */
    public function get_use_coupon_order($totalPrice = 0)
    {
        return JsonService::successful(StoreCouponUser::beUsableCouponList($this->userInfo['uid'],$totalPrice));
    }


    /**
     * 领取优惠券
     * @param string $couponId
     * @return \think\response\Json
     */
    public function user_get_coupon($couponId = '')
    {
        if(!$couponId || !is_numeric($couponId)) return JsonService::fail('参数错误!');
        if(StoreCouponIssue::issueUserCoupon($couponId,$this->userInfo['uid'])){
            return JsonService::successful('领取成功');
        }else{
            return JsonService::fail(StoreCouponIssue::getErrorInfo('领取失败!'));
        }
    }

    /**
     * 获取一条优惠券
     * @param int $couponId
     * @return \think\response\Json
     */
    public function get_coupon_rope($couponId = 0){
        if(!$couponId) return JsonService::fail('参数错误');
        $couponUser = StoreCouponUser::validAddressWhere()->where('id',$couponId)->where('uid',$this->userInfo['uid'])->find();
        return JsonService::successful($couponUser);
    }
    /**
     * 获取  可以领取的优惠券
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_issue_coupon_list($limit = 2,$page=0)
    {
        return JsonService::successful(StoreCouponIssue::getIssueCouponList($this->uid,$limit,$page));
    }
}