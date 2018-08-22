<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/29
 */

namespace app\wap\model\store;


use basic\ModelBasic;
use service\UtilService;
use traits\ModelTrait;

class StoreProductReply extends ModelBasic
{
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
        return self::set($group);
    }

    public static function productValidWhere($alias = '')
    {
        $model = new self;
        if($alias){
            $model->alias($alias);
            $alias .= '.';
        }
        return $model->where("{$alias}is_del",0)->where("{$alias}reply_type",'product');
    }

    public static function getProductReplyList($productId,$order = 'All',$first = 0,$limit = 8)
    {
        $model = self::productValidWhere('A')->where('A.product_id',$productId)
            ->field('A.product_score,A.service_score,A.comment,A.pics,A.add_time,B.nickname,B.avatar,C.cart_info,A.merchant_reply_content')
            ->join('__USER__ B','A.uid = B.uid')
            ->join('__STORE_ORDER_CART_INFO__ C','A.unique = C.unique');
        $baseOrder = 'A.add_time DESC,A.product_score DESC, A.service_score DESC';
        if($order == 'new') $model->order($baseOrder);
        else if($order == 'pic') $model->where('A.pics',['<>',''],['<>','[]'])->order('LENGTH(A.comment) DESC,'.$baseOrder);
        else $model->order('LENGTH(A.comment) DESC,'.$baseOrder);
        $list = $model->limit($first,$limit)->select()->toArray()?:[];
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
        $res['add_time'] = date('Y-m-d H:i',$res['add_time']);
        $res['star'] = ceil(($res['product_score'] + $res['service_score'])/2);
        $res['comment'] = $res['comment']?:'此用户没有填写评价';
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
            ->field('A.product_score,A.service_score,A.comment,A.pics,A.add_time,B.nickname,B.avatar,C.cart_info')
            ->join('__USER__ B','A.uid = B.uid')
            ->join('__STORE_ORDER_CART_INFO__ C','A.unique = C.unique')
            ->order('LENGTH(A.comment) DESC,A.product_score DESC, A.service_score DESC, A.add_time DESC')->find();
        if(!$res) return null;
        return self::tidyProductReply($res->toArray());
    }

}