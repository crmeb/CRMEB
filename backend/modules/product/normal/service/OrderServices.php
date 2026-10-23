<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\service;

use app\services\order\OrderCenterServices;
use crmeb\basic\BaseService;
use modules\product\normal\dao\NormalOrderDao;
use modules\product\normal\handler\NormalOrderAdapter;

/**
 * 实物商品订单服务
 * 统一管理扩展表（eb_pt_normal_order）与中台表（eb_order_center）的同步
 * 所有写操作自动保证两表一致性
 */
class OrderServices extends BaseService
{
    /**
     * 订单扩展表 DAO
     */
    protected $dao;

    /**
     * 中台订单服务
     */
    protected OrderCenterServices $centerServices;

    public function __construct(NormalOrderDao $dao)
    {
        $this->dao = $dao;
        $this->centerServices = app(OrderCenterServices::class);
    }

    // ===== 查询 =====

    /**
     * 订单列表（分页，查扩展表）
     * @param array $where
     * @return array ['list' => [], 'count' => int]
     */
    public function getList(array $where): array
    {
        $page = max(1, (int)($where['page'] ?? 1));
        $limit = max(1, (int)($where['limit'] ?? 20));

        $condition = [];
        if (!empty($where['order_no'])) {
            $condition['order_no'] = $where['order_no'];
        }
        if (!empty($where['status'])) {
            $condition['status'] = $where['status'];
        }
        if (!empty($where['real_name'])) {
            $condition['real_name'] = $where['real_name'];
        }

        $list = $this->dao->selectList($condition, '*', $page, $limit, 'id DESC');
        $count = $this->dao->count($condition);

        return compact('list', 'count');
    }

    /**
     * 获取订单扩展信息
     * @param string $orderNo
     * @return array
     */
    public function getExtendInfo(string $orderNo): array
    {
        $info = $this->dao->getOne(['order_no' => $orderNo]);
        return $info ? $info->toArray() : [];
    }

    /**
     * 从扩展表获取单条订单数据
     * @param array $where
     * @param string $field
     * @return array|null
     */
    public function getOne(array $where, string $field = '*'): ?array
    {
        $info = $this->dao->getOne($where, $field);
        return $info ? $info->toArray() : null;
    }

    // ===== 创建 =====

    /**
     * 创建订单扩展记录
     * @param string $orderNo
     * @param int $productId
     * @return void
     */
    public function createOrder(string $orderNo, int $productId): void
    {
        $this->dao->save([
            'order_no'   => $orderNo,
            'product_id' => $productId,
            'status'     => NormalOrderAdapter::EXT_STATUS_PENDING_PAYMENT,
        ]);
    }

    // ===== 状态同步（扩展表 + 中台表） =====

    /**
     * 更新订单扩展表状态，并同步中台表 status_code
     * @param string $orderNo 订单号
     * @param string $status 扩展表状态常量（NormalOrderAdapter::EXT_STATUS_*）
     * @param string $centerStatusCode 中台状态码（OrderTypeAdapterInterface::STATUS_*）
     * @param array $extraCenterFields 额外中台字段（如 ['pay_time' => time()]）
     * @return void
     */
    public function updateStatus(string $orderNo, string $status, string $centerStatusCode = '', array $extraCenterFields = []): void
    {
        // 更新扩展表
        $this->dao->update(['order_no' => $orderNo], ['status' => $status]);

        // 同步中台表
        if ($centerStatusCode) {
            $centerData = array_merge(['status_code' => $centerStatusCode], $extraCenterFields);
            $this->centerServices->updateRecord($orderNo, $centerData);
        }
    }

    /**
     * 只同步中台表数据（不修改扩展表）
     * @param string $orderNo
     * @param array $data
     * @return void
     */
    public function syncCenter(string $orderNo, array $data): void
    {
        $this->centerServices->updateRecord($orderNo, $data);
    }

    // ===== 配送 =====

