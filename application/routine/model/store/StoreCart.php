<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace app\routine\model\store;


use app\routine\model\store\StoreCombination;
use basic\ModelBasic;
use traits\ModelTrait;

class StoreCart extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    public static function setCart($uid,$product_id,$cart_num = 1,$product_attr_unique = '',$type='product',$is_new = 0,$combination_id=0,$seckill_id = 0,$bargain_id = 0)
    {
        if($cart_num < 1) $cart_num = 1;
        if($seckill_id){
            $StoreSeckillinfo = StoreSeckill::getValidProduct($seckill_id);
            if(!$StoreSeckillinfo)
                return self::setErrorInfo('该产品已下架或删除');
            $userbuycount = StoreOrder::where(['uid'=>$uid,'paid'=>1,'seckill_id'=>$seckill_id])->count();
            if($StoreSeckillinfo['num'] <= $userbuycount)
                return self::setErrorInfo('每人限购'.$StoreSeckillinfo['num'].'一件');
            if(StoreSeckill::getProductStock($seckill_id) < $cart_num)
                return self::setErrorInfo('该产品库存不足'.$cart_num);
            $where = ['type'=>$type,'uid'=>$uid,'product_id'=>$product_id,'product_attr_unique'=>$product_attr_unique,'is_new'=>$is_new,'is_pay'=>0,'is_del'=>0,'seckill_id'=>$seckill_id];
            if($cart = self::where($where)->find()){
                $cart->cart_num = $cart_num;
                $cart->add_time = time();
                $cart->save();
                return $cart;
            }else{
                return self::set(compact('uid','product_id','cart_num','product_attr_unique','is_new','type','seckill_id'));
            }
        }elseif($bargain_id){
            if(!StoreBargain::validBargain($bargain_id))
                return self::setErrorInfo('该产品已下架或删除');
            if(StoreBargain::getBargainStock($bargain_id) < $cart_num)
                return self::setErrorInfo('该产品库存不足'.$cart_num);
            $where = ['type'=>$type,'uid'=>$uid,'product_id'=>$product_id,'product_attr_unique'=>$product_attr_unique,'is_new'=>$is_new,'is_pay'=>0,'is_del'=>0,'bargain_id'=>$bargain_id];
            if($cart = self::where($where)->find()){
                $cart->cart_num = $cart_num;
                $cart->add_time = time();
                $cart->save();
                return $cart;
            }else{
                return self::set(compact('uid','product_id','cart_num','product_attr_unique','is_new','type','bargain_id'));
            }
        }elseif($combination_id){//拼团
            if(!StoreCombination::getCombinationStock($combination_id,$cart_num))
                return self::setErrorInfo('该产品库存不足'.$cart_num);
            if(!StoreCombination::isValidCombination($combination_id))
                return self::setErrorInfo('该产品已下架或删除');
        }else{
            if(!StoreProduct::isValidProduct($product_id))
                return self::setErrorInfo('该产品已下架或删除');
            if(!StoreProductAttr::issetProductUnique($product_id,$product_attr_unique))
                return self::setErrorInfo('请选择有效的产品属性');
            if(StoreProduct::getProductStock($product_id,$product_attr_unique) < $cart_num)
                return self::setErrorInfo('该产品库存不足'.$cart_num);
        }
        $where = ['type'=>$type,'uid'=>$uid,'product_id'=>$product_id,'product_attr_unique'=>$product_attr_unique,'is_new'=>$is_new,'is_pay'=>0,'is_del'=>0,'combination_id'=>$combination_id];
        if($cart = self::where($where)->find()){
            $cart->cart_num = $cart_num;
            $cart->add_time = time();
            $cart->save();
            return $cart;
        }else{
            return self::set(compact('uid','product_id','cart_num','product_attr_unique','is_new','type','combination_id'));
        }

    }

    public static function removeUserCart($uid,$ids)
    {
        return self::where('uid',$uid)->where('id','IN',$ids)->update(['is_del'=>1]);
    }

    public static function getUserCartNum($uid,$type)
    {
        return self::where('uid',$uid)->where('type',$type)->where('is_pay',0)->where('is_del',0)->where('is_new',0)->count();
    }

    public static function changeUserCartNum($cartId,$cartNum,$uid)
    {
        return self::where('uid',$uid)->where('id',$cartId)->update(['cart_num'=>$cartNum]);
    }

    public static function getUserProductCartList($uid,$cartIds='',$status=0)
    {
        $productInfoField = 'id,image,slider_image,price,ot_price,vip_price,postage,mer_id,give_integral,cate_id,sales,stock,store_name,store_info,unit_name,is_show,is_del,is_postage,cost';
        $seckillInfoField = 'id,image,price,ot_price,postage,give_integral,sales,stock,title as store_name,unit_name,is_show,is_del,is_postage,cost';
        $bargainInfoField = 'id,image,min_price as price,price as ot_price,postage,give_integral,sales,stock,title as store_name,unit_name,status as is_show,is_del,is_postage,cost';
        $combinationInfoField = 'id,image,price,postage,sales,stock,title as store_name,is_show,is_del,is_postage,cost';
        $model = new self();
        $valid = $invalid = [];
        $model = $model->where('uid',$uid)->where('type','product')->where('is_pay',0)
            ->where('is_del',0);
        if(!$status) $model->where('is_new',0);
        if($cartIds) $model->where('id','IN',$cartIds);
        $list = $model->select()->toArray();
        if(!count($list)) return compact('valid','invalid');
        foreach ($list as $k=>$cart){
            if($cart['seckill_id']){
                $product = StoreSeckill::field($seckillInfoField)
                    ->find($cart['seckill_id'])->toArray();
            }elseif($cart['bargain_id']){
                $product = StoreBargain::field($bargainInfoField)
                    ->find($cart['bargain_id'])->toArray();
            }elseif($cart['combination_id']){
                $product = StoreCombination::field($combinationInfoField)
                    ->find($cart['combination_id'])->toArray();
            }else{
                $product = StoreProduct::field($productInfoField)
                    ->find($cart['product_id'])->toArray();
            }
            $cart['productInfo'] = $product;
            //商品不存在
            if(!$product){
                $model->where('id',$cart['id'])->update(['is_del'=>1]);
                //商品删除或无库存
            }else if(!$product['is_show'] || $product['is_del'] || !$product['stock']){
                $invalid[] = $cart;
                //商品属性不对应
            }else if(!StoreProductAttr::issetProductUnique($cart['product_id'],$cart['product_attr_unique']) && !$cart['combination_id'] && !$cart['seckill_id']){
                $invalid[] = $cart;
                //正常商品
            }else{
                if($cart['product_attr_unique']){
                    $attrInfo = StoreProductAttr::uniqueByAttrInfo($cart['product_attr_unique']);
                    //商品没有对应的属性
                    if(!$attrInfo || !$attrInfo['stock'])
                        $invalid[] = $cart;
                    else{
                        $cart['productInfo']['attrInfo'] = $attrInfo;
                        $cart['truePrice'] = (float)$attrInfo['price'];
                        $cart['trueStock'] = $attrInfo['stock'];
                        $cart['costPrice'] = $cart['productInfo']['cost'];
                        $cart['productInfo']['image'] = empty($attrInfo['image']) ? $cart['productInfo']['image'] : $attrInfo['image'];
                        $valid[] = $cart;
                    }
                }else{
                    $cart['truePrice'] = (float)$cart['productInfo']['price'];
                    $cart['trueStock'] = $cart['productInfo']['stock'];
                    $cart['costPrice'] = $cart['productInfo']['cost'];
                    $valid[] = $cart;
                }
            }
        }

        foreach ($valid as $k=>$cart){
            if($cart['trueStock'] < $cart['cart_num']){
                $cart['cart_num'] = $cart['trueStock'];
                $model->where('id',$cart['id'])->update(['cart_num'=>$cart['cart_num']]);
                $valid[$k] = $cart;
            }
        }

        return compact('valid','invalid');
    }

    /**
     * 拼团
     * @param $uid
     * @param string $cartIds
     * @return array
     */
    public static function getUserCombinationProductCartList($uid,$cartIds='')
    {
        $productInfoField = 'id,image,slider_image,price,cost,ot_price,vip_price,postage,mer_id,give_integral,cate_id,sales,stock,store_name,unit_name,is_show,is_del,is_postage';
        $model = new self();
        $valid = $invalid = [];
        $model = $model->where('uid',$uid)->where('type','product')->where('is_pay',0)
            ->where('is_del',0);
        if($cartIds) $model->where('id','IN',$cartIds);
        $list = $model->select()->toArray();
        if(!count($list)) return compact('valid','invalid');
        foreach ($list as $k=>$cart){
            $product = StoreProduct::field($productInfoField)
                ->find($cart['product_id'])->toArray();
            $cart['productInfo'] = $product;
            //商品不存在
            if(!$product){
                $model->where('id',$cart['id'])->update(['is_del'=>1]);
            //商品删除或无库存
            }else if(!$product['is_show'] || $product['is_del'] || !$product['stock']){
                $invalid[] = $cart;
            //商品属性不对应
//            }else if(!StoreProductAttr::issetProductUnique($cart['product_id'],$cart['product_attr_unique'])){
//                $invalid[] = $cart;
            //正常商品
            }else{
                $cart['truePrice'] = (float)StoreCombination::where('id',$cart['combination_id'])->value('price');
                $cart['costPrice'] = (float)StoreCombination::where('id',$cart['combination_id'])->value('cost');
                $cart['trueStock'] = StoreCombination::where('id',$cart['combination_id'])->value('stock');
                $valid[] = $cart;
            }
        }

        foreach ($valid as $k=>$cart){
            if($cart['trueStock'] < $cart['cart_num']){
                $cart['cart_num'] = $cart['trueStock'];
                $model->where('id',$cart['id'])->update(['cart_num'=>$cart['cart_num']]);
                $valid[$k] = $cart;
            }
        }

        return compact('valid','invalid');
    }


}