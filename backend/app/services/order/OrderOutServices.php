<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\order;

use app\dao\order\OrderCenterDao;
use crmeb\basic\BaseService;
use app\services\pay\PayServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;

/**
 * 对外接口订单服务（outapi 订单管理）
 * 已全量中台化：基于订单中台 eb_order_center 查询/操作，替代旧 OutStoreOrderServices
 * Class OrderOutServices
 * @package app\services\order
 * @method getOrderIdsCount(array $ids) 获取订单id下没有删除的订单数量
 * @method OrderCenterDao getUserOrderDetail(string $key, int $uid, array $with) 获取订单详情
 * @method chartTimePrice($start, $stop) 获取当前时间到指定时间的支付金额 管理员
 * @method chartTimeNumber($start, $stop) 获取当前时间到指定时间的支付订单数 管理员
 * @method together(array $where, string $field, string $together = 'sum') 聚合查询
 * @method getBuyCount($uid, $type, $typeId) 获取用户已购买此活动商品的个数
 * @method getDistinctCount(array $where, $field, ?bool $search = true)
 * @method getTrendData($time, $type, $timeType, $str) 用户趋势
 * @method getRegion($time, $channelType) 地域统计
 * @method getProductTrend($time, $timeType, $field, $str) 商品趋势
 */
class OrderOutServices extends BaseService
{
    /**
     * @var OrderCenterDao
     */
    protected $dao;

    /**
     * 发货类型
     * 系统不支持拆单/分批发货，此处不声明拆分类型（旧拆分发货已随拆单功能下线）
     * @var string[]
     */
    public $deliveryType = ['send' => '商家配送', 'express' => '快递配送', 'fictitious' => '虚拟发货'];

