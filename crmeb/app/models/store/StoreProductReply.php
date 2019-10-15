<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/29
 */

namespace app\models\store;


use crmeb\basic\BaseModel;
use crmeb\services\UtilService;
use crmeb\traits\ModelTrait;

/**
 * TODO 产品评价Model
 * Class StoreProductReply
 * @package app\models\store
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

    protected $insert = ['add_time'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    protected function setPicsAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    protected function getPicsAttr($value)
    {
        return json_decode($value,true);
    }

    public static function reply($group,$type = 'product')
    {
        $group['reply_type'] = $type;
        return self::create($group);
    }

    public static function productValidWhere($alias = '')
    {
        if($alias){
            $model = self::alias($alias);
            $alias .= '.';
        }else{
            $model = new self;
        }
        return $model->where("{$alias}is_del",0)->where("{$alias}reply_type",'product');
    }

    /*
     * 设置查询产品评论条件
     * @param int $productId 产品id
     * @param string $order 排序方式
     * @return object
     * */
    public static function setProductReplyWhere($productId,$type=0,$alias='A')
    {
        $model = self::productValidWhere($alias)->where('A.product_id',$productId)
            ->field('A.product_score,A.service_score,A.comment,A.merchant_reply_content,A.merchant_reply_time,A.pics,A.add_time,B.nickname,B.avatar,C.cart_info,A.merchant_reply_content')
            ->join('__user__ B','A.uid = B.uid')
            ->join('__store_order_cart_info__ C','A.unique = C.unique');
        switch ($type){
            case 1:
                $model=$model->where('A.product_score',5);//好评
                break;
            case 2:
                $model=$model->where('A.product_score','<',5)->where('A.product_score','>',2);//中评
                break;
            case 3:
                $model=$model->where('A.product_score','<',2);//差评
                break;
        }
        return $model;
    }

    public static function getProductReplyList($productId,$order = 0,$page = 0,$limit = 8)
    {
        $model = self::setProductReplyWhere($productId,$order);
        if($page) $model = $model->page((int)$page,(int)$limit);
        $list = $model->select()->toArray()?:[];
        foreach ($list as $k=>$reply){
            $list[$k] = self::tidyProductReply($reply);
        }
        return $list;
    }

    public static function tidyProductReply($res)
    {
        $res['cart_info'] = json_decode($res['cart_info'],true)?:[];
        $res['suk'] = isset($res['cart_info']['productInfo']['attrInfo']) ? $res['cart_info']['productInfo']['attrInfo']['suk'] : '';
        $res['nickname'] = UtilService::anonymity($res['nickname']);
        $res['merchant_reply_time'] = date('Y-m-d H:i',$res['merchant_reply_time']);
        $res['add_time'] = date('Y-m-d H:i',$res['add_time']);
        $res['star'] = bcadd($res['product_score'],$res['service_score'],2);
        $res['star'] =bcdiv($res['star'],2,0);
        $res['comment'] = $res['comment'] ? :'此用户没有填写评价';
        unset($res['cart_info']);
        return $res;
    }

    public static function isReply($unique,$reply_type = 'product')
    {
        return self::be(['unique'=>$unique,'reply_type'=>$reply_type]);
    }

    public static function getRecProductReply($productId)
    {
        $res = self::productValidWhere('A')->where('A.product_id',$productId)
            ->field('A.product_score,A.service_score,A.comment,A.merchant_reply_content,A.merchant_reply_time,A.pics,A.add_time,B.nickname,B.avatar,C.cart_info')
            ->join('__user__ B','A.uid = B.uid')
            ->join('__store_order_cart_info__ C','A.unique = C.unique')
            ->order('A.add_time DESC,A.product_score DESC, A.service_score DESC, A.add_time DESC')->find();
        if(!$res) return null;
        return self::tidyProductReply($res->toArray());
    }

    public static function productReplyCount($productId)
    {
//        \think\Db::listen(function($sql, $time, $explain){
//            // 记录SQL
//            echo $sql. ' ['.$time.'s]';
//        });
        $data['sum_count']=self::setProductReplyWhere($productId)->count();
        $data['good_count']=self::setProductReplyWhere($productId,1)->count();
        $data['in_count']=self::setProductReplyWhere($productId,2)->count();
        $data['poor_count']=self::setProductReplyWhere($productId,3)->count();
        $data['reply_chance']=bcdiv($data['good_count'],$data['sum_count'],2);
        $data['reply_star']=bcmul($data['reply_chance'],5,0);
        $data['reply_chance']=bcmul($data['reply_chance'],100,2);
        return $data;
    }

}