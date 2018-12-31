<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/20
 */

namespace app\wap\model\store;


use basic\ModelBasic;
use traits\ModelTrait;

class StoreCouponUser extends ModelBasic
{
    use ModelTrait;

    public static function getUserAllCoupon($uid)
    {
        self::checkInvalidCoupon();
        $couponList = self::where('uid',$uid)->order('is_fail ASC,status ASC,add_time DESC')->select()->toArray();
        return self::tidyCouponList($couponList);
    }

    public static function getUserValidCoupon($uid)
    {
        self::checkInvalidCoupon();
        $couponList = self::where('uid',$uid)->where('status',0)->order('is_fail ASC,status ASC,add_time DESC')->select()->toArray();
        return self::tidyCouponList($couponList);
    }

    public static function beUsableCoupon($uid,$price)
    {
      if(is_array($price)) return self::where('uid',$uid)->where('is_fail',0)->where('status',0)->where('use_min_price','<=',$price['totalPrice'])->find();
      else  return self::where('uid',$uid)->where('is_fail',0)->where('status',0)->where('use_min_price','<=',$price)->find();

    }

    public static function validAddressWhere($model=null,$prefix = '')
    {
        self::checkInvalidCoupon();
        if($prefix) $prefix .='.';
        $model = self::getSelfModel($model);
        return $model->where("{$prefix}is_fail",0)->where("{$prefix}status",0);
    }

    public static function checkInvalidCoupon()
    {
        self::where('end_time','<',time())->where('status',0)->update(['status'=>2]);
    }

    public static function tidyCouponList($couponList)
    {
        $time = time();
        foreach ($couponList as $k=>$coupon){
            $coupon['_add_time'] = date('Y/m/d',$coupon['add_time']);
            $coupon['_end_time'] = date('Y/m/d',$coupon['end_time']);
            $coupon['use_min_price'] = floatval($coupon['use_min_price']);
            $coupon['coupon_price'] = floatval($coupon['coupon_price']);
            if($coupon['is_fail']){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已失效';
            }else if ($coupon['status'] == 1){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已使用';
            }else if ($coupon['status'] == 2){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已过期';
            }else if($coupon['add_time'] > $time || $coupon['end_time'] < $time){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已过期';
            }else{
                if($coupon['add_time']+ 3600*24 > $time){
                    $coupon['_type'] = 2;
                    $coupon['_msg'] = '可使用';
                }else{
                    $coupon['_type'] = 1;
                    $coupon['_msg'] = '可使用';
                }
            }
            $couponList[$k] = $coupon;
        }
        return $couponList;
    }

    public static function getUserValidCouponCount($uid)
    {
        self::checkInvalidCoupon();
        return self::where('uid',$uid)->where('status',0)->order('is_fail ASC,status ASC,add_time DESC')->count();
    }

    public static function useCoupon($id)
    {
        return self::where('id',$id)->update(['status'=>1,'use_time'=>time()]);
    }

    public static function addUserCoupon($uid,$cid,$type = 'get')
    {
        $couponInfo = StoreCoupon::find($cid);
        if(!$couponInfo) return self::setErrorInfo('优惠劵不存在!');
        $data = [];
        $data['cid'] = $couponInfo['id'];
        $data['uid'] = $uid;
        $data['coupon_title'] = $couponInfo['title'];
        $data['coupon_price'] = $couponInfo['coupon_price'];
        $data['use_min_price'] = $couponInfo['use_min_price'];
        $data['add_time'] = time();
        $data['end_time'] = $data['add_time']+$couponInfo['coupon_time']*86400;
        $data['type'] = $type;
        return self::set($data);
    }

}