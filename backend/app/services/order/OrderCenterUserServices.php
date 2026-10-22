<?php
declare(strict_types=1);

namespace app\services\order;

use app\dao\order\AfterSaleDao;
use app\model\order\OrderCenter;
use app\services\pay\PayRecordServices;
use app\services\order\StoreCartServices;
use app\services\user\UserServices;
use app\services\user\UserAddressServices;
use app\services\system\store\SystemStoreServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use crmeb\trade\order\type\OrderTypeRegistry;
use crmeb\trade\product\ProductTypeRegistry;
use crmeb\trade\mode\TradeModeRegistry;
use think\facade\Log;

/**
 * 订单中台用户端服务层
 * 提供用户端专用的订单管理功能：列表查询、统计、取消、删除、退款、礼物等
 */
class OrderCenterUserServices extends OrderCenterServices
{
    /**
     * 查询当前用户可访问的订单中台记录 ID
     *
     * 可访问范围：
     * 1. 订单归属人（uid 匹配）；
     * 2. 送礼订单的领取人（gift_uid 匹配）——领取礼物后需查看物流、核销与领取进度。
     *
     * @param string $orderNo 订单号
     * @param int $uid 当前用户 ID
     * @return int|null 无权访问或订单不存在时返回 null
     */
    public function getOwnedOrderIdByNo(string $orderNo, int $uid): ?int
    {
        $order = $this->dao->get(['order_no' => $orderNo], ['id', 'uid', 'gift_uid', 'extra_info']);
        if (!$order) {
            return null;
        }
        $order = is_array($order) ? $order : $order->toArray();
        if ((int)($order['uid'] ?? 0) === $uid) {
            return (int)$order['id'];
        }
        // 送礼订单：领取人可查看该订单（优先实体列，历史数据回退 extra_info）
        $extra = $this->parseExtraInfo($order['extra_info'] ?? null);
        $giftUid = (int)($order['gift_uid'] ?? $extra['gift']['gift_uid'] ?? ($extra['gift_uid'] ?? 0));
        return $giftUid > 0 && $giftUid === $uid ? (int)$order['id'] : null;
    }

    /**
     * 送礼订单详情按查看者身份裁剪渲染配置
     *
     * - 购买者：隐藏收货信息、物流轨迹，以及确认收货/申请退款/查看物流按钮；
     * - 领取人：只读视角，保留收货信息、物流轨迹与确认收货/查看物流按钮，
     *   隐藏金额/支付/发票等下单人信息。
     * 非送礼订单不做任何处理。
     *
     * @param array $result getDetailWithConfig 返回的 page_config 结构
     * @param int $viewerUid 查看者用户 ID
     * @return array 裁剪后的结构
     */
    public function applyGiftViewerPolicy(array $result, int $viewerUid): array
    {
        $sections = $result['page_config']['sections'] ?? [];
        if (empty($sections)) {
            return $result;
        }

        // 从订单信息 section 读取送礼标识、下单人与领取人
        $fields = [];
        foreach ($sections as $section) {
            if (($section['key'] ?? '') === 'order_info') {
                $fields = $section['props']['fields'] ?? [];
                break;
            }
        }
        if ((int)($fields['is_gift'] ?? 0) !== 1) {
            return $result;
        }

        $ownerUid = (int)($fields['uid'] ?? 0);
        $giftUid = (int)($fields['gift_uid'] ?? 0);
        $isReceiver = $ownerUid !== $viewerUid && $giftUid === $viewerUid;

        if ($isReceiver) {
            // 领取人：隐藏金额/支付/发票与下单人权益入口，保留收货信息、物流轨迹
            $sections = array_values(array_filter(
                $sections,
                fn($section) => !in_array($section['key'] ?? '', ['pricing', 'payment', 'invoice', 'coupon_gift', 'points_reward'], true)
            ));
            $result['page_config']['extensions']['viewer_role'] = 'gift_receiver';
            $visibleActions = ['confirmReceive', 'viewLogistics', 'goComment'];
            $hiddenActions = [];
        } else {
            // 购买者：不展示收货信息与物流轨迹，隐藏确认收货/申请退款/查看物流
            $sections = array_values(array_filter(
                $sections,
                fn($section) => !in_array($section['key'] ?? '', ['delivery', 'logistics'], true)
            ));
            $visibleActions = [];
            $hiddenActions = ['confirmReceive', 'applyRefund', 'viewLogistics'];
        }

        // 按查看者身份处理礼物信息与底部操作按钮
        foreach ($sections as &$section) {
            $sectionKey = (string)($section['key'] ?? '');
            // 礼物信息：送礼人展示领取人，领取人展示赠送人
            if ($sectionKey === 'gift') {
                $gift = $section['props']['gift'] ?? [];
                if ($isReceiver) {
                    $section['props']['role'] = 'receiver';
                    $section['props']['display'] = $gift['sender'] ?? null;
                } else {
                    $section['props']['role'] = 'sender';
                    $section['props']['display'] = $gift['receiver'] ?? null;
                }
                continue;
            }
            if ($sectionKey !== 'buttons') {
                continue;
            }
            $section['props']['buttons'] = array_values(array_filter(
                $section['props']['buttons'] ?? [],
                function ($button) use ($isReceiver, $visibleActions, $hiddenActions) {
                    $action = (string)($button['action'] ?? '');
                    return $isReceiver
                        ? in_array($action, $visibleActions, true)
                        : !in_array($action, $hiddenActions, true);
                }
            ));
            $section['visible'] = !empty($section['props']['buttons']);
        }
        unset($section);

        $result['page_config']['sections'] = $sections;
        return $result;
    }

