<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/28
 */

namespace app\admin\model\user;


use traits\ModelTrait;
use basic\ModelBasic;

/**
 * 用户充值记录
 * Class UserRecharge
 * @package app\admin\model\user
 */
 class UserRecharge extends ModelBasic
{
    use ModelTrait;

     public static function systemPage($where){

         $model = new self;
         $model = $model->alias('A');
         if($where['order_id'] != '') {
             $model = $model->whereOr('A.order_id','like',"%$where[order_id]%");
             $model = $model->whereOr('A.id',(int)$where['order_id']);
             $model = $model->whereOr('B.nickname','like',"%$where[order_id]%");
         }
         $model = $model->where('A.recharge_type','weixin');
         $model = $model->where('A.paid',1);
         $model = $model->field('A.*,B.nickname');
         $model = $model->join('__USER__ B','A.uid = B.uid','RIGHT');
         $model = $model->order('A.id desc');

         return self::page($model,$where);

     }

}