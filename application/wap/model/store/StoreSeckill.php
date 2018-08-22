<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace app\wap\model\store;


use basic\ModelBasic;

class StoreSeckill extends ModelBasic
{

    protected function getImagesAttr($value)
    {
        return json_decode($value,true)?:[];
    }


    /**
     * 获取所有秒杀产品
     * @param string $field
     * @return array
     */
    public static function getListAll($field = 'id,product_id,image,title,price,ot_price,start_time,stop_time,stock,sales'){
        $time = time();
        $model = self::where('is_del',0)->where('status',1)->where('stock','>',0)->field($field)
            ->where('start_time','<',$time)->where('stop_time','>',$time)->order('sort DESC,add_time DESC');
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }
    /**
     * 获取热门推荐的秒杀产品
     * @param int $limit
     * @param string $field
     * @return array
     */
    public static function getHotList($limit = 0,$field = 'id,product_id,image,title,price,ot_price,start_time,stop_time,stock')
    {
        $time = time();
        $model = self::where('is_hot',1)->where('is_del',0)->where('status',1)->where('stock','>',0)->field($field)
            ->where('start_time','<',$time)->where('stop_time','>',$time)->order('sort DESC,add_time DESC');
        if($limit) $model->limit($limit);
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }

    /**
     * 获取一条秒杀产品
     * @param $id
     * @param string $field
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function getValidProduct($id,$field = '*')
    {
        $time = time();
        return self::where('id',$id)->where('is_del',0)->where('status',1)->where('start_time','<',$time)->where('stop_time','>',$time)
            ->field($field)->find();
    }

    public static function initFailSeckill()
    {
        self::where('is_hot',1)->where('is_del',0)->where('status','<>',1)->where('stop_time','<',time())->update(['status'=>'-1']);
    }

    public static function idBySimilaritySeckill($id,$limit = 4,$field='*')
    {
        $time = time();
        $list = [];
        $productId = self::where('id',$id)->value('product_id');
        if($productId){
            $list = array_merge($list, self::where('product_id',$productId)->where('id','<>',$id)
                ->where('is_del',0)->where('status',1)->where('stock','>',0)
                ->field($field)->where('start_time','<',$time)->where('stop_time','>',$time)
                ->order('sort DESC,add_time DESC')->limit($limit)->select()->toArray());
        }
        $limit = $limit - count($list);
        if($limit){
            $list = array_merge($list,self::getHotList($limit,$field));
        }

        return $list;
    }

    public static function getProductStock($id){
        $stock = self::where('id',$id)->value('stock');
        if(self::where('id',$id)->where('num','gt',$stock)->count()){//单次购买的产品多于库存
            return $stock;
        }else{
            return self::where('id',$id)->value('num');
        }
    }

    /**
     * 修改秒杀库存
     * @param int $num
     * @param int $seckillId
     * @return bool
     */
    public static function decSeckillStock($num = 0,$seckillId = 0){
        $res = false !== self::where('id',$seckillId)->dec('stock',$num)->inc('sales',$num)->update();
        return $res;
    }

}