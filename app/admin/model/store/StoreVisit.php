<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\store;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\admin\model\user\User;

/**
 * 商品浏览分析
 * Class StoreVisit
 * @package app\admin\model\store
 */
class StoreVisit extends BaseModel
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
    protected $name = 'store_visit';

    use ModelTrait;

    /**
     * @param $date
     * @param array $class
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getVisit($date,$class=[]){
        $model=new self();
        switch ($date){
            case null:case 'today':case 'week':case 'year':
                if($date==null) $date='month';
                $model=$model->whereTime('add_time',$date);
                break;
            case 'quarter':
                list($startTime,$endTime)=User::getMonth('n');
                $model = $model->where('add_time','>',$startTime);
                $model = $model->where('add_time','<',$endTime);
                break;
            default:
                list($startTime,$endTime)=explode('-',$date);
                $model = $model->where('add_time','>',strtotime($startTime));
                $model = $model->where('add_time','<',strtotime($endTime));
                break;
        }
        $list=$model->group('type')->field('sum(count) as sum,product_id,cate_id,type,content')->order('sum desc')->limit(0,10)->select()->toArray();
        $view=[];
        foreach ($list as $key=>$val){
            $now_list['name']=$val['type']=='viwe'?'浏览量':'搜索';
            $now_list['value']=$val['sum'];
            $now_list['class']=isset($class[$key])?$class[$key]:'';
            $view[]=$now_list;
        }
        if(empty($list)){
            $view=[['name'=>'暂无数据', 'value'=>100, 'class'=>'']];
        }
        return $view;
    }
}