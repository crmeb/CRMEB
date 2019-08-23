<?php
namespace app\ebapi\controller;

use app\core\model\routine\RoutineFormId;//待完善
use app\core\model\user\UserLevel;
use service\JsonService;
use app\core\util\SystemConfigService;
use service\UtilService;
use think\Request;
use app\core\behavior\GoodsBehavior;//待完善
use app\ebapi\model\store\StoreCouponUser;
use app\ebapi\model\store\StoreOrder;
use app\ebapi\model\store\StoreProductAttrValue;
use app\ebapi\model\store\StoreCart;
use app\ebapi\model\user\User;
use app\ebapi\model\store\StorePink;
use app\ebapi\model\store\StoreBargainUser;
use app\ebapi\model\store\StoreBargainUserHelp;

/**
 * 小程序 购物车,新增订单等 api接口
 * Class AuthApi
 * @package app\ebapi\controller
 *
 */
class AuthApi extends AuthController
{

    /**
     * 购物车
     * @return \think\response\Json
     */
    public function get_cart_list()
    {
        return JsonService::successful(StoreCart::getUserProductCartList($this->userInfo['uid']));
    }


    /*
     * 获取订单支付状态
     * @param string ordre_id 订单id
     * @return json
     * */
    public function get_order_pay_info($order_id = '')
    {
        if ($order_id == '') return JsonService::fail('缺少参数');
        return JsonService::successful(StoreOrder::tidyOrder(StoreOrder::where('order_id', $order_id)->find()));
    }
    /**
     * 订单页面
     * @param Request $request
     * @return \think\response\Json
     */
    public function confirm_order(Request $request)
    {
        $data = UtilService::postMore(['cartId'], $request);
        $cartId = $data['cartId'];
        if (!is_string($cartId) || !$cartId) return JsonService::fail('请提交购买的商品');
        $cartGroup = StoreCart::getUserProductCartList($this->userInfo['uid'], $cartId, 1);
        if (count($cartGroup['invalid'])) return JsonService::fail($cartGroup['invalid'][0]['productInfo']['store_name'] . '已失效!');
        if (!$cartGroup['valid']) return JsonService::fail('请提交购买的商品');
        $cartInfo = $cartGroup['valid'];
        $priceGroup = StoreOrder::getOrderPriceGroup($cartInfo);
        $other = [
            'offlinePostage' => SystemConfigService::get('offline_postage'),
            'integralRatio' => SystemConfigService::get('integral_ratio')
        ];
        $usableCoupon = StoreCouponUser::beUsableCoupon($this->userInfo['uid'], $priceGroup['totalPrice']);
        $cartIdA = explode(',', $cartId);
        if (count($cartIdA) > 1) $seckill_id = 0;
        else {
            $seckillinfo = StoreCart::where('id', $cartId)->find();
            if ((int)$seckillinfo['seckill_id'] > 0) $seckill_id = $seckillinfo['seckill_id'];
            else $seckill_id = 0;
        }
        $data['usableCoupon'] = $usableCoupon;
        $data['seckill_id'] = $seckill_id;
        $data['cartInfo'] = $cartInfo;
        $data['priceGroup'] = $priceGroup;
        $data['orderKey'] = StoreOrder::cacheOrderInfo($this->userInfo['uid'], $cartInfo, $priceGroup, $other);
        $data['offlinePostage'] = $other['offlinePostage'];
        $vipId=UserLevel::getUserLevel($this->uid);
        $this->userInfo['vip']=$vipId !==false ? true : false;
        if($this->userInfo['vip']){
            $this->userInfo['vip_id']=$vipId;
            $this->userInfo['discount']=UserLevel::getUserLevelInfo($vipId,'discount');
        }
        $data['userInfo']=$this->userInfo;
        $data['integralRatio'] = $other['integralRatio'];
        return JsonService::successful($data);
    }

