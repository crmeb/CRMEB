<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\store;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 评论管理 model
 * Class StoreProductReply
 * @package app\admin\model\store
 */
class StoreProductReply extends BaseModel
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
    protected $name = 'store_product_reply';

    use ModelTrait;

    protected function getPicsAttr($value)
    {
        return json_decode($value,true);
    }
    /*
     * 设置where条件
     * @param array $where
     * @param string $alias
     * @param object $model
     * */
    public static function valiWhere($where,$alias='',$joinAlias='',$model=null)
    {
        $model=is_null($model) ? new self() : $model;
        if($alias){
            $model=$model->alias($alias);
            $alias.='.';
        }
        $joinAlias=$joinAlias ? $joinAlias.'.' : '';
        if(isset($where['title']) && $where['title']!='') $model=$model->where("{$alias}comment",'LIKE',"%$where[title]%");
        if(isset($where['is_reply']) && $where['is_reply']!='') $model= $where['is_reply'] >= 0 ? $model->where("{$alias}is_reply",$where['is_reply']) : $model->where("{$alias}is_reply",'>',0);
        if(isset($where['producr_id']) && $where['producr_id']!=0) $model=$model->where($alias.'product_id',$where['producr_id']);
        if(isset($where['product_name']) && $where['product_name']) $model=$model->where("{$joinAlias}store_name",'LIKE',"%$where[product_name]%");
        return $model->where("{$alias}is_del",0);
    }

    public static function getProductImaesList($where)
    {
        $list=self::valiWhere($where,'a','p')->group('p.id')->join('__wechat_user__ u','u.uid=a.uid')->join("__store_product__ p",'a.product_id=p.id')->field(['p.id','p.image','p.store_name','p.price'])->page($where['page'],$where['limit'])->select();
        $list=count($list) ? $list->toArray() : [];
        foreach ($list as &$item){
            $item['store_name']=self::getSubstrUTf8($item['store_name'],10,'UTF-8','');
        }

        return $list;
    }

    public static function getProductReplyList($where)
    {
        $data=self::valiWhere($where,'a','p')->join("__store_product__ p",'a.product_id=p.id')
            ->join('__wechat_user__ u','u.uid=a.uid')
            ->order('a.add_time desc,a.is_reply asc')
            ->field('a.*,u.nickname,u.headimgurl as avatar')
            ->page((int)$where['message_page'],(int)$where['limit'])
            ->select();
        $data=count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $item['time']=\crmeb\services\UtilService::timeTran($item['add_time']);
        }
        $count=self::valiWhere($where,'a','p')->join('__wechat_user__ u','u.uid=a.uid')->join("__store_product__ p",'a.product_id=p.id')->count();
        return ['list'=>$data,'count'=>$count];
    }
    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where){
        $model = new self;
        if($where['comment'] != '')  $model = $model->where('r.comment','LIKE',"%$where[comment]%");
        if($where['is_reply'] != ''){
            if($where['is_reply'] >= 0){
                $model = $model->where('r.is_reply',$where['is_reply']);
            }else{
                $model = $model->where('r.is_reply','>',0);
            }
        }
        if($where['product_id'])  $model = $model->where('r.product_id',$where['product_id']);
        $model = $model->alias('r')->join('__wechat_user__ u','u.uid=r.uid');
        $model = $model->join('__store_product__ p','p.id=r.product_id');
        $model = $model->where('r.is_del',0);
        $model = $model->field('r.*,u.nickname,u.headimgurl,p.store_name');
        $model = $model->order('r.add_time desc,r.is_reply asc');
        return self::page($model,function($itme){

        },$where);
    }

}