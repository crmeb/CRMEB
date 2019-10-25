<?php
namespace app\api\controller\admin;

use app\models\store\StoreOrder;
use app\models\store\StoreOrderStatus;
use app\models\store\StorePink;
use app\models\store\StoreService;
use app\models\user\User;
use app\models\user\UserBill;
use app\Request;
use crmeb\repositories\OrderRepository;
use crmeb\services\MiniProgramService;
use crmeb\services\UtilService;
use crmeb\services\WechatService;

/**
 * 订单类
 * Class StoreOrderController
 * @package app\api\controller\admin\order
 */
class StoreOrderController
{
    /**
     *  订单数据统计
     * @param Request $request
     * @return mixed
     */
    public function statistics(Request $request)
    {
        $uid = $request->uid();
        if(!StoreService::orderServiceStatus($uid))
            return app('json')->fail('权限不足');
        $dataCount = StoreOrder::getOrderDataAdmin();
        $dataPrice = StoreOrder::getOrderTimeData();
        $data = array_merge($dataCount, $dataPrice);
        return app('json')->successful($data);
    }

    /**
     * 订单每月统计数据
     * @param Request $request
     * @return mixed
     */
    public function data(Request $request)
    {
        $uid = $request->uid();
        if(!StoreService::orderServiceStatus($uid))
            return app('json')->fail('权限不足');
        list($page, $limit) = UtilService::getMore([['page',1],['limit',7]], $request, true);
        if(!$limit) return app('json')->successful([]);
        $data = StoreOrder::getOrderDataPriceCount($page, $limit);
        if($data)  return app('json')->successful($data->toArray());
        return app('json')->successful([]);
    }