    /**
     * 更新配送信息（扩展表 + 中台表同步）
     * 同时更新扩展表状态为待收货，同步中台 delivery_time
     * @param string $orderNo
     * @param string $deliveryName 快递公司/配送员
     * @param string $deliveryId 快递单号/电话
     * @param string $deliveryType 配送方式（express/send/fictitious）
     * @param int $deliveryUid 送货人uid（仅送货方式有值，随 extra_info.delivery 存中台表 JSON）
     * @return void
     */
    public function updateDeliveryInfo(string $orderNo, string $deliveryName, string $deliveryId, string $deliveryType = 'express', int $deliveryUid = 0): void
    {
        // 更新扩展表配送信息 + 状态
        $this->dao->update(['order_no' => $orderNo], [
            'delivery_name' => $deliveryName,
            'delivery_id'   => $deliveryId,
            'delivery_type' => $deliveryType,
            'status'        => NormalOrderAdapter::EXT_STATUS_PENDING_RECEIVE,
        ]);

        // 同步中台表 extra_info.delivery + delivery_time
        $centerOrder = $this->centerServices->getOne(['order_no' => $orderNo], 'extra_info');
        $extraInfo = $centerOrder['extra_info'] ?? [];
        if (is_string($extraInfo)) {
            $extraInfo = json_decode($extraInfo, true) ?: [];
        }
        $extraInfo['delivery'] = [
            'delivery_type' => $deliveryType,
            'delivery_name' => $deliveryName,
            'delivery_id'   => $deliveryId,
            'delivery_uid'  => $deliveryUid,
        ];
        $this->centerServices->updateRecord($orderNo, [
            'extra_info'    => json_encode($extraInfo, JSON_UNESCAPED_UNICODE),
            'delivery_time' => time(),
        ]);

        // 普通发货路径不经过 OrderDeliveryServices::doDelivery，落库后补发统一发货事件。
        $eventOrder = $this->centerServices->getOne(['order_no' => $orderNo]);
        $eventOrder = $eventOrder ? (is_object($eventOrder) ? $eventOrder->toArray() : (array)$eventOrder) : [];
        $eventOrder['order_id'] = (string)($eventOrder['order_no'] ?? $orderNo);
        $eventExtraInfo = $eventOrder['extra_info'] ?? $extraInfo;
        if (is_string($eventExtraInfo)) {
            $eventExtraInfo = json_decode($eventExtraInfo, true) ?: [];
        }
        $storeNames = [];
        foreach (($eventExtraInfo['items'] ?? []) as $item) {
            $name = (string)($item['store_name'] ?? '');
            if ($name !== '') {
                $storeNames[] = $name;
            }
        }
        $storeName = implode('|', $storeNames) ?: (string)($eventOrder['title'] ?? '');
        $eventType = $deliveryType === 'send' ? 'deliver' : ($deliveryType === 'express' ? 'postage' : 'virtual');
        event('OrderDelivery', [
            $eventOrder,
            $storeName,
            [
                'delivery_name' => $deliveryName,
                'delivery_id'   => $deliveryId,
                'delivery_type' => $deliveryType,
            ],
            $eventType,
        ]);
    }

    // ===== 价格 =====

    /**
     * 更新订单价格（扩展表 + 中台表同步）
     * @param string $orderNo
     * @param float $totalPrice 商品总价
     * @param float $payPrice 实际支付
     * @param float $payPostage 邮费
     * @return void
     */
    public function updatePrice(string $orderNo, float $totalPrice, float $payPrice, float $payPostage = 0): void
    {
        // 记录改价前实付金额，用于派发事件时计算差额
        $oldOrder = $this->centerServices->getOne(['order_no' => $orderNo], 'pay_price');
        $oldPayPrice = (float)(is_array($oldOrder) ? ($oldOrder['pay_price'] ?? 0) : ($oldOrder?->pay_price ?? 0));

        $priceData = [
            'total_price' => $totalPrice,
            'pay_price'   => $payPrice,
            'pay_postage' => $payPostage,
        ];

        // 更新扩展表
        $this->dao->update(['order_no' => $orderNo], $priceData);

        // 同步中台表
        $this->centerServices->updateRecord($orderNo, $priceData);

        // 改价完成后派发事件：记录改价日志 + 提醒用户按新价格支付（监听器内部自行隔离失败）
        try {
            $centerOrder = $this->centerServices->getOne(['order_no' => $orderNo]);
            $order = $centerOrder ? (is_array($centerOrder) ? $centerOrder : $centerOrder->toArray()) : [];
            $order['order_id'] = $orderNo;
            $order['change_price'] = (string)round($oldPayPrice - $payPrice, 2); // 差额（正=降价，负=涨价）
            event('OrderPriceChange', [$order, $payPrice]);
        } catch (\Throwable $e) {
            \think\facade\Log::error('派发 OrderPriceChange 事件失败:' . $e->getMessage());
        }
    }

    // ===== 地址 =====

    /**
     * 更新收货地址（扩展表 + 中台表 extra_info.contact 同步）
     * @param string $orderNo
     * @param string $realName 收货人姓名
     * @param string $phone 收货人电话
     * @param string $address 收货地址
     * @return void
     */
    public function updateAddress(string $orderNo, string $realName, string $phone, string $address): void
    {
        // 更新扩展表
        $this->dao->update(['order_no' => $orderNo], [
            'real_name'    => $realName,
            'user_phone'   => $phone,
            'user_address' => $address,
        ]);

        // 同步中台表 extra_info.contact
        $centerOrder = $this->centerServices->getOne(['order_no' => $orderNo], 'extra_info');
        $extraInfo = $centerOrder['extra_info'] ?? [];
        if (is_string($extraInfo)) {
            $extraInfo = json_decode($extraInfo, true) ?: [];
        }
        $parts = explode(' ', $address, 2);
        $extraInfo['contact'] = [
            'name'        => $realName,
            'phone'       => $phone,
            'address'     => $address,
            'address_obj' => [
                'detail' => $parts[1] ?? $parts[0] ?? '',
            ],
        ];
        $this->centerServices->updateRecord($orderNo, [
            'extra_info' => json_encode($extraInfo, JSON_UNESCAPED_UNICODE),
        ]);
    }

