<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\controller\admin;

use app\adminapi\controller\AuthController;
use app\dao\order\OrderCenterDao;
use app\dao\order\OrderLogDao;
use app\services\order\OrderCenterServices;
use app\services\order\AfterSaleServices;
use app\services\order\OrderWriteOffServices;
use app\services\shipping\ExpressServices;
use app\services\driver\express\ExpressConfigServices;
use app\services\order\OrderDeliveryServices;
use modules\product\normal\dao\NormalOrderDao;
use modules\product\normal\handler\NormalOrderAdapter;
use app\services\personnel\ServicePersonnelServices;
use modules\product\normal\service\OrderServices;
use think\facade\Log;
use app\services\pay\PayServices;
use crmeb\trade\order\type\OrderTypeAdapterInterface;
use crmeb\exceptions\ApiException;

/**
 * 实物商品后台订单控制器
 * 从订单中台（eb_order_center）获取摘要数据，从扩展表（eb_pt_normal_order）获取扩展信息
 */
class OrderController extends AuthController
{
    /**
     * 订单统计
     * 遍历中台状态树，自动为每个节点（含子状态）计算订单数量和金额
     * 业务状态数量由状态树的 children 决定，无需硬编码
     */
    public function statistics(OrderCenterServices $centerServices)
    {
        // 提取前端搜索参数，与 list() 一致
        $params = $this->request->getMore([
            ['keyword', ''],
            ['search_field', ''],
            ['order_no', ''],
            ['date_range', ''],
            ['pay_type', ''],
            ['type', ''],
        ]);

        // 订单类型映射
        $activityType = '';
        if (!empty($params['type'])) {
            $activityType = $this->mapOrderType($params['type']);
        }

        $where = [
            'order_category' => 'goods',
            'product_type'   => 'normal',
            'keyword'        => $params['keyword'] ?? '',
            'search_field'   => $params['search_field'] ?? '',
            'order_no'       => $params['order_no'] ?? '',
            'date_range'     => $params['date_range'] ?? '',
            'pay_type'       => $params['pay_type'] ?? '',
            'activity_type'  => $activityType,
        ];
        $data = $centerServices->getStatistics($where);

        $baseWhere = [
            ['order_category', '=', 'goods'],
            ['product_type', '=', 'normal'],
        ];

        // 构建公共筛选条件（订单号、关键词、日期范围、支付方式）
        $commonFilter = $this->buildSearchFilter($params, $centerServices);

        $businessStats = [];

        /** @var OrderCenterDao $centerDao */
        $centerDao = app(OrderCenterDao::class);

        // 遍历状态树，自动为每个有子状态的节点计数
        foreach (NormalOrderAdapter::getStatusTree() as $statusCode => $node) {
            // 售后特殊处理：通过售后表统计有售后记录的订单数，而非仅 status_code=after_sale
            if ($statusCode === 'after_sale') {
                $candidateQuery = $centerDao->getDb()
                    ->where($baseWhere)
                    ->where('is_del', '=', 0)
                    ->where('is_system_del', '=', 0)
                    ->where('status_code', '<>', 'canceled');
                $this->applyCommonFilter($candidateQuery, $commonFilter);
                $candidateOrderNos = $candidateQuery->column('order_no');
                $afterSaleOrderNos = app(\app\dao\order\AfterSaleDao::class)
                    ->getOrderNosByAfterSaleStatus('all', $candidateOrderNos);
                $businessStats['after_sale'] = count($afterSaleOrderNos);
                continue;
            }

            if (empty($node['children'])) {
                // 无子状态 → 直接按 status_code 计数
                $query = $centerDao->getDb()
                    ->where($baseWhere)
                    ->where('status_code', '=', $statusCode)
                    ->where('is_del', '=', 0)->where('is_system_del', '=', 0)
                    ->where('status_code', '<>', 'canceled');
                $this->applyCommonFilter($query, $commonFilter);
                $businessStats[$statusCode] = $query->count();
            } else {
                // 有子状态 → 为每个子状态单独计数
                foreach ($node['children'] as $childKey => $child) {
                    $query = $centerDao->getDb()
                        ->where($baseWhere)
                        ->where('is_del', '=', 0)->where('is_system_del', '=', 0)
                        ->where('status_code', '<>', 'canceled');
                    $this->applyCommonFilter($query, $commonFilter);
                    // 应用子状态特有的筛选条件
                    if (!empty($child['filter'])) {
                        foreach ($child['filter'] as $cond) {
                            if (str_contains($cond[0] ?? '', 'pt_normal_order.')) {
                                // 扩展表条件 → 两步查询 order_no + IN
                                $orderNos = NormalOrderAdapter::resolveExtensionFilter([$cond]);
                                $query->whereIn('order_no', $orderNos);
                            } else {
                                // 中台字段条件 → 直接 WHERE
                                $query->where(...$cond);
                            }
                        }
                    }
                    $businessStats[$childKey] = $query->count();
                }
            }
        }

        // 已删除（用户删除且后台未删，与「已删除」页签列表口径一致）
        $delQuery = $centerDao->getDb()
            ->where($baseWhere)->where('is_del', '=', 1)->where('is_system_del', '=', 0);
        $this->applyCommonFilter($delQuery, $commonFilter);
        $businessStats['deleted'] = $delQuery->count();

        $data['business_status_counts'] = $businessStats;

        return app('json')->success($data);
    }

    /**
     * 根据前端搜索参数构建公共筛选条件（闭包数组）
     * 关键词/订单号筛选逻辑与 OrderCenterServices::getAdminList 保持一致
     * @param array $params [keyword, search_field, order_no, date_range, pay_type, type]
     * @param OrderCenterServices $centerServices 订单中台服务（复用关键词搜索闭包构建）
     * @return array 闭包数组，每个闭包可直接传给 $query->where()
     */
    private function buildSearchFilter(array $params, OrderCenterServices $centerServices): array
    {
        $filters = [];

        // 订单号独立搜索框：前缀匹配
        if (!empty($params['order_no'])) {
            $orderNo = addcslashes(trim((string) $params['order_no']), '%_');
            $filters[] = fn($q) => $q->where('order_no', 'LIKE', "{$orderNo}%");
        }

        // 关键词搜索（search_field 指定字段，口径与中台列表一致）
        $keyword = trim((string) ($params['keyword'] ?? ''));
        if ($keyword !== '') {
            $filters[] = $centerServices->buildKeywordFilterClosure($keyword, (string) ($params['search_field'] ?? 'all'));
        }

        if (!empty($params['date_range'])) {
            [$start, $end] = $this->parseDateRange($params['date_range']);
            $filters[] = fn($q) => $q->where('add_time', 'BETWEEN', [$start, $end]);
        }

        if (!empty($params['pay_type'])) {
            $payType = $params['pay_type'];
            $filters[] = fn($q) => $q->where('pay_type', '=', $payType);
        }

        // 订单类型筛选（前端数字 → activity_type 字符串）
        if (!empty($params['type'])) {
            $activityType = $this->mapOrderType($params['type']);
            if ($activityType !== '') {
                $filters[] = fn($q) => $q->where('activity_type', '=', $activityType);
            }
        }

        return $filters;
    }

