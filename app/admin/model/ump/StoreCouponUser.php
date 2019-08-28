<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\ump;

use app\admin\model\wechat\WechatUser as UserModel;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * Class StoreCategory
 * @package app\admin\model\store
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

    use ModelTrait;

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where){
        $model = new self;
        if($where['status'] != '')  $model = $model->where('status',$where['status']);
        if($where['is_fail'] != '')  $model = $model->where('status',$where['is_fail']);
        if($where['coupon_title'] != '')  $model = $model->where('coupon_title','LIKE',"%$where[coupon_title]%");
        if($where['nickname'] != ''){
            $uid = UserModel::where('nickname','LIKE',"%$where[nickname]%")->column('uid','uid');
            $model = $model->where('uid','IN',implode(',',$uid));
        };
//        $model = $model->where('is_del',0);
        $model = $model->order('id desc');
        return self::page($model,function ($item){
            $item['nickname'] = UserModel::where('uid',$item['uid'])->value('nickname');
        },$where);
    }

    /**
     * 给用户发放优惠券
     * @param $coupon
     * @param $user
     * @return int|string
     */
    public static function setCoupon($coupon,$user){
        $data = array();
        foreach ($user as $k=>$v){
            $data[$k]['cid'] = $coupon['id'];
            $data[$k]['uid'] = $v;
            $data[$k]['coupon_title'] = $coupon['title'];
            $data[$k]['coupon_price'] = $coupon['coupon_price'];
            $data[$k]['use_min_price'] = $coupon['use_min_price'];
            $data[$k]['add_time'] = time();
            $data[$k]['end_time'] = $data[$k]['add_time']+$coupon['coupon_time']*86400;
        }
        $data_num = array_chunk($data,30);
        self::beginTrans();
        $res = true;
        foreach ($data_num as $k=>$v){
          $res = $res && self::insertAll($v);
        }
        self::checkTrans($res);
        return $res;
    }

    /**
     * TODO 恢复优惠券
     * @param $id
     * @return StoreCouponUser|bool
     */
    public static function recoverCoupon($id)
    {
        $status = self::where('id',$id)->value('status');
        if($status) return self::where('id',$id)->update(['status'=>0,'use_time'=>'']);
        else return true;
    }
}