    // ===== 积分 =====

    /**
     * 更新订单赠送积分信息（仅同步中台表 extra_info）
     * @param string $orderNo
     * @param int $giveIntegral 赠送积分数
     * @return void
     */
    public function updateIntegral(string $orderNo, int $giveIntegral): void
    {
        $centerOrder = $this->centerServices->getOne(['order_no' => $orderNo], 'extra_info');
        $extraInfo = $centerOrder['extra_info'] ?? [];
        if (is_string($extraInfo)) {
            $extraInfo = json_decode($extraInfo, true) ?: [];
        }
        $extraInfo['give_integral'] = $giveIntegral;
        $this->centerServices->updateRecord($orderNo, [
            'extra_info' => json_encode($extraInfo, JSON_UNESCAPED_UNICODE),
        ]);
    }

    // ===== 软删除 =====

    /**
     * 软删除订单（中台 is_system_del = 1）
     * 中台 delete 守卫要求用户已删，仅用于后台清理用户删除过的订单；
     * 扩展表 eb_pt_normal_order 无删除列，删除标记以中台为唯一真源
     * @param int $id 中台表 id
     * @return void
     */
    public function softDelete(int $id): void
    {
        $this->centerServices->delete($id);
    }

    /**
     * 批量软删除订单
     * @param array $ids 中台表 id 数组
     * @return void
     */
    public function batchSoftDelete(array $ids): void
    {
        foreach ($ids as $id) {
            $this->softDelete((int)$id);
        }
    }

    // ===== 确认收货 =====

    /**
     * 确认收货（扩展表状态 + 中台表状态同步）
     * @param string $orderNo
     * @return void
     */
    public function confirmReceipt(string $orderNo): void
    {
        $extend = $this->dao->getOne(['order_no' => $orderNo]);
        if (!$extend) {
            throw new \RuntimeException('订单不存在');
        }
        if (($extend['status'] ?? '') !== NormalOrderAdapter::EXT_STATUS_PENDING_RECEIVE) {
            throw new \RuntimeException('订单尚未发货或已确认收货');
        }

        // 后台确认收货与用户端共用安装分流：无安装进入待评价，有安装进入待安装。
        $targetStatus = !empty($extend['has_installation'])
            ? NormalOrderAdapter::EXT_STATUS_PENDING_INSTALL
            : NormalOrderAdapter::EXT_STATUS_PENDING_REVIEW;
        NormalOrderAdapter::instance()->advance($orderNo, $targetStatus);
        $this->centerServices->updateRecord($orderNo, ['take_time' => time()]);
    }

    // ===== 支付确认（线下支付） =====

    /**
     * 确认线下支付
     * @param string $orderNo
     * @return void
     */
    public function confirmOfflinePay(string $orderNo): void
    {
        $this->dao->update(['order_no' => $orderNo], [
            'paid'  => 1,
            'status' => NormalOrderAdapter::EXT_STATUS_PENDING_SHIPPED,
        ]);
        $this->centerServices->syncStatus($orderNo, 'processing', ['pay_time' => time(), 'pay_status' => 1, 'pay_type' => 'offline']);
    }

    // ===== 配送信息修改（已发货订单） =====

    /**
     * 修改已发货订单的配送信息（不改状态，仅更新快递公司/快递单号）
     * 扩展表 + 中台表 extra_info.delivery 同步
     * @param string $orderNo 订单号
     * @param string $deliveryName 快递公司名称
     * @param string $deliveryId 快递单号
     * @param string $deliveryCode 快递公司编码
     * @return void
     */
    public function updateDistribution(string $orderNo, string $deliveryName, string $deliveryId, string $deliveryCode = ''): void
    {
        // 更新扩展表配送信息
        $extData = [
            'delivery_name' => $deliveryName,
            'delivery_id'   => $deliveryId,
        ];
        if ($deliveryCode) {
            $extData['delivery_code'] = $deliveryCode;
        }
        $this->dao->update(['order_no' => $orderNo], $extData);

        // 同步中台表 extra_info.delivery
        $centerOrder = $this->centerServices->getOne(['order_no' => $orderNo], 'extra_info');
        $extraInfo = $centerOrder['extra_info'] ?? [];
        if (is_string($extraInfo)) {
            $extraInfo = json_decode($extraInfo, true) ?: [];
        }
        $extraInfo['delivery'] = array_merge($extraInfo['delivery'] ?? [], [
            'delivery_name' => $deliveryName,
            'delivery_id'   => $deliveryId,
        ]);
        if ($deliveryCode) {
            $extraInfo['delivery']['delivery_code'] = $deliveryCode;
        }
        $this->centerServices->updateRecord($orderNo, [
            'extra_info' => json_encode($extraInfo, JSON_UNESCAPED_UNICODE),
        ]);
    }
}
