<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\services\order;

use app\services\BaseServices;
use app\dao\order\StoreOrderDao;
use app\services\user\UserServices;
use think\exception\ValidateException;

/**
 * 订单拆分
 * Class StoreOrderSplitServices
 * @package app\services\order
 */
class StoreOrderSplitServices extends BaseServices
{
    /**
     * 需要清空恢复默认数据字段
     * @var string[]
     */
    protected $order_data = ['id', 'status', 'refund_status', 'refund_type', 'refund_express', 'refund_reason_wap_img', 'refund_reason_wap_explain', 'refund_reason_time', 'refund_reason_wap', 'refund_reason', 'refund_price', 'delivery_name', 'delivery_code', 'delivery_type', 'delivery_id', 'fictitious_content', 'delivery_uid'];

    /**
     * 构造方法
     * StoreOrderRefundServices constructor.
     * @param StoreOrderDao $dao
     */
    public function __construct(StoreOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 订单拆分
     * @param int $id
     * @param array $cart_ids
     * @param array $orderInfo
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function split(int $id, array $cart_ids, $orderInfo = [])
    {
        $ids = array_unique(array_column($cart_ids, 'cart_id'));
        if (!$cart_ids || !$ids) {
            return false;
        }
        if (!$orderInfo) {
            $orderInfo = $this->dao->get($id, ['*']);
        }
        if (!$orderInfo) {
            throw new ValidateException('订单未能查到,不能拆分订单!');
        }
        /** @var StoreOrderCreateServices $storeOrderCreateServices */
        $storeOrderCreateServices = app()->make(StoreOrderCreateServices::class);
        $orderInfo = is_object($orderInfo) ? $orderInfo->toArray() : $orderInfo;
        foreach ($this->order_data as $field) {
            unset($orderInfo[$field]);
        }
        $order_data = $orderInfo;
        $order_data['pid'] = $id;
        mt_srand();
        $order_data['order_id'] = $orderInfo['order_id'] . '_' . rand(100, 999);
        $order_data['cart_id'] = [];
        $order_data['unique'] = $storeOrderCreateServices->getNewOrderId('');
        $order_data['add_time'] = time();
        $order_data['mark'] = '拆分订单';
        $new_order = $this->dao->save($order_data);
        if (!$new_order) {
            throw new ValidateException('生成新订单失败');
        }
        $new_id = (int)$new_order->id;
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $statusService->save([
            'oid' => $new_id,
            'change_type' => 'split_create_order',
            'change_message' => '发货拆分订单生成',
            'change_time' => time()
        ]);
        /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
        $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        //订单下原商品信息
        $cartInfo = $storeOrderCartInfoServices->getColumn(['oid' => $id, 'cart_id' => $ids], 'cart_num,surplus_num,cart_info', 'cart_id');
        $cart_data = $cart_data_all = $update_data = [];
        $cart_data['oid'] = $new_id;
        foreach ($cart_ids as $cart) {
            $surplus_num = $cartInfo[$cart['cart_id']]['surplus_num'] ?? 0;
            if (!isset($cartInfo[$cart['cart_id']]) || !$surplus_num) continue;
            $_info = is_string($cartInfo[$cart['cart_id']]['cart_info']) ? json_decode($cartInfo[$cart['cart_id']]['cart_info'], true) : $cartInfo[$cart['cart_id']]['cart_info'];
            $cart_data['cart_id'] = $storeOrderCreateServices->getNewOrderId('');
            $cart_data['product_id'] = $_info['product_id'];
            $cart_data['old_cart_id'] = $cart['cart_id'];
            $cart_data['cart_num'] = $cart['cart_num'];
            $cart_data['unique'] = md5($cart_data['cart_id'] . '_' . $cart_data['oid']);
            if ($cart['cart_num'] >= $surplus_num) {//拆分完成
                $cart_data['cart_num'] = $surplus_num;
                $update_data['split_status'] = 2;
                $update_data['surplus_num'] = 0;
            } else {//拆分部分数量
                $update_data['surplus_num'] = bcsub((string)$surplus_num, $cart['cart_num'], 0);
                $update_data['split_status'] = $update_data['surplus_num'] > 0 ? 1 : 2;
            }
            $_info = $this->slpitComputeOrderCart($cart_data['cart_num'], $_info);
            $_info['id'] = $cart_data['cart_id'];
            $cart_data['cart_info'] = json_encode($_info);

            //修改原来订单商品信息
            if (false === $storeOrderCartInfoServices->update(['oid' => $id, 'cart_id' => $cart['cart_id']], $update_data)) {
                throw new ValidateException('修改原来订单商品拆分状态失败，请稍候重试');
            }
            $cart_data_all[] = $cart_data;
        }
        if (!$storeOrderCartInfoServices->saveAll($cart_data_all)) {
            throw new ValidateException('新增拆分订单商品信息失败');
        }
        $new_order = $this->dao->get($new_id);
        $this->splitComputeOrder($new_id, $cart_data_all, $new_order);
        return $new_order;
    }

    /**
     * 重新计算新订单中价格等信息
     * @param int $id
     * @param $orderInfo
     * @param array $cart_info_data
     */
    public function splitComputeOrder(int $id, array $cart_info_data, $new_order)
    {
        $order_update['cart_id'] = array_column($cart_info_data, 'cart_id');
        $order_update['total_num'] = array_sum(array_column($cart_info_data, 'cart_num'));
        $pay_price = $total_price = $coupon_price = $deduction_price = $use_integral = $pay_postage = $gainIntegral = $one_brokerage = $two_brokerage = 0;
        foreach ($cart_info_data as $cart) {
            $_info = json_decode($cart['cart_info'], true);
            $pay_price = bcadd((string)$pay_price, bcmul((string)$_info['truePrice'], (string)$cart['cart_num'], 4), 2);
            $total_price = bcadd((string)$total_price, bcmul((string)$_info['truePrice'], (string)$cart['cart_num'], 4), 2);
            $deduction_price = bcadd((string)$deduction_price, (string)$_info['integral_price'], 2);
            $coupon_price = bcadd((string)$coupon_price, (string)$_info['coupon_price'], 2);
            $use_integral = bcadd((string)$use_integral, (string)$_info['use_integral'], 0);
            $pay_postage = bcadd((string)$pay_postage, (string)$_info['postage_price'], 2);
            $cartInfoGainIntegral = bcmul((string)$cart['cart_num'], (string)($_info['productInfo']['give_integral'] ?? '0'), 0);
            $gainIntegral = bcadd((string)$gainIntegral, (string)$cartInfoGainIntegral, 0);
            $one_brokerage = bcadd((string)$one_brokerage, (string)$_info['one_brokerage'], 2);
            $two_brokerage = bcadd((string)$two_brokerage, (string)$_info['two_brokerage'], 2);
        }

        $order_update['coupon_id'] = array_unique(array_column($cart_info_data, 'coupon_id'));
        $order_update['pay_price'] = bcadd((string)$pay_price, (string)$pay_postage, 2);
        $order_update['total_price'] = $total_price;
        $order_update['deduction_price'] = $deduction_price;
        $order_update['coupon_price'] = $coupon_price;
        $order_update['use_integral'] = $use_integral;
        $order_update['gain_integral'] = $gainIntegral;
        $order_update['pay_postage'] = $pay_postage;
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        if ($userServices->checkUserPromoter($new_order['spread_uid'])) $order_update['one_brokerage'] = $one_brokerage;
        if ($userServices->checkUserPromoter($new_order['spread_two_uid'])) $order_update['two_brokerage'] = $two_brokerage;
        if (false === $this->dao->update($id, $order_update, 'id')) {
            throw new ValidateException('保存新订单商品信息失败');
        }
        return true;
    }

    /**
     * 部分发货重新计算订单商品：实际金额、优惠、积分等金额
     * @param int $cart_num
     * @param array $cart_info
     * @return array
     */
    public function slpitComputeOrderCart(int $cart_num, array $cart_info)
    {
        if (!$cart_num || !$cart_info) return [];
        $new_cart_info = $cart_info;
        /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
        $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $splitdCartInfo = $storeOrderCartInfoServices->getColumn(['old_cart_id' => $cart_info['id']], 'cart_info', '');
        $deliver_num = $cart_num;
        if ($splitdCartInfo) {
            foreach ($splitdCartInfo as $k => &$v) {
                $v = is_string($v) ? json_decode($v, true) : $v;
                $deliver_num = bcadd((string)$deliver_num, (string)$v['cart_num'], 0);
            }
        }
        $new_cart_info['cart_num'] = $cart_num;
        $compute_arr = ['coupon_price', 'integral_price', 'postage_price', 'use_integral', 'one_brokerage', 'two_brokerage', 'sum_true_price'];
        $scale = 2;
        foreach ($compute_arr as $field) {
            if (!isset($cart_info[$field]) || !$cart_info[$field]) {
                $new_cart_info[$field] = 0;
                continue;
            }
            if ($field == 'use_integral') $scale = 0;
            if ($deliver_num < $cart_info['cart_num']) {//分批发货 还有剩余
                $new_cart_info[$field] = bcmul((string)$cart_num, bcdiv((string)$cart_info[$field], (string)$cart_info['cart_num'], 4), $scale);
            } else {
                if ($splitdCartInfo) {//分批发货完成
                    $new_cart_info[$field] = bcsub((string)$cart_info[$field], (string)array_sum(array_column($splitdCartInfo, $field)), $scale);
                } else {//第一次直接全部数量发货
                    $new_cart_info[$field] = $cart_info[$field];
                }
            }
        }
        return $new_cart_info;
    }
}
