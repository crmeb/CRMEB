<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/20
 */

namespace app\models\store;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 优惠券发放Model
 * Class StoreCouponUser
 * @package app\models\store
 */
class StoreCouponUser extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_coupon_user';

    protected $type = [
        'coupon_price'=>'float',
        'use_min_price'=>'float',
    ];

    protected $hidden = [
        'uid'
    ];

    use ModelTrait;

    /**
     * TODO 获取用户优惠券（全部）
     * @param $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserAllCoupon($uid)
    {
        self::checkInvalidCoupon();
        $couponList = self::where('uid',$uid)->order('is_fail ASC,status ASC,add_time DESC')->select()->toArray();
        return self::tidyCouponList($couponList);
    }
    /**
     * 获取用户优惠券（未使用）
     * @return \think\response\Json
     */
    public static function getUserValidCoupon($uid)
    {
        self::checkInvalidCoupon();
        $couponList = self::where('uid',$uid)->where('status',0)->order('is_fail ASC,status ASC,add_time DESC')->select()->toArray();
        return self::tidyCouponList($couponList);
    }
    /**
     * 获取用户优惠券（已使用）
     * @return \think\response\Json
     */
    public static function getUserAlreadyUsedCoupon($uid)
    {
        self::checkInvalidCoupon();
        $couponList = self::where('uid',$uid)->where('status',1)->order('is_fail ASC,status ASC,add_time DESC')->select()->toArray();
        return self::tidyCouponList($couponList);
    }
    /**
     * 获取用户优惠券（已过期）
     * @return \think\response\Json
     */
    public static function getUserBeOverdueCoupon($uid)
    {
        self::checkInvalidCoupon();
        $couponList = self::where('uid',$uid)->where('status',2)->order('is_fail ASC,status ASC,add_time DESC')->select()->toArray();
        return self::tidyCouponList($couponList);
    }
    public static function beUsableCoupon($uid,$price)
    {
        return self::where('uid',$uid)->where('is_fail',0)->where('status',0)->where('use_min_price','<=',$price)->find();
    }

    /**
     * 获取用户可以使用的优惠券
     * @param $uid
     * @param $price
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function beUsableCouponList($uid,$price=0){
        $list = self::where('uid',$uid)->where('is_fail',0)->where('status',0)->where('use_min_price','<=',$price)->order('coupon_price','DESC')->select();
        $list = count($list) ? $list->hidden(['type','status','is_fail'])->toArray() : [];
        foreach ($list as &$item){
            $item['add_time'] = date('Y/m/d',$item['add_time']);
            $item['end_time'] = date('Y/m/d',$item['end_time']);
        }
        return $list;
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
            $coupon['use_min_price'] = number_format($coupon['use_min_price'],2);
            $coupon['coupon_price'] = number_format($coupon['coupon_price'],2);
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
        return self::create($data);
    }

}