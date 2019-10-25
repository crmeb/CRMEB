<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/17
 */

namespace app\admin\model\ump;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreCouponIssue extends BaseModel
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
    protected $name = 'store_coupon_issue';

    use ModelTrait;

    protected $insert = ['add_time'];

    public static function stsypage($where){
        $model = self::alias('A')->field('A.*,B.title')->join('__store_coupon__ B','A.cid = B.id')->where('A.is_del',0)->order('A.add_time DESC');
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

    public static function setIssue($cid,$total_count = 0,$start_time = 0,$end_time = 0,$remain_count = 0,$status = 0,$is_permanent=0)
    {
        return self::create(compact('cid','start_time','end_time','total_count','remain_count','status','is_permanent'));
    }
}