    /**
     * OrderOutServices constructor.
     * @param OrderCenterDao $dao
     */
    public function __construct(OrderCenterDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderList(array $where)
    {
        $where['order_status'] = $where['status'];
        unset($where['status']);
        if (!is_numeric($where['paid'])) {
            $where['paid'] = -1;
        }
        [$page, $limit] = $this->getPageValue();
        $field = ['id', 'pid', 'order_id', 'trade_no', 'uid', 'freight_price', 'real_name', 'user_phone', 'user_address', 'total_num',
            'total_price', 'total_postage', 'pay_price', 'coupon_price', 'deduction_price', 'paid', 'pay_time', 'pay_type', 'add_time',
            'shipping_type', 'status', 'refund_status', 'delivery_name', 'delivery_code', 'delivery_id'];
        $data = $this->dao->getOutOrderList($where, $field, $page, $limit);
        $count = $this->dao->count($where);
        $list = $this->tidyOrderList($data);
        return compact('list', 'count');
    }

    /**
     * 数据转换
     * @param array $data
     * @return array
     */
    public function tidyOrderList(array $data)
    {
        foreach ($data as &$item) {
            // 从订单中台 extra_info.items 构造商品列表（旧 cart_info 表已废弃）
            $item['items'] = $this->buildOrderItemsFromExtraInfo((string)$item['order_id']);
            $item['pay_type_name'] = PayServices::getPayType()[$item['pay_type']] ?? '其他方式';
            unset($item['refund_status'], $item['shipping_type']);
        }
        return $data;
    }

    /**
     * 订单详情
     * @param string $orderId 订单号
     * @param int $id 订单ID
     * @return mixed
     */
    public function getInfo(string $orderId = '', int $id = 0)
    {
        $field = ['id', 'pid', 'order_id', 'trade_no', 'uid', 'freight_price', 'real_name', 'user_phone', 'user_address', 'total_num',
            'total_price', 'total_postage', 'pay_price', 'coupon_price', 'deduction_price', 'paid', 'pay_time', 'pay_type', 'add_time',
            'shipping_type', 'status', 'refund_status', 'delivery_name', 'delivery_code', 'delivery_id', 'refund_type', 'delivery_type', 'pink_id', 'use_integral', 'back_integral'];

        if ($id > 0) {
            $where = $id;
        } else {
            $where = ['order_id' => $orderId];
        }

        if (!$orderInfo = $this->dao->get($where, $field, ['invoice'])) {
            throw new ApiException('订单不存在');
        }

        if (!$orderInfo['invoice']) {
            $orderInfo['invoice'] = new \StdClass();
        } else {
            $orderInfo['invoice']->hidden(['uid', 'category', 'id', 'order_id', 'add_time']);
        }

        $orderInfo = $this->tidyOrder($orderInfo->toArray(), true);
        //核算优惠金额
        $vipTruePrice = array_column($orderInfo['items'], 'vip_sum_truePrice');
        $vipTruePrice = round(array_sum($vipTruePrice), 2);
        $orderInfo['vip_true_price'] = sprintf("%.2f", $vipTruePrice ?: '0.00');
        $orderInfo['total_price'] = bcadd($orderInfo['total_price'], $orderInfo['vip_true_price'], 2);
        return $orderInfo;
    }

    /**
     * 订单详情数据格式化
     * @param $order
     * @param bool $detail 是否需要订单商品详情
     * @return mixed
     */
    public function tidyOrder($order, bool $detail = false)
    {
        if ($detail == true && isset($order['id'])) {
            // 从订单中台 extra_info.items 构造商品列表（旧 cart_info 表已废弃）
            $order['items'] = $this->buildOrderItemsFromExtraInfo((string)$order['order_id']);
        }

        $order['pay_type_name'] = PayServices::getPayType()[$order['pay_type']] ?? '其他方式';

        // 处理未支付状态
        if (!$order['paid']) {
            if ($order['pay_type'] == 'offline') {
                $order['status_name'] = '线下付款,未支付';
            } else {
                $order['status_name'] = '未支付';
            }
        } elseif ($order['status'] == 4 || $order['status'] == 1) { // 合并待收货逻辑
            $order['status_name'] = '待收货';
        } elseif ($order['refund_status'] == 1) {
            if (in_array($order['refund_type'], [0, 1, 2, 4, 5])) {
                $order['status_name'] = '申请退款中';
            }
        } elseif ($order['refund_status'] == 2 || $order['refund_type'] == 6) {
            $order['status_name'] = '已退款';
        } elseif ($order['refund_status'] == 3) {
            $order['status_name'] = '部分退款（子订单）';
        } elseif ($order['refund_status'] == 4) {
            $order['status_name'] = '子订单全部申请退款中';
        } elseif (!$order['status']) {
            if ($order['pink_id']) {
                //通过事件代理查询拼团状态，避免 app 层直接引用 modules
                //使用 proxy_event_result 安全提取第一个非 null 结果，避免多监听器返回 null 数组时取值错误
                $results = event('ActivityDataProxy', ['mode' => 'combination', 'sub_service' => 'pink', 'action' => 'getCount', 'where' => ['id' => $order['pink_id'], 'status' => 1]]);
                if (proxy_event_result($results, false)) {
                    $order['status_name'] = '拼团中';
                } else {
                    $order['status_name'] = '未发货';
                }
            } else {
                if ($order['shipping_type'] === 1) {
                    $order['status_name'] = '未发货';
                } else {
                    $order['status_name'] = '待核销';
                }
            }
        } elseif ($order['status'] == 2) {
            $order['status_name'] = '待评价';
        } elseif ($order['status'] == 3) {
            $order['status_name'] = '交易完成';
        } else {
            // 处理未知状态
            $order['status_name'] = '未知状态';
        }
        unset($order['pink_id'], $order['refund_type']);
        return $order;
    }

    /**
     * 订单收货
     * @param string $orderId 订单号
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function receive(string $orderId): bool
    {
        $order = $this->dao->get(['order_id' => $orderId]);
        if (!$order) {
            throw new ApiException('订单不存在');
        }

        if ($order['status'] == 2) {
            throw new ApiException('不能重复收货');
        }

        if (($order['paid'] == 1 && $order['status'] == 1) || $order['pay_type'] == 'offline') {
            $data['status'] = 2;
        } else {
            throw new ApiException('请先发货或者送货');
        }

        if (!$this->dao->update($order['id'], $data)) {
            throw new ApiException('收货失败,请稍候再试');
        }

        /** @var OrderTakeServices $takeServices */
        $takeServices = app()->make(OrderTakeServices::class);
        if (!$takeServices->storeProductOrderUserTakeDelivery($order)) {
            throw new ApiException('收货失败,请稍候再试');
        }
        return true;
    }