    /**
     * 获取用户订单列表
     * 仅返回用户端所需字段，支持按订单大类和状态筛选
     * @param int $uid 用户ID
     * @param array $where 查询条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array 返回分页数据（total、list）
     */
    public function getList(int $uid, array $where, int $page, int $limit): array
    {
        $searchWhere = [
            ['is_del', '=', 0],
        ];
        // 订单大类筛选
        if (!empty($where['order_category'])) {
            $searchWhere[] = ['order_category', '=', $where['order_category']];
        }
        // 订单状态筛选
        if (!empty($where['status_code'])) {
            $statusCodes = explode(',', $where['status_code']);
            if (count($statusCodes) > 1) {
                $searchWhere[] = ['status_code', 'IN', $statusCodes];
            } else {
                $searchWhere[] = ['status_code', '=', $where['status_code']];
            }
        }
        // 产品类型筛选
        if (!empty($where['product_type'])) {
            $searchWhere[] = ['product_type', '=', $where['product_type']];
        }
        // 产品类型反向筛选（待使用：非实物业态，product_type <> normal）
        // 注意：SQL 中 NULL <> 'normal' 结果为 NULL 不会被选中，故 NULL 行天然排除
        if (!empty($where['product_type_ne'])) {
            $searchWhere[] = ['product_type', '<>', $where['product_type_ne']];
        }
        // 发货状态筛选（delivery_time int 默认 0，0=未发货）：待发货=未发货，待收货=已发货未收货
        if (isset($where['delivery_time'])) {
            $searchWhere[] = ['delivery_time', '=', (int)$where['delivery_time']];
        }
        if (array_key_exists('delivery_time_ne', $where)) {
            $searchWhere[] = ['delivery_time', '<>', (int)$where['delivery_time_ne']];
        }
        if (isset($where['take_time'])) {
            $searchWhere[] = ['take_time', '=', (int)$where['take_time']];
        }

        // 列表范围：本人下单 + 本人领取的礼品订单（userOrderQuery 内按 uid/gift_uid 索引查询）
        $list = $this->dao->userOrderQuery($uid)
            ->where($searchWhere)
            ->field('uid, gift_uid, order_no, source_type, source_id, order_category, product_type, activity_type, status_code,
             total_price, pay_price, total_num, title, image,
             subtitle, pay_type, shipping_type, extra_info, detail_route,
             add_time, pay_time, delivery_time, take_time, gift_price')
            ->page($page, $limit)
            ->order('id DESC')
            ->select()
            ->toArray();
        $total = (int)$this->dao->userOrderQuery($uid)->where($searchWhere)->count();

        // 批量加载扩展表子状态，供列表页按钮和扩展文案精确判断
        $sourceStatusMap = $this->loadSourceStatusesForList($list);

        foreach ($list as &$item) {
            // status_text: 中台统一状态码的中文名，如 pending_payment→"待支付"、processing→"进行中"、completed→"已完成"、canceled→"已取消"
            $item['status_text'] = OrderCenter::$statusMap[$item['status_code']] ?? '未知';
            // extra_info: 将 JSON 字符串解析为关联数组，包含 contact（收货信息）、delivery（物流信息）、business_status（业务状态快照，如"已退款"）等
            $item['extra_info'] = $this->parseExtraInfo($item['extra_info']);
            // business_status: 从 extra_info 提取到顶层，供前端首页卡片直接判断业务状态，如 "已退款"、"已取消"、"已完成"
            $item['business_status'] = $item['extra_info']['business_status'] ?? null;
            unset($item['extra_info']['business_status']);
            // source_status: 扩展表子状态（如 eb_pt_normal_order.status），精确到具体产品类型的履约阶段。例：普通实物订单 delivery_type="pickup" 且 status=2 时表示"待核销"
            $item['source_status'] = $sourceStatusMap[$item['order_no']] ?? '';
            // extend_text: 扩展文案，展示在列表项底部。例："还剩 1 天自动确认收货"、"已发货：顺丰快递 SF1234567890"
            $item['extend_text'] = $this->buildListExtendText($item);
            // order_buttons: 当前状态下可操作按钮列表。例：待支付 → ["pay", "cancel"]；已完成未评价 → ["delete", "evaluate"]
            $item['order_buttons'] = $this->getOrderButtons($item);
            // type_label: 订单类型短标签（秒杀/积分/充值/网盘/卡密等），用于列表项角标展示
            // 优先级：活动类型 > 订单大类 > 产品类型，各维度均通过注册中心/事件动态获取，新增类型无需改前端
            $item['type_label'] = $this->resolveTypeLabel($item);
            // 送礼订单标识与礼品附加费，供列表角标与金额展示
            $itemExtra = $item['extra_info'] ?? [];
            $item['is_gift'] = (!empty($itemExtra['gift']) || isset($itemExtra['gift_mark'])) ? 1 : 0;
            $item['gift_price'] = $item['gift_price'] ?? '0.00';
            // 礼物领取人视角（非下单人）：只读列表，仅保留查看物流，避免出现退款/取消/删除等下单人操作
            $item['is_gift_receiver'] = ((int)($item['uid'] ?? 0) !== $uid && (int)($item['gift_uid'] ?? 0) === $uid) ? 1 : 0;
            if ($item['is_gift_receiver']) {
                $item['order_buttons'] = array_values(array_filter(
                    $item['order_buttons'] ?? [],
                    fn($button) => in_array($button['action'] ?? '', ['confirmReceive', 'viewLogistics', 'goComment'], true)
                ));
            }
        }

        // 为 viewAfterSale 按钮批量注入 after_sale_no（与详情接口 getOrderButtonsFromHandler 同口径）
        // 前端 viewAfterSale action 依赖 params.after_sale_no 才能跳转到售后详情页
        $this->injectAfterSaleNoForList($list);

        $eventData = ['list' => &$list];
        // 触发事件，允许第三方扩展订单列表
        event('OrderCenterListExtend', $eventData);
        $list = $eventData['list'];

        return compact('total', 'list');
    }

    /**
     * 为列表项 viewAfterSale 按钮批量注入 after_sale_no
     * 取订单最新一条售后单（不限状态），与详情接口 getOrderButtonsFromHandler 同口径；
     * 仅含该按钮的订单才参与查询，按订单号一次批量取回，避免逐行点查
     * @param array $list 订单列表（引用传递，原地补齐按钮参数）
     */
    protected function injectAfterSaleNoForList(array &$list): void
    {
        $orderNos = [];
        foreach ($list as $item) {
            foreach ($item['order_buttons'] ?? [] as $button) {
                if (($button['action'] ?? '') === 'viewAfterSale') {
                    $orderNos[(string)($item['order_no'] ?? '')] = true;
                    break;
                }
            }
        }
        $orderNos = array_filter(array_keys($orderNos));
        if (empty($orderNos)) {
            return;
        }

        try {
            $map = $this->app->make(AfterSaleDao::class)->getLatestNoMapByOrderNos($orderNos);
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] injectAfterSaleNoForList failed: ' . $e->getMessage());
            return;
        }

        foreach ($list as &$item) {
            $afterSaleNo = $map[(string)($item['order_no'] ?? '')] ?? '';
            if ($afterSaleNo === '') {
                continue;
            }
            foreach ($item['order_buttons'] as &$button) {
                if (($button['action'] ?? '') === 'viewAfterSale') {
                    $button['params'] = array_merge($button['params'] ?? [], ['after_sale_no' => $afterSaleNo]);
                }
            }
            unset($button);
        }
        unset($item);
    }

