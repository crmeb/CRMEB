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
     * @return void
     */
    public function updateDeliveryInfo(string $orderNo, string $deliveryName, string $deliveryId, string $deliveryType = 'express'): void
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

    /**
     * 拆单发货（中台化，旧拆单服务已随 eb_store_order_cart_info 表删除）
     * 将订单中部分商品拆出为独立子订单并立即发货，原订单保留剩余商品待后续发货
     * 仅支持整行拆分（不支持同商品部分数量拆分，部分数量场景走售后引擎）
     * @param string $orderNo 原订单号
     * @param array $cartIds 拆分商品行 [{cart_id, cart_num}]，cart_num 须等于快照行数量
     * @param array $deliveryData 发货数据 {delivery_type, delivery_name, delivery_id}
     * @return array [子订单号, 原订单号]
     */
    public function splitOrderDelivery(string $orderNo, array $cartIds, array $deliveryData): array
    {
        // 1. 读取中台订单与商品快照
        $centerOrder = $this->centerServices->getOne(['order_no' => $orderNo]);
        if (!$centerOrder) {
            throw new \RuntimeException('订单不存在');
        }
        $centerOrder = $centerOrder->toArray();
        // 仅已支付、未退款/未取消的订单可拆单发货
        if ((int)($centerOrder['pay_status'] ?? 0) !== 1) {
            throw new \RuntimeException('订单未支付，不能拆单发货');
        }
        if (in_array($centerOrder['status_code'] ?? '', ['refunded', 'canceled', 'after_sale'], true)) {
            throw new \RuntimeException('订单当前状态不支持拆单发货');
        }
        $extraInfo = $centerOrder['extra_info'] ?? [];
        if (is_string($extraInfo)) {
            $extraInfo = json_decode($extraInfo, true) ?: [];
        }
        $items = $extraInfo['items'] ?? [];
        if (count($items) < 2) {
            throw new \RuntimeException('订单商品不足两行，无需拆单');
        }

        // 2. 整行拆分校验：按 cart_id 匹配快照行，数量必须整行
        $selectMap = [];
        foreach ($cartIds as $cart) {
            $cartId = (int)($cart['cart_id'] ?? 0);
            $cartNum = (int)($cart['cart_num'] ?? 0);
            if ($cartId <= 0 || $cartNum <= 0) {
                throw new \RuntimeException('拆分商品参数错误');
            }
            $matched = null;
            foreach ($items as $item) {
                if ((int)($item['cart_id'] ?? 0) === $cartId) {
                    $matched = $item;
                    break;
                }
            }
            if ($matched === null) {
                throw new \RuntimeException('拆分商品不在订单内');
            }
            if ($cartNum !== (int)($matched['cart_num'] ?? 0)) {
                throw new \RuntimeException('仅支持整行拆分，拆分数量须等于商品数量');
            }
            $selectMap[$cartId] = true;
        }

        // 3. 快照分组：拆分行 / 剩余行
        $selected = $remain = [];
        foreach ($items as $item) {
            if (isset($selectMap[(int)($item['cart_id'] ?? 0)])) {
                $selected[] = $item;
            } else {
                $remain[] = $item;
            }
        }
        if (!$selected || !$remain) {
            throw new \RuntimeException('拆分后原订单与子订单商品均不能为空');
        }

        // 4. 金额分摊：按商品金额占比拆分，尾差归原订单（保证两单之和等于原单）
        $selGoods = $remGoods = '0';
        $selPostage = $remPostage = '0';
        foreach ($selected as $item) {
            $selGoods = bcadd($selGoods, bcmul((string)($item['true_price'] ?? 0), (string)($item['cart_num'] ?? 0), 2), 2);
            $selPostage = bcadd($selPostage, (string)($item['postage_price'] ?? 0), 2);
        }
        foreach ($remain as $item) {
            $remGoods = bcadd($remGoods, bcmul((string)($item['true_price'] ?? 0), (string)($item['cart_num'] ?? 0), 2), 2);
            $remPostage = bcadd($remPostage, (string)($item['postage_price'] ?? 0), 2);
        }
        $goodsTotal = bcadd($selGoods, $remGoods, 2);
        if (bccomp($goodsTotal, '0', 2) <= 0) {
            throw new \RuntimeException('订单金额异常，不能拆单');
        }
        // 比例分摊的金额列：商品总价/实付/优惠/积分抵扣/会员折/运费（运费按行归集已算出，其余按比例）
        $ratioFields = ['total_price', 'pay_price', 'coupon_price', 'deduction_price', 'member_discount', 'vip_discount', 'use_integral_num'];
        $childAmounts = [];
        foreach ($ratioFields as $field) {
            $original = (string)($centerOrder[$field] ?? 0);
            $isInt = $field === 'use_integral_num';
            $scale = $isInt ? 0 : 2;
            $childVal = $isInt
                ? bcmul($original, bcdiv($selGoods, $goodsTotal, 4), 0)
                : bcdiv(bcmul($original, $selGoods, 4), $goodsTotal, 2);
            $childAmounts[$field] = $childVal;
            // 原单金额 = 原值 - 子单值（尾差归原单）
            $centerOrder[$field] = $isInt
                ? bcsub($original, $childVal, 0)
                : bcsub($original, $childVal, 2);
        }
        // 运费按 items 行归集
        $childAmounts['pay_postage'] = $selPostage;
        $centerOrder['pay_postage'] = $remPostage;
        // 件数按行数量
        $selNum = array_sum(array_column($selected, 'cart_num'));
        $remNum = array_sum(array_column($remain, 'cart_num'));

        // 5. 事务：子订单创建 + 原订单扣减 + 扩展表同步 + 子订单发货
        $childNo = $this->centerServices->getNewOrderId('cp');
        $now = time();
        $this->transaction(function () use ($orderNo, $childNo, $centerOrder, $extraInfo, $selected, $remain, $childAmounts, $selNum, $remNum, $now) {
            /** @var \app\dao\order\OrderCenterDao $centerDao */
            $centerDao = app(\app\dao\order\OrderCenterDao::class);
            // 5.1 扩展表：复制原扩展行为子订单（发货字段清空，状态待发货）
            $extRow = $this->dao->getOne(['order_no' => $orderNo]);
            $extData = $extRow ? $extRow->toArray() : [];
            unset($extData['id']);
            $extData['order_no'] = $childNo;
            $extData['product_id'] = (int)($selected[0]['product_id'] ?? 0);
            $extData['sku_id'] = $selected[0]['attr_info']['unique'] ?? ($extData['sku_id'] ?? '');
            $extData['pay_price'] = $childAmounts['pay_price'];
            $extData['total_price'] = $childAmounts['total_price'];
            $extData['status'] = NormalOrderAdapter::EXT_STATUS_PENDING_SHIPPED;
            $extData['delivery_name'] = '';
            $extData['delivery_id'] = '';
            $extData['delivery_type'] = '';
            $extData['add_time'] = $now;
            $extData['update_time'] = $now;
            $childExt = $this->dao->save($extData);
            if (!$childExt) {
                throw new \RuntimeException('生成子订单扩展记录失败');
            }

            // 5.2 中台表：复制原行为子订单（items=拆分行，金额分摊，发货状态清零）
            $childCenter = $centerOrder;
            unset($childCenter['id']);
            $childCenter['order_no'] = $childNo;
            $childCenter['source_id'] = (int)$childExt->id;
            foreach ($childAmounts as $field => $val) {
                $childCenter[$field] = $val;
            }
            $childCenter['total_num'] = $selNum;
            $childCenter['title'] = $selected[0]['store_name'] ?? '';
            $childCenter['image'] = $selected[0]['image'] ?? '';
            $childCenter['refund_status'] = 0;
            $childCenter['refund_price'] = '0.00';
            $childCenter['delivery_time'] = 0;
            $childCenter['take_time'] = 0;
            $childCenter['refund_time'] = 0;
            $childCenter['add_time'] = $now;
            // 子单 extra_info：items=拆分行 + 记录拆单来源
            $childExtra = $extraInfo;
            $childExtra['items'] = $selected;
            $childExtra['sku_info'] = $selected[0]['attr_info'] ?? ($childExtra['sku_info'] ?? []);
            $childExtra['split_parent'] = $orderNo;
            $childCenter['extra_info'] = json_encode($childExtra, JSON_UNESCAPED_UNICODE);
            if (!$centerDao->save($childCenter)) {
                throw new \RuntimeException('生成子订单失败');
            }

            // 5.3 中台表：原订单扣减（items=剩余行 + 金额 + 标题）
            $parentExtra = $extraInfo;
            $parentExtra['items'] = $remain;
            $parentExtra['sku_info'] = $remain[0]['attr_info'] ?? ($parentExtra['sku_info'] ?? []);
            // 拆单血缘记录（追加，不覆盖既有子单）
            $children = $parentExtra['split_children'] ?? [];
            $children[] = $childNo;
            $parentExtra['split_children'] = $children;
            $centerDao->update(['order_no' => $orderNo], [
                'total_price'    => $centerOrder['total_price'],
                'pay_price'      => $centerOrder['pay_price'],
                'pay_postage'    => $centerOrder['pay_postage'],
                'coupon_price'   => $centerOrder['coupon_price'],
                'deduction_price'=> $centerOrder['deduction_price'],
                'member_discount'=> $centerOrder['member_discount'],
                'vip_discount'   => $centerOrder['vip_discount'],
                'use_integral_num'=> $centerOrder['use_integral_num'],
                'total_num'      => $remNum,
                'title'          => $remain[0]['store_name'] ?? '',
                'image'          => $remain[0]['image'] ?? '',
                'extra_info'     => json_encode($parentExtra, JSON_UNESCAPED_UNICODE),
            ]);

            // 5.4 扩展表：原订单金额同步
            $this->dao->update(['order_no' => $orderNo], [
                'pay_price'   => $centerOrder['pay_price'],
                'total_price' => $centerOrder['total_price'],
            ]);
        });

        // 6. 子订单发货（复用统一发货落库：扩展表状态+中台 delivery/extra_info.delivery/delivery_time）
        $this->updateDeliveryInfo($childNo, (string)($deliveryData['delivery_name'] ?? ''), (string)($deliveryData['delivery_id'] ?? ''), (string)($deliveryData['delivery_type'] ?? 'express'));

        // 7. 双订单日志
        /** @var \app\dao\order\OrderLogDao $logDao */
        $logDao = app(\app\dao\order\OrderLogDao::class);
        $uid = (int)($centerOrder['uid'] ?? 0);
        $statusCode = (string)($centerOrder['status_code'] ?? '');
        $logDao->save([
            'order_no' => $orderNo, 'uid' => $uid, 'operator_id' => 0, 'operator_type' => 'admin',
            'from_status' => $statusCode, 'to_status' => $statusCode,
            'content' => '拆单发货：拆出子订单 ' . $childNo . '（' . $selNum . ' 件）', 'add_time' => $now,
        ]);
        $logDao->save([
            'order_no' => $childNo, 'uid' => $uid, 'operator_id' => 0, 'operator_type' => 'admin',
            'from_status' => $statusCode, 'to_status' => $statusCode,
            'content' => '拆单发货（来源订单 ' . $orderNo . '）：' . ($deliveryData['delivery_name'] ?? ''), 'add_time' => $now,
        ]);

        return [$childNo, $orderNo];
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
