<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/28
 */

namespace app\admin\model\user;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\services\PHPExcelService;

/**
 * 用户充值记录
 * Class UserRecharge
 * @package app\admin\model\user
 */
 class UserRecharge extends BaseModel
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
     protected $name = 'user_recharge';

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
         $model = $model->join('__user__ B','A.uid = B.uid','RIGHT');
         $model = $model->order('A.id desc');

         return self::page($model,$where);

     }

     /*
      * 设置查询条件
      * @param array $where
      * @param object $model
      * */
     public static function setWhere($where,$model = null ,$alias = '',$join= '')
     {
        $model = is_null($model) ? new self() : $model;
        if($alias){
            $model = $model->alias('a');
            $alias .='.';
        }
        if($where['data']) $model = self::getModelTime($where,$model,"{$alias}add_time");
        if($where['paid'] != '') $model = $model->where("{$alias}paid",$where['paid']);
        if($where['nickname']) $model = $model->where("{$alias}uid|{$alias}order_id".($join ? '|'.$join : ''),'LIKE',"%$where[nickname]%");
        return $model->order("{$alias}add_time desc");
     }

     /*
      * 查找用户充值金额记录
      * @param array $where
      * @return array
      *
      * */
     public static function getUserRechargeList($where){
         $model = self::setWhere($where,null,'a','u.nickname')->join('user u','u.uid=a.uid')->field(['a.*','u.nickname','u.avatar']);
         if($where['excel']) self::saveExcel($model->select()->toArray());
         $data = $model->page($where['page'],$where['limit'])->select();
         $data = count($data) ? $data->toArray() : [];
         foreach ($data as &$item){
             switch ($item['recharge_type']){
                 case 'routine':
                     $item['_recharge_type']= '小程序充值';
                     break;
                 case 'weixin':
                     $item['_recharge_type']= '公众号充值';
                     break;
                 default:
                     $item['_recharge_type']= '其他充值';
                     break;
             }
             $item['_pay_time'] = $item['pay_time'] ? date('Y-m-d H:i:s',$item['pay_time']) : '暂无';
             $item['_add_time'] = $item['add_time'] ? date('Y-m-d H:i:s',$item['add_time']) : '暂无';
             $item['paid_type'] = $item['paid'] ? '已支付':'未支付';
         }
         $count = self::setWhere($where,null,'a','u.nickname')->join('user u','u.uid=a.uid')->count();
         return compact('data','count');
     }

     public static function saveExcel($data){
         $excel = [];
         foreach ($data as $item){
             switch ($item['recharge_type']){
                 case 'routine':
                     $item['_recharge_type']= '小程序充值';
                     break;
                 case 'weixin':
                     $item['_recharge_type']= '公众号充值';
                     break;
                 default:
                     $item['_recharge_type']= '其他充值';
                     break;
             }
             $item['_pay_time'] = $item['pay_time'] ? date('Y-m-d H:i:s',$item['pay_time']) : '暂无';
             $item['_add_time'] = $item['add_time'] ? date('Y-m-d H:i:s',$item['add_time']) : '暂无';
             $item['paid_type'] = $item['paid'] ? '已支付':'未支付';

             $excel[] = [
                 $item['nickname'],
                 $item['price'],
                 $item['paid_type'],
                 $item['_recharge_type'],
                 $item['_pay_time'],
                 $item['paid'] == 1 && $item['refund_price'] == $item['price'] ? '已退款':'未退款',
                 $item['_add_time']
             ];
         }
         PHPExcelService::setExcelHeader(['昵称/姓名','充值金额','是否支付','充值类型','支付事件','是否退款','添加时间'])
             ->setExcelTile('充值记录','充值记录'.time(),' 生成时间：'.date('Y-m-d H:i:s',time()))
             ->setExcelContent($excel)
             ->ExcelSave();
     }
     /*
      * 获取用户充值数据
      * @return array
      * */
     public static function getDataList($where){
         return [
             [
                 'name'=>'充值总金额',
                 'field'=>'元',
                 'count'=>self::setWhere($where,null,'a','u.nickname')->join('user u','u.uid=a.uid')->sum('a.price'),
                 'background_color'=>'layui-bg-cyan',
                 'col'=>3,
             ],
             [
                 'name'=>'充值退款金额',
                 'field'=>'元',
                 'count'=>self::setWhere($where,null,'a','u.nickname')->join('user u','u.uid=a.uid')->sum('a.refund_price'),
                 'background_color'=>'layui-bg-cyan',
                 'col'=>3,
             ],
             [
                 'name'=>'小程序充值金额',
                 'field'=>'元',
                 'count'=>self::setWhere($where,null,'a','u.nickname')->where('a.recharge_type','routine')->join('user u','u.uid=a.uid')->sum('a.price'),
                 'background_color'=>'layui-bg-cyan',
                 'col'=>3,
             ],
             [
                 'name'=>'公众号充值金额',
                 'field'=>'元',
                 'count'=>self::setWhere($where,null,'a','u.nickname')->where('a.recharge_type','weixin')->join('user u','u.uid=a.uid')->sum('a.price'),
                 'background_color'=>'layui-bg-cyan',
                 'col'=>3,
             ],
        ];
     }

}