    /**
     * 批量加载列表项的扩展表子状态
     * 按 source_type 分组后一次性查询各扩展表，避免 N+1
     * @param array $list 订单列表
     * @return array 以 order_no 为键的扩展状态映射
     */
    protected function loadSourceStatusesForList(array $list): array
    {
        if (empty($list)) {
            return [];
        }

        // 按 source_type 分组收集 order_no
        $grouped = [];
        foreach ($list as $item) {
            $sourceType = $item['source_type'] ?? '';
            // source_type 为空的历史脏数据跳过，不查扩展表
            if (!$sourceType) {
                continue;
            }
            $grouped[$sourceType][] = $item['order_no'];
        }

        $statusMap = [];
        foreach ($grouped as $sourceType => $orderNos) {
            try {
                $adapter = OrderTypeRegistry::getAdapter($sourceType);
                $modelClass = $adapter::getModelClass();
                $records = app()->make($modelClass)->db()
                    ->whereIn('order_no', array_unique($orderNos))
                    ->column('status', 'order_no');
                foreach ($records as $orderNo => $status) {
                    $statusMap[$orderNo] = $status;
                }
            } catch (\Throwable $e) {
                \think\facade\Log::warning('[OrderCenter] 批量加载扩展状态失败: ' . $e->getMessage());
            }
        }

        return $statusMap;
    }

    /**
     * 构建列表页订单扩展信息文案
     * 根据订单大类、产品类型、活动类型等业务标识生成简短说明
     * @param array $order 订单数据
     * @return string 返回扩展信息文案，无则返回空字符串
     */
    protected function buildListExtendText(array $order): string
    {
        // 优先使用业务状态详情
        $businessStatus = $order['business_status'] ?? [];
        if (!empty($businessStatus['detail']) && is_string($businessStatus['detail'])) {
            return $businessStatus['detail'];
        }

        $activityType = $order['activity_type'] ?? '';
        $orderCategory = $order['order_category'] ?? '';

        // 活动类型文案：从 TradeModeRegistry 动态获取，扩展模块注册即生效
        // 'normal' 是默认非活动模式，不显示活动标签
        if ($activityType && $activityType !== 'normal') {
            /** @var TradeModeRegistry $tradeModeRegistry */
            $tradeModeRegistry = app()->make(TradeModeRegistry::class);
            if ($tradeModeRegistry->has($activityType)) {
                $handler = $tradeModeRegistry->get($activityType);
                return $handler->getLabel() . '活动订单';
            }
        }

        // 订单大类文案：从 OrderTypeRegistry 动态获取，各适配器注册即生效
        // 普通商品订单已有「实物商品」角标，不再重复展示“商品订单”占位文案
        if ($orderCategory && $orderCategory !== 'goods') {
            $categoryMap = OrderTypeRegistry::getCategoryMap();
            if (isset($categoryMap[$orderCategory])) {
                return $categoryMap[$orderCategory];
            }
        }

        return '';
    }

    /**
     * 解析订单类型短标签（列表项角标用）
     * 优先级：活动类型 > 订单大类 > 产品类型
     * - 活动类型：从 TradeModeRegistry 动态获取短标签（秒杀/积分等），扩展模块注册即生效
     * - 订单大类：触发 OrderTypeLabelQuery 事件，由各业务域模块监听返回短标签（充值/会员等），app 层不硬编码
     * - 产品类型：从 ProductTypeRegistry 动态获取类型名称（网盘/卡密等），新增产品类型注册即生效
     * 普通商品订单（normal + goods 大类 + 无活动）返回空字符串，不显示角标
     * @param array $order 订单数据
     * @return string 返回类型短标签，无则返回空字符串
     */
    protected function resolveTypeLabel(array $order): string
    {
        // 1. 活动类型：非 normal 时取交易模式短标签（秒杀/积分等）
        $activityType = $order['activity_type'] ?? '';
        if ($activityType && $activityType !== 'normal') {
            try {
                $tradeModeRegistry = app()->make(TradeModeRegistry::class);
                if ($tradeModeRegistry->has($activityType)) {
                    return $tradeModeRegistry->get($activityType)->getLabel();
                }
            } catch (\Throwable $e) {
                // 交易模式未注册时静默降级，继续尝试其他维度
            }
        }

        // 2. 订单大类：非 goods 时触发事件，由各业务域模块返回短标签（充值/会员等）
        $orderCategory = $order['order_category'] ?? '';
        if ($orderCategory && $orderCategory !== 'goods') {
            $results = event('OrderTypeLabelQuery', [
                'dimension'      => 'order_category',
                'order_category' => $orderCategory,
                'source_type'    => $order['source_type'] ?? '',
            ]);
            foreach ((array)$results as $label) {
                if (is_string($label) && $label !== '') {
                    return $label;
                }
            }
        }

        // 3. 产品类型：非 normal 时取产品类型名称（网盘/卡密等）
        $productType = $order['product_type'] ?? '';
        if ($productType) {
            try {
                $registry = app()->make(ProductTypeRegistry::class);
                if ($registry->has($productType)) {
                    return $registry->get($productType)->getTypeName();
                }
            } catch (\Throwable $e) {
                // 产品类型未注册时静默降级
            }
        }

        return '';
    }

