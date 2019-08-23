<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/20
 */

namespace app\ebapi\model\store;

use app\core\model\routine\RoutineTemplate;
use app\core\util\ApiLogs;
use app\ebapi\model\user\User;
use app\ebapi\model\user\UserAddress;
use app\core\model\user\UserBill;
use app\ebapi\model\user\WechatUser;
use basic\ModelBasic;
use app\core\behavior\OrderBehavior;
use app\core\behavior\GoodsBehavior;
use app\core\behavior\UserBehavior;
use app\core\behavior\PaymentBehavior;
use service\HookService;
use app\core\util\MiniProgramService;
use app\core\util\SystemConfigService;
use think\Cache;
use think\Exception;
use think\exception\PDOException;
use traits\ModelTrait;

class StoreOrder extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    protected static $payType = ['weixin'=>'微信支付','yue'=>'余额支付','offline'=>'线下支付'];

    protected static $deliveryType = ['send'=>'商家配送','express'=>'快递配送'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    protected function setCartIdAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    protected function getCartIdAttr($value)
    {
        return json_decode($value,true);
    }

    /**获取订单组信息
     * @param $cartInfo
     * @return array
     */
    public static function getOrderPriceGroup($cartInfo)
    {
        $storePostage = floatval(SystemConfigService::get('store_postage'))?:0;//邮费基础价
        $storeFreePostage =  floatval(SystemConfigService::get('store_free_postage'))?:0;//满额包邮
        $totalPrice = self::getOrderSumPrice($cartInfo,'truePrice');//获取订单总金额
        $costPrice = self::getOrderSumPrice($cartInfo,'costPrice');//获取订单成本价
        $vipPrice = self::getOrderSumPrice($cartInfo,'vip_truePrice');//获取订单会员优惠金额
        //如果满额包邮等于0
        if(!$storeFreePostage) {
            $storePostage = 0;
        }else{
            foreach ($cartInfo as $cart){
                if(!$cart['productInfo']['is_postage'])//若果产品不包邮
                    $storePostage = bcadd($storePostage,$cart['productInfo']['postage'],2);

            }
            if($storeFreePostage <= $totalPrice) $storePostage = 0;//如果总价大于等于满额包邮 邮费等于0
        }
//        $totalPrice = bcadd($totalPrice,$storePostage,2);
        return compact('storePostage','storeFreePostage','totalPrice','costPrice','vipPrice');
    }

    /**获取某个字段总金额
     * @param $cartInfo
     * @param $key 键名
     * @return int|string
     */
    public static function getOrderSumPrice($cartInfo,$key='truePrice')
    {
        $SumPrice = 0;
        foreach ($cartInfo as $cart){
            $SumPrice = bcadd($SumPrice,bcmul($cart['cart_num'],$cart[$key],2),2);
        }
        return $SumPrice;
    }


    /**
     * 拼团
     * @param $cartInfo
     * @return array
     */
    public static function getCombinationOrderPriceGroup($cartInfo)
    {
        $storePostage = floatval(SystemConfigService::get('store_postage'))?:0;
        $storeFreePostage =  floatval(SystemConfigService::get('store_free_postage'))?:0;
        $totalPrice = self::getCombinationOrderTotalPrice($cartInfo);
        $costPrice = self::getCombinationOrderTotalPrice($cartInfo);
        if(!$storeFreePostage) {
            $storePostage = 0;
        }else{
            foreach ($cartInfo as $cart){
                if(!StoreCombination::where('id',$cart['combination_id'])->value('is_postage'))
                    $storePostage = bcadd($storePostage,StoreCombination::where('id',$cart['combination_id'])->value('postage'),2);
            }
            if($storeFreePostage <= $totalPrice) $storePostage = 0;
        }
        return compact('storePostage','storeFreePostage','totalPrice','costPrice');
    }

    /**
     * 拼团价格
     * @param $cartInfo
     * @return float
     */
    public static function getCombinationOrderTotalPrice($cartInfo)
    {
        $totalPrice = 0;
        foreach ($cartInfo as $cart){
            if($cart['combination_id']){
                 $totalPrice = bcadd($totalPrice,bcmul($cart['cart_num'],StoreCombination::where('id',$cart['combination_id'])->value('price'),2),2);
            }
        }
        return (float)$totalPrice;
    }

    public static function cacheOrderInfo($uid,$cartInfo,$priceGroup,$other = [],$cacheTime = 600)
    {
        $key = md5(time());
        Cache::set('user_order_'.$uid.$key,compact('cartInfo','priceGroup','other'),$cacheTime);
        return $key;
    }

    public static function getCacheOrderInfo($uid,$key)
    {
        $cacheName = 'user_order_'.$uid.$key;
        if(!Cache::has($cacheName)) return null;
        return Cache::get($cacheName);
    }

    public static function clearCacheOrderInfo($uid,$key)
    {
        Cache::clear('user_order_'.$uid.$key);
    }

    /**生成订单
     * @param $uid
     * @param $key
     * @param $addressId
     * @param $payType
     * @param bool $useIntegral
     * @param int $couponId
     * @param string $mark
     * @param int $combinationId
     * @param int $pinkId
     * @param int $seckill_id
     * @param int $bargain_id
     * @return bool|object
     */
    public static function cacheKeyCreateOrder($uid,$key,$addressId,$payType,$useIntegral = false,$couponId = 0,$mark = '',$combinationId = 0,$pinkId = 0,$seckill_id=0,$bargain_id=0)
    {
        if(!array_key_exists($payType,self::$payType)) return self::setErrorInfo('选择支付方式有误!');
        if(self::be(['unique'=>$key,'uid'=>$uid])) return self::setErrorInfo('请勿重复提交订单');
        $userInfo = User::getUserInfo($uid);
        if(!$userInfo) return  self::setErrorInfo('用户不存在!');
        $cartGroup = self::getCacheOrderInfo($uid,$key);
        if(!$cartGroup) return self::setErrorInfo('订单已过期,请刷新当前页面!');
        $cartInfo = $cartGroup['cartInfo'];
        $priceGroup = $cartGroup['priceGroup'];
        $other = $cartGroup['other'];
        $payPrice = (float)$priceGroup['totalPrice'];
        $payPostage = $priceGroup['storePostage'];
        if(!$addressId) return self::setErrorInfo('请选择收货地址!');
        if(!UserAddress::be(['uid'=>$uid,'id'=>$addressId,'is_del'=>0]) || !($addressInfo = UserAddress::find($addressId)))
            return self::setErrorInfo('地址选择有误!');

        //使用优惠劵
        $res1 = true;
        if($couponId){
            $couponInfo = StoreCouponUser::validAddressWhere()->where('id',$couponId)->where('uid',$uid)->find();
            if(!$couponInfo) return self::setErrorInfo('选择的优惠劵无效!');
            if($couponInfo['use_min_price'] > $payPrice)
                return self::setErrorInfo('不满足优惠劵的使用条件!');
            $payPrice = (float)bcsub($payPrice,$couponInfo['coupon_price'],2);
            $res1 = StoreCouponUser::useCoupon($couponId);
            $couponPrice = $couponInfo['coupon_price'];
        }else{
            $couponId = 0;
            $couponPrice = 0;
        }
        if(!$res1) return self::setErrorInfo('使用优惠劵失败!');

        //是否包邮
        if((isset($other['offlinePostage'])  && $other['offlinePostage'] && $payType == 'offline')) $payPostage = 0;
        $payPrice = (float)bcadd($payPrice,$payPostage,2);

        //积分抵扣
        $res2 = true;
        if($useIntegral && $userInfo['integral'] > 0){
            $deductionPrice = (float)bcmul($userInfo['integral'],$other['integralRatio'],2);
            if($deductionPrice < $payPrice){
                $payPrice = bcsub($payPrice,$deductionPrice,2);
                $usedIntegral = $userInfo['integral'];
                $res2 = false !== User::edit(['integral'=>0],$userInfo['uid'],'uid');
            }else{
                $deductionPrice = $payPrice;
                $usedIntegral = (float)bcdiv($payPrice,$other['integralRatio'],2);
                $res2 = false !== User::bcDec($userInfo['uid'],'integral',$usedIntegral,'uid');
                $payPrice = 0;
            }
            $res2 = $res2 && false != UserBill::expend('积分抵扣',$uid,'integral','deduction',$usedIntegral,$key,$userInfo['integral'],'购买商品使用'.floatval($usedIntegral).'积分抵扣'.floatval($deductionPrice).'元');
        }else{
            $deductionPrice = 0;
            $usedIntegral = 0;
        }
        if(!$res2) return self::setErrorInfo('使用积分抵扣失败!');

        $cartIds = [];
        $totalNum = 0;
        $gainIntegral = 0;
        foreach ($cartInfo as $cart){
            $cartIds[] = $cart['id'];
            $totalNum += $cart['cart_num'];
            $gainIntegral = bcadd($gainIntegral,isset($cart['productInfo']['give_integral']) ? bcmul($cart['productInfo']['give_integral'],$cart['cart_num'],2) : 0,2);
        }
        $orderInfo = [
            'uid'=>$uid,
            'order_id'=>self::getNewOrderId(),
            'real_name'=>$addressInfo['real_name'],
            'user_phone'=>$addressInfo['phone'],
            'user_address'=>$addressInfo['province'].' '.$addressInfo['city'].' '.$addressInfo['district'].' '.$addressInfo['detail'],
            'cart_id'=>$cartIds,
            'total_num'=>$totalNum,
            'total_price'=>$priceGroup['totalPrice'],
            'total_postage'=>$priceGroup['storePostage'],
            'coupon_id'=>$couponId,
            'coupon_price'=>$couponPrice,
            'pay_price'=>$payPrice,
            'pay_postage'=>$payPostage,
            'deduction_price'=>$deductionPrice,
            'paid'=>0,
            'pay_type'=>$payType,
            'use_integral'=>$usedIntegral,
            'gain_integral'=>$gainIntegral,
            'mark'=>htmlspecialchars($mark),
            'combination_id'=>$combinationId,
            'pink_id'=>$pinkId,
            'seckill_id'=>$seckill_id,
            'bargain_id'=>$bargain_id,
            'cost'=>$priceGroup['costPrice'],
            'is_channel'=>1,
            'unique'=>$key
        ];
        $order = self::set($orderInfo);
        if(!$order)return self::setErrorInfo('订单生成失败!');
        $res5 = true;
        foreach ($cartInfo as $cart)
        {
            //减库存加销量
            if($combinationId) $res5 = $res5 && StoreCombination::decCombinationStock($cart['cart_num'],$combinationId);
            else if($seckill_id) $res5 = $res5 && StoreSeckill::decSeckillStock($cart['cart_num'],$seckill_id);
            else if($bargain_id) $res5 = $res5 && StoreBargain::decBargainStock($cart['cart_num'],$bargain_id);
            else $res5 = $res5 && StoreProduct::decProductStock($cart['cart_num'],$cart['productInfo']['id'],isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique']:'');
        }
        //保存购物车商品信息
        $res4 = false !== StoreOrderCartInfo::setCartInfo($order['id'],$cartInfo);
        //购物车状态修改
        $res6 = false !== StoreCart::where('id','IN',$cartIds)->update(['is_pay'=>1]);
        if(!$res4 || !$res5 || !$res6) return self::setErrorInfo('订单生成失败!');
        try{
            HookService::listen('store_product_order_create',$order,compact('cartInfo','addressId'),false,GoodsBehavior::class);
        }catch (\Exception $e){
            return self::setErrorInfo($e->getMessage());
        }
        self::clearCacheOrderInfo($uid,$key);
        self::commitTrans();
        StoreOrderStatus::status($order['id'],'cache_key_create_order','订单生成');
        return $order;
    }

    /*
     * 回退积分
     * @param array $order 订单信息
     * @return boolean
     * */
    public static function RegressionIntegral($order)
    {
        if($order['paid'] || $order['status']==-2 || $order['is_del']) return true;
        if($order['use_integral'] <= 0) return true;
        if((int)$order['status']!=-2 && (int)$order['refund_status']!=2 && $order['back_integral'] >= $order['use_integral']) return true;
        $res=User::bcInc($order['uid'],'integral',$order['use_integral']);
        if(!$res) return self::setErrorInfo('回退积分增加失败');
        UserBill::income('积分回退',$order['uid'],'integral','deduction',$order['use_integral'],$order['unique'],User::where('uid',$order['uid'])->value('integral'),'购买商品失败,回退积分'.floatval($order['use_integral']));
        return false !== self::where('order_id',$order['order_id'])->update(['back_integral'=>$order['use_integral']]);
    }

    /*
     * 回退库存和销量
     * @param array $order 订单信息
     * @return boolean
     * */
    public static function RegressionStock($order)
    {
        if($order['paid'] || $order['status'] == -2 || $order['is_del']) return true;
        $combinationId = $order['combination_id'];
        $seckill_id = $order['seckill_id'];
        $bargain_id = $order['bargain_id'];
        $res5 = true;
        $cartInfo=StoreOrderCartInfo::where('cart_id','in',$order['cart_id'])->select();
        foreach ($cartInfo as $cart)
        {
            //增库存减销量
            if($combinationId) $res5 = $res5 && StoreCombination::incCombinationStock($cart['cart_info']['cart_num'],$combinationId);
            else if($seckill_id) $res5 = $res5 && StoreSeckill::incSeckillStock($cart['cart_info']['cart_num'],$seckill_id);
            else if($bargain_id) $res5 = $res5 && StoreBargain::incBargainStock($cart['cart_info']['cart_num'],$bargain_id);
            else $res5 = $res5 && StoreProduct::incProductStock($cart['cart_info']['cart_num'],$cart['cart_info']['productInfo']['id'],isset($cart['cart_info']['productInfo']['attrInfo']) ? $cart['cart_info']['productInfo']['attrInfo']['unique']:'');
        }
        return $res5;
    }

    /*
     * 回退优惠卷
     * @param array $order 订单信息
     * @return boolean
     * */
    public static function RegressionCoupon($order)
    {
        if($order['paid'] || $order['status']==-2 || $order['is_del']) return true;
        $res=true;
        if($order['coupon_id'] && StoreCouponUser::be(['id'=>$order['coupon_id'],'uid'=>$order['uid'],'status'=>1])){
            $res= $res && false !== StoreCouponUser::where(['id'=>$order['coupon_id'],'uid'=>$order['uid']])->update(['status'=>0,'use_time'=>0]);
        }
        return $res;
    }

    /*
     * 取消订单
     * @param string order_id 订单id
     * */
    public static function cancelOrder($order_id)
    {
        $order=self::where('order_id',$order_id)->find();
        if(!$order) return self::setErrorInfo('没有查到此订单');
        self::beginTrans();
        try{
            $res=self::RegressionIntegral($order) && self::RegressionStock($order) && self::RegressionCoupon($order);
            if($res){
                $order->is_del=1;
                $order->save();
                self::commitTrans();
                return true;
            }
        }catch (\Exception $e){
            self::rollbackTrans();
            return self::setErrorInfo(['line'=>$e->getLine(),'message'=>$e->getMessage()]);
        }
    }

    public static function getNewOrderId()
    {
        $count = (int) self::where('add_time',['>=',strtotime(date("Y-m-d"))],['<',strtotime(date("Y-m-d",strtotime('+1 day')))])->count();
        return 'wx'.date('YmdHis',time()).(10000+$count+1);
    }

    public static function changeOrderId($orderId)
    {
        $ymd = substr($orderId,2,8);
        $key = substr($orderId,16);
        return 'wx'.$ymd.date('His').$key;
    }
    //TODO JS支付
    public static function jsPay($orderId,$field = 'order_id')
    {
        if(is_string($orderId))
            $orderInfo = self::where($field,$orderId)->find();
        else
            $orderInfo = $orderId;
        if(!$orderInfo || !isset($orderInfo['paid'])) exception('支付订单不存在!');
        if($orderInfo['paid']) exception('支付已支付!');
        if($orderInfo['pay_price'] <= 0) exception('该支付无需支付!');
        $openid = WechatUser::getOpenId($orderInfo['uid']);
        return MiniProgramService::jsPay($openid,$orderInfo['order_id'],$orderInfo['pay_price'],'productr',SystemConfigService::get('site_name'));
    }
    //TODO 余额支付
    public static function yuePay($order_id,$uid,$formId = '')
    {
        $orderInfo = self::where('uid',$uid)->where('order_id',$order_id)->where('is_del',0)->find();
        if(!$orderInfo) return self::setErrorInfo('订单不存在!');
        if($orderInfo['paid']) return self::setErrorInfo('该订单已支付!');
//        if($orderInfo['pay_type'] != 'yue') return self::setErrorInfo('该订单不能使用余额支付!');
        $userInfo = User::getUserInfo($uid);
        if($userInfo['now_money'] < $orderInfo['pay_price'])
            return self::setErrorInfo(['status'=>'pay_deficiency','msg'=>'余额不足'.floatval($orderInfo['pay_price'])]);
        self::beginTrans();

        $res1 = false !== User::bcDec($uid,'now_money',$orderInfo['pay_price'],'uid');
        $res2 = UserBill::expend('购买商品',$uid,'now_money','pay_product',$orderInfo['pay_price'],$orderInfo['id'],$userInfo['now_money'],'余额支付'.floatval($orderInfo['pay_price']).'元购买商品');
        $res3 = self::paySuccess($order_id,'yue',$formId);//余额支付成功
        try{
            HookService::listen('yue_pay_product',$userInfo,$orderInfo,false,PaymentBehavior::class);
        }catch (\Exception $e){
            self::rollbackTrans();
            return self::setErrorInfo($e->getMessage());
        }
        $res = $res1 && $res2 && $res3;
        self::checkTrans($res);
        return $res;
    }

    /**
     * 微信支付 为 0元时
     * @param $order_id
     * @param $uid
     * @return bool
     */
    public static function jsPayPrice($order_id,$uid,$formId = ''){
        $orderInfo = self::where('uid',$uid)->where('order_id',$order_id)->where('is_del',0)->find();
        if(!$orderInfo) return self::setErrorInfo('订单不存在!');
        if($orderInfo['paid']) return self::setErrorInfo('该订单已支付!');
        $userInfo = User::getUserInfo($uid);
        self::beginTrans();
        $res1 = UserBill::expend('购买商品',$uid,'now_money','pay_product',$orderInfo['pay_price'],$orderInfo['id'],$userInfo['now_money'],'微信支付'.floatval($orderInfo['pay_price']).'元购买商品');
        $res2 = self::paySuccess($order_id,'weixin',$formId);//微信支付为0时
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }

   

    /**
     * 用户申请退款
     * @param $uni
     * @param $uid
     * @param string $refundReasonWap
     * @return bool
     */
    public static function orderApplyRefund($uni, $uid,$refundReasonWap = '',$refundReasonWapExplain = '',$refundReasonWapImg = array())
    {
        $order = self::getUserOrderDetail($uid,$uni);
        if(!$order) return self::setErrorInfo('支付订单不存在!');
        if($order['refund_status'] == 2) return self::setErrorInfo('订单已退款!');
        if($order['refund_status'] == 1) return self::setErrorInfo('正在申请退款中!');
        if($order['status'] == 1) return self::setErrorInfo('订单当前无法退款!');
        self::beginTrans();
        $res1 = false !== StoreOrderStatus::status($order['id'],'apply_refund','用户申请退款，原因：'.$refundReasonWap);
        $res2 = false !== self::edit(['refund_status'=>1,'refund_reason_time'=>time(),'refund_reason_wap'=>$refundReasonWap,'refund_reason_wap_explain'=>$refundReasonWapExplain,'refund_reason_wap_img'=>json_encode($refundReasonWapImg)],$order['id'],'id');
        $res = $res1 && $res2;
        self::checkTrans($res);
        if(!$res)
            return self::setErrorInfo('申请退款失败!');
        else{
            $adminIds = SystemConfigService::get('site_store_admin_uids');
            if(!empty($adminIds)){
                try{
                    if(!($adminList = array_unique(array_filter(explode(',',trim($adminIds)))))){
                        self::setErrorInfo('申请退款成功,');
                        return false;
                    }
                    RoutineTemplate::sendOrderRefundStatus($order,$refundReasonWap,$adminList);//小程序 发送模板消息
                }catch (\Exception $e){}
            }
            return true;
        }
    }

    /**
     * //TODO 支付成功后
     * @param $orderId
     * @param $paytype
     * @param $notify
     * @return bool
     */
    public static function paySuccess($orderId,$paytype='weixin',$formId = '')
    {
        $order = self::where('order_id',$orderId)->find();
        $resPink = true;
        $res1 = self::where('order_id',$orderId)->update(['paid'=>1,'pay_type'=>$paytype,'pay_time'=>time()]);//订单改为支付
        User::bcInc($order['uid'],'pay_count',1,'uid');
        if($order->combination_id && $res1 && !$order->refund_status) $resPink = StorePink::createPink($order);//创建拼团
        $oid = self::where('order_id',$orderId)->value('id');
        StoreOrderStatus::status($oid,'pay_success','用户付款成功');
        RoutineTemplate::sendOrderSuccess($formId,$orderId);
        HookService::afterListen('user_level',User::where('uid',$order['uid'])->find(),null,false,UserBehavior::class);
        //TODO 订单通知给管理员
        try{
            $pay_type = '微信支付';
            if($order['pay_type']=='weixin'){
                $pay_type = '微信支付';
            }else if($order['pay_type']=='yue'){
                $pay_type = '余额支付';
            }
            $nickname=User::where('uid',$order['uid'])->value('nickname');
            \app\core\util\WechatTemplateService::sendAdminNoticeTemplate([
                'first'=>"亲,您有一个新订单 \n订单号:{$order['order_id']} \n支付金额:{$order['pay_price']} \n支付方式:{$pay_type} \n微信昵称:{$nickname}",
                'keyword1'=>'新订单',
                'keyword2'=>'已支付',
                'keyword3'=>date('Y/m/d H:i',time()),
                'remark'=>'用户备注:'.$order['mark'],
            ]);
        }catch(\Exception $e){}
        $res = $res1 && $resPink;
        return false !== $res;
    }

    /*
     * 线下支付消息通知
     * 待完善
     *
     * */
    public static function createOrderTemplate($order)
    {

        //$goodsName = StoreOrderCartInfo::getProductNameList($order['id']);
//        RoutineTemplateService::sendTemplate(WechatUser::getOpenId($order['uid']),RoutineTemplateService::ORDER_CREATE, [
//            'first'=>'亲，您购买的商品已支付成功',
//            'keyword1'=>date('Y/m/d H:i',$order['add_time']),
//            'keyword2'=>implode(',',$goodsName),
//            'keyword3'=>$order['order_id'],
//            'remark'=>'点击查看订单详情'
//        ],Url::build('/wap/My/order',['uni'=>$order['order_id']],true,true));
//        RoutineTemplateService::sendAdminNoticeTemplate([
//            'first'=>"亲,您有一个新订单 \n订单号:{$order['order_id']}",
//            'keyword1'=>'新订单',
//            'keyword2'=>'线下支付',
//            'keyword3'=>date('Y/m/d H:i',time()),
//            'remark'=>'请及时处理'
//        ]);
    }

    public static function getUserOrderDetail($uid,$key)
    {
        return self::where('order_id|unique',$key)->where('uid',$uid)->where('is_del',0)->find();
    }


    /**
     * TODO 订单发货
     * @param array $postageData 发货信息
     * @param string $oid orderID
     */
    public static function orderPostageAfter($postageData, $oid)
    {
        $order = self::where('id',$oid)->find();
        $url ='/pages/order_details/index?order_id='.$order['order_id'];
        $group = [
            'first'=>'亲,您的订单已发货,请注意查收',
            'remark'=>'点击查看订单详情'
        ];
        if($postageData['delivery_type'] == 'send'){//送货
            $goodsName = StoreOrderCartInfo::getProductNameList($order['id']);
            $group = array_merge($group,[
                'keyword1'=>$goodsName,
                'keyword2'=>$order['pay_type'] == 'offline' ? '线下支付' : date('Y/m/d H:i',$order['pay_time']),
                'keyword3'=>$order['user_address'],
                'keyword4'=>$postageData['delivery_name'],
                'keyword5'=>$postageData['delivery_id']
            ]);
            RoutineTemplate::sendOut('ORDER_DELIVER_SUCCESS',$order['uid'],$group,$url);
        }else if($postageData['delivery_type'] == 'express'){//发货
            $group = array_merge($group,[
                'keyword1'=>$order['order_id'],
                'keyword2'=>$postageData['delivery_name'],
                'keyword3'=>$postageData['delivery_id']
            ]);
            RoutineTemplate::sendOut('ORDER_POSTAGE_SUCCESS',$order['uid'],$group,$url);
        }
    }
    /** 收货后发送模版消息
     * @param $order
     */
    public static function orderTakeAfter($order)
    {
        $title = '';
        $cartInfo = self::getDb('StoreOrderCartInfo')->where('oid', $order['id'])->column('cart_info');
        if(count($cartInfo)){
            foreach ($cartInfo as $key=>&$cart){
                $cart = json_decode($cart,true);
                $title .= $cart['productInfo']['store_name'].',';
            }
        }
        if(strlen(trim($title))) $title = substr($title,0,bcsub(strlen($title),1,0));
        else{
            $cartInfo = self::getDb('store_cart')->alias('a')->where('a.id','in',implode(',',json_decode($order['cart_id'],true)))->find();
            $title = StoreProduct::getProductField($cartInfo['product_id'],'store_name');
        }
        if($order['is_channel']){//小程序
            RoutineTemplate::sendOut('OREDER_TAKEVER',$order['uid'],[
                'keyword1'=>$order['order_id'],
                'keyword2'=>$title,
                'keyword3'=>$order['pay_price'],
                'keyword4'=>date('Y-m-d H:i:s',time()),
            ]);
        }else{
            $openid = WechatUser::where('uid',$order['uid'])->value('openid');
            \app\core\util\WechatTemplateService::sendTemplate($openid,\app\core\util\WechatTemplateService::ORDER_TAKE_SUCCESS,[
                'first'=>'亲，您的订单已收货',
                'keyword1'=>$order['order_id'],
                'keyword2'=>'已收货',
                'keyword3'=>date('Y-m-d H:i:s',time()),
                'keyword4'=>$title,
                'remark'=>'感谢您的光临！'
            ]);
        }
    }

    /**
     * 删除订单
     * @param $uni
     * @param $uid
     * @return bool
     */
    public static function removeOrder($uni, $uid)
    {
        $order = self::getUserOrderDetail($uid,$uni);
        if(!$order) return self::setErrorInfo('订单不存在!');
        $order = self::tidyOrder($order);
        if($order['_status']['_type'] != 0 && $order['_status']['_type']!= -2 && $order['_status']['_type'] != 4)
            return self::setErrorInfo('该订单无法删除!');
        if(false !== self::edit(['is_del'=>1],$order['id'],'id') && false !==StoreOrderStatus::status($order['id'],'remove_order','删除订单')) {
            //未支付和已退款的状态下才可以退积分退库存退优惠券
            if($order['_status']['_type']== 0 || $order['_status']['_type']== -2) {
                HookService::afterListen('store_order_regression_all',$order,null,false,OrderBehavior::class);
            }
            return true;
        }else
            return self::setErrorInfo('订单删除失败!');
    }


    /**
     * //TODO 用户确认收货
     * @param $uni
     * @param $uid
     */
    public static function takeOrder($uni, $uid)
    {
        $order = self::getUserOrderDetail($uid,$uni);
        if(!$order) return self::setErrorInfo('订单不存在!');
        $order = self::tidyOrder($order);
        if($order['_status']['_type'] != 2)  return self::setErrorInfo('订单状态错误!');
        self::beginTrans();
        if(false !== self::edit(['status'=>2],$order['id'],'id') &&
            false !== StoreOrderStatus::status($order['id'],'user_take_delivery','用户已收货')){
            try{
                HookService::listen('store_product_order_user_take_delivery',$order,$uid,false,GoodsBehavior::class);
            }catch (\Exception $e){
                self::rollbackTrans();
                return self::setErrorInfo($e->getMessage());
            }
            self::commitTrans();
            return true;
        }else{
            self::rollbackTrans();
            return false;
        }
    }

    public static function tidyOrder($order,$detail = false,$isPic=false)
    {
        if($detail == true && isset($order['id'])){
            $cartInfo = self::getDb('StoreOrderCartInfo')->where('oid',$order['id'])->column('cart_info','unique')?:[];
            $info=[];
            foreach ($cartInfo as $k=>$cart){
                $cart=json_decode($cart, true);
                $cart['unique']=$k;
                //新增是否评价字段
                $cart['is_reply'] = self::getDb('store_product_reply')->where('unique',$k)->count();
                array_push($info,$cart);
                unset($cart);
            }
            $order['cartInfo'] = $info;
        }

        $status = [];
        if(!$order['paid'] && $order['pay_type'] == 'offline' && !$order['status'] >= 2){
            $status['_type'] = 9;
            $status['_title'] = '线下付款';
            $status['_msg'] = '等待商家处理,请耐心等待';
            $status['_class'] = 'nobuy';
        }else if(!$order['paid']){
            $status['_type'] = 0;
            $status['_title'] = '未支付';
            //系统预设取消订单时间段
            $keyValue=['order_cancel_time','order_activity_time','order_bargain_time','order_seckill_time','order_pink_time'];
            //获取配置
            $systemValue=SystemConfigService::more($keyValue);
            //格式化数据
            $systemValue=self::setValeTime($keyValue,is_array($systemValue) ? $systemValue:[]);
            if($order['pink_id'] || $order['combination_id']){
                $order_pink_time=$systemValue['order_pink_time']  ? $systemValue['order_pink_time'] : $systemValue['order_activity_time'];
                $time=bcadd($order['add_time'],$order_pink_time*3600,0);
                $status['_msg'] = '请在'.date('Y-m-d H:i:s',$time).'前完成支付!';
            }else if($order['seckill_id']){
                $order_seckill_time=$systemValue['order_seckill_time']  ? $systemValue['order_seckill_time'] : $systemValue['order_activity_time'];
                $time=bcadd($order['add_time'],$order_seckill_time*3600,0);
                $status['_msg'] = '请在'.date('Y-m-d H:i:s',$time).'前完成支付!';
            }else if($order['bargain_id']){
                $order_bargain_time=$systemValue['order_bargain_time']  ? $systemValue['order_bargain_time'] : $systemValue['order_activity_time'];
                $time=bcadd($order['add_time'],$order_bargain_time*3600,0);
                $status['_msg'] = '请在'.date('Y-m-d H:i:s',$time).'前完成支付!';
            }else{
                $time=bcadd($order['add_time'],$systemValue['order_cancel_time']*3600,0);
                $status['_msg'] = '请在'.date('Y-m-d H:i:s',$time).'前完成支付!';
            }
            $status['_class'] = 'nobuy';
        }else if($order['refund_status'] == 1){
            $status['_type'] = -1;
            $status['_title'] = '申请退款中';
            $status['_msg'] = '商家审核中,请耐心等待';
            $status['_class'] = 'state-sqtk';
        }else if($order['refund_status'] == 2){
            $status['_type'] = -2;
            $status['_title'] = '已退款';
            $status['_msg'] = '已为您退款,感谢您的支持';
            $status['_class'] = 'state-sqtk';
        }else if(!$order['status']){
            if($order['pink_id']){
                if(StorePink::where('id',$order['pink_id'])->where('status',1)->count()){
                    $status['_type'] = 1;
                    $status['_title'] = '拼团中';
                    $status['_msg'] = '等待其他人参加拼团';
                    $status['_class'] = 'state-nfh';
                }else{
                    $status['_type'] = 1;
                    $status['_title'] = '未发货';
                    $status['_msg'] = '商家未发货,请耐心等待';
                    $status['_class'] = 'state-nfh';
                }
            }else{
                $status['_type'] = 1;
                $status['_title'] = '未发货';
                $status['_msg'] = '商家未发货,请耐心等待';
                $status['_class'] = 'state-nfh';
            }
        }else if($order['status'] == 1){
           if($order['delivery_type'] == 'send'){//TODO 送货
               $status['_type'] = 2;
               $status['_title'] = '待收货';
               $status['_msg'] = date('m月d日H时i分',StoreOrderStatus::getTime($order['id'],'delivery')).'服务商已送货';
               $status['_class'] = 'state-ysh';
           }else if($order['delivery_type'] == 'express'){//TODO  发货
               $status['_type'] = 2;
               $status['_title'] = '待收货';
               $status['_msg'] = date('m月d日H时i分',StoreOrderStatus::getTime($order['id'],'delivery_goods')).'服务商已发货';
               $status['_class'] = 'state-ysh';
           }else if($order['delivery_type'] == 'fictitious'){
               $status['_type'] = 2;
               $status['_title'] = '虚拟发货';
               $status['_msg'] = date('m月d日H时i分',StoreOrderStatus::getTime($order['id'],'delivery_fictitious')).'服务商已发货';
               $status['_class'] = 'state-ysh';
           }
        }else if($order['status'] == 2){
            $status['_type'] = 3;
            $status['_title'] = '待评价';
            $status['_msg'] = '已收货,快去评价一下吧';
            $status['_class'] = 'state-ypj';
        }else if($order['status'] == 3){
            $status['_type'] = 4;
            $status['_title'] = '交易完成';
            $status['_msg'] = '交易完成,感谢您的支持';
            $status['_class'] = 'state-ytk';
        }
        if(isset($order['pay_type']))
            $status['_payType'] = isset(self::$payType[$order['pay_type']]) ? self::$payType[$order['pay_type']] : '其他方式';
        if(isset($order['delivery_type']))
            $status['_deliveryType'] = isset(self::$deliveryType[$order['delivery_type']]) ? self::$deliveryType[$order['delivery_type']] : '其他方式';
        $order['_status'] = $status;
        $order['_pay_time']=isset($order['pay_time']) && $order['pay_time'] != null ? date('Y-m-d H:i:s',$order['pay_time']) : date('Y-m-d H:i:s',$order['add_time']);
        $order['_add_time']=isset($order['add_time']) ? (strstr($order['add_time'],'-')===false ? date('Y-m-d H:i:s',$order['add_time']) : $order['add_time'] ): '';
        $order['status_pic']='';
        //获取产品状态图片
        if($isPic){
            $order_details_images=\app\core\util\GroupDataService::getData('order_details_images') ? : [];
            foreach ($order_details_images as $image){
                if(isset($image['order_status']) && $image['order_status']==$order['_status']['_type']){
                    $order['status_pic']=$image['pic'];
                    break;
                }
            }
        }
        return $order;
    }

    public static function statusByWhere($status,$uid=0,$model = null)
    {
//        $orderId = StorePink::where('uid',$uid)->where('status',1)->column('order_id','id');//获取正在拼团的订单编号
        if($model == null) $model = new self;
        if('' === $status)
            return $model;
        else if($status == 0)
            return $model->where('paid',0)->where('status',0)->where('refund_status',0);
        else if($status == 1)//待发货
            return $model->where('paid',1)->where('status',0)->where('refund_status',0);
        else if($status == 2)
            return $model->where('paid',1)->where('status',1)->where('refund_status',0);
        else if($status == 3)
            return $model->where('paid',1)->where('status',2)->where('refund_status',0);
        else if($status == 4)
            return $model->where('paid',1)->where('status',3)->where('refund_status',0);
        else if($status == -1)
            return $model->where('paid',1)->where('refund_status',1);
        else if($status == -2)
            return $model->where('paid',1)->where('refund_status',2);
        else if($status == -3)
            return $model->where('paid',1)->where('refund_status','IN','1,2');
//        else if($status == 11){
//            return $model->where('order_id','IN',implode(',',$orderId));
//        }
        else
            return $model;
    }

    public static function getUserOrderList($uid,$status = '',$page = 0,$limit = 8)
    {
        $list = self::statusByWhere($status,$uid)->where('is_del',0)->where('uid',$uid)
            ->field('add_time,seckill_id,bargain_id,combination_id,id,order_id,pay_price,total_num,total_price,pay_postage,total_postage,paid,status,refund_status,pay_type,coupon_price,deduction_price,pink_id,delivery_type,is_del')
            ->order('add_time DESC')->page((int)$page,(int)$limit)->select()->toArray();
        foreach ($list as $k=>$order){
            $list[$k] = self::tidyOrder($order,true);
        }
       
        return $list;
    }

    /**
     * 获取推广人地下用户的订单金额
     * @param string $uid
     * @param string $status
     * @return array
     */
    public static function getUserOrderCount($uid = '',$status = ''){
        $res = self::statusByWhere($status,$uid)->where('uid','IN',$uid)->column('pay_price');
        return $res;
    }

    public static function searchUserOrder($uid,$order_id)
    {
        $order = self::where('uid',$uid)->where('order_id',$order_id)->where('is_del',0)->field('seckill_id,bargain_id,combination_id,id,order_id,pay_price,total_num,total_price,pay_postage,total_postage,paid,status,refund_status,pay_type,coupon_price,deduction_price,delivery_type')
            ->order('add_time DESC')->find();
        if(!$order)
            return false;
        else
            return self::tidyOrder($order->toArray(),true);

    }

    public static function orderOver($oid)
    {
        $res = self::edit(['status'=>'3'],$oid,'id');
        if(!$res) exception('评价后置操作失败!');
        StoreOrderStatus::status($oid,'check_order_over','用户评价');
    }

    public static function checkOrderOver($oid)
    {
        $uniqueList = StoreOrderCartInfo::where('oid',$oid)->column('unique');
        if(StoreProductReply::where('unique','IN',$uniqueList)->where('oid',$oid)->count() == count($uniqueList)){
            HookService::listen('store_product_order_over',$oid,null,false,GoodsBehavior::class);
            self::orderOver($oid);
        }
    }


    public static function getOrderStatusNum($uid)
    {
        $noBuy = self::where('uid',$uid)->where('paid',0)->where('is_del',0)->where('pay_type','<>','offline')->count();
        $noPostageNoPink = self::where('o.uid',$uid)->alias('o')->where('o.paid',1)->where('o.pink_id',0)->where('o.is_del',0)->where('o.status',0)->where('o.pay_type','<>','offline')->count();
        $noPostageYesPink = self::where('o.uid',$uid)->alias('o')->join('StorePink p','o.pink_id = p.id')->where('p.status',2)->where('o.paid',1)->where('o.is_del',0)->where('o.status',0)->where('o.pay_type','<>','offline')->count();
        $noPostage = bcadd($noPostageNoPink,$noPostageYesPink);
        $noTake = self::where('uid',$uid)->where('paid',1)->where('is_del',0)->where('status',1)->where('pay_type','<>','offline')->count();
        $noReply = self::where('uid',$uid)->where('paid',1)->where('is_del',0)->where('status',2)->count();
        $noPink = self::where('o.uid',$uid)->alias('o')->join('StorePink p','o.pink_id = p.id')->where('p.status',1)->where('o.paid',1)->where('o.is_del',0)->where('o.status',0)->where('o.pay_type','<>','offline')->count();
        $noRefund = self::where('uid',$uid)->where('paid',1)->where('is_del',0)->where('refund_status','IN','1,2')->count();
        return compact('noBuy','noPostage','noTake','noReply','noPink','noRefund');
    }

    public static function gainUserIntegral($order)
    {
        if($order['gain_integral'] > 0){
            $userInfo = User::getUserInfo($order['uid']);
            ModelBasic::beginTrans();
            $res1 = false != User::where('uid',$userInfo['uid'])->update(['integral'=>bcadd($userInfo['integral'],$order['gain_integral'],2)]);
            $res2 = false != UserBill::income('购买商品赠送积分',$order['uid'],'integral','gain',$order['gain_integral'],$order['id'],$userInfo['integral'],'购买商品赠送'.floatval($order['gain_integral']).'积分');
            $res = $res1 && $res2;
            ModelBasic::checkTrans($res);
            return $res;
        }
        return true;
    }

    /**
     * 获取当前订单中有没有拼团存在
     * @param $pid
     * @return int|string
     */
    public static function getIsOrderPink($pid = 0 ,$uid = 0){
        return self::where('uid',$uid)->where('pink_id',$pid)->where('refund_status',0)->where('is_del',0)->count();
    }

    /**
     * 获取order_id
     * @param $pid
     * @return mixed
     */
    public static function getStoreIdPink($pid = 0 ,$uid = 0){
        return self::where('uid',$uid)->where('pink_id',$pid)->where('is_del',0)->value('order_id');
    }

    /**
     * 删除当前用户拼团未支付的订单
     */
    public static function delCombination(){
        self::where('combination','GT',0)->where('paid',0)->where('uid',User::getActiveUid())->delete();
    }

    public static function getUserPrice($uid =0){
        if(!$uid) return 0;
        $price = self::where('paid',1)->where('uid',$uid)->where('status',2)->where('refund_status',0)->column('pay_price','id');
        $count = 0;
        if($price){
            foreach ($price as $v){
                $count = bcadd($count,$v,2);
            }
        }
        return $count;
    }


    /*
     * 个人中心获取个人订单列表和订单搜索
     * @param int $uid 用户uid
     * @param int | string 查找订单类型
     * @param int $first 分页
     * @param int 每页显示多少条
     * @param string $search 订单号
     * @return array
     * */
    public static function getUserOrderSearchList($uid,$type,$page,$limit,$search)
    {
        if($search){
            $order = self::searchUserOrder($uid,$search)?:[];
            $list = $order == false ? [] : [$order];
        }else{
            $list = self::getUserOrderList($uid,$type,$page,$limit);
        }
        foreach ($list as $k=>$order){
            $list[$k] = self::tidyOrder($order,true);
            if($list[$k]['_status']['_type'] == 3){
                foreach ($order['cartInfo']?:[] as $key=>$product){
                    $list[$k]['cartInfo'][$key]['is_reply'] = StoreProductReply::isReply($product['unique'],'product');
                    $list[$k]['cartInfo'][$key]['add_time'] = date('Y-m-d H:i',$product['add_time']);
                }
            }
        }
        return $list;
    }

    /*
     * 获取用户下级的订单
     * @param int $xuid 下级用户用户uid
     * @param int $uid 用户uid
     * @param int $type 订单类型
     * @param int $first 截取行数
     * @param int $limit 展示条数
     * @return array
     * */
    public static function getSubordinateOrderlist($xUid,$uid,$type,$first,$limit)
    {
        $list = [];
        if(!$xUid){
            $arr = User::getOneSpreadUid($uid);
            foreach($arr as $v) $list = StoreOrder::getUserOrderList($v,$type,$first,$limit);
        }else $list = self::getUserOrderList($xUid,$type,$first,$limit);
        foreach ($list as $k=>$order){
            $list[$k] = self::tidyOrder($order,true);
            if($list[$k]['_status']['_type'] == 3){
                foreach ($order['cartInfo']?:[] as $key=>$product){
                    $list[$k]['cartInfo'][$key]['is_reply'] = StoreProductReply::isReply($product['unique'],'product');
                }
            }
        }
        return $list;
    }

    /*
     * 获取某个用户的订单统计数据
     * @param int $uid 用户uid
     * */
    public static function getOrderData($uid)
    {
        $data['order_count']=self::where(['is_del'=>0,'paid'=>1,'uid'=>$uid,'refund_status'=>0])->count();
        $data['sum_price']=self::where(['is_del'=>0,'paid'=>1,'uid'=>$uid,'refund_status'=>0])->sum('pay_price');
        $data['unpaid_count']=self::statusByWhere(0,$uid)->where('is_del',0)->where('uid',$uid)->count();
        $data['unshipped_count']=self::statusByWhere(1,$uid)->where('is_del',0)->where('uid',$uid)->count();
        $data['received_count']=self::statusByWhere(2,$uid)->where('is_del',0)->where('uid',$uid)->count();
        $data['evaluated_count']=self::statusByWhere(3,$uid)->where('is_del',0)->where('uid',$uid)->count();
        $data['complete_count']=self::statusByWhere(4,$uid)->where('is_del',0)->where('uid',$uid)->count();
        return $data;
    }


    /*
     * 累计消费
     * **/
    public static function getOrderStatusSum($uid)
    {
        return self::where(['uid'=>$uid,'is_del'=>0,'paid'=>1])->sum('pay_price');
    }


    /**
     * 获取余额支付的金额
     * @param $uid
     * @return float|int
     */
    public static function getOrderStatusYueSum($uid)
    {
        return self::where(['uid'=>$uid,'is_del'=>0,'pay_type'=>'yue','paid'=>1])->sum('pay_price');
    }
    public static function getPinkOrderId($id){
        return self::where('id',$id)->value('order_id');
    }

    /*
     * 未支付订单自动取消
     * @param int $limit 分页截取条数
     * @param $prefid 缓存名称
     * @param $expire 缓存时间
     * */
    public static function orderUnpaidCancel($limit=10,$prefid=ApiLogs::ORDER_UNPAID_PAGE,$expire=3600)
    {
        //系统预设取消订单时间段
        $keyValue=['order_cancel_time','order_activity_time','order_bargain_time','order_seckill_time','order_pink_time'];
        //未支付查询条件
        $UnPaidwhere=['paid'=>0,'is_del'=>0,'status'=>0,'refund_status'=>0];
        //获取配置
        $systemValue=SystemConfigService::more($keyValue);
        //格式化数据
        $systemValue=self::setValeTime($keyValue,is_array($systemValue) ? $systemValue:[]);
        //检查是否有未支付的订单
        $unPidCount=self::where($UnPaidwhere)->count();
        if(!$unPidCount) return null;
        //总分页条数
        $pagesSum=ceil(bcdiv($unPidCount,$limit,2));
        if(Cache::has($prefid)){
            $pages=Cache::get($prefid);
            $pages++;
            Cache::set($prefid,$pages,$expire);
        }else{
            $pages=1;
            Cache::set($prefid,$pages,$expire);
        }
        if($pages > $pagesSum) Cache::set($prefid,0,$expire);
        self::beginTrans();
        try{
            $res=true;
            $orderList = self::where($UnPaidwhere)->field(['add_time','pink_id','order_id','seckill_id','bargain_id','combination_id','status','cart_id','use_integral',
                'refund_status','uid','unique','back_integral','coupon_id','paid','is_del'])->page($pages,$limit)->select();
            foreach ($orderList as $order){
                if($order['seckill_id']){
                    //优先使用单独配置的过期时间
                    $order_seckill_time=$systemValue['order_seckill_time']  ? $systemValue['order_seckill_time'] : $systemValue['order_activity_time'];
                    $res=$res && self::RegressionAll($order_seckill_time,$order);
                }else if($order['bargain_id']){
                    $order_bargain_time=$systemValue['order_bargain_time'] ? $systemValue['order_bargain_time'] : $systemValue['order_activity_time'];
                    $res=$res && self::RegressionAll($order_bargain_time,$order);
                }else if($order['pink_id'] || $order['combination_id']){
                    $order_pink_time=$systemValue['order_pink_time'] ? $systemValue['order_pink_time'] : $systemValue['order_activity_time'];
                    $res=$res && self::RegressionAll($order_pink_time,$order);
                }else{
                    $res=$res && self::RegressionAll($systemValue['order_cancel_time'],$order);
                }
            }
            if($res) self::commitTrans();
        }catch (PDOException $e){
            self::rollbackTrans();
            ApiLogs::writeLog(['file'=>$e->getFile(),'line'=>$e->getLine(),'message'=>$e->getMessage()],'s');
        }catch (\think\Exception $e){
            self::rollbackTrans();
            ApiLogs::recodeErrorLog($e);
        }

    }

    /*
     * 未支付订单超过预设时间回退所有,如果不设置未支付过期时间，将不取消订单
     * @param int $time 预设时间
     * @param array $order 订单详情
     * @return boolean
     * */
    protected static function RegressionAll($time,$order)
    {
        if($time==0) return true;
        if(($order['add_time']+bcmul($time,3600,0)) < time()){
            $res1=self::RegressionStock($order);
            $res2=self::RegressionIntegral($order);
            $res3=self::RegressionCoupon($order);
            $res = $res1 && $res2 && $res3;
            if($res) $res = false !== self::where(['order_id'=>$order['order_id']])->update(['is_del'=>1,'mark'=>'订单未支付已超过系统预设时间']);
            return $res;
        }else
            return true;
    }

    /*
     * 格式化数据
     * @param $array 原本数据键
     * @param $array 需要格式化的数据
     * @param int $default 默认值
     * @return array
     * */
    protected static function setValeTime(array $array,$value,$default=0)
    {
        foreach ($array as $item) {
            if(!isset($value[$item]))
                $value[$item]=$default;
            else if(is_string($value[$item]))
                $value[$item]=(float)$value[$item];
        }
        return $value;
    }
}