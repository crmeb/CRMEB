<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace app\ebapi\model\store;

use basic\ModelBasic;
use traits\ModelTrait;

class StoreBargain extends ModelBasic
{
    use ModelTrait;

    /**
     * 正在开启的砍价活动
     * @return $this
     */
    public static function validWhere($status = 1){
        return  self::where('is_del',0)->where('status',$status)->where('start_time','LT',time())->where('stop_time','GT',time());
    }

    /**
     * 判断砍价产品是否开启
     * @param int $bargainId
     * @return int|string
     */
    public static function validBargain($bargainId = 0){
        $model = self::validWhere();
        return $bargainId ? $model->where('id',$bargainId)->count() : $model->count();
    }

    /**
     * TODO 获取正在开启的砍价产品编号
     * @return array
     */
    public static function validBargainNumber(){
        return self::validWhere()->column('id');
    }

    /**
     * TODO 获取正在进行中的砍价产品
     * @param int $offset
     * @param int $limit
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getList($offset = 0,$limit = 20,$field = 'id,product_id,title,price,min_price,image'){
        $model = self::validWhere();
        $list = $model->field($field)->limit($offset,$limit)->select()->each(function ($item){ $item['people'] = count(StoreBargainUser::getUserIdList($item['id']));});
        if($list) return $list->toArray();
        else return [];
    }

    /**
     * TODO 获取一条正在进行中的砍价产品
     * @param int $bargainId  $bargainId 砍价产品编号
     * @param string $field
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getBargainTerm($bargainId = 0,$field = 'id,product_id,bargain_num,num,unit_name,image,title,price,min_price,image,description,start_time,stop_time,rule'){
        if(!$bargainId) return [];
        $model = self::validWhere();
        $bargain = $model->field($field)->where('id',$bargainId)->find();
        if($bargain) return $bargain->toArray();
        else return [];
    }

    /**
     * 获取一条砍价产品
     * @param int $bargainId
     * @param string $field
     * @return array
     */
    public static function getBargain($bargainId = 0,$field = 'id,product_id,title,price,min_price,image'){
        if(!$bargainId) return [];
        $model = new self();
        $bargain = $model->field($field)->where('id',$bargainId)->find();
        if($bargain) return $bargain->toArray();
        else return [];
    }

    /**
     * 获取最高价和最低价
     * @param int $bargainId
     * @return array
     */
    public static function getBargainMaxMinPrice($bargainId = 0){
        if(!$bargainId) return [];
        return self::where('id',$bargainId)->field('bargain_min_price,bargain_max_price')->find()->toArray();
    }

    /**
     * 获取砍价次数
     * @param int $bargainId
     * @return mixed
     */
    public static function getBargainNum($bargainId = 0){
       return self::where('id',$bargainId)->value('bargain_num');
    }

    /**
     * 判断当前砍价是否活动进行中
     * @param int $bargainId
     * @return bool
     */
    public static function setBargainStatus($bargainId = 0){
        $model = self::validWhere();
        $count = $model->where('id',$bargainId)->count();
        if($count) return true;
        else return false;
    }

    /**
     * 获取库存
     * @param int $bargainId
     * @return mixed
     */
    public static function getBargainStock($bargainId = 0){
        return self::where('id',$bargainId)->value('stock');
    }
    /**
     * 修改销量和库存
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public static function decBargainStock($num,$bargainId)
    {
        $res = false !== self::where('id',$bargainId)->dec('stock',$num)->inc('sales',$num)->update();
        return $res;
    }

    /**
     * 增加库存减销量
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public static function IncBargainStock($num,$bargainId)
    {
        $bargain=self::where('id',$bargainId)->field(['stock','sales'])->find();
        if(!$bargain) return true;
        if($bargain->sales > 0) $bargain->sales=bcsub($bargain->sales,$num,0);
        if($bargain->sales < 0) $bargain->sales=0;
        $bargain->stock=bcadd($bargain->stock,$num,0);
        return $bargain->save();
    }

    /**
     * TODO 获取所有砍价产品的浏览量
     * @return mixed
     */
    public static function getBargainLook(){
        return self::sum('look');
    }

    /**
     * TODO 获取所有砍价产品的分享量
     * @return mixed
     */
    public static function getBargainShare(){
        return self::sum('share');
    }

    /**
     * TODO 添加砍价产品分享次数
     * @param int $id
     * @return StoreBargain|bool
     */
    public static function addBargainShare($id = 0){
        if(!$id) return false;
        return self::where('id',$id)->inc('share',1)->update();
    }

    /**
     * TODO 添加砍价产品浏览次数
     * @param int $id  $id 砍价产品编号
     * @return StoreBargain|bool
     */
    public static function addBargainLook($id = 0){
        if(!$id) return false;
        return self::where('id',$id)->inc('look',1)->update();
    }
}