    /**
     * 获取用户订单统计数据
     * 获取指定用户各状态订单数量和总金额，支持缓存
     * 返回前端友好的合并状态统计：待支付、待收货、待使用、待评价、退款/售后
     * 待收货/待使用由 STATUS_PROCESSING 按 product_type 拆分：normal=待收货，其他=待使用
     * @param int $uid 用户ID
     * @return array 返回统计数据（all、status_counts、grouped）
     */
    public function getUserStatistics(int $uid): array
    {
        // 缓存 key 带结构版本 v4 + 失效版本号：订单状态流转经 syncStatus/updateRecord 递增
        // order_center:stat:ver 强制换 key 刷新（结构变更升级 v4：统计范围含领取的礼品订单），旧 key 靠自身 TTL 自然过期
        $ver = (int) app()->cache->get('order_center:stat:ver');
        $cacheKey = 'order_center:user_stat:v4:' . $uid . ':' . $ver;

        return app()->cache->remember($cacheKey, function () use ($uid) {
            // 统计范围：本人下单 + 本人领取的礼品订单（与订单列表口径一致）
            // 金额只累计本人实际支付金额，收到的礼物不计入个人消费
            $baseQuery = function () use ($uid) {
                return $this->dao->userOrderQuery($uid)->where('is_del', 0);
            };
            $amountField = 'SUM(CASE WHEN uid = ' . $uid . ' THEN pay_price ELSE 0 END) as amount';

            $statusStats = $baseQuery()
                ->field('status_code, COUNT(*) as count, ' . $amountField)
                ->group('status_code')
                ->select()
                ->toArray();

            $all = $baseQuery()
                ->field('COUNT(*) as count, ' . $amountField)
                ->find();

            $statusCounts = [];
            foreach (OrderCenter::$statusMap as $code => $label) {
                $hit = array_values(array_filter($statusStats, fn($s) => $s['status_code'] === $code));
                $statusCounts[$code] = [
                    'count'  => (int) ($hit[0]['count'] ?? 0),
                    'amount' => (string) ($hit[0]['amount'] ?? '0.00'),
                ];
            }

            // STATUS_PROCESSING 按产品类型拆分：非实物业态=待使用(虚拟/卡密/酒店/门票等)
            // 实物(normal)进行中再按发货状态细分为待发货/待收货，与订单列表 tab 及角标口径一致
            $processingByType = $baseQuery()
                ->where('status_code', OrderCenter::STATUS_PROCESSING)
                ->field('product_type, COUNT(*) as count, ' . $amountField)
                ->group('product_type')
                ->select()
                ->toArray();
            $useCount = 0;
            $useAmount = '0.00';
            foreach ($processingByType as $row) {
                $type = (string)($row['product_type'] ?? '');
                $isDelivery = false;
                try {
                    $handler = app()->make(ProductTypeRegistry::class)->get($type);
                    $isDelivery = (($handler->getCapabilities()['order']['status_group'] ?? 'delivery') === 'delivery');
                } catch (\Throwable $e) {
                    $isDelivery = false;
                }
                if (!$isDelivery) {
                    $useCount += (int) ($row['count'] ?? 0);
                    $useAmount = bcadd($useAmount, (string) ($row['amount'] ?? '0.00'), 2);
                }
            }

            // 实物进行中按发货状态拆分：待发货=未发货（delivery_time=0），待收货=已发货未收货
            $pendingShip = $baseQuery()
                ->where('status_code', OrderCenter::STATUS_PROCESSING)
                ->where('product_type', 'normal')
                ->where('delivery_time', 0)
                ->field('COUNT(*) as count, ' . $amountField)
                ->find();
            $pendingReceipt = $baseQuery()
                ->where('status_code', OrderCenter::STATUS_PROCESSING)
                ->where('product_type', 'normal')
                ->where('delivery_time', '<>', 0)
                ->where('take_time', 0)
                ->field('COUNT(*) as count, ' . $amountField)
                ->find();

            $grouped = [
                'pending_payment' => [
                    'count'  => $statusCounts[OrderCenter::STATUS_PENDING_PAYMENT]['count'] ?? 0,
                    'amount' => $statusCounts[OrderCenter::STATUS_PENDING_PAYMENT]['amount'] ?? '0.00',
                ],
                'pending_delivery' => [
                    'count'  => (int) ($pendingShip['count'] ?? 0),
                    'amount' => (string) ($pendingShip['amount'] ?? '0.00'),
                ],
                'pending_receipt' => [
                    'count'  => (int) ($pendingReceipt['count'] ?? 0),
                    'amount' => (string) ($pendingReceipt['amount'] ?? '0.00'),
                ],
                'pending_use' => [
                    'count'  => $useCount,
                    'amount' => $useAmount,
                ],
                'pending_comment' => [
                    'count'  => $statusCounts[OrderCenter::STATUS_PENDING_REVIEW]['count'] ?? 0,
                    'amount' => $statusCounts[OrderCenter::STATUS_PENDING_REVIEW]['amount'] ?? '0.00',
                ],
                'completed' => [
                    'count'  => $statusCounts[OrderCenter::STATUS_COMPLETED]['count'] ?? 0,
                    'amount' => $statusCounts[OrderCenter::STATUS_COMPLETED]['amount'] ?? '0.00',
                ],
                'after_sale' => [
                    'count'  => ($statusCounts[OrderCenter::STATUS_AFTER_SALE]['count'] ?? 0) + ($statusCounts[OrderCenter::STATUS_REFUNDING]['count'] ?? 0) + ($statusCounts[OrderCenter::STATUS_REFUNDED]['count'] ?? 0),
                    'amount' => bcadd(bcadd($statusCounts[OrderCenter::STATUS_AFTER_SALE]['amount'] ?? '0.00', $statusCounts[OrderCenter::STATUS_REFUNDING]['amount'] ?? '0.00', 2), $statusCounts[OrderCenter::STATUS_REFUNDED]['amount'] ?? '0.00', 2),
                ],
            ];

            return [
                'all' => [
                    'count'  => (int) ($all['count'] ?? 0),
                    'amount' => (string) ($all['amount'] ?? '0.00'),
                ],
                'status_counts' => $statusCounts,
                'grouped' => $grouped,
            ];
        }, 300);
    }

    /**
     * 删除订单（用户端）
     * 仅允许已取消、已退款、待支付状态的订单被删除
     * @param string $uni 订单号（order_no）
     * @param int $uid 用户ID
     * @return bool 返回操作结果
     * @throws ApiException 订单不存在或状态不满足删除条件时抛出
     */
    public function removeOrder(string $uni, int $uid)
    {
        $order = $this->dao->get(['order_no' => $uni]);
        if (!$order) {
            throw new ApiException('订单不存在');
        }
        $orderArr = $order->toArray();

        // 验证订单归属
        if ((int)($orderArr['uid'] ?? 0) !== $uid) {
            throw new ApiException('订单不存在');
        }

        // 仅允许删除：已取消、已退款（全额）、待支付的订单
        $statusCode = $orderArr['status_code'] ?? '';
        if (!in_array($statusCode, [
            OrderCenter::STATUS_CANCELED,
            OrderCenter::STATUS_REFUNDED,
            OrderCenter::STATUS_PENDING_PAYMENT,
        ])) {
            throw new ApiException('该订单无法删除');
        }

        $this->transaction(function () use ($order) {
            $order->is_del = 1;
            if (!$order->save()) {
                throw new ApiException('删除失败');
            }

            // 订单删除事件
            event('OrderDelete', [$order->toArray(), 'user_delete']);
        });

        // 同步更新中台记录状态
        $this->syncStatus($orderArr['order_no'], OrderCenter::STATUS_CANCELED);

        return true;
    }