    /**
     * 订单列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        $uid = $request->uid();
        if(!StoreService::orderServiceStatus($uid))
            return app('json')->fail('权限不足');
        $where = UtilService::getMore([
            ['status',''],
            ['is_del',0],
            ['data',''],
            ['type',''],
            ['order',''],
            ['page',0],
            ['limit',0]
        ], $request);
        if(!$where['limit']) return app('json')->successful([]);
        return app('json')->successful(StoreOrder::orderList($where));
    }

    /**
     * 订单详情
     * @param Request $request
     * @param $orderId
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail(Request $request, $orderId)
    {
        $uid = $request->uid();
        if(!StoreService::orderServiceStatus($uid))
            return app('json')->fail('权限不足');
        $order = StoreOrder::getAdminOrderDetail($orderId, 'id,uid,order_id,add_time,status,total_num,total_price,total_postage,pay_price,pay_postage,paid,refund_status,remark,pink_id,combination_id,mark,seckill_id,bargain_id,delivery_type,pay_type,real_name,user_phone,user_address,coupon_price,freight_price,delivery_name,delivery_type,delivery_id');
        if(!$order) return app('json')->fail('订单不存在');
        $order = $order->toArray();
        $nickname = User::getUserInfo($order['uid'], 'nickname')['nickname'];
        $orderInfo = StoreOrder::tidyAdminOrder([$order], true)[0];
        unset($orderInfo['uid'], $orderInfo['seckill_id'], $orderInfo['pink_id'], $orderInfo['combination_id'], $orderInfo['bargain_id'], $orderInfo['status'], $orderInfo['total_postage']);
        $orderInfo['nickname'] = $nickname;
        return app('json')->successful('ok',$orderInfo);
    }

    /**
     * 订单发货获取订单信息
     * @param Request $request
     * @param $orderId
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delivery_gain(Request $request, $orderId)
    {
        $uid = $request->uid();
        if(!StoreService::orderServiceStatus($uid))
            return app('json')->fail('权限不足');
        $order = StoreOrder::getAdminOrderDetail($orderId, 'real_name,user_phone,user_address,order_id,uid,status,paid');
        if(!$order) return app('json')->fail('订单不存在');
        if($order['paid']){
            $order['nickname'] = User::getUserInfo($order['uid'], 'nickname')['nickname'];
            $order = $order->hidden(['uid','status','paid'])->toArray();
            return app('json')->successful('ok',$order);
        }
        return app('json')->fail('状态错误');
    }

    /**
     * 订单发货
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delivery_keep(Request $request)
    {
        $uid = $request->uid();
        if(!StoreService::orderServiceStatus($uid))
            return app('json')->fail('权限不足');
        list($order_id, $delivery_type, $delivery_name, $delivery_id) = UtilService::postMore([
            ['order_id',''],
            ['delivery_type', 0],
            ['delivery_name', ''],
            ['delivery_id', ''],
        ], $request, true);
        $order = StoreOrder::getAdminOrderDetail($order_id, 'id,status,paid');
        if(!$order) return app('json')->fail('订单不存在');
        if(!$order['status'] && $order['paid']){
            $deliveryTypeArr = ['send','express','fictitious'];
            if(!strlen(trim($delivery_type))) return app('json')->fail('请填写发货方式');
            if(!in_array($delivery_type, $deliveryTypeArr)) return app('json')->fail('发货方式错误');
            if($delivery_type == 'express'){
                if(!strlen(trim($delivery_name))) return app('json')->fail('请选择快递公司');
                if(!strlen(trim($delivery_id))) return app('json')->fail('请填写快递单号');
            }
            if($delivery_type == 'send'){
                if(!strlen(trim($delivery_name))) return app('json')->fail('请填写发货人');
                if(!strlen(trim($delivery_id))) return app('json')->fail('请填写发货手机号');
             }
            $data['status'] = 1;
            $data['delivery_type'] = $delivery_type;
            $data['delivery_name'] = $delivery_name;
            $data['delivery_id'] = $delivery_id;
            $res = StoreOrder::edit($data, $order['id'], 'id');
            if($res){
                if($delivery_type == 'express'){
                    StoreOrderStatus::status($order['id'],'delivery_goods','已发货 快递公司：'.$data['delivery_name'].' 快递单号：'.$data['delivery_id']);
                }else if($delivery_type == 'send'){
                    StoreOrderStatus::status($order['id'],'delivery','已配送 发货人：'.$delivery_name.' 发货人电话：'.$delivery_id);
                }else if($delivery_type == 'fictitious'){
                    StoreOrderStatus::status($order['id'],'delivery_fictitious','虚拟产品已发货');
                }
            }
            event('StoreProductOrderDeliveryGoodsAfter',[$data,$order['id']]);
            return app('json')->successful('发货成功!');
        }
        return app('json')->fail('状态错误');
    }

    /**
     * 订单改价
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function price(Request $request)
    {
        $uid = $request->uid();
        if(!StoreService::orderServiceStatus($uid))
            return app('json')->fail('权限不足');
        list($order_id, $price) = UtilService::postMore([
            ['order_id',''],
            ['price', '']
        ], $request, true);
        $order = StoreOrder::getAdminOrderDetail($order_id, 'id,paid,pay_price,order_id,total_price,total_postage,pay_postage,gain_integral');
        if(!$order) return app('json')->fail('订单不存在');
        $order = $order->toArray();
        if(!$order['paid']){
            if($price === '') return app('json')->fail('请填写实际支付金额');
            if($price < 0) return app('json')->fail('实际支付金额不能小于0元');
            if($order['pay_price'] == $price) return app('json')->successful('修改成功');
            if(!StoreOrder::edit(['pay_price'=>$price], $order['id'], 'id'))
                return app('json')->fail('状态错误');
            $order['pay_price'] = $price;
            event('StoreProductOrderEditAfter',[$order,$order['id']]);
            StoreOrderStatus::status($order['id'],'order_edit','修改实际支付金额'.$price);
            return app('json')->successful('修改成功');
        }
        return app('json')->fail('状态错误');
    }

    /**
     * 订单备注
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function remark(Request $request)
    {
        $uid = $request->uid();
        if(!StoreService::orderServiceStatus($uid))
            return app('json')->fail('权限不足');
        list($order_id, $remark) = UtilService::postMore([
            ['order_id',''],
            ['remark', '']
        ], $request, true);
        $order = StoreOrder::getAdminOrderDetail($order_id, 'id');
        if(!$order) return app('json')->fail('订单不存在');
        $order = $order->toArray();
        if(!strlen(trim($remark))) return app('json')->fail('请填写备注内容');
        if(!StoreOrder::edit(['remark'=>$remark],$order['id']))
            return app('json')->fail('备注失败');
        return app('json')->successful('备注成功');
    }

    /**
     * 订单交易额/订单数量时间统计
     * @param Request $request
     * @return bool
     */
    public function time(Request $request)
    {
        $uid = $request->uid();
        if(!StoreService::orderServiceStatus($uid))
            return app('json')->fail('权限不足');
        list($start, $stop, $type) = UtilService::getMore([
            ['start',strtotime(date('Y-m'))],
            ['stop',time()],
            ['type',1]
        ], $request, true);
        if($start == $stop) return false;
        if($start > $stop){
            $middle = $stop;
            $stop = $start;
            $start = $middle;
        }
        $space = bcsub($stop, $start,0);//间隔时间段
        $front = bcsub($start, $space, 0);//第一个时间段
        if($type == 1){//销售额
            $frontPrice = StoreOrder:: getOrderTimeBusinessVolumePrice($front, $start);
            $afterPrice = StoreOrder:: getOrderTimeBusinessVolumePrice($start, $stop);
            $chartInfo = StoreOrder::chartTimePrice($start, $stop);
            $data['chart'] = $chartInfo;//营业额图表数据
            $data['time'] = $afterPrice;//时间区间营业额
            $increase = (float)bcsub($afterPrice, $frontPrice, 2); //同比上个时间区间增长营业额
            $growthRate = abs($increase);
            if($growthRate == 0) $data['growth_rate'] = 0;
            else if($frontPrice == 0) $data['growth_rate'] = $growthRate;
            else $data['growth_rate'] = (int)bcmul(bcdiv($growthRate, $frontPrice, 2), 100, 0);//时间区间增长率
            $data['increase_time'] = abs($increase); //同比上个时间区间增长营业额
            $data['increase_time_status'] =  $increase >= 0 ? 1 : 2; //同比上个时间区间增长营业额增长 1 减少 2
        }else{//订单数
            $frontNumber = StoreOrder:: getOrderTimeBusinessVolumeNumber($front, $start);
            $afterNumber = StoreOrder:: getOrderTimeBusinessVolumeNumber($start, $stop);
            $chartInfo = StoreOrder::chartTimeNumber($start, $stop);
            $data['chart'] = $chartInfo;//订单数图表数据
            $data['time'] = $afterNumber;//时间区间订单数
            $increase = (int)bcsub($afterNumber, $frontNumber, 0); //同比上个时间区间增长订单数
            $growthRate = abs($increase);
            if($growthRate == 0) $data['growth_rate'] = 0;
            else if($frontNumber == 0) $data['growth_rate'] = $growthRate;
            else $data['growth_rate'] = (int)bcmul(bcdiv($growthRate, $frontNumber, 2), 100, 0);//时间区间增长率
            $data['increase_time'] = abs($increase); //同比上个时间区间增长营业额
            $data['increase_time_status'] =  $increase >= 0 ? 1 : 2; //同比上个时间区间增长营业额增长 1 减少 2
        }
        return app('json')->successful($data);
    }

