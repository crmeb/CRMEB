<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\services\order;

use app\services\BaseServices;
use app\dao\order\StoreOrderDao;
use crmeb\exceptions\AdminException;

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
     * 主订单平行拆单
     * @param $id
     * @param $cart_ids
     * @param $orderInfo
     * @return false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function equalSplit($id, $cart_ids, $orderInfo)
    {
        /** @var StoreOrderCreateServices $storeOrderCreateServices */
        $storeOrderCreateServices = app()->make(StoreOrderCreateServices::class);
        /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
        $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);


        $ids = array_unique(array_column($cart_ids, 'cart_id'));
        if (!$cart_ids || !$ids) return false;
        if (!$orderInfo) $orderInfo = $this->dao->get($id, ['*']);
        if (!$orderInfo) throw new AdminException(400118);
        $old_order = $orderInfo;
        $orderInfo = $orderInfoOld = is_object($orderInfo) ? $orderInfo->toArray() : $orderInfo;
        foreach ($this->order_data as $field) {
            unset($orderInfo[$field]);
        }
        unset($orderInfoOld['id']);
        //获取拆分之后的两个cart_id
        $cartInfo = $storeOrderCartInfoServices->getColumn(['oid' => $id], 'cart_num,surplus_num,cart_info', 'cart_id');
        $new_cart_ids = array_combine(array_column($cart_ids, 'cart_id'), $cart_ids);
        $other_cart_ids = [];
        foreach ($cartInfo as $cart_id => $cart) {
            if (!isset($new_cart_ids[$cart_id]) && $cart['surplus_num']) {//无拆分
                $other = ['cart_id' => (string)$cart_id, 'cart_num' => $cart['surplus_num']];
            } else if ($new_cart_ids[$cart_id]['cart_num'] < $cart['surplus_num']) {
                $other = ['cart_id' => (string)$cart_id, 'cart_num' => bcsub((string)$cart['surplus_num'], (string)$new_cart_ids[$cart_id]['cart_num'], 0)];
            } else {
                continue;
            }
            $other_cart_ids[] = $other;
        }
        $cart_ids_arr = ['new' => $cart_ids, 'other' => $other_cart_ids];
        if (empty($cart_ids_arr['other'])) return [$old_order, ['id' => 0]];
        return $this->transaction(function () use ($id, $cart_ids_arr, $orderInfo, $orderInfoOld, $cartInfo, $storeOrderCreateServices, $storeOrderCartInfoServices, $statusService) {
            $order = $otherOrder = [];
            $statusData = $statusService->getColumn(['oid' => $id], '*');
            //订单实际支付金额
            $order_pay_price = bcsub((string)bcadd((string)$orderInfo['total_price'], (string)$orderInfo['pay_postage'], 2), (string)bcadd((string)$orderInfo['deduction_price'], (string)$orderInfo['coupon_price'], 2), 2);
            //有改价
            $change_price = $order_pay_price != $orderInfo['pay_price'];
            foreach ($cart_ids_arr as $key => $cart_ids) {
                if ($orderInfo['pid'] == 0 || ($orderInfo['pid'] > 0 && $key == 'new')) {
                    $order_data = $key == 'other' ? $orderInfoOld : $orderInfo;
                    $order_data['pid'] = $orderInfo['pid'] > 0 ? $orderInfo['pid'] : $id;
                    mt_srand();
                    $order_data['order_id'] = $storeOrderCreateServices->getNewOrderId('cp');
                    $order_data['cart_id'] = [];
                    $order_data['unique'] = $storeOrderCreateServices->getNewOrderId('');
                    $new_order = $this->dao->save($order_data);
                    if (!$new_order) {
                        throw new AdminException(400544);
                    }
                    $new_id = (int)$new_order->id;
                    $allData = [];
                    foreach ($statusData as $data) {
                        $data['oid'] = $new_id;
                        $allData[] = $data;
                    }
                    if ($allData) {
                        $statusService->saveAll($allData);
                    }
                } else {
                    $new_id = $id;
                }
                $statusService->save([
                    'oid' => $new_id,
                    'change_type' => 'split_create_order',
                    'change_message' => '拆分订单生成',
                    'change_time' => time()
                ]);

                $cart_data_all = [];
                foreach ($cart_ids as $cart) {
                    if ($orderInfo['pid'] == 0 || $orderInfo['pid'] > 0 && $key == 'new') {
                        $_info = is_string($cartInfo[$cart['cart_id']]['cart_info']) ? json_decode($cartInfo[$cart['cart_id']]['cart_info'], true) : $cartInfo[$cart['cart_id']]['cart_info'];
                        $new_cart_data['oid'] = $new_id;
                        $new_cart_data['uid'] = $orderInfo['uid'];
                        $new_cart_data['cart_id'] = $storeOrderCreateServices->getNewOrderId('');
                        $new_cart_data['product_id'] = $_info['product_id'];
                        $new_cart_data['old_cart_id'] = $cart['cart_id'];
                        $new_cart_data['cart_num'] = $cart['cart_num'];
                        $new_cart_data['surplus_num'] = $cart['cart_num'];
                        $new_cart_data['unique'] = md5($new_cart_data['cart_id'] . '_' . $new_cart_data['oid']);
                        $_info = $this->slpitComputeOrderCart($new_cart_data['cart_num'], $_info, $key);
                        $_info['id'] = $new_cart_data['cart_id'];
                        $new_cart_data['cart_info'] = json_encode($_info);
                        $cart_data_all[] = $new_cart_data;
                    } else {
                        $cart_info = $storeOrderCartInfoServices->get(['cart_id' => $cart['cart_id']]);
                        $_info = is_string($cartInfo[$cart['cart_id']]['cart_info']) ? json_decode($cartInfo[$cart['cart_id']]['cart_info'], true) : $cartInfo[$cart['cart_id']]['cart_info'];
                        $cart_info->cart_num = $cart['cart_num'];
                        $cart_info->refund_num = 0;
                        $cart_info->surplus_num = $cart['cart_num'];
                        $_info = $this->slpitComputeOrderCart($cart['cart_num'], $_info, $key);
                        $_info['id'] = $cart_info['cart_id'];
                        $cart_info->cart_info = json_encode($_info);
                        $cart_info->save();
                        $cart_data_all[] = [
                            'oid' => $new_id,
                            'uid' => $orderInfo['uid'],
                            'cart_id' => $cart_info->cart_id,
                            'product_id' => $cart_info->product_id,
                            'old_cart_id' => $cart_info->old_cart_id,
                            'cart_num' => $cart_info->cart_num,
                            'surplus_num' => $cart_info->surplus_num,
                            'unique' => $cart_info->unique,
                            'cart_info' => json_encode($_info),
                        ];
                    }
                }

                if ($orderInfo['pid'] > 0 && $key == 'other') {
                    $storeOrderCartInfoServices->delete(['oid' => $new_id]);
                }
                $storeOrderCartInfoServices->saveAll($cart_data_all);

                $new_order = $this->dao->get($new_id);
                $storeOrderCartInfoServices->clearOrderCartInfo($new_id);
                $this->splitComputeOrder((int)$new_id, $cart_data_all, (float)($change_price ? $order_pay_price : 0), (float)$orderInfo['pay_price'], (float)($new_order['pay_price'] ?? 0));
                $new_order = $this->dao->get($new_id);
                if ($key == 'new') {
                    $order = $new_order;
                } else {
                    $otherOrder = $new_order;
                }
            }
            if (!$orderInfo['pid']) $this->dao->update($id, ['pid' => -1]);

            //处理申请开票记录
            /** @var StoreOrderInvoiceServices $storeOrderInvoiceServics */
            $storeOrderInvoiceServics = app()->make(StoreOrderInvoiceServices::class);
            $storeOrderInvoiceServics->splitOrderInvoice((int)$id);
            return [$order, $otherOrder];
        });
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
            throw new AdminException(400118);
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
        $new_order = $this->dao->save($order_data);
        if (!$new_order) {
            throw new AdminException(400544);
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
                throw new AdminException(400545);
            }
            $cart_data_all[] = $cart_data;
        }
        if (!$storeOrderCartInfoServices->saveAll($cart_data_all)) {
            throw new AdminException(400546);
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
    public function splitComputeOrder(int $id, array $cart_info_data, float $order_pay_price = 0.00, float $pay_price = 0.00, float $pre_pay_price = 0.00)
    {
        $order_update['cart_id'] = array_column($cart_info_data, 'cart_id');
        $order_update['total_num'] = array_sum(array_column($cart_info_data, 'cart_num'));
        $total_price = $coupon_price = $deduction_price = $use_integral = $pay_postage = $gainIntegral = $one_brokerage = $two_brokerage = $staffBrokerage = $agentBrokerage = $divisionBrokerage = 0;
        foreach ($cart_info_data as $cart) {
            $_info = json_decode($cart['cart_info'], true);
            $total_price = bcadd((string)$total_price, (string)$_info['sum_true_price'], 2);
            $deduction_price = bcadd((string)$deduction_price, (string)$_info['integral_price'], 2);
            $coupon_price = bcadd((string)$coupon_price, (string)$_info['coupon_price'], 2);
            $use_integral = bcadd((string)$use_integral, (string)$_info['use_integral'], 0);
            $pay_postage = isset($_info['postage_price']) ? bcadd((string)$pay_postage, (string)$_info['postage_price'], 2) : 0;
            $cartInfoGainIntegral = bcmul((string)$cart['cart_num'], (string)($_info['productInfo']['give_integral'] ?? '0'), 0);
            $gainIntegral = bcadd((string)$gainIntegral, (string)$cartInfoGainIntegral, 0);
            $one_brokerage = bcadd((string)$one_brokerage, (string)$_info['one_brokerage'], 2);
            $two_brokerage = bcadd((string)$two_brokerage, (string)$_info['two_brokerage'], 2);
            $staffBrokerage = bcadd((string)$staffBrokerage, (string)$_info['staff_brokerage'], 2);
            $agentBrokerage = bcadd((string)$agentBrokerage, (string)$_info['agent_brokerage'], 2);
            $divisionBrokerage = bcadd((string)$divisionBrokerage, (string)$_info['division_brokerage'], 2);
        }

        $order_update['coupon_id'] = array_unique(array_column($cart_info_data, 'coupon_id'));
        $order_update['pay_price'] = bcadd((string)$total_price, (string)$pay_postage, 2);
        //有订单原来支付金额 改价订单
        if ($order_pay_price) {
            if ($pre_pay_price) {//上一个已经计算 这里减法
                $order_update['pay_price'] = bcsub((string)$pay_price, (string)$pre_pay_price, 2);
            } else {//按比例计算实际支付金额
                $order_update['pay_price'] = bcmul((string)bcdiv((string)$pay_price, (string)$order_pay_price, 4), (string)$order_update['pay_price'], 2);
            }
        }

        $order_update['total_price'] = bcadd((string)$total_price, (string)bcadd((string)$deduction_price, (string)$coupon_price, 2), 2);
        $order_update['deduction_price'] = $deduction_price;
        $order_update['coupon_price'] = $coupon_price;
        $order_update['use_integral'] = $use_integral;
        $order_update['gain_integral'] = $gainIntegral;
        $order_update['pay_postage'] = $pay_postage;
        $order_update['one_brokerage'] = $one_brokerage;
        $order_update['two_brokerage'] = $two_brokerage;
        $order_update['staff_brokerage'] = $staffBrokerage;
        $order_update['agent_brokerage'] = $agentBrokerage;
        $order_update['division_brokerage'] = $divisionBrokerage;
        if (false === $this->dao->update($id, $order_update, 'id')) {
            throw new AdminException(400547);
        }
        return true;
    }

    /**
     * 部分发货重新计算订单商品：实际金额、优惠、积分等金额
     * @param int $cart_num
     * @param array $cart_info
     * @return array
     */
    public function slpitComputeOrderCart(int $cart_num, array $cart_info, $orderType = 'new')
    {
        if (!$cart_num || !$cart_info) return [];
        if ($cart_num >= $cart_info['cart_num']) return $cart_info;
        $new_cart_info = $cart_info;
        $new_cart_info['cart_num'] = $cart_num;
        $compute_arr = ['coupon_price', 'integral_price', 'postage_price', 'use_integral', 'one_brokerage', 'two_brokerage', 'staff_brokerage', 'agent_brokerage', 'division_brokerage', 'sum_true_price'];
        foreach ($compute_arr as $field) {
            if (!isset($cart_info[$field]) || !$cart_info[$field]) {
                $new_cart_info[$field] = 0;
                continue;
            }
            $scale = 2;
            if ($field == 'use_integral') $scale = 0;
            $new_cart_info[$field] = bcmul((string)$cart_num, bcdiv((string)$cart_info[$field], (string)$cart_info['cart_num'], 4), $scale);
            if ($orderType == 'new') {//拆出
                $new_cart_info[$field] = bcmul((string)$cart_num, bcdiv((string)$cart_info[$field], (string)$cart_info['cart_num'], 4), $scale);
            } else {
                $field_number = bcmul((string)bcsub((string)$cart_info['cart_num'], (string)$cart_num, 0), bcdiv((string)$cart_info[$field], (string)$cart_info['cart_num'], 4), $scale);
                $new_cart_info[$field] = bcsub((string)$cart_info[$field], (string)$field_number, $scale);
            }
        }
        return $new_cart_info;
    }

    /**
     * 获取整理后的订单商品信息
     * @param int $id
     * @param array $cart_ids
     * @param array $orderInfo
     * @return array|false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSplitOrderCartInfo(int $id, array $cart_ids, $orderInfo = [])
    {
        $ids = array_unique(array_column($cart_ids, 'cart_id'));
        if (!$cart_ids || !$ids) {
            return false;
        }
        if (!$orderInfo) {
            $orderInfo = $this->dao->get($id, ['*']);
        }
        if (!$orderInfo) {
            throw new AdminException(400118);
        }
        /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
        $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $cartInfo = $storeOrderCartInfoServices->getCartColunm(['oid' => $id, 'cart_id' => $ids], '*', 'cart_id');
        $cart_data_all = [];
        foreach ($cart_ids as $cart) {
            $surplus_num = $cartInfo[$cart['cart_id']]['surplus_num'] ?? 0;
            if (!isset($cartInfo[$cart['cart_id']]) || !$surplus_num) continue;
            $_info = is_string($cartInfo[$cart['cart_id']]['cart_info']) ? json_decode($cartInfo[$cart['cart_id']]['cart_info'], true) : $cartInfo[$cart['cart_id']]['cart_info'];
            $cart_data = $cartInfo[$cart['cart_id']];
            $cart_data['oid'] = $id;
            $cart_data['product_id'] = $_info['product_id'];
            $cart_data['old_cart_id'] = $cart['cart_id'];
            $cart_data['cart_num'] = $cart['cart_num'];
            $cart_data['surplus_num'] = $cart['cart_num'];
            $cart_data['split_surplus_num'] = $cart['cart_num'];

            $_info = $this->slpitComputeOrderCart($cart_data['cart_num'], $_info);
            $_info['id'] = $cart_data['cart_id'];
            $cart_data['cart_info'] = $_info;
            $cart_data_all[] = $cart_data;
            unset($cartInfo[$cart['cart_id']]);
        }
        return $cart_data_all;
    }
}