    /**
     * 取消订单（用户端）
     * 仅允许取消待支付状态的订单
     * @param string $orderNo 订单号（order_no）
     * @param int $uid 用户ID
     * @return bool 返回操作结果
     * @throws ApiException 订单不存在、已取消或已支付时抛出
     */
    public function cancelOrder(string $orderNo, int $uid)
    {
        $order = $this->dao->get(['order_no' => $orderNo, 'uid' => $uid, 'is_del' => 0]);
        if (!$order) {
            throw new ApiException('订单不存在');
        }
        $orderArr = $order->toArray();

        // 已取消的订单不可重复取消
        if ($orderArr['status_code'] === OrderCenter::STATUS_CANCELED) {
            throw new ApiException('订单已取消，请勿重复操作！');
        }
        // 已支付的订单不可取消（应在支付前取消）
        if ((int)($orderArr['pay_status'] ?? 0) !== 0) {
            throw new ApiException('订单已经支付无法取消');
        }

        app()->make(PayRecordServices::class)->closeBeforeCancel($orderNo);
        $this->transaction(function () use ($order, $uid) {
            $order = $this->dao->getByIdForUpdate((int)$order['id']);
            if ((int)$order['pay_status'] !== 0) throw new ApiException('订单已经支付无法取消');
            if ($order['status_code'] === OrderCenter::STATUS_CANCELED) return;
            // 修改订单状态为已取消，同时记录取消时间（与超时自动取消的 cancel_time 口径一致）
            $order->status_code = OrderCenter::STATUS_CANCELED;
            $order->cancel_time = time();
            if (!$order->save()) {
                throw new ApiException('取消失败');
            }

            // 在事务内触发订单取消事件，保证数据一致性
            event('OrderCancel', [$order->toArray(), $uid]);
        });

        // 同步中台状态
        $this->syncStatus($orderNo, OrderCenter::STATUS_CANCELED);

        return true;
    }

    /**
     * 代付详情
     * 获取订单代付相关信息，包含下单人和代付人信息
     * @param int $orderId 订单中台记录ID
     * @param int $uid 当前用户ID
     * @return array 返回代付详情数据
     * @throws ApiException 订单不存在时抛出
     */
    public function getFriendDetail(int $orderId, int $uid)
    {
        // 代付详情不需要物流轨迹，跳过第三方物流查询
        $detail = $this->getDetail($orderId, false);
        if (!$detail) {
            throw new ApiException('订单不存在');
        }

        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($detail['uid']);

        // 从 extra_info 读取代付人信息
        $payUid = $detail['extra_info']['pay_uid'] ?? 0;
        $friendInfo = $payUid ? $userServices->get($payUid) : null;

        // 构建商品明细（与旧版 cartInfo 结构兼容）
        $cartInfo = $detail['order_items'] ?? [];

        return [
            'id'           => $detail['id'],
            'order_no'     => $detail['order_no'],
            // Keep the legacy mobile client contract; order_id is the order
            // number used by the order detail route, not the center table id.
            'order_id'    => $detail['order_no'],
            'uid'          => $detail['uid'],
            'avatar'       => $userInfo['avatar'] ?? '',
            'nickname'     => $userInfo['nickname'] ?? '',
            'cartInfo'     => $cartInfo,
            'paid'         => $detail['pay_status'] == OrderCenter::PAY_PAID ? 1 : 0,
            'total_num'    => $detail['total_num'] ?? 0,
            'pay_price'    => $detail['pay_price'] ?? '0.00',
            'type'         => $uid == $detail['uid'] ? 0 : 1,
            'pay_uid'      => $friendInfo['uid'] ?? 0,
            'pay_nickname' => $friendInfo['nickname'] ?? '',
            'pay_avatar'   => $friendInfo['avatar'] ?? '',
        ];
    }

    /**
     * 获取退款商品列表
     * 返回订单商品明细，支持按指定 cart_ids 筛选退款商品和数量
     * @param array $cartIds 退款商品列表 [['cart_id' => x, 'cart_num' => n], ...]
     * @param int $id 订单中台记录ID
     * @return array 返回包含 cartInfo 和 _status 的数据
     * @throws ApiException 订单不存在或退款参数错误时抛出
     */
    public function refundCartInfoList(array $cartIds = [], int $id = 0)
    {
        // 退款商品列表不需要物流轨迹，跳过第三方物流查询
        $detail = $this->getDetail($id, false);
        if (!$detail) {
            throw new ApiException('订单不存在');
        }

        $cartInfo = $detail['order_items'] ?? [];
        foreach ($cartInfo as &$item) {
            if (is_array($item) && array_key_exists('cart_id', $item)) {
                $item['cart_id'] = (string)$item['cart_id'];
            }
        }
        unset($item);
        $data = [];

        if ($cartIds) {
            foreach ($cartIds as $cart) {
                if (!isset($cart['cart_id']) || !$cart['cart_id'] || !isset($cart['cart_num']) || !$cart['cart_num'] || $cart['cart_num'] <= 0) {
                    throw new ApiException('请重新选择退款商品或件数');
                }
            }
            $cartIdsMap = array_combine(array_column($cartIds, 'cart_id'), $cartIds);
            $i = 0;
            // 退款商品按订单快照中的 cart_id 精确匹配，BIGINT 已由上方统一转成字符串。
            foreach ($cartInfo as $item) {
                $itemKey = (string)($item['cart_id'] ?? '');
                if ($itemKey && isset($cartIdsMap[$itemKey])) {
                    $data['cartInfo'][$i] = $item;
                    if (isset($cartIdsMap[$itemKey]['cart_num'])) {
                        $data['cartInfo'][$i]['cart_num'] = $cartIdsMap[$itemKey]['cart_num'];
                    }
                    $i++;
                }
            }
        }

        // 构建 _status，兼容旧版格式
        $data['_status'] = $this->buildRefundOrderStatus($detail);
        // 判断是否支持退货：由各产品类型 Handler 独立声明（supportsReturn），中台不硬编码产品类型
        try {
            $productTypeRegistry = app()->make(ProductTypeRegistry::class);
            $productType = (string)($detail['product_type'] ?? '');
            if ($productType === '') throw new ApiException('订单产品类型缺失');
            $data['_status']['_is_back'] = $productTypeRegistry->get($productType)->supportsReturn();
        } catch (\Throwable $e) {
            // Registry 异常时兜底支持退货，保证退款页可正常渲染
            $data['_status']['_is_back'] = false;
        }
        $data['cartInfo'] = $data['cartInfo'] ?? $cartInfo;

        return $data;
    }

