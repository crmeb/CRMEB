<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/18
 */

namespace app\ebapi\model\store;


use basic\ModelBasic;
use traits\ModelTrait;

class StoreCouponIssue extends ModelBasic
{
    use ModelTrait;

    public static function getIssueCouponList($uid,$limit,$page=0)
    {
        $model = self::validWhere('A')->join('__STORE_COUPON__ B','A.cid = B.id')
            ->field('A.*,B.coupon_price,B.use_min_price')->order('B.sort DESC,A.id DESC');
        if($page) $list=$model->page((int)$page,(int)$limit)->select()->toArray()?:[];
        else $list=$model->limit($limit)->select()->toArray()?:[];
        foreach ($list as &$v){
            $v['is_use'] = StoreCouponIssueUser::be(['uid'=>$uid,'issue_coupon_id'=>$v['id']]);
            if(!$v['is_use']){
                $v['is_use']=$v['remain_count'] <= 0 && !$v['is_permanent'] ? 2 : $v['is_use'];
            }
            if(!$v['end_time']){
                $v['add_time']= '';
                $v['end_time'] = '不限时';
            }else{
                $v['add_time']=date('Y/m/d',$v['add_time']);
                $v['end_time']=$v['end_time'] ? date('Y/m/d',$v['end_time']) : date('Y/m/d',time()+86400);
            }
            $v['coupon_price']=(int)$v['coupon_price'];
        }
        return $list;
    }
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
        if($issueCouponInfo['remain_count'] <= 0 && !$issueCouponInfo['is_permanent']) return self::setErrorInfo('抱歉优惠卷已经领取完了！');
        self::beginTrans();
        $res1 = false != StoreCouponUser::addUserCoupon($uid,$issueCouponInfo['cid']);
        $res2 = false != StoreCouponIssueUser::addUserIssue($uid,$id);
        $res3 = true;
        if($issueCouponInfo['total_count'] > 0){
            $issueCouponInfo['remain_count'] -= 1;
            $res3 = false !== $issueCouponInfo->save();
        }
        $res = $res1 && $res2 && $res3;
        self::checkTrans($res);
        return $res;
    }

}