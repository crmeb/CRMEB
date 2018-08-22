<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/18
 */

namespace app\wap\model\store;


use basic\ModelBasic;
use think\Db;
use traits\ModelTrait;

class StoreCouponIssue extends ModelBasic
{
    use ModelTrait;

    /**
     * @param string $prefix
     * @return $this
     */
    public static function validWhere($prefix = '')
    {
        $model = new self;
        if($prefix){
            $model->alias($prefix);
            $prefix .= '.';
        }
        $newTime = time();
        return $model->where("{$prefix}status",1)
            ->where(function($query) use($newTime,$prefix){
                $query->where(function($query) use($newTime,$prefix){
                    $query->where("{$prefix}start_time",'<',$newTime)->where("{$prefix}end_time",'>',$newTime);
                })->whereOr(function ($query) use($prefix){
                    $query->where("{$prefix}start_time",0)->where("{$prefix}end_time",0);
                });
            })->where("{$prefix}is_del",0);
    }


    public static function issueUserCoupon($id,$uid)
    {
        $issueCouponInfo = self::validWhere()->where('id',$id)->find();
        if(!$issueCouponInfo) return self::setErrorInfo('领取的优惠劵已领完或已过期!');
        if(StoreCouponIssueUser::be(['uid'=>$uid,'issue_coupon_id'=>$id]))
            return self::setErrorInfo('已领取过该优惠劵!');
        self::beginTrans();
        if($issueCouponInfo['is_permanent']==0 && $issueCouponInfo['total_count'] ==0)
            return self::setErrorInfo('该优惠劵已领完');
        $res1 = false != StoreCouponUser::addUserCoupon($uid,$issueCouponInfo['cid']);
        $res2 = false != StoreCouponIssueUser::addUserIssue($uid,$id);
        $res3 = false;
        if($issueCouponInfo['total_count'] > 0){
            $issueCouponInfo['remain_count'] -= 1;
            $res3 = false !== $issueCouponInfo->save();
        }
        $res = $res1 && $res2 & $res3;
        self::checkTrans($res);
        return $res;
    }

}