    /**
     * 构建退款订单的状态信息
     * 从订单详情提取状态，生成兼容旧版 _status 格式
     * @param array $detail 订单详情
     * @return array 返回状态信息数组
     */
    private function buildRefundOrderStatus(array $detail): array
    {
        $statusCode = $detail['status_code'] ?? '';
        $statusMap = [
            OrderCenter::STATUS_PENDING_PAYMENT => ['_title' => '待支付', '_type' => 0, '_msg' => '', '_class' => ''],
            OrderCenter::STATUS_PROCESSING      => ['_title' => '进行中', '_type' => 1, '_msg' => '', '_class' => ''],
            OrderCenter::STATUS_PENDING_REVIEW  => ['_title' => '待评价', '_type' => 2, '_msg' => '', '_class' => ''],
            OrderCenter::STATUS_COMPLETED       => ['_title' => '已完成', '_type' => 3, '_msg' => '', '_class' => ''],
            OrderCenter::STATUS_CANCELED        => ['_title' => '已取消', '_type' => 4, '_msg' => '', '_class' => ''],
            OrderCenter::STATUS_AFTER_SALE      => ['_title' => '售后中', '_type' => -1, '_msg' => '', '_class' => ''],
            OrderCenter::STATUS_REFUNDING       => ['_title' => '售后中', '_type' => -1, '_msg' => '', '_class' => ''],
            OrderCenter::STATUS_REFUNDED        => ['_title' => '已退款', '_type' => -2, '_msg' => '', '_class' => ''],
        ];

        return $statusMap[$statusCode] ?? ['_title' => '未知', '_type' => 0, '_msg' => '', '_class' => ''];
    }

    /**
     * 再次下单
     * 根据原订单商品重新加入购物车，不支持活动商品（拼团/砍价/秒杀/预售）
     * @param StoreCartServices $services 购物车服务
     * @param string $uni 订单号（order_no）
     * @param int $uid 用户ID
     * @return array 返回购物车ID数组
     * @throws ApiException 参数错误或订单不存在时抛出
     */
    public function againOrder(StoreCartServices $services, string $uni, int $uid): array
    {
        if (!$uni) {
            throw new ApiException('参数错误');
        }

        $detail = $this->getDetailByOrderNo($uni, false);
        if (!$detail || (int)($detail['uid'] ?? 0) !== $uid) {
            throw new ApiException('订单不存在');
        }

        // 活动商品不能再来一单（'normal'/空 表示普通购买，允许再次下单）
        $activityType = $detail['activity_type'] ?? '';
        if ($activityType && $activityType !== 'normal') {
            $activityNames = [
                'combination' => '拼团商品不能再来一单，请在拼团商品内自行下单',
                'bargain'     => '砍价商品不能再来一单，请在砍价商品内自行下单',
                'seckill'     => '秒杀商品不能再来一单，请在秒杀商品内自行下单',
                'advance'     => '预售商品不能再来一单，请在预售商品内自行下单',
            ];
            throw new ApiException($activityNames[$activityType] ?? '活动商品不能再来一单');
        }

        $cartInfo = $detail['order_items'] ?? [];
        if (!$cartInfo) {
            throw new ApiException('订单商品信息不存在');
        }

        $cateId = [];
        foreach ($cartInfo as $v) {
            // 优先使用 attr_info.unique（标准商品为 SKU md5 码、网盘/虚拟商品为 SKU id），
            // 兜底 sku 规格文案，保证各类产品类型再次下单都能命中正确 SKU
            $cateId[] = $services->setCart(
                $uid,
                (int)($v['product_id'] ?? 0),
                (int)($v['cart_num'] ?? 1),
                $v['attr_info']['unique'] ?? $v['sku'] ?? '',
                0,
                true
            );
        }

        if (!$cateId) {
            throw new ApiException('再来一单失败，请重新下单');
        }

        return $cateId;
    }

    /**
     * 检查发货方式
     * 根据购物车商品判断支持的发货方式（快递/到店自提/混合）
     * @param int $uid 用户ID
     * @param string|array $cartIds 购物车id串或以逗号分隔的缓存key
     * @param bool $new 是否来自新购物车（缓存key模式）
     * @return array 返回 ['type' => 0|1|2]（0=混合,1=快递,2=到店自提）
     * @throws ApiException 数据不存在时抛出
     */
    public function checkShipping(int $uid, $cartIds, bool $new): array
    {
        if ($new) {
            $cartIds = explode(',', $cartIds);
            $cartInfo = [];
            foreach ($cartIds as $key) {
                $info = CacheService::get($key);
                if ($info) {
                    $cartInfo[] = $info;
                }
            }
        } else {
            /** @var StoreCartServices $cartServices */
            $cartServices = app()->make(StoreCartServices::class);
            $cartInfo = $cartServices->getCartList(['uid' => $uid, 'status' => 1, 'id' => $cartIds], 0, 0, ['productInfo', 'attrInfo']);
        }
        if (!$cartInfo) {
            throw new ApiException('数据不存在');
        }
        $arr = [];
        foreach ($cartInfo as $item) {
            $arr[] = $this->getProductLogistics($item['productInfo']);
        }
        $res = array_unique(explode(',', implode(',', $arr)));
        if (count($res) == 2) {
            return ['type' => 0];
        } else {
            if ($res[0] == 2 && sys_config('store_self_mention') == 0) {
                return ['type' => 1];
            }
            return ['type' => (int)$res[0]];
        }
    }

    /**
     * 从商品信息中推导物流方式（兼容 eb_product_center 无 logistics 字段）
     * @param array $productInfo 商品信息
     * @return string logistics 值，1=快递, 2=自提, "1,2"=都支持
     */
    protected function getProductLogistics(array $productInfo): string
    {
        // 有旧 logistics 字段直接返回
        if (isset($productInfo['logistics'])) {
            return (string)$productInfo['logistics'];
        }
        // 从 delivery_methods JSON 推导
        $deliveryMethods = $productInfo['delivery_methods'] ?? [];
        if (is_string($deliveryMethods)) {
            $deliveryMethods = json_decode($deliveryMethods, true) ?: [];
        }
        $hasExpress = in_array('express', $deliveryMethods, true);
        $hasPickup = in_array('pickup', $deliveryMethods, true);
        if ($hasExpress && $hasPickup) {
            return '1,2';
        }
        if ($hasPickup) {
            return '2';
        }
        return '1';
    }