    /**
     * 发货
     * @param string $orderId 订单号
     * @param array $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delivery(string $orderId, array $data)
    {
        $orderInfo = $this->dao->get(['order_id' => $orderId]);
        if (!$orderInfo) {
            throw new ApiException('订单未能查到,不能发货');
        }

        /** @var OrderDeliveryServices $deliveryServices */
        $deliveryServices = app()->make(OrderDeliveryServices::class);
        return $deliveryServices->delivery((int)$orderInfo['id'], $data);
    }

    /**
     * 设置发票
     * @param string $orderId 订单号
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setInvoice(string $orderId, array $data): bool
    {
        $orderInfo = $this->dao->get(['order_id' => $orderId], ['id'], ['invoice']);
        if (!$orderInfo) {
            throw new AdminException('订单不存在');
        }

        if (!$orderInfo->invoice || !$invoiceId = $orderInfo->invoice->id) {
            throw new ApiException('数据不存在');
        }

        /** @var OrderInvoiceServices $invoiceServices */
        $invoiceServices = app()->make(OrderInvoiceServices::class);
        return $invoiceServices->setInvoice($invoiceId, $data);
    }

    /**
     * 修改配送信息
     * @param string $orderId 订单号
     * @param array $data
     * @return mixed
     */
    public function updateDistribution(string $orderId, array $data)
    {
        $orderInfo = $this->dao->get(['order_id' => $orderId]);
        if (!$orderInfo) {
            throw new AdminException('订单不存在');
        }

        /** @var OrderDeliveryServices $deliveryServices */
        $deliveryServices = app()->make(OrderDeliveryServices::class);
        return $deliveryServices->updateDistribution($orderInfo['id'], $data);
    }

    /**
     * 订单推送
     * @param int $id
     * @param string $pushUrl
     * @return bool
     */
    public function orderCreatePush(int $id, string $pushUrl): bool
    {
        $orderInfo = $this->getInfo('', $id);
        return out_push($pushUrl, $orderInfo, '订单');
    }

    /**
     * 支付推送
     * @param int $id
     * @param string $pushUrl
     * @return bool
     */
    public function paySuccessPush(int $id, string $pushUrl): bool
    {
        $orderInfo = $this->getInfo('', $id);
        return out_push($pushUrl, $orderInfo, '订单支付');
    }

    /**
     * 从订单中台 extra_info.items 构造订单商品列表
     * 用于订单列表/详情等场景（旧订单购物车明细服务已删除）
     * 字段映射与后台发货列表一致，refund_num 默认 0（items 中不存储退款数）
     * @param string $orderNo 订单号
     * @return array 商品列表数组
     */
    protected function buildOrderItemsFromExtraInfo(string $orderNo): array
    {
        /** @var OrderCenterDao $centerDao */
        $centerDao = app()->make(OrderCenterDao::class);
        $centerOrder = $centerDao->get(['order_no' => $orderNo], ['extra_info']);
        $list = [];
        if ($centerOrder) {
            $extra = $centerOrder['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            foreach ($extra['items'] ?? [] as $item) {
                $cartNum = (int)($item['cart_num'] ?? 0);
                $list[] = [
                    'cart_id'     => $item['cart_id'] ?? 0,
                    'store_name'  => $item['store_name'] ?? '',
                    'suk'         => $item['sku'] ?? ($item['attr_info']['suk'] ?? ''),
                    'image'       => $item['image'] ?? ($item['attr_info']['image'] ?? ''),
                    'price'       => sprintf("%.2f", (float)($item['true_price'] ?? $item['price'] ?? 0)),
                    'cart_num'    => $cartNum,
                    'surplus_num' => $item['surplus_num'] ?? $cartNum,
                    'refund_num'  => $item['refund_num'] ?? 0,
                ];
            }
        }
        return $list;
    }
}
