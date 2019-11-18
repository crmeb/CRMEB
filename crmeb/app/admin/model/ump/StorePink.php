<?php
namespace app\admin\model\ump;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use app\admin\model\order\StoreOrder;

class StorePink extends BaseModel
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
    protected $name = 'store_pink';

    use ModelTrait;

    /**分页列表
     * @param $where
     * @return array
     */
    public static function systemPage($where){
        $model = new self;
        $model = $model->alias('p');
        $model = $model->field('p.*,c.title,u.nickname,u.avatar');
        if($where['data'] !== ''){
            list($startTime,$endTime) = explode(' - ',$where['data']);
            $model = $model->where('p.add_time','>',strtotime($startTime));
            $model = $model->where('p.add_time','<',strtotime($endTime));
        }
        if($where['status']) $model = $model->where('p.status',$where['status']);
        $model = $model->where('p.k_id',0);
        $model = $model->order('p.id desc');
        $model = $model->join('store_combination c','c.id=p.cid');
        $model = $model->join('user u','u.uid = p.uid');
        return self::page($model,function($item)use($where){
            $item['count_people'] = bcadd(self::where('k_id',$item['id'])->count(),1,0);
        },$where);
    }
    /**
     * 获取当前产品参与的人数
     * @param int $combinationId
     * @return int|string
     */
    public static function getCountPeopleAll($combinationId = 0){
        if(!$combinationId) return self::count();
        return self::where('cid',$combinationId)->count();
    }

    /**
     * 获取当前产品参与的团数
     * @param int $combinationId
     * @return int|string
     */
    public static function getCountPeoplePink($combinationId = 0){
        if(!$combinationId) return self::where('k_id',0)->count();
        return self::where('cid',$combinationId)->where('k_id',0)->count();
    }
    /**
     * 获取一条拼团数据
     * @param $id
     * @return mixed
     */
    public static function getPinkUserOne($id){
        $model = new self();
        $model = $model->alias('p');
        $model = $model->field('p.*,u.nickname,u.avatar');
        $model = $model->where('id',$id);
        $model = $model->join('user u','u.uid = p.uid');
        $list = $model->find();
        if($list) return $list->toArray();
        else return [];
    }
    /**
     * 获取拼团的团员
     * @param $id
     * @return mixed
     */
    public static function getPinkMember($id){
        $model = new self();
        $model = $model->alias('p');
        $model = $model->field('p.*,u.nickname,u.avatar');
        $model = $model->where('k_id',$id);
        $model = $model->where('is_refund',0);
        $model = $model->join('user u','u.uid = p.uid');
        $model = $model->order('id asc');
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }
    /**
     * 拼团退款
     * @param $id
     * @return bool
     */
    public static function setRefundPink($oid){
        $res = true;
        $order = StoreOrder::where('id',$oid)->find();
        if($order['pink_id']) $id = $order['pink_id'];
        else return $res;
        $count = self::where('id',$id)->where('uid',$order['uid'])->find();//正在拼团 团长
        $countY = self::where('k_id',$id)->where('uid',$order['uid'])->find();//正在拼团 团员
        if(!$count && !$countY) return $res;
        if($count){//团长
            //判断团内是否还有其他人  如果有  团长为第二个进团的人
            $kCount = self::where('k_id',$id)->order('add_time asc')->find();
            if($kCount){
                $res11 = self::where('k_id',$id)->update(['k_id'=>$kCount['id']]);
                $res12 = self::where('id',$kCount['id'])->update(['stop_time'=>$count['add_time']+86400,'k_id'=>0]);
                $res1 = $res11 && $res12;
                $res2 = self::where('id',$id)->update(['stop_time'=>time()-1,'k_id'=>0,'is_refund'=>$kCount['id'],'status'=>3]);
            }else{
                $res1 = true;
                $res2 = self::where('id',$id)->update(['stop_time'=>time()-1,'k_id'=>0,'is_refund'=>$id,'status'=>3]);
            }
            //修改结束时间为前一秒  团长ID为0
            $res = $res1 && $res2;
        }else if($countY){//团员
            $res =  self::where('id',$countY['id'])->update(['stop_time'=>time()-1,'k_id'=>0,'is_refund'=>$id,'status'=>3]);
        }
        return $res;

    }
}