    /**
     * 礼品详情
     * 获取礼物订单的分享详情，供领取者查看
     * @param int $oid 订单中台记录ID
     * @return array 返回礼品详情数据
     * @throws ApiException 订单不存在时抛出
     */
    public function giftDetail(int $oid)
    {
        // 礼品详情不需要物流轨迹，跳过第三方物流查询
        $detail = $this->getDetail($oid, false);
        if (!$detail) {
            throw new ApiException('订单不存在');
        }

        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($detail['uid']);

        // 商品快照：中台 order_items 为扁平结构，旧版页面（orderGoods 组件）按 productInfo 嵌套结构渲染，
        // 这里在保留扁平字段的同时补齐 productInfo 等旧版字段，供礼物领取页等旧页面直接复用
        $orderItems = is_array($detail['order_items'] ?? null) ? $detail['order_items'] : [];
        $cartInfo = [];
        foreach ($orderItems as $item) {
            if (!is_array($item)) {
                continue;
            }
            // 已是旧版结构时直接透传
            if (!empty($item['productInfo']) && is_array($item['productInfo'])) {
                $cartInfo[] = $item;
                continue;
            }
            $attr = is_array($item['attr_info'] ?? null) ? $item['attr_info'] : [];
            $cartInfo[] = array_merge($item, [
                'unique'      => (string)($item['unique'] ?? ($attr['unique'] ?? '')),
                'is_valid'    => $item['is_valid'] ?? true,
                'is_reply'    => (int)($item['is_reply'] ?? 0),
                'refund_num'  => (int)($item['refund_num'] ?? 0),
                'productInfo' => [
                    'product_id'    => (int)($item['product_id'] ?? ($attr['product_id'] ?? 0)),
                    'store_name'    => (string)($item['store_name'] ?? ''),
                    'image'         => (string)($item['image'] ?? ($attr['image'] ?? '')),
                    'price'         => (string)($item['price'] ?? ($attr['price'] ?? '0.00')),
                    'ot_price'      => (string)($attr['ot_price'] ?? ($item['ot_price'] ?? '0.00')),
                    'store_mention' => (int)($item['store_mention'] ?? 1),
                    'virtual_type'  => (int)($item['virtual_type'] ?? 0),
                    'time_id'       => (int)($item['time_id'] ?? 0),
                    'attrInfo'      => $attr ? array_merge($attr, [
                        'image' => (string)($attr['image'] ?? ($item['image'] ?? '')),
                        'price' => (string)($attr['price'] ?? ($item['price'] ?? '0.00')),
                        'suk'   => (string)($attr['suk'] ?? ($item['sku'] ?? '')),
                    ]) : null,
                ],
            ]);
        }

        // 从 extra_info 提取物流方式信息
        $arr = [];
        foreach ($cartInfo as $cartInfoItem) {
            $arr[] = $cartInfoItem['productInfo']['logistics'] ?? '';
        }
        $res = array_unique(explode(',', implode(',', $arr)));
        if (count($res) == 2) {
            $type = 0;
        } else {
            if ($res[0] == 2 && sys_config('store_self_mention') == 0) {
                $type = 1;
            } else {
                $type = (int)$res[0];
            }
        }

        // 从 extra_info 提取礼物相关字段
        $extraInfo = $detail['extra_info'] ?? [];
        $giftKey = md5($detail['id'] . '_' . $detail['order_no'] . '_' . $detail['uid']);

        return [
            'id'         => $detail['id'],
            'order_no'   => $detail['order_no'],
            'uid'        => $detail['uid'],
            'avatar'     => set_file_url($userInfo['avatar'] ?? ''),
            'nickname'   => $userInfo['nickname'] ?? '',
            'cartInfo'   => $cartInfo,
            'paid'       => $detail['pay_status'] == OrderCenter::PAY_PAID ? 1 : 0,
            'total_num'  => $detail['total_num'] ?? 0,
            'pay_price'  => $detail['pay_price'] ?? '0.00',
            'gift_key'   => $giftKey,
            'gift_mark'  => $extraInfo['gift_mark'] ?? '',
            'gift_uid'   => (int)($detail['gift_uid'] ?? $extraInfo['gift']['gift_uid'] ?? ($extraInfo['gift_uid'] ?? 0)),
            'is_gift'    => (!empty($extraInfo['gift']) || isset($extraInfo['gift_mark'])) ? 1 : 0,
            'refund_status' => $detail['refund_status'] ?? 0,
            'type'       => $type,
            'store_self_mention' => (int)sys_config('store_self_mention') ?? 0,
        ];
    }

    /**
     * 领取礼物
     * @param int $uid 领取者用户ID
     * @param int $oid 订单中台记录ID
     * @param string $giftKey 礼物密钥
     * @param int $shippingType 配送方式 1=快递 2=到店自提
     * @param string $name 收货人姓名
     * @param string $phone 收货人电话
     * @param int $addressId 地址ID（shippingType=1时使用）
     * @param int $storeId 门店ID（shippingType=2时使用）
     * @return bool 返回操作结果
     * @throws ApiException|AdminException 订单不存在或参数错误时抛出
     */
    public function receiveGift(int $uid, int $oid, string $giftKey, int $shippingType, string $name, string $phone, int $addressId = 0, int $storeId = 0)
    {
        $order = $this->dao->get($oid);
        if (!$order) {
            throw new AdminException('订单不存在');
        }
        $orderArr = $order->toArray();

        // 验证礼物密钥
        if ($giftKey != md5($orderArr['id'] . '_' . $orderArr['order_no'] . '_' . $orderArr['uid'])) {
            throw new AdminException('领取失败');
        }
        // 已退款订单不能领取
        if (($orderArr['refund_status'] ?? 0) != 0) {
            throw new AdminException('订单已退款');
        }
        // 不能领取自己的礼物
        if ((int)$orderArr['uid'] === $uid) {
            throw new AdminException('不能领取自己的礼物');
        }

        // 解析 extra_info
        $extraInfo = is_array($orderArr['extra_info'] ?? null) ? $orderArr['extra_info'] : (json_decode((string)($orderArr['extra_info'] ?? '{}'), true) ?: []);
        $currentGiftUid = $extraInfo['gift']['gift_uid'] ?? 0;
        // 如果已被别人领取且不是本人
        if ($currentGiftUid != 0 && $currentGiftUid != $uid) {
            return false;
        }

        $address = '';
        if ($shippingType == 1 && $addressId) {
            $addressInfo = app()->make(UserAddressServices::class)->getOne(['uid' => $uid, 'id' => $addressId, 'is_del' => 0]);
            $name = $addressInfo['real_name'] ?? '';
            $phone = $addressInfo['phone'] ?? '';
            $address = ($addressInfo['province'] ?? '') . ' ' . ($addressInfo['city'] ?? '') . ' ' . ($addressInfo['district'] ?? '') . ' ' . ($addressInfo['detail'] ?? '');
        }

        $verifyCode = '';
        if ($shippingType == 2 && $storeId) {
            $storeId = app()->make(SystemStoreServices::class)->getStoreDispose($storeId, 'id');
            if (!$storeId) {
                throw new ApiException('门店选择错误');
            }
            // 核销码生成收口至中台
            $verifyCode = app()->make(OrderCenterServices::class)->getStoreCode();
        }

        // 更新 extra_info 中的礼物和联系人信息
        // gift_time 供订单详情「礼品已领」进度节点展示；本人重复领取时保留首次领取时间
        $giftTime = (int)($extraInfo['gift']['gift_time'] ?? 0) ?: time();
        $extraInfo['gift'] = [
            'gift_uid'  => $uid,
            'gift_key'  => $giftKey,
            'gift_time' => $giftTime,
        ];
        // 联系人信息
        $extraInfo['contact'] = [    
            'name'    => $name,
            'phone'   => $phone,
            'address' => $address ?: ($extraInfo['contact']['address'] ?? ''),
        ];
        // shipping_type/store_id/verify_code 已归属中台表实体列
        $extraInfo['delivery'] = array_merge($extraInfo['delivery'] ?? [], [
            'delivery_type' => $shippingType == 2 ? 'write_off' : 'express',
        ]);

        $this->dao->update($oid, [
            'shipping_type' => $shippingType,
            'store_id'      => $storeId,
            'verify_code'   => $verifyCode,
            // 领取人写入实体列，供用户订单列表按 gift_uid 索引查询
            'gift_uid'      => $uid,
            'extra_info'    => $this->encodeExtraInfo($extraInfo),
        ]);
        // 领取后礼物订单进入领取人订单列表，清除统计缓存
        $this->clearStatCache();

        return true;
    }

