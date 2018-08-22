<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/17
 */

namespace app\admin\model\ump;


use basic\ModelBasic;
use traits\ModelTrait;

class StoreCouponIssue extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    public static function stsypage($where){
        $model = self::alias('A')->field('A.*,B.title')->join('__STORE_COUPON__ B','A.cid = B.id')->where('A.is_del',0)->order('A.add_time DESC');
        if(isset($where['status']) && $where['status']!=''){
            $model=$model->where('A.status',$where['status']);
        }
        if(isset($where['coupon_title']) && $where['coupon_title']!=''){
            $model=$model->where('B.title','LIKE',"%$where[coupon_title]%");
        }
        return self::page($model);
    }

    protected function setAddTimeAttr()
    {
        return time();
    }

    public static function setIssue($cid,$total_count = 0,$start_time = 0,$end_time = 0,$remain_count = 0,$status = 0)
    {
        return self::set(compact('cid','start_time','end_time','total_count','remain_count','status'));
    }
}