    /**
     * 订单支付
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function offline(Request $request){
        list($orderId) = UtilService::postMore([['order_id', '']], $request, true);
        $orderInfo = StoreOrder::getAdminOrderDetail($orderId, 'id');
        if(!$orderInfo) return app('json')->fail('参数错误');
        $id = $orderInfo->id;
        $res = StoreOrder::updateOffline($id);
        if($res){
            event('StoreProductOrderOffline',[$id]);
            StoreOrderStatus::status($id,'offline','线下付款');
            return app('json')->successful('修改成功!');
        }
        return app('json')->fail(StoreOrder::getErrorInfo('修改失败!'));
    }

    /**
     * 订单退款
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refund(Request $request)
    {
        list($orderId, $price, $type) = UtilService::postMore([
            ['order_id',''],
            ['price', 0],
            ['type',1],
        ], $request, true);
        if(!strlen(trim($orderId))) return app('json')->fail('参数错误');
        $orderInfo = StoreOrder::getAdminOrderDetail($orderId);
        if(!$orderInfo) return app('json')->fail('数据不存在!');
        $orderInfo = $orderInfo->toArray();
        if($type == 1)
            $data['refund_status'] = 2;
        else if($type == 2)
            $data['refund_status'] = 0;
        else
            return app('json')->fail('退款修改状态错误');
        if($orderInfo['pay_price'] == 0 || $type == 2) {
            StoreOrder::update($data,['order_id'=>$orderId]);
            return app('json')->successful('修改退款状态成功!');
        }
        if($orderInfo['pay_price'] == $orderInfo['refund_price']) return app('json')->fail('已退完支付金额!不能再退款了');
        if(!$price) return app('json')->fail('请输入退款金额');
        $data['refund_price'] = bcadd($price, $orderInfo['refund_price'], 2);
        $bj = bccomp((float)$orderInfo['pay_price'],(float)$data['refund_price'],2);
        if($bj < 0) return app('json')->fail('退款金额大于支付金额，请修改退款金额');
        $refundData['pay_price'] = $orderInfo['pay_price'];
        $refundData['refund_price'] = $price;
        if($orderInfo['pay_type'] == 'weixin'){
            if($orderInfo['is_channel']){// 小程序
                try{
                    MiniProgramService::payOrderRefund($orderInfo['order_id'], $refundData);
                }catch(\Exception $e){
                    return app('json')->fail($e->getMessage());
                }
            }else{// 公众号
                try{
                    WechatService::payOrderRefund($orderInfo['order_id'], $refundData);
                }catch(\Exception $e){
                    return app('json')->fail($e->getMessage());
                }
            }
        }else if($orderInfo['pay_type'] == 'yue'){//余额
            StoreOrder::beginTrans();
            $userInfo = User::getUserInfo($orderInfo['uid'], 'now_money');
            if(!$userInfo){
                StoreOrder::rollbackTrans();
                return app('json')->fail('订单用户不存在');
            }
            $res1 = User::bcInc($orderInfo['uid'],'now_money',$price,'uid');
            $res2 = $res2 = UserBill::income('商品退款',$orderInfo['uid'],'now_money','pay_product_refund', $price, $orderInfo['id'],bcadd($userInfo['now_money'], $price,2),'订单退款到余额'.floatval($price).'元');
            try{
                OrderRepository::storeOrderYueRefund($orderInfo, $refundData);
            }catch (\Exception $e){
                StoreOrder::rollbackTrans();
                return app('json')->fail($e->getMessage());
            }
            $res = $res1 && $res2;
            StoreOrder::checkTrans($res);
            if(!$res) return app('json')->fail('余额退款失败!');
        }
        $resEdit = StoreOrder::edit($data,$orderInfo['id'], 'id');
        if($resEdit){
            $data['type'] = $type;
            if($data['type'] == 1)  StorePink::setRefundPink($orderInfo['id']);
            try{
                OrderRepository::storeProductOrderRefundY($data, $orderInfo['id']);
            }catch (\Exception $e){
                return app('json')->fail($e->getMessage());
            }
            StoreOrderStatus::status($orderInfo['id'],'refund_price','退款给用户'.$price.'元');
            return app('json')->successful('修改成功!');
        }else{
            StoreOrderStatus::status($orderInfo['id'],'refund_price','退款给用户'.$price.'元失败');
            return app('json')->successful('修改失败!');
        }
    }
}