    /**
     * 将公共筛选条件应用到查询
     * @param \think\db\Query $query
     * @param array $filters 闭包数组
     */
    private function applyCommonFilter($query, array $filters): void
    {
        foreach ($filters as $closure) {
            $query->where($closure);
        }
    }

    /**
     * 解析日期范围字符串为时间戳数组
     * @param string $dateRange 日期范围字符串，格式：2026-07-01,2026-07-31
     * @return array [开始时间戳, 结束时间戳(含当日23:59:59)]
     */
    private function parseDateRange(string $dateRange): array
    {
        $parts = explode(',', $dateRange);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('日期范围格式错误，期望格式: 2026-07-01,2026-07-31');
        }
        $start = strtotime(trim($parts[0]));
        $end   = strtotime(trim($parts[1]));
        if ($start === false || $end === false) {
            throw new \InvalidArgumentException('日期格式错误');
        }
        return [$start, $end + 86399];
    }

    /**
     * 将前端订单类型数字映射为 activity_type 字符串
     * @param string $type 前端传来的类型值（1=普通, 2=拼团, 3=秒杀, 4=砍价, 5=预售）
     * @return string activity_type 值，普通订单返回空字符串（不筛选）
     */
    private function mapOrderType(string $type): string
    {
        $map = [
            '1' => '',           // 普通订单 → 不筛选
            '2' => 'combination', // 拼团
            '3' => 'seckill',     // 秒杀
            '4' => 'bargain',     // 砍价
            '5' => 'advance',     // 预售
        ];
        return $map[$type] ?? '';
    }

    /**
     * 从订单中台筛选实物商品订单，支持多维度搜索和筛选
     * 前端传入 business_status（业务状态），控制器翻译为中台通用查询条件
     */
    public function list(OrderCenterServices $centerServices)
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 20],
            ['order_no', ''],
            ['status_code', ''],
            ['keyword', ''],
            ['search_field', ''],
            ['date_range', ''],
            ['pay_type', ''],
            ['type', ''],
        ]);
        $where['order_category'] = 'goods';
        $where['product_type'] = 'normal';
        $where['is_deleted'] = false;

        // 订单类型映射：前端数字 → activity_type 字符串
        $type = $where['type'] ?? '';
        if (!empty($type)) {
            $activityType = $this->mapOrderType($type);
            if ($activityType !== '') {
                $where['activity_type'] = $activityType;
            }
        }
        unset($where['type']);

        // 将业务状态翻译为中台通用查询条件
        $businessStatus = $this->request->get('business_status', '');
        if (!empty($businessStatus)) {
            $this->translateBusinessStatus($businessStatus, $where);
        }

        $result = $centerServices->getAdminList($where, (int)$where['page'], (int)$where['limit']);
        return app('json')->success($result);
    }

    /**
     * 将业务状态翻译为中台通用查询条件
     * 遍历中台状态树，找到 businessStatus 对应的节点：
     * - 如果 businessStatus 是顶层状态（无子状态）→ 直接按 status_code 筛选
     * - 如果 businessStatus 是某个顶层状态下的子状态 → 按父级 status_code + 子状态 filter 筛选
     * - 特殊状态 deleted → is_del = 1
     * @param string $businessStatus 业务状态标识
     * @param array &$where 查询条件数组（引用传递）
     */
    private function translateBusinessStatus(string $businessStatus, array &$where): void
    {
        // 特殊状态：已删除
        if ($businessStatus === 'deleted') {
            $where['is_deleted'] = true;
            return;
        }

        // 特殊状态：售后（通过售后表筛选所有有售后记录的订单，而非仅 status_code=after_sale）
        // 避免遗漏已退款(refunded)或进行中(processing)但有售后记录的订单
        if ($businessStatus === 'after_sale') {
            $where['after_sale'] = 'all';
            return;
        }

        // 遍历状态树，查找匹配的节点
        foreach (NormalOrderAdapter::getStatusTree() as $statusCode => $node) {
            // 顶层状态直接命中
            if ($businessStatus === $statusCode) {
                $where['status_code'] = $statusCode;
                return;
            }
            // 在子状态中查找
            foreach (($node['children'] ?? []) as $childKey => $child) {
                if ($businessStatus === $childKey) {
                    $where['status_code'] = $statusCode;
                    // 应用子状态特有的筛选条件
                    if (!empty($child['filter'])) {
                        // 拆分为中台字段条件 和 扩展表字段条件
                        $centerFilter = [];
                        $extFilter = [];
                        foreach ($child['filter'] as $cond) {
                            if (str_contains($cond[0] ?? '', 'pt_normal_order.')) {
                                $extFilter[] = $cond;
                            } else {
                                $centerFilter[] = $cond;
                            }
                        }
                        // 扩展表条件：两步查询，先查 order_no 再 IN
                        if (!empty($extFilter)) {
                            $where['_order_nos'] = NormalOrderAdapter::resolveExtensionFilter($extFilter);
                        }
                        // 中台字段条件：放入闭包透传
                        if (!empty($centerFilter)) {
                            $where['extra_conditions'][] = function ($query) use ($centerFilter) {
                                foreach ($centerFilter as $cond) {
                                    $query->where(...$cond);
                                }
                            };
                        }
                    }
                    return;
                }
            }
        }
    }

    /**
     * 订单详情
     * 从中台获取完整详情（含商品明细、时间线、操作按钮等）
     */
    public function read(string $orderNo, OrderCenterServices $centerServices)
    {
        try {
            $detail = $centerServices->getDetailByOrderNo($orderNo);
            return app('json')->success($detail);
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取改价表单数据
     * 返回当前订单的价格信息，供前端编辑
     */
    public function editPrice(OrderCenterServices $centerServices)
    {
        $orderNo = $this->request->param('order_no', '');
        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }
        $order = $centerServices->getOne(['order_no' => $orderNo],
            'order_no, status_code, delivery_time, is_del, total_price, pay_price, pay_postage, coupon_price, deduction_price, use_integral_num, extra_info');
        if (!$order) {
            return app('json')->fail('订单不存在');
        }
        $data = $order->toArray();
        // 解析 extra_info 中的积分字段（旧订单 fallback）
        $extraInfo = $data['extra_info'] ?? [];
        if (is_string($extraInfo)) {
            $extraInfo = json_decode($extraInfo, true) ?? [];
        }
        $data['give_integral'] = $extraInfo['give_integral'] ?? 0;
        $data['use_integral'] = $data['use_integral_num'] ?? $extraInfo['use_integral_num'] ?? 0;
        $data['backed_integral'] = $extraInfo['backed_integral'] ?? 0;
        // 仅保留配送方式，供前端与列表同口径计算业务状态（待发货/待收货/待到店）
        $data['extra_info'] = ['delivery_type' => (string)($extraInfo['delivery_type'] ?? '')];
        return app('json')->success($data);
    }

    /**
     * 改价保存
     * 通过 OrderServices 统一更新扩展表和中台表的价格信息
     */
    public function updatePrice(OrderCenterServices $centerServices, OrderServices $orderServices)
    {
        $orderNo = $this->request->param('order_no', '');
        $totalPrice = (float)$this->request->param('total_price', 0);
        $payPrice = (float)$this->request->param('pay_price', 0);
        $payPostage = (float)$this->request->param('pay_postage', 0);

        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }
        if ($totalPrice < 0 || $payPrice < 0) {
            return app('json')->fail('价格不能为负数');
        }

        try {
            // 获取当前订单状态（用于日志）
            $order = $centerServices->getOne(['order_no' => $orderNo], 'uid, status_code');
            if (!$order) {
                return app('json')->fail('订单不存在');
            }
            $currentStatus = $order['status_code'] ?? '';
            $uid = (int)($order['uid'] ?? 0);

            // 通过 OrderServices 统一更新扩展表 + 中台表
            $orderServices->updatePrice($orderNo, $totalPrice, $payPrice, $payPostage);

            // 写入订单记录
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $orderNo,
                'uid'           => $uid,
                'operator_id'   => (int)($this->adminInfo['id'] ?? 0),
                'operator_type' => 'admin',
                'from_status'   => $currentStatus,
                'to_status'     => $currentStatus,
                'content'       => '管理员改价：商品总价调整为¥' . $totalPrice . '，实际支付调整为¥' . $payPrice . '，配送邮费调整为¥' . $payPostage,
                'add_time'      => time(),
            ]);

            return app('json')->success('改价成功');
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] updatePrice failed: ' . $e->getMessage());
            return app('json')->fail('改价失败');
        }
    }

    /**
     * 发货
     * 支持三种配送方式：express（快递）、send（配送/送货）、fictitious（虚拟/无需配送）
     * 通过 OrderServices 统一更新扩展表 + 中台表
     */
    public function shipment(OrderServices $orderServices)
    {
        $orderNo = $this->request->param('order_no', '');
        $deliveryType = $this->request->param('delivery_type', 'express');

        if (empty($orderNo)) {
            return app('json')->fail('请选择要发货的订单');
        }

        // 获取订单基本信息，用于记录日志
        $centerServices = app(OrderCenterServices::class);
        $orderForLog = $centerServices->getOne(['order_no' => $orderNo], 'uid, status_code');
        $logUid = (int)($orderForLog['uid'] ?? 0);
        $currentStatus = $orderForLog['status_code'] ?? '';

        try {
            $expressRecordType = (int)$this->request->param('express_record_type', 1);
            switch ($deliveryType) {
                case 'express':
                    // 快递发货
                    $deliveryName = $this->request->param('delivery_name', '');
                    $deliveryId = $this->request->param('delivery_id', '');
                    if (empty($deliveryName)) {
                        return app('json')->fail('请选择快递公司');
                    }
                    // 电子面单发货：快递单号由面单下单后回填，无需手输
                    if ($expressRecordType !== 2 && empty($deliveryId)) {
                        return app('json')->fail('请填写快递单号');
                    }
                    break;
                case 'send':
                    // 配送/送货
                    $deliveryName = $this->request->param('sh_delivery_name', '');
                    $deliveryId = $this->request->param('sh_delivery_id', '');
                    $shDeliveryUid = (int)$this->request->param('sh_delivery_uid', 0);
                    if (empty($deliveryName)) {
                        return app('json')->fail('请选择送货人');
                    }
                    if (empty($deliveryId)) {
                        return app('json')->fail('请输入送货人电话');
                    }
                    break;
                case 'fictitious':
                    // 虚拟发货（无需配送）
                    $fictitiousContent = $this->request->param('fictitious_content', '');
                    $deliveryName = '虚拟发货';
                    $deliveryId = $fictitiousContent ?: date('YmdHis');
                    break;
                default:
                    return app('json')->fail('不支持的发货类型');
            }

            // 电子面单发货：走完整发货管线（一号通面单下单→回填运单号→事件同步扩展表/中台）
            $dumpResult = [];
            if ($deliveryType === 'express' && (int)$expressRecordType === 2) {
                $centerOrder = $centerServices->getOne(['order_no' => $orderNo], 'id');
                if (!$centerOrder) {
                    return app('json')->fail('订单不存在');
                }
                $deliveryData = [
                    'type' => 1,
                    'delivery_name' => $deliveryName,
                    // 兼容只传快递公司名称的调用：按名称回退查公司编码
                    'delivery_code' => $this->request->param('delivery_code', '')
                        ?: (string)app(ExpressServices::class)->value(['name' => $deliveryName], 'code'),
                    'delivery_id' => '',
                    'express_record_type' => 2,
                    'express_temp_id' => (string)$this->request->param('express_temp_id', ''),
                    'to_name' => $this->request->param('to_name', ''),
                    'to_tel' => $this->request->param('to_tel', ''),
                    'to_addr' => $this->request->param('to_addr', ''),
                ];
                /** @var OrderDeliveryServices $deliveryServices */
                $deliveryServices = app(OrderDeliveryServices::class);
                $dumpResult = $deliveryServices->delivery((int)$centerOrder['id'], $deliveryData);
                // 面单下单返回的运单号
                $deliveryId = (string)($dumpResult['kuaidinum'] ?? $deliveryId);
            } else {
                // 通过 OrderServices 统一更新扩展表配送信息 + 状态 + 中台表同步
                $orderServices->updateDeliveryInfo($orderNo, $deliveryName, $deliveryId, $deliveryType, $shDeliveryUid ?? 0);
            }

            // 写入订单记录
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $deliveryLabel = ['express' => '快递', 'send' => '配送/送货', 'fictitious' => '虚拟发货'];
            $logDao->save([
                'order_no'      => $orderNo,
                'uid'           => $logUid,
                'operator_id'   => (int)($this->adminInfo['id'] ?? 0),
                'operator_type' => 'admin',
                'from_status'   => $currentStatus,
                'to_status'     => $currentStatus,
                'content'       => '管理员订单发货：' . ($deliveryLabel[$deliveryType] ?? $deliveryType) . '（' . $deliveryName . '）',
                'add_time'      => time(),
            ]);

            // 电子面单发货时带回运单号与面单图片，供前端展示打印
            if ($deliveryType === 'express' && (int)$expressRecordType === 2) {
                return app('json')->success('发货成功', [
                    'delivery_id' => $deliveryId,
                    'kuaidi_label' => $dumpResult['label'] ?? '',
                ]);
            }
            return app('json')->success('发货成功');
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] shipment failed: ' . $e->getMessage());
            return app('json')->fail('发货失败：' . $e->getMessage());
        }
    }

    /**
     * 获取发货详情
     * 返回订单用户信息和商品列表，供发货弹窗展示
     */
    public function getShipmentDetail(OrderCenterServices $centerServices, NormalOrderDao $normalOrderDao)
    {
        $orderNo = $this->request->param('order_no', '');
        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }

        try {
            // 从中台获取订单基本信息
            $centerOrder = $centerServices->getOne(['order_no' => $orderNo],
                'order_no, uid, extra_info, status_code, pay_status');
            if (!$centerOrder) {
                return app('json')->fail('订单不存在');
            }
            $centerOrder = $centerOrder->toArray();

            // 从扩展表获取用户信息（新订单可能没有扩展表记录，需兼容）
            $extOrder = $normalOrderDao->getOne(['order_no' => $orderNo],
                'real_name, user_phone, user_address') ?? [];

            // 商品快照：下单时写入 extra_info.items（新架构唯一商品快照来源）
            $extraInfo = $centerOrder['extra_info'] ?? [];
            if (is_string($extraInfo)) {
                $extraInfo = json_decode($extraInfo, true) ?: [];
            }
            $items = $extraInfo['items'] ?? [];

            // 格式化地址：contact.address 可能是对象，需转为字符串
            $addressFromExtra = '';
            if (isset($extraInfo['contact']['address'])) {
                $addr = $extraInfo['contact']['address'];
                $addressFromExtra = is_array($addr)
                    ? implode('', array_filter([
                        $addr['province'] ?? '',
                        $addr['city'] ?? '',
                        $addr['district'] ?? '',
                        $addr['detail'] ?? '',
                    ]))
                    : ($addr ?: '');
            }

            $data = [
                'order_no'     => $orderNo,
                'real_name'    => ($extOrder['real_name'] ?? '') ?: ($extraInfo['contact']['name'] ?? ''),
                'user_phone'   => ($extOrder['user_phone'] ?? '') ?: ($extraInfo['contact']['phone'] ?? ''),
                'user_address' => ($extOrder['user_address'] ?? '') ?: $addressFromExtra,
                'total_num'    => count($items),
                'items'        => $items,
            ];

            return app('json')->success($data);
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] getShipmentDetail failed: ' . $e->getMessage());
            return app('json')->fail('获取发货详情失败');
        }
    }

    /**
     * 获取配送员列表
     */
    public function getDeliveryPersons(ServicePersonnelServices $deliveryServices)
    {
        try {
            $list = $deliveryServices->getDeliveryList();
            return app('json')->success($list['list'] ?? []);
        } catch (\Throwable $e) {
            return app('json')->fail('获取配送员列表失败');
        }
    }

    /**
     * 获取电子面单模板
     */
    public function getExpressTemp()
    {
        $com = $this->request->param('com', '');
        try {
            $data = app(\app\services\driver\express\ExpressTemplateServices::class)
                ->getExpressList($com);
            return app('json')->success($data);
        } catch (\Throwable $e) {
            return app('json')->fail('获取电子面单模板失败');
        }
    }

    /**
     * 获取商家寄件配置信息
     * 返回电子面单开关与寄件人默认信息（姓名、电话、地址），用于发货弹窗
     * 开关与寄件人信息以电子面单配置表为准（旧 sys_config config_export_open 键已废弃）
     */
    public function getSheetInfo()
    {
        try {
            /** @var ExpressConfigServices $expressConfig */
            $expressConfig = app(ExpressConfigServices::class);
            $config = $expressConfig->getConfig();
            $data = [
                // 寄件人信息优先取面单配置，未配置时回退店铺信息
                'to_name'     => $config['to_name'] ?: sys_config('store_name', ''),
                'to_tel'      => $config['to_tel'] ?: sys_config('store_phone', ''),
                'to_addr'     => $config['to_address'] ?: sys_config('store_address', ''),
                'export_open' => (int)$config['open'],
            ];
            return app('json')->success($data);
        } catch (\Throwable $e) {
            return app('json')->fail('获取配置信息失败');
        }
    }

    /**
     * 获取可拆分发货商品列表
     */
    public function getSplitCartInfo(OrderCenterServices $centerServices)
    {
        $orderNo = $this->request->param('order_no', '');
        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }
        try {
            $centerOrder = $centerServices->getOne(['order_no' => $orderNo], 'extra_info');
            if (!$centerOrder) {
                return app('json')->fail('订单不存在');
            }
            $centerOrder = $centerOrder->toArray();

            $extraInfo = $centerOrder['extra_info'] ?? [];
            if (is_string($extraInfo)) $extraInfo = json_decode($extraInfo, true) ?: [];
            // 商品快照：下单时写入 extra_info.items（新架构唯一商品快照来源）
            $items = $extraInfo['items'] ?? [];

            return app('json')->success($items);
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] getSplitCartInfo failed: ' . $e->getMessage());
            return app('json')->fail('获取拆分商品列表失败');
        }
    }

    /**
     * 批量发货
     * 上传 Excel 文件（列：订单号、快递公司、快递单号），逐条发货
     * 通过 OrderServices::updateDeliveryInfo 统一处理扩展表 + 中台表同步
     */
    public function batchShipment()
    {
        $file = $this->request->param('file', '');
        if (empty($file)) {
            return app('json')->fail('请上传文件');
        }

        try {
            $filePath = public_path() . substr($file, 1);
            if (!is_file($filePath)) {
                return app('json')->fail('文件不存在');
            }
            $suffix = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (!in_array($suffix, ['xls', 'xlsx'])) {
                return app('json')->fail('文件格式不正确，请上传xls或xlsx格式');
            }

            $success = 0;
            $fail = 0;
            $errors = [];

            // 读取 Excel
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            // 跳过表头
            array_shift($rows);

            $orderServices = app(OrderServices::class);

            foreach ($rows as $index => $row) {
                $orderNo = trim((string)($row[0] ?? ''));
                $deliveryName = trim((string)($row[1] ?? ''));
                $deliveryId = trim((string)($row[2] ?? ''));
                if (empty($orderNo) && empty($deliveryName) && empty($deliveryId)) {
                    continue;
                }
                if (empty($orderNo)) {
                    $fail++;
                    $errors[] = '第' . ($index + 2) . '行：订单号为空';
                    continue;
                }
                if (empty($deliveryName) || empty($deliveryId)) {
                    $fail++;
                    $errors[] = '第' . ($index + 2) . '行（' . $orderNo . '）：快递公司或快递单号为空';
                    continue;
                }
                try {
                    // 通过 OrderServices 统一更新配送信息 + 状态 + 中台表同步
                    $orderServices->updateDeliveryInfo($orderNo, $deliveryName, $deliveryId, 'express');
                    $success++;
                } catch (\Throwable $e) {
                    $fail++;
                    $errors[] = '第' . ($index + 2) . '行（' . $orderNo . '）：' . $e->getMessage();
                }
            }

            $msg = "批量发货完成：成功{$success}条，失败{$fail}条";
            return app('json')->success($msg, ['success' => $success, 'fail' => $fail, 'errors' => $errors]);
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] batchShipment failed: ' . $e->getMessage());
            return app('json')->fail('批量发货失败：' . $e->getMessage());
        }
    }

    /**
     * 确认收货（后台手动确认）
     * 通过 OrderServices 统一更新扩展表状态 + 中台表状态
     */
    public function confirmReceipt(OrderServices $orderServices, OrderCenterServices $centerServices, OrderWriteOffServices $writeOffServices)
    {
        $orderNo = $this->request->param('order_no', '');
        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }
        try {
            $order = $centerServices->getOne(['order_no' => $orderNo], 'order_no, shipping_type, verify_code');
            if (!$order) {
                return app('json')->fail('订单不存在');
            }
            $order = $order->toArray();
            if ((int)($order['shipping_type'] ?? 0) === 2) {
                if (empty($order['verify_code'])) {
                    return app('json')->fail('订单核销码缺失，无法核销');
                }
                $writeOffServices->writeOffOrder((string)$order['verify_code'], 1, (int)($this->adminId ?? 0), 1);
                return app('json')->success('到店核销成功');
            }

            $orderServices->confirmReceipt($orderNo);
            return app('json')->success('确认收货成功');
        } catch (ApiException $e) {
            // 业务校验类异常返回具体原因（如"该订单已被核销"），不吞成通用文案
            return app('json')->fail($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] confirmReceipt failed: ' . $e->getMessage());
            return app('json')->fail('确认收货失败');
        }
    }

    /**
     * 退款表单配置
     * 返回当前订单的退款信息，供前端渲染退款表单
     */
    public function getRefundForm(OrderCenterServices $centerServices)
    {
        $orderNo = $this->request->param('order_no', '');
        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }
        $order = $centerServices->getOne(['order_no' => $orderNo],
            'order_no, pay_price, refund_price, refund_status, pay_type, status_code, product_type, order_category, activity_type, uid');
        if (!$order) {
            return app('json')->fail('订单不存在');
        }
        $order = $order->toArray();
        if ((int)$order['refund_status'] === 2) {
            return app('json')->fail('该订单已退款完成');
        }
        if (($order['product_type'] ?? 'normal') !== 'normal') {
            return app('json')->fail('该订单不是实物商品订单');
        }
        /** @var AfterSaleServices $afterSaleServices */
        $afterSaleServices = app(AfterSaleServices::class);
        $maxRefund = $afterSaleServices->getMaxRefundAmount($orderNo);
        if (bccomp($maxRefund, '0.00', 2) <= 0) {
            return app('json')->fail('当前订单暂无可退金额');
        }
        return app('json')->success([
            'order_no'      => $order['order_no'],
            'pay_price'     => $order['pay_price'],
            'refund_price'  => $order['refund_price'] ?? '0.00',
            'max_refund'    => $maxRefund,
            'pay_type'      => $order['pay_type'],
            'status_code'   => $order['status_code'],
        ]);
    }

    /**
     * 执行退款（后台主动退款）
     * 使用 AfterSaleServices 走售后退款流程
     */
    public function processRefund(OrderCenterServices $centerServices)
    {
        $orderNo = $this->request->param('order_no', '');
        $refundPrice = (float)$this->request->param('refund_price', 0);

        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }
        if ($refundPrice <= 0) {
            return app('json')->fail('请输入退款金额');
        }

        try {
            $order = $centerServices->getOne(['order_no' => $orderNo],
                'order_no, pay_price, refund_price, refund_status, pay_status, status_code, product_type, order_category, activity_type, uid');
            if (!$order) {
                return app('json')->fail('订单不存在');
            }
            $order = $order->toArray();

            // 校验是否可退款
            if ((int)$order['refund_status'] === 2) {
                return app('json')->fail('该订单已退款完成');
            }
            if (($order['product_type'] ?? 'normal') !== 'normal') {
                return app('json')->fail('该订单不是实物商品订单');
            }
            /** @var AfterSaleServices $afterSaleServices */
            $afterSaleServices = app(AfterSaleServices::class);
            $maxRefund = $afterSaleServices->getMaxRefundAmount($orderNo);
            if (bccomp((string)$refundPrice, $maxRefund, 2) > 0) {
                return app('json')->fail('退款金额不能超过可退金额（¥' . $maxRefund . '）');
            }

            // 后台退款也走统一售后状态机，确保售后记录、库存、订单状态和支付退款保持一致。
            $afterSale = $afterSaleServices->apply([
                'order_no'    => $orderNo,
                'uid'         => (int)$order['uid'],
                'type'        => 'refund_only',
                'refund_price'=> $refundPrice,
                'reason'      => '后台主动退款',
                'evidence_images' => [],
            ]);
            $afterSaleId = (int)($afterSale['id'] ?? 0);
            if ($afterSaleId <= 0) {
                throw new \RuntimeException('售后单创建失败');
            }
            $afterSaleServices->audit($afterSaleId, 'passed', (int)($this->adminId ?? 0), '', '后台主动退款');
            $refund = $afterSaleServices->refund($afterSaleId, $refundPrice, [
                'refund_type' => 'original',
                'admin_id'    => (int)($this->adminId ?? 0),
            ]);
            $state = (string)($refund['state'] ?? '');
            $message = $state === \app\model\order\RefundOrder::STATE_COMPLETED
                ? '退款成功'
                : ($state === \app\model\order\RefundOrder::STATE_MANUAL_REVIEW ? '退款已转人工复核' : '退款已受理，正在处理中');
            return app('json')->success($message, [
                'after_sale_no' => $afterSale['after_sale_no'] ?? '',
                'refund_no'     => $refund['refund_no'] ?? '',
                'state'         => $state,
            ]);
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] processRefund failed: ' . $e->getMessage());
            return app('json')->fail('退款失败：' . $e->getMessage());
        }
    }

    /**
     * 修改订单备注
     */
    public function remark(OrderCenterServices $centerServices)
    {
        $id = (int)$this->request->param('id', 0);
        $remark = $this->request->param('remark', '');
        if ($id <= 0) {
            return app('json')->fail('参数错误');
        }
        if (empty($remark)) {
            return app('json')->fail('备注不能为空');
        }
        try {
            // 获取订单信息，用于记录日志
            $order = $centerServices->getOne(['id' => $id], 'order_no, uid, status_code');
            if (!$order) {
                return app('json')->fail('订单不存在');
            }
            $currentStatus = $order['status_code'] ?? '';

            $centerServices->updateRemark($id, $remark);

            // 写入订单记录
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $order['order_no'],
                'uid'           => (int)($order['uid'] ?? 0),
                'operator_id'   => (int)($this->adminInfo['id'] ?? 0),
                'operator_type' => 'admin',
                'from_status'   => $currentStatus,
                'to_status'     => $currentStatus,
                'content'       => '管理员修改订单备注：' . $remark,
                'add_time'      => time(),
            ]);

            return app('json')->success('备注成功');
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] remark failed: ' . $e->getMessage());
            return app('json')->fail('备注失败');
        }
    }

    /**
     * 删除订单（软删除）
     * 通过 OrderServices 统一软删除中台表 + 扩展表
     * 限制：仅允许删除未支付或已取消的订单
     */
    public function delete(int $id, OrderCenterServices $centerServices, OrderServices $orderServices)
    {
        if ($id <= 0) {
            return app('json')->fail('参数错误');
        }

        try {
            // softDelete → 中台 delete：仅允许删除用户已删的订单，写 is_system_del 隐藏后台
            $orderServices->softDelete($id);
            return app('json')->success('删除成功');
        } catch (ApiException $e) {
            return app('json')->fail($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] delete failed: ' . $e->getMessage());
            return app('json')->fail('删除失败');
        }
    }

    /**
     * 修改收货地址
     * 通过 OrderServices 统一更新扩展表 + 中台表 extra_info.contact
     * 限制：已发货订单锁定，不允许修改地址
     */
    public function editAddress(OrderCenterServices $centerServices, OrderServices $orderServices)
    {
        $orderNo = $this->request->param('order_no', '');
        $realName = $this->request->param('real_name', '');
        $phone = $this->request->param('user_phone', '');
        $address = $this->request->param('user_address', '');

        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }

        // 校验订单是否已发货
        $order = $centerServices->getOne(['order_no' => $orderNo], 'status_code, delivery_time, pay_status, extra_info');
        if (!$order) {
            return app('json')->fail('订单不存在');
        }
        $orderData = $order->toArray();
        if (!empty($orderData['delivery_time']) && (int)$orderData['delivery_time'] > 0) {
            return app('json')->fail('该订单已发货，地址已锁定，无法修改。如需修改请先联系物流拦截');
        }

        if (empty($realName)) {
            return app('json')->fail('请填写收货人姓名');
        }
        if (empty($phone)) {
            return app('json')->fail('请填写收货人电话');
        }
        if (empty($address)) {
            return app('json')->fail('请填写收货人地址');
        }

        try {
            // 通过 OrderServices 统一更新扩展表 + 中台表
            $orderServices->updateAddress($orderNo, $realName, $phone, $address);
            return app('json')->success('修改地址成功');
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] editAddress failed: ' . $e->getMessage());
            return app('json')->fail('修改地址失败');
        }
    }

    /**
     * 获取订单操作记录
     * 从 eb_order_log 表获取订单操作日志
     */
    public function getRecordList(OrderCenterServices $centerServices)
    {
        $orderNo = $this->request->param('order_no', '');
        $id = (int)$this->request->param('id', 0);

        if (empty($orderNo) && $id <= 0) {
            return app('json')->fail('参数错误');
        }

        try {
            // 如果没有订单号，通过 ID 查询
            if (empty($orderNo) && $id > 0) {
                $order = $centerServices->get($id, ['order_no']);
                if ($order) {
                    $orderNo = $order['order_no'];
                }
            }
            if (empty($orderNo)) {
                return app('json')->success([]);
            }

            $logDao = app(\app\dao\order\OrderLogDao::class);
            $list = $logDao->getLogList(['order_no' => $orderNo]);
            // 格式化字段：映射 eb_order_log 字段到前端所需格式
            $typeMap = [
                'pending_payment' => 'create',
                'processing'      => 'pay',
                'pending_review'  => 'confirm',
                'completed'       => 'confirm',
                'canceled'        => 'cancel',
                'after_sale'      => 'refund',
                'refunding'       => 'refund',
                'refunded'        => 'refund',
            ];
            foreach ($list as &$item) {
                $item['change_time'] = $item['add_time']
                    ? date('Y-m-d H:i:s', $item['add_time'])
                    : '--';
                $content = (string)($item['content'] ?? '');
                // 根据状态和文案推导操作类型，避免“已收货 / 待安装”这类日志被误标成支付
                $changeType = $typeMap[$item['to_status'] ?? ''] ?? ($item['action'] ?? '');
                if ($content !== '') {
                    if (strpos($content, '已收货') !== false || strpos($content, '待安装') !== false || strpos($content, '待评价') !== false) {
                        $changeType = 'confirm';
                    } elseif (strpos($content, '发货') !== false) {
                        $changeType = 'delivery';
                    }
                }
                $item['change_type'] = $changeType;
                $item['change_message'] = $content !== '' ? $content : '--';
            }
            return app('json')->success($list);
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] getRecordList failed: ' . $e->getMessage());
            return app('json')->success([]);
        }
    }

    /**
     * 小票打印数据
     * 返回收银小票数据（含商品明细、实付金额、商家信息），前端通过 window.print 打印
     */
    public function printReceipt(OrderCenterServices $centerServices)
    {
        $orderNo = $this->request->param('order_no', '');
        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }
        try {
            // 获取订单完整详情
            $detail = $centerServices->getDetailByOrderNo($orderNo);
            // 获取商家信息
            $storeName = sys_config('store_name', 'CRMEB商城');
            $storeLogo = sys_config('store_logo', '');
            $storePhone = sys_config('store_phone', '');
            $storeAddress = sys_config('store_address', '');
            // 获取订单商品明细
            $items = $detail['items'] ?? [];
            $printItems = [];
            foreach ($items as $item) {
                $printItems[] = [
                    'name'     => $item['store_name'] ?? $item['title'] ?? '',
                    'sku'      => $item['sku'] ?? '',
                    'price'    => $item['true_price'] ?? $item['price'] ?? '0.00',
                    'quantity' => $item['cart_num'] ?? 1,
                    'subtotal' => bcmul((string)($item['true_price'] ?? $item['price'] ?? '0'), (string)($item['cart_num'] ?? 1), 2),
                ];
            }
            $data = [
                'order_no'      => $detail['order_no'],
                'pay_time'      => $detail['pay_time'] ?? 0,
                'pay_price'     => $detail['pay_price'] ?? '0.00',
                'total_price'   => $detail['total_price'] ?? '0.00',
                'pay_postage'   => $detail['pay_postage'] ?? '0.00',
                'coupon_price'  => $detail['coupon_price'] ?? '0.00',
                'deduction_price' => $detail['deduction_price'] ?? '0.00',
                'contact_phone' => $detail['contact']['phone'] ?? $detail['user_phone'] ?? '',
                'contact_name'  => $detail['contact']['name'] ?? $detail['real_name'] ?? '',
                'items'         => $printItems,
                // 商家信息
                'store'         => [
                    'name'    => $storeName,
                    'logo'    => $storeLogo,
                    'phone'   => $storePhone,
                    'address' => $storeAddress,
                ],
            ];
            return app('json')->success($data);
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] printReceipt failed: ' . $e->getMessage());
            return app('json')->fail('获取小票数据失败');
        }
    }

    /**
     * 配货单打印数据
     * 仅返回商品名称、规格、数量（隐藏价格和支付信息），供仓库拣货使用
     */
    public function printPickList(OrderCenterServices $centerServices)
    {
        $orderNo = $this->request->param('order_no', '');
        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }
        try {
            $detail = $centerServices->getDetailByOrderNo($orderNo);
            $items = $detail['items'] ?? [];
            $pickItems = [];
            foreach ($items as $item) {
                $pickItems[] = [
                    'name'     => $item['store_name'] ?? $item['title'] ?? '',
                    'sku'      => $item['sku'] ?? '',
                    'quantity' => $item['cart_num'] ?? 1,
                ];
            }
            $data = [
                'order_no'    => $detail['order_no'],
                'items'       => $pickItems,
                'remark'      => $detail['remark'] ?? '',
            ];
            return app('json')->success($data);
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] printPickList failed: ' . $e->getMessage());
            return app('json')->fail('获取配货单数据失败');
        }
    }

    /**
     * 获取快递公司列表
     */
    public function getExpressList()
    {
        try {
            $expressServices = app(\app\services\shipping\ExpressServices::class);
            $list = $expressServices->express(['is_show' => 1]);
            return app('json')->success($list);
        } catch (\Throwable $e) {
            return app('json')->fail('获取快递公司列表失败');
        }
    }

    /**
     * 确认付款（线下支付）
     * 通过 OrderServices 统一更新扩展表 + 中台表状态
     */
    public function confirmPay(OrderCenterServices $centerServices, OrderServices $orderServices)
    {
        $orderNo = $this->request->param('order_no', '');
        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }

        $order = $centerServices->getOne(['order_no' => $orderNo], 'status_code, pay_status, pay_type');
        if (!$order) {
            return app('json')->fail('订单不存在');
        }
        $orderData = $order->toArray();

        if ($orderData['pay_status'] == 1) {
            return app('json')->fail('订单已支付，无需重复确认');
        }
        if ($orderData['pay_type'] !== 'offline') {
            return app('json')->fail('仅支持确认线下支付订单');
        }

        try {
            $orderServices->confirmOfflinePay($orderNo);
            return app('json')->success('确认付款成功');
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] confirmPay failed: ' . $e->getMessage());
            return app('json')->fail('确认付款失败');
        }
    }

    /**
     * 批量删除订单
     * 通过 OrderServices 统一软删除中台表 + 扩展表
     * 仅允许删除未支付或已取消的订单
     */
    public function batchDelete(OrderCenterServices $centerServices, OrderServices $orderServices)
    {
        $ids = $this->request->param('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return app('json')->fail('请选择要删除的订单');
        }

        $success = 0;
        $fail = 0;
        $errors = [];

        foreach ($ids as $id) {
            try {
                // 与单笔删除一致：由中台守卫校验「用户已删」，写 is_system_del
                $orderServices->softDelete((int)$id);
                $success++;
            } catch (\Throwable $e) {
                $fail++;
                $errors[] = "订单ID {$id} 删除失败：" . $e->getMessage();
            }
        }

        $msg = "批量删除完成：成功{$success}条，失败{$fail}条";
        return app('json')->success($msg, ['success' => $success, 'fail' => $fail, 'errors' => $errors]);
    }

    /**
     * 订单导出
     * 导出当前筛选条件下的订单列表（Excel格式）
     */
    public function export(OrderCenterServices $centerServices)
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 200],
            ['order_no', ''],
            ['status_code', ''],
            ['keyword', ''],
            ['search_field', ''],
            ['date_range', ''],
            ['pay_type', ''],
            ['type', ''],
            ['is_deleted', ''],
        ]);
        $where['order_category'] = 'goods';
        $where['product_type'] = 'normal';

        try {
            $result = $centerServices->getAdminList($where, (int)$where['page'], (int)$where['limit']);
            $list = $result['list'] ?? [];

            // 构建导出数据结构
            $header = ['订单号', '商品名称', '用户信息', '支付方式', '订单金额', '实际支付', '订单状态', '创建时间', '收货人', '收货电话', '收货地址'];
            $fileKey = ['order_no', 'store_name', 'user_info', 'pay_type', 'total_price', 'pay_price', 'status_name', 'add_time', 'real_name', 'user_phone', 'user_address'];
            $filename = '实物商品订单导出_' . date('YmdHis');

            // 从 eb_pay_channel 表动态读取支付方式名称，新增支付方式后自动生效
            $payTypeMap = PayServices::getPayType();
            $statusMap = OrderTypeAdapterInterface::STATUS_MAP;

            $exportData = [];
            foreach ($list as $item) {
                $items = $item['items'] ?? [];
                $storeName = '';
                if (!empty($items)) {
                    $first = $items[0];
                    $storeName = $first['store_name'] ?? $first['title'] ?? '';
                }

                $exportData[] = [
                    'order_no'    => $item['order_no'] ?? '',
                    'store_name'  => $storeName,
                    'user_info'   => ($item['contact_name'] ?? $item['real_name'] ?? '') . ' / ' . ($item['contact_phone'] ?? $item['user_phone'] ?? ''),
                    'pay_type'    => $payTypeMap[$item['pay_type'] ?? ''] ?? ($item['pay_type'] ?? ''),
                    'total_price' => $item['total_price'] ?? '0.00',
                    'pay_price'   => $item['pay_price'] ?? '0.00',
                    'status_name' => $statusMap[$item['status_code'] ?? ''] ?? ($item['status_code'] ?? ''),
                    'add_time'    => $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '',
                    'real_name'   => $item['contact_name'] ?? $item['real_name'] ?? '',
                    'user_phone'  => $item['contact_phone'] ?? $item['user_phone'] ?? '',
                    'user_address'=> $item['user_address'] ?? '',
                ];
            }

            return app('json')->success([
                'header'   => $header,
                'fileKey'  => $fileKey,
                'filename' => $filename,
                'export'   => $exportData,
            ]);
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] export failed: ' . $e->getMessage());
            return app('json')->fail('导出失败');
        }
    }

    /**
     * 保存积分（赠送积分 / 退回积分）
     * 通过 OrderServices 更新中台表 extra_info，并触发用户积分变动
     */
    public function updateIntegral(OrderCenterServices $centerServices, OrderServices $orderServices)
    {
        $orderNo = $this->request->param('order_no', '');
        $giveIntegral = (int)$this->request->param('give_integral', 0);
        $backIntegral = (int)$this->request->param('back_integral', 0);

        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }
        if ($giveIntegral < 0 || $backIntegral < 0) {
            return app('json')->fail('积分不能为负数');
        }

        try {
            $order = $centerServices->getOne(['order_no' => $orderNo], 'uid, extra_info, status_code');
            if (!$order) {
                return app('json')->fail('订单不存在');
            }
            $orderData = $order->toArray();
            $currentStatus = $orderData['status_code'] ?? '';

            // 通过 OrderServices 更新中台表赠送积分
            $orderServices->updateIntegral($orderNo, $giveIntegral);

            $contentParts = [];

            // 赠送积分给用户
            if ($giveIntegral > 0) {
                /** @var \app\services\user\UserBillServices $billServices */
                $billServices = app(\app\services\user\UserBillServices::class);
                $billServices->billIntegralIncome('pay_give_integral', (int)$orderData['uid'], $giveIntegral, 0, '管理员后台赠送积分：' . $giveIntegral);
                $contentParts[] = '赠送积分：' . $giveIntegral;
            }

            // 退回积分（退还用户下单使用的积分）
            if ($backIntegral > 0) {
                /** @var \app\services\user\UserBillServices $billServices */
                $billServices = app(\app\services\user\UserBillServices::class);
                $billServices->billIntegralIncome('pay_product_integral_back', (int)$orderData['uid'], $backIntegral, 0, '管理员后台退回下单使用积分：' . $backIntegral);
                $contentParts[] = '退回下单使用积分：' . $backIntegral;
            }

            // 写入订单记录
            if (!empty($contentParts)) {
                /** @var OrderLogDao $logDao */
                $logDao = app()->make(OrderLogDao::class);
                $logDao->save([
                    'order_no'      => $orderNo,
                    'uid'           => (int)($orderData['uid'] ?? 0),
                    'operator_id'   => (int)($this->adminInfo['id'] ?? 0),
                    'operator_type' => 'admin',
                    'from_status'   => $currentStatus,
                    'to_status'     => $currentStatus,
                    'content'       => '管理员积分操作：' . implode('；', $contentParts),
                    'add_time'      => time(),
                ]);
            }

            return app('json')->success('积分更新成功');
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] updateIntegral failed: ' . $e->getMessage());
            return app('json')->fail('积分更新失败');
        }
    }

    /**
     * 修改已发货订单的配送信息
     * 仅允许修改快递公司/快递单号，不改订单状态
     * 通过 OrderServices 统一更新扩展表 + 中台表
     */
    public function updateDistribution(OrderCenterServices $centerServices, OrderServices $orderServices)
    {
        $orderNo = $this->request->param('order_no', '');
        $deliveryName = $this->request->param('delivery_name', '');
        $deliveryId = $this->request->param('delivery_id', '');
        $deliveryCode = $this->request->param('delivery_code', '');

        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }

        // 校验订单是否已发货
        $order = $centerServices->getOne(['order_no' => $orderNo], 'status_code, delivery_time');
        if (!$order) {
            return app('json')->fail('订单不存在');
        }
        if (empty($order['delivery_time']) || (int)$order['delivery_time'] <= 0) {
            return app('json')->fail('该订单尚未发货，无法修改配送信息');
        }

        if (empty($deliveryName)) {
            return app('json')->fail('请填写配送公司/人员名称');
        }
        if (empty($deliveryId)) {
            return app('json')->fail('请填写快递单号/联系方式');
        }

        try {
            // 通过 OrderServices 统一更新扩展表 + 中台表
            $orderServices->updateDistribution($orderNo, $deliveryName, $deliveryId, $deliveryCode);

            // 写入订单记录
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $orderNo,
                'uid'           => (int)($order['uid'] ?? 0),
                'operator_id'   => (int)($this->adminInfo['id'] ?? 0),
                'operator_type' => 'admin',
                'from_status'   => $order['status_code'] ?? '',
                'to_status'     => $order['status_code'] ?? '',
                'content'       => '管理员修改配送信息：' . $deliveryName . ' / ' . $deliveryId,
                'add_time'      => time(),
            ]);

            return app('json')->success('修改配送信息成功');
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] updateDistribution failed: ' . $e->getMessage());
            return app('json')->fail('修改配送信息失败');
        }
    }

    /**
     * 拆单发货（中台化）
     * 将订单中部分商品拆出为独立子订单并单独发货（OrderServices::splitOrderDelivery）
     * 基于 extra_info.items 快照整行拆分，金额按比例分摊
     */
    public function splitDelivery(OrderCenterServices $centerServices, OrderServices $orderServices)
    {
        $orderNo = $this->request->param('order_no', '');
        $deliveryType = $this->request->param('delivery_type', 'express');
        $cartIds = $this->request->param('cart_ids', []);

        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }
        if (empty($cartIds) || !is_array($cartIds)) {
            return app('json')->fail('请选择要拆单发货的商品');
        }

        // 校验 cart_ids 格式
        foreach ($cartIds as &$cart) {
            if (!isset($cart['cart_id']) || !$cart['cart_id'] || !isset($cart['cart_num']) || !$cart['cart_num']) {
                return app('json')->fail('请重新选择发货商品，或填写发货件数');
            }
            $cart['cart_id'] = (int)$cart['cart_id'];
            $cart['cart_num'] = (int)$cart['cart_num'];
        }

        $centerOrder = $centerServices->getOne(['order_no' => $orderNo], 'id, uid, status_code');
        if (!$centerOrder) {
            return app('json')->fail('订单不存在');
        }

        try {
            // 构建发货数据
            $deliveryData = [
                'delivery_type' => $deliveryType,
            ];

            switch ($deliveryType) {
                case 'express':
                    $deliveryData['delivery_name'] = $this->request->param('delivery_name', '');
                    $deliveryData['delivery_id'] = $this->request->param('delivery_id', '');
                    if (empty($deliveryData['delivery_name'])) {
                        return app('json')->fail('请选择快递公司');
                    }
                    if (empty($deliveryData['delivery_id'])) {
                        return app('json')->fail('请填写快递单号');
                    }
                    $deliveryName = $deliveryData['delivery_name'];
                    break;
                case 'send':
                    $deliveryData['delivery_name'] = $this->request->param('sh_delivery_name', '');
                    $deliveryData['delivery_id'] = $this->request->param('sh_delivery_id', '');
                    if (empty($deliveryData['delivery_name'])) {
                        return app('json')->fail('请选择送货人');
                    }
                    if (empty($deliveryData['delivery_id'])) {
                        return app('json')->fail('请输入送货人电话');
                    }
                    $deliveryName = $deliveryData['delivery_name'];
                    break;
                case 'fictitious':
                    $deliveryData['delivery_name'] = '虚拟发货';
                    $deliveryData['delivery_id'] = $this->request->param('fictitious_content', '');
                    $deliveryName = '虚拟发货';
                    break;
                default:
                    return app('json')->fail('不支持的发货类型');
            }

            // 中台拆单发货：拆出子订单并立即发货（模块内自治实现）
            [$childOrderNo] = $orderServices->splitOrderDelivery($orderNo, $cartIds, $deliveryData);

            // 写入订单记录
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $orderNo,
                'uid'           => (int)($centerOrder['uid'] ?? 0),
                'operator_id'   => (int)($this->adminInfo['id'] ?? 0),
                'operator_type' => 'admin',
                'from_status'   => $centerOrder['status_code'] ?? '',
                'to_status'     => $centerOrder['status_code'] ?? '',
                'content'       => '管理员拆单发货：' . $deliveryName . '，子订单：' . $childOrderNo,
                'add_time'      => time(),
            ]);

            return app('json')->success(['child_order_no' => $childOrderNo], '拆单发货成功');
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] splitDelivery failed: ' . $e->getMessage());
            return app('json')->fail('拆单发货失败：' . $e->getMessage());
        }
    }

    /**
     * 物流查询
     * 根据订单号查询物流轨迹
     */
    public function getExpress(OrderCenterServices $centerServices, ExpressServices $expressServices)
    {
        $orderNo = $this->request->param('order_no', '');
        if (empty($orderNo)) {
            return app('json')->fail('参数错误');
        }

        try {
            $detail = $centerServices->getDetailByOrderNo($orderNo);
            if (empty($detail)) {
                return app('json')->fail('订单不存在');
            }

            $deliveryName = $detail['delivery_name'] ?? '';
            $deliveryId = $detail['delivery_id'] ?? '';
            $deliveryType = $detail['delivery_type'] ?? '';
            if ($deliveryType !== 'express' || empty($deliveryId)) {
                return app('json')->fail('快递单号不存在');
            }

            // 根据 delivery_name 查找快递公司 code
            $expressCode = '';
            $expressList = $expressServices->expressList(['is_show' => 1]);
            foreach ($expressList as $exp) {
                if ($exp['name'] === $deliveryName) {
                    $expressCode = $exp['code'];
                    break;
                }
            }

            $cacheName = $orderNo . '_' . $deliveryId;
            $phone = $detail['contact']['phone'] ?? $detail['user_phone'] ?? '';

            $data = [
                'delivery_name' => $deliveryName,
                'delivery_id'   => $deliveryId,
                'result'        => $expressServices->query($cacheName, $deliveryId, $expressCode ?: null, $phone),
            ];
            return app('json')->success($data);
        } catch (\Throwable $e) {
            Log::error('[NormalOrder] getExpress failed: ' . $e->getMessage());
            return app('json')->fail('物流查询失败：' . $e->getMessage());
        }
    }
}