    /**
     * 缓存订单信息（下单确认页数据暂存）
     * @param int $uid 用户ID
     * @param array $cartInfo 购物车信息
     * @param array $priceData 价格数据
     * @param array $other 其他扩展数据
     * @param int $cacheTime 缓存时间（秒），默认600秒
     * @return string 返回缓存key
     */
    public function cacheOrderInfo(int $uid, array $cartInfo, array $priceData, array $other = [], int $cacheTime = 600): string
    {
        $cacheKey = 'user_order_' . $uid . '_' . md5(json_encode($cartInfo) . time() . uniqid());
        CacheService::set($cacheKey, compact('cartInfo', 'priceData') + $other, $cacheTime);
        return $cacheKey;
    }

    /**
     * 获取订单确认缓存的互斥锁 key。
     * 订单确认缓存是用户级凭证，必须限制为当前用户生成的完整 key。
     *
     * @param int $uid 用户ID
     * @param string $key 订单确认缓存 key
     * @return string|null 非当前用户缓存返回 null
     */
    public function getOrderCacheLockKey(int $uid, string $key): ?string
    {
        $prefix = 'user_order_' . $uid . '_';
        if ($uid <= 0 || $key === '' || !str_starts_with($key, $prefix)) {
            return null;
        }

        return 'order_cache_create_' . hash('sha256', $uid . ':' . $key);
    }

    /**
     * 尝试锁定订单确认缓存，防止同一个确认凭证并发创建多笔订单。
     *
     * @param int $uid 用户ID
     * @param string $key 订单确认缓存 key
     * @param int $timeout 锁超时时间（秒），默认与订单确认缓存的 600 秒生命周期一致
     * @return string|null null 为非法 key，空字符串表示已有请求正在创建，非空字符串为锁 key
     */
    public function acquireOrderCacheLock(int $uid, string $key, int $timeout = 600): ?string
    {
        $lockKey = $this->getOrderCacheLockKey($uid, $key);
        if ($lockKey === null) {
            return null;
        }

        try {
            return CacheService::setMutex($lockKey, $timeout) ? $lockKey : '';
        } catch (\Throwable $e) {
            Log::error('[OrderCenterUserServices] 订单确认缓存加锁失败: ' . $e->getMessage());
            throw new \RuntimeException('订单创建服务暂不可用，请稍后重试', 0, $e);
        }
    }

    /**
     * 释放订单确认缓存锁。
     *
     * @param string $lockKey 锁 key
     * @return void
     */
    public function releaseOrderCacheLock(string $lockKey): void
    {
        if ($lockKey === '') {
            return;
        }

        try {
            CacheService::delMutex($lockKey);
        } catch (\Throwable $e) {
            Log::warning('[OrderCenterUserServices] 订单确认缓存解锁失败: ' . $e->getMessage());
        }
    }

    /**
     * 消费订单确认缓存。
     * 删除失败且缓存仍存在时返回 false，由调用方保留互斥锁直到超时，避免再次创建订单。
     *
     * @param int $uid 用户ID
     * @param string $key 订单确认缓存 key
     * @return bool
     */
    public function consumeOrderCache(int $uid, string $key): bool
    {
        if ($this->getOrderCacheLockKey($uid, $key) === null) {
            return false;
        }

        try {
            if (CacheService::delete($key)) {
                return true;
            }

            // Redis 删除不存在的 key 会返回 false；再次读取确认缓存是否确实已经不存在。
            return CacheService::get($key, null) === null;
        } catch (\Throwable $e) {
            Log::error('[OrderCenterUserServices] 订单确认缓存消费失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取缓存的订单信息
     * @param int $uid 用户ID
     * @param string $key 缓存key
     * @return array|null 返回缓存的订单数据，不存在则返回null
     */
    public function getCacheOrderInfo(int $uid, string $key): ?array
    {
        if ($this->getOrderCacheLockKey($uid, $key) === null) {
            return null;
        }

        $cacheInfo = CacheService::get($key, null);
        return is_array($cacheInfo) ? $cacheInfo : null;
    }

    /**
     * 获取用户购买过的商品ID列表（中台化读取）
     * 扫描该用户已支付订单的 extra_info.items 聚合 product_id（旧订单购物车明细服务已删除）
     * @param int $uid 用户ID
     * @return array
     */
    public function getUserCartProductIds(int $uid): array
    {
        $extras = $this->dao->getColumn(['uid' => $uid, 'paid' => 1], 'extra_info');
        $productIds = [];
        foreach ($extras as $extra) {
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            foreach ($extra['items'] ?? [] as $item) {
                if (!empty($item['product_id'])) {
                    $productIds[] = (int)$item['product_id'];
                }
            }
        }
        return array_values(array_unique($productIds));
    }
}