    /*
     * 获取小程序订单列表统计数据
     *
     * */
    public function get_order_data()
    {
        return JsonService::successful(StoreOrder::getOrderData($this->uid));
    }
    /**
     * 过度查$uniqueId
     * @param string $productId
     * @param int $cartNum
     * @param string $uniqueId
     * @return \think\response\Json
     */
    public function unique()
    {
        $productId = $_GET['productId'];
        if (!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $uniqueId = StoreProductAttrValue::where('product_id', $productId)->value('unique');
        $data = $this->set_cart($productId, $cartNum = 1, $uniqueId);
        if ($data == true) {
            return JsonService::successful('ok');
        }
    }
    /**
     * 加入到购物车
     * @param string $productId
     * @param int $cartNum
     * @param string $uniqueId
     * @return \think\response\Json
     */
    public function set_cart($productId = '', $cartNum = 1, $uniqueId = '')
    {
        if (!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $res = StoreCart::setCart($this->userInfo['uid'], $productId, $cartNum, $uniqueId, 'product');
        if (!$res) return JsonService::fail(StoreCart::getErrorInfo());
        else return JsonService::successful('ok', ['cartId' => $res->id]);
    }

    /**
     * 拼团 秒杀 砍价 加入到购物车
     * @param string $productId
     * @param int $cartNum
     * @param string $uniqueId
     * @param int $combinationId
     * @param int $secKillId
     * @return \think\response\Json
     */
    public function now_buy($productId = '', $cartNum = 1, $uniqueId = '', $combinationId = 0, $is_new = 0,$secKillId = 0, $bargainId = 0)
    {
        if (!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        if ($bargainId && StoreBargainUserHelp::getSurplusPrice($bargainId, $this->userInfo['uid'])) return JsonService::fail('请先砍价');
        $res = StoreCart::setCart($this->userInfo['uid'], $productId, $cartNum, $uniqueId, 'product', $is_new, $combinationId, $secKillId, $bargainId);
        if (!$res) return JsonService::fail(StoreCart::getErrorInfo());
        else  return JsonService::successful('ok', ['cartId' => $res->id]);
    }
    /**
     * 获取购物车数量
     * @return \think\response\Json
     */
    public function get_cart_num()
    {
        return JsonService::successful('ok', StoreCart::getUserCartNum($this->userInfo['uid'], 'product'));
    }
    
    /**
     * 修改购物车产品数量
     * @param string $cartId
     * @param string $cartNum
     * @return \think\response\Json
     */
    public function change_cart_num($cartId = '', $cartNum = '')
    {
        if (!$cartId || !$cartNum || !is_numeric($cartId) || !is_numeric($cartNum)) return JsonService::fail('参数错误!');
        $res = StoreCart::changeUserCartNum($cartId, $cartNum, $this->userInfo['uid']);
        if ($res)  return JsonService::successful();
        else return JsonService::fail(StoreCart::getErrorInfo('修改失败'));
    }

    /**
     * 删除购物车产品
     * @param string $ids
     * @return \think\response\Json
     */
    public function remove_cart($ids = '')
    {
        if (!$ids) return JsonService::fail('参数错误!');
        if(StoreCart::removeUserCart($this->userInfo['uid'], $ids))
            return JsonService::successful();
        else
            return JsonService::fail('清除失败！');
    }
    /**
     * 创建订单
     * @param string $key
     * @return \think\response\Json
     */
    public function create_order($key = '')
    {
        if (!$key) return JsonService::fail('参数错误!');
        if (StoreOrder::be(['order_id|unique' => $key, 'uid' => $this->userInfo['uid'], 'is_del' => 0]))
            return JsonService::status('extend_order', '订单已生成', ['orderId' => $key, 'key' => $key]);
        list($addressId, $couponId, $payType, $useIntegral, $mark, $combinationId, $pinkId, $seckill_id, $formId, $bargainId) = UtilService::postMore([
            'addressId', 'couponId', 'payType', 'useIntegral', 'mark', ['combinationId', 0], ['pinkId', 0], ['seckill_id', 0], ['formId', ''], ['bargainId', '']
        ], Request::instance(), true);
        $payType = strtolower($payType);
        if ($bargainId) StoreBargainUser::setBargainUserStatus($bargainId, $this->userInfo['uid']); //修改砍价状态
        if ($pinkId) if (StorePink::getIsPinkUid($pinkId, $this->userInfo['uid'])) return JsonService::status('ORDER_EXIST', '订单生成失败，你已经在该团内不能再参加了', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $this->userInfo['uid'])]);
        if ($pinkId) if (StoreOrder::getIsOrderPink($pinkId, $this->userInfo['uid'])) return JsonService::status('ORDER_EXIST', '订单生成失败，你已经参加该团了，请先支付订单', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $this->userInfo['uid'])]);
        $order = StoreOrder::cacheKeyCreateOrder($this->userInfo['uid'], $key, $addressId, $payType, $useIntegral, $couponId, $mark, $combinationId, $pinkId, $seckill_id, $bargainId);
        $orderId = $order['order_id'];
        $info = compact('orderId', 'key');
        if ($orderId) {
            switch ($payType) {
                case "weixin":
                    $orderInfo = StoreOrder::where('order_id', $orderId)->find();
                    if (!$orderInfo || !isset($orderInfo['paid'])) exception('支付订单不存在!');
                    if ($orderInfo['paid']) exception('支付已支付!');
                    //如果支付金额为0
                    if (bcsub((float)$orderInfo['pay_price'], 0, 2) <= 0) {
                        //创建订单jspay支付
                        if (StoreOrder::jsPayPrice($orderId, $this->userInfo['uid'], $formId))
                            return JsonService::status('success', '微信支付成功', $info);
                        else
                            return JsonService::status('pay_error', StoreOrder::getErrorInfo());
                    } else {
                        RoutineFormId::SetFormId($formId, $this->uid);
                        try {
                            $jsConfig = StoreOrder::jsPay($orderId); //创建订单jspay
                            if(isset($jsConfig['package']) && $jsConfig['package']){
                                $package=str_replace('prepay_id=','',$jsConfig['package']);
                                for($i=0;$i<3;$i++){
                                    RoutineFormId::SetFormId($package, $this->uid);
                                }
                            }
                        } catch (\Exception $e) {
                            return JsonService::status('pay_error', $e->getMessage(), $info);
                        }
                        $info['jsConfig'] = $jsConfig;
                        return JsonService::status('wechat_pay', '订单创建成功', $info);
                    }
                    break;
                case 'yue':
                    if (StoreOrder::yuePay($orderId, $this->userInfo['uid'], $formId))
                        return JsonService::status('success', '余额支付成功', $info);
                    else {
                        $errorinfo = StoreOrder::getErrorInfo();
                        if (is_array($errorinfo))
                            return JsonService::status($errorinfo['status'], $errorinfo['msg'], $info);
                        else
                            return JsonService::status('pay_error', $errorinfo);
                    }
                    break;
                case 'offline':
                    RoutineFormId::SetFormId($formId, $this->uid);
                    //                RoutineTemplate::sendOrderSuccess($formId,$orderId);//发送模板消息
                    return JsonService::status('success', '订单创建成功', $info);
                    break;
            }
        } else return JsonService::fail(StoreOrder::getErrorInfo('订单生成失败!'));
    }

    /*
     * 再来一单
     *
     * */
    public function again_order($uni = ''){
        if(!$uni) return JsonService::fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($this->userInfo['uid'],$uni);
        if(!$order) return JsonService::fail('订单不存在!');
        $order = StoreOrder::tidyOrder($order,true);
        $res = array();
        foreach ($order['cartInfo'] as $v) {
            if($v['combination_id']) return JsonService::fail('拼团产品不能再来一单，请在拼团产品内自行下单!');
            else if($v['bargain_id']) return JsonService::fail('砍价产品不能再来一单，请在砍价产品内自行下单!');
            else if($v['seckill_id']) return JsonService::fail('秒杀产品不能再来一单，请在砍价产品内自行下单!');
            else $res[] = StoreCart::setCart($this->userInfo['uid'], $v['product_id'], $v['cart_num'], isset($v['productInfo']['attrInfo']['unique']) ? $v['productInfo']['attrInfo']['unique'] : '', 'product', 0, 0);
        }
        $cateId = [];
        foreach ($res as $v){
            if(!$v) return JsonService::fail('再来一单失败，请重新下单!');
            $cateId[] = $v['id'];
        }
        return JsonService::successful('ok',implode(',',$cateId));
    }

    //TODO 支付订单
    /**
     * 支付订单
     * @param string $uni
     * @return \think\response\Json
     */
    public function pay_order($uni = '', $paytype = 'weixin')
    {
        if (!$uni) return JsonService::fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($this->userInfo['uid'], $uni);
        if (!$order) return JsonService::fail('订单不存在!');
        if ($order['paid']) return JsonService::fail('该订单已支付!');
        if ($order['pink_id']) if (StorePink::isPinkStatus($order['pink_id'])) return JsonService::fail('该订单已失效!');
        $order['pay_type'] = $paytype; //重新支付选择支付方式
        switch ($order['pay_type']) {
            case 'weixin':
                try {
                    $jsConfig = StoreOrder::jsPay($order); //订单列表发起支付
                    if(isset($jsConfig['package']) && $jsConfig['package']){
                        $package=str_replace('prepay_id=','',$jsConfig['package']);
                        for($i=0;$i<3;$i++){
                            RoutineFormId::SetFormId($package, $this->uid);
                        }
                    }
                } catch (\Exception $e) {
                    return JsonService::fail($e->getMessage());
                }
                return JsonService::status('wechat_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                break;
            case 'yue':
                if ($res = StoreOrder::yuePay($order['order_id'], $this->userInfo['uid']))
                    return JsonService::successful('余额支付成功');
                else {
                    $error = StoreOrder::getErrorInfo();
                    return JsonService::fail(is_array($error) && isset($error['msg']) ? $error['msg'] : $error);
                }
                break;
            case 'offline':
                StoreOrder::createOrderTemplate($order);
                return JsonService::successful('订单创建成功');
                break;
        }
    }

    /*
     * 未支付的订单取消订单回退积分,回退优惠券,回退库存
     * @param string $order_id 订单id
     * */
    public function cancel_order($order_id = '')
    {
        if (StoreOrder::cancelOrder($order_id))
            return JsonService::successful('取消订单成功');
        else
            return JsonService::fail(StoreOrder::getErrorInfo());
    }

    /**
     * 申请退款
     * @param string $uni
     * @param string $text
     * @return \think\response\Json
     */
    public function apply_order_refund(Request $request)
    {
        $data = UtilService::postMore([
            ['text', ''],
            ['refund_reason_wap_img', ''],
            ['refund_reason_wap_explain', ''],
            ['uni', '']
        ], $request);
        $uni = $data['uni'];
        unset($data['uni']);
        if ($data['refund_reason_wap_img']) $data['refund_reason_wap_img'] = explode(',', $data['refund_reason_wap_img']);
        if (!$uni || $data['text'] == '') return JsonService::fail('参数错误!');
        $res = StoreOrder::orderApplyRefund($uni, $this->userInfo['uid'], $data['text'], $data['refund_reason_wap_explain'], $data['refund_reason_wap_img']);
        if ($res)
            return JsonService::successful();
        else
            return JsonService::fail(StoreOrder::getErrorInfo());
    }


    /**
     * 再来一单
     * @param string $uni
     */
    public function order_details($uni = '')
    {

        if (!$uni) return JsonService::fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($this->userInfo['uid'], $uni);
        if (!$order) return JsonService::fail('订单不存在!');
        $order = StoreOrder::tidyOrder($order, true);
        $res = array();
        foreach ($order['cartInfo'] as $v) {
            if ($v['combination_id']) return JsonService::fail('拼团产品不能再来一单，请在拼团产品内自行下单!');
            else  $res[] = StoreCart::setCart($this->userInfo['uid'], $v['product_id'], $v['cart_num'], isset($v['productInfo']['attrInfo']['unique']) ? $v['productInfo']['attrInfo']['unique'] : '', 'product', 0, 0);
        }
        $cateId = [];
        foreach ($res as $v) {
            if (!$v) return JsonService::fail('再来一单失败，请重新下单!');
            $cateId[] = $v['id'];
        }
        return JsonService::successful('ok', implode(',', $cateId));
    }
    /**
     * 购物车库存修改
     * @param int $cartId
     * @param int $cartNum
     */
    public function set_buy_cart_num($cartId = 0, $cartNum = 0)
    {
        if (!$cartId) return JsonService::fail('参数错误');
        $res = StoreCart::edit(['cart_num' => $cartNum], $cartId);
        if ($res) return JsonService::successful();
        else return JsonService::fail('修改失败');
    }
}
