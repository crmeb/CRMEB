<?php
declare(strict_types=1);

namespace app\services\order;

use app\dao\order\AfterSaleDao;
use app\dao\order\OrderCenterDao;
use app\dao\order\OrderInvoiceDao;
use app\model\order\OrderCenter;
use app\model\product\product\StoreVisit;
use app\model\user\User;
use app\services\product\product\StoreProductReplyServices;
use app\services\shipping\ExpressServices;
use app\jobs\UnpaidOrderSend;
use crmeb\services\CacheService;
use crmeb\basic\BaseService;
use crmeb\trade\order\type\OrderTypeRegistry;
use crmeb\trade\order\type\OrderTypeAdapterInterface;
use crmeb\trade\order\type\OrderCenterSyncInterface;
use crmeb\trade\mode\TradeModeRegistry;
use crmeb\trade\product\ProductTypeRegistry;
use crmeb\utils\SnowflakeIdGenerator;
use crmeb\exceptions\ApiException;
use app\dao\driver\pay\PayChannelDao;
use app\dao\user\UserBillDao;
use think\facade\Log;

/**
 * 订单中台服务层
 * 提供订单中台的核心业务逻辑，包括列表查询、统计、创建、同步等功能
 * 实现 OrderCenterSyncInterface 契约，供 crmeb 核心层通过接口反转依赖
 */
class OrderCenterServices extends BaseService implements OrderCenterSyncInterface
{
    /**
     * 数据访问层实例
     * @var OrderCenterDao
     */
    protected $dao;

    /**
     * 初始化服务
     */
    protected function initialize(): void
    {
        $this->dao = $this->app->make(OrderCenterDao::class);
    }






    /**
     * 解析日期范围参数
     * 将"2026-07-01,2026-07-13"格式转换为时间戳范围
     * @param string $dateRange 日期范围字符串
     * @return array 返回[开始时间戳, 结束时间戳]
     * @throws \InvalidArgumentException 日期格式错误时抛出异常
     */
    private function parseDateRange(string $dateRange): array
    {
        $parts = explode(',', $dateRange);
        if (count($parts) !== 2) throw new \InvalidArgumentException('日期范围格式错误，期望格式: 2026-07-01,2026-07-13');

        $start = strtotime(trim($parts[0]));
        $end   = strtotime(trim($parts[1]));
        if ($start === false || $end === false) throw new \InvalidArgumentException('日期格式错误');

        return [$start, $end + 86399];
    }

    /**
     * 获取订单类型适配器（统一入口，含注册校验与异常兜底）
     * 旧订单 source_type（如 store_order）未注册适配器时返回 null，调用方按降级逻辑处理
     * @param string $sourceType 源订单类型标识
     * @return OrderTypeAdapterInterface|null 适配器实例，未注册或异常返回 null
     */
    protected function resolveOrderAdapter(string $sourceType): ?OrderTypeAdapterInterface
    {
        if ($sourceType === '' || !in_array($sourceType, OrderTypeRegistry::getRegisteredSourceTypes(), true)) {
            return null;
        }
        try {
            return OrderTypeRegistry::getAdapter($sourceType);
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] resolveOrderAdapter failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 获取订单操作按钮（列表用）
     * 委托给订单类型适配器，由各产品类型根据自身流程决定按钮
     * @param array $order 订单数据（需包含 source_type、status_code）
     * @return array 返回按钮数组
     */
    protected function getOrderButtons(array $order): array
    {
        $adapter = $this->resolveOrderAdapter((string)($order['source_type'] ?? ''));
        if (!$adapter) {
            return [];
        }

        try {
            return $adapter->getStatusButtons($order['status_code'] ?? '', $order);
        } catch (\Throwable $e) {
            Log::warning('getOrderButtons failed: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * 解析extra_info字段
     * 兼容ORM已自动解析的JSON类型和原始字符串
     * @param mixed $value JSON字符串、数组或null
     * @return array 返回解析后的数组
     */
    protected function parseExtraInfo($value): array
    {
        if ($value === null || $value === '') return [];
        // ORM已自动解析JSON类型为数组
        if (is_array($value)) return $value;
        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            \think\facade\Log::warning('extra_info JSON 解析失败: ' . json_last_error_msg());
            return [];
        }
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * 编码extra_info字段
     * 将数组转换为JSON字符串，处理编码错误
     * @param mixed $value 数组或JSON字符串
     * @return string|null 返回JSON字符串或null
     */
    protected function encodeExtraInfo($value): ?string
    {
        if ($value === null || $value === []) return null;
        if (is_string($value)) {
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) return $value;
        }
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $encoded === false ? null : $encoded;
    }

    /**
     * 补充解析 product_id
     * 当 extra_info 中缺失 product_id 时（旧订单），从 order_items 或源订单表获取
     * @param array $detail 订单详情数据
     * @return int 返回商品ID，获取不到返回0
     */
    protected function resolveProductId(array $detail): int
    {
        // 优先从 order_items 获取
        if (!empty($detail['order_items'])) {
            return (int)($detail['order_items'][0]['product_id'] ?? 0);
        }

        // 其次从源订单表获取（复用 getSourceOrderData）
        $sourceOrder = $this->getSourceOrderData(
            $detail['source_type'] ?? '',
            (int)($detail['source_id'] ?? 0)
        );
        if ($sourceOrder) {
            return (int)($sourceOrder['product_id'] ?? 0);
        }

        return 0;
    }

    /**
     * 创建订单中台记录
     * 使用saveOrUpdate确保幂等性，避免重复创建
     * @param array $data 订单数据
     * @return bool 返回是否创建成功
     */
    public function createRecord(array $data): bool
    {
        $extraInfo = $data['extra_info'] ?? [];

        $insert = [
            'order_no'       => $data['order_no'], // 
            'source_type'    => $data['source_type'],
            'source_id'      => $data['source_id'],
            'uid'            => $data['uid'],
            'order_category' => $data['order_category'],
            'product_type'   => $data['product_type'] ?? '',
            'activity_type'  => $data['activity_type'] ?? '',
            'activity_id'    => $data['activity_id'] ?? 0,
            'status_code'    => $data['status_code'],
            'pay_status'     => $data['pay_status'] ?? 0,
            'refund_status'  => $data['refund_status'] ?? 0,
            'total_price'    => $data['total_price'] ?? 0,
            'pay_price'      => $data['pay_price'] ?? 0,
            'pay_postage'    => $data['pay_postage'] ?? 0,
            'refund_price'   => $data['refund_price'] ?? 0,
            'pay_type'         => $data['pay_type'] ?? '',
            'coupon_price'     => $data['coupon_price'] ?? '0.00',
            'deduction_price'  => $data['deduction_price'] ?? '0.00',
            'use_integral_num' => $data['use_integral_num'] ?? 0,
            'member_discount'  => $data['member_discount'] ?? '0.00',
            'vip_discount'     => $data['vip_discount'] ?? '0.00',
            'gift_price'       => $data['gift_price'] ?? '0.00',
            'title'            => $data['title'] ?? '',
            'image'          => $data['image'] ?? '',
            'total_num'      => $data['total_num'] ?? 0,
            'subtitle'       => $data['subtitle'] ?? '',
            'store_id'       => $data['store_id'] ?? 0,
            'shipping_type'  => $data['shipping_type'] ?? 1,
            'verify_code'    => $data['verify_code'] ?? '',
            'mark'           => $data['mark'] ?? '',
            'custom_form'    => !empty($data['custom_form']) ? $this->encodeExtraInfo($data['custom_form']) : null,
            'invoice'        => !empty($data['invoice']) ? $this->encodeExtraInfo($data['invoice']) : null,
            'extra_info'     => $this->encodeExtraInfo($extraInfo),
            'detail_route'   => $data['detail_route'] ?? '',
            'add_time'       => time(),
        ];

        $result = $this->dao->saveOrUpdate(['order_no' => $data['order_no']], $insert);
        $this->clearStatCache();
        return (bool) $result;
    }

    /**
     * 订单中台统计缓存 key（带版本号：失效走版本递增，旧 key 靠自身 TTL 自然过期）
     * cache 的 delete 不支持通配，此前 delete('order_center:stat:*') 为静默无效调用
     * @param array $where 统计查询条件
     * @return string 缓存 key
     */
    protected function statCacheKey(array $where): string
    {
        $ver = (int) app()->cache->get('order_center:stat:ver');

        return 'order_center:stat:v' . ($ver ?: 1) . ':' . md5(json_encode($where));
    }

    /**
     * 清除订单中台统计缓存
     */
    protected function clearStatCache(): void
    {
        app()->cache->set('order_center:stat:ver', (int) app()->cache->get('order_center:stat:ver') + 1, 86400);
    }

    /**
     * 获取支付方式中文名称
     * 从 eb_pay_channel 表动态读取，避免硬编码
     * @param string $payType 支付方式 key
     * @return string 返回支付方式中文名称，未匹配则返回原值
     */
    protected function getPayTypeName(string $payType): string
    {
        static $map = [];
        if (empty($map)) {
            try {
                $channels = app()->make(PayChannelDao::class)->getAllEnabled();
                foreach ($channels as $ch) {
                    $map[$ch['channel_key']] = $ch['channel_name'];
                }
                // 内部虚拟支付方式，无对应支付渠道记录
                $map['free'] = '积分兑换';
            } catch (\Throwable $e) {
                Log::error('[OrderCenter] 读取支付渠道失败: ' . $e->getMessage());
            }
        }
        return $map[$payType] ?? $payType;
    }

    /**
     * 同步订单状态
     * 更新统一状态码及其他附加字段，清除统计缓存
     * 订单中台记录应在订单创建流程中已写入，此处仅做更新；若记录缺失则抛异常暴露数据不一致
     * @param string $orderNo 订单号
     * @param string $statusCode 统一状态码
     * @param array $extra 附加更新字段
     * @return bool 返回是否更新成功
     * @throws \RuntimeException 订单中台记录不存在时抛出
     */
    public function syncStatus(string $orderNo, string $statusCode, array $extra = []): bool
    {
        $update = array_merge(['status_code' => $statusCode], $extra);
        $centerDao = $this->dao;
        $exists = $centerDao->get(['order_no' => $orderNo], ['id']);
        if (!$exists) {
            throw new \RuntimeException("[OrderCenter] syncStatus 失败：订单 {$orderNo} 中台记录不存在");
        }
        $centerDao->update(['order_no' => $orderNo], $update);
        // 同步更新 extra_info.business_status，使详情页状态标签与 status_code 一致
        $statusText = OrderCenter::$statusMap[$statusCode] ?? $statusCode;
        $currentExtra = $this->dao->getDb()->where('order_no', $orderNo)->value('extra_info');
        $extraArray = $this->parseExtraInfo($currentExtra);
        $extraArray['business_status'] = [
            'code'   => $statusCode,
            'text'   => $statusText,
            'detail' => '',
            'extend' => new \stdClass(),
        ];
        $this->dao->update(['order_no' => $orderNo], [
            'extra_info' => $this->encodeExtraInfo($extraArray),
        ]);
        $this->clearStatCache();
        return true;
    }

    /**
     * 更新订单中台记录
     * 通用更新方法，清除统计缓存
     * @param string $orderNo 订单号
     * @param array $data 更新数据
     * @return bool 返回是否更新成功
     */
    public function updateRecord(string $orderNo, array $data): bool
    {
        $result = $this->dao->update(['order_no' => $orderNo], $data) !== false;
        $this->clearStatCache();
        return $result;
    }

    /**
     * 订单评价完成后检查
     * 订单内全部商品均已评价且当前处于待评价状态时，流转为已完成
     * @param int $orderId 订单中台 ID
     * @return bool 是否触发了状态流转
     */
    public function checkOrderOver(int $orderId): bool
    {
        $order = $this->dao->get((int)$orderId, ['id', 'order_no', 'source_type', 'status_code', 'extra_info']);
        if (!$order) {
            return false;
        }
        $order = is_object($order) ? $order->toArray() : (array)$order;
        if (($order['status_code'] ?? '') !== OrderCenter::STATUS_PENDING_REVIEW) {
            return false;
        }
        $extra = $this->parseExtraInfo($order['extra_info'] ?? null);
        $allUniques = [];
        foreach ($extra['items'] ?? [] as $item) {
            $unique = $item['attr_info']['unique'] ?? ($item['unique'] ?? '');
            if ($unique !== '') {
                $allUniques[] = (string)$unique;
            }
        }
        if (empty($allUniques)) {
            return false;
        }
        /** @var \app\services\product\product\StoreProductReplyServices $replyServices */
        $replyServices = $this->app->make(\app\services\product\product\StoreProductReplyServices::class);
        $replied = array_unique($replyServices->getRepliedUniques($allUniques));
        if (count($replied) < count(array_unique($allUniques))) {
            return false;
        }
        return $this->completeOrderStatus($order);
    }

    protected function completeOrderStatus(array $order): bool
    {
        try {
            OrderTypeRegistry::getAdapter((string)($order['source_type'] ?? ''))
                ->advance((string)$order['order_no'], OrderCenter::STATUS_COMPLETED);
            return true;
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] complete source order failed: ' . $e->getMessage());
            return $this->syncStatus((string)$order['order_no'], OrderCenter::STATUS_COMPLETED);
        }
    }

    /**
     * 从源订单同步到订单中台
     * 根据source_type自动推导状态，支持自定义数据构建回调
     * @param string $sourceType 源表类型标识
     * @param array $sourceOrder 源订单数据
     * @param callable|null $buildData 自定义数据构建回调
     * @return bool 返回是否同步成功
     */
    public function syncFromSource(string $sourceType, array $sourceOrder, ?callable $buildData = null): bool
    {
        if ($buildData) {
            $data = $buildData($sourceOrder);
        } else {
            $data = $this->buildDefaultData($sourceType, $sourceOrder);
        }

        $exists = $this->dao->get(['order_no' => $data['order_no']]);
        if ($exists) {
            return $this->updateRecord($data['order_no'], $data);
        }
        return $this->createRecord($data);
    }

    /**
     * 构建默认同步数据
     * 通过状态注册器自动推导统一状态码、支付状态、退款状态
     * @param string $sourceType 源表类型标识
     * @param array $sourceOrder 源订单数据
     * @return array 返回构建的数据数组
     */
    protected function buildDefaultData(string $sourceType, array $sourceOrder): array
    {
        return [
            'order_no'       => $sourceOrder['order_no'] ?? $sourceOrder['order_id'] ?? '',
            'source_type'    => $sourceType,
            'source_id'      => $sourceOrder['id'] ?? 0,
            'uid'            => $sourceOrder['uid'] ?? 0,
            'status_code'    => OrderTypeRegistry::resolveStatusCode($sourceType, $sourceOrder),
            'pay_status'     => OrderTypeRegistry::resolvePayStatus($sourceType, $sourceOrder),
            'refund_status'  => OrderTypeRegistry::resolveRefundStatus($sourceType, $sourceOrder),
            'total_price'    => $sourceOrder['total_price'] ?? 0,
            'pay_price'      => $sourceOrder['pay_price'] ?? $sourceOrder['price'] ?? 0,
            'pay_postage'    => $sourceOrder['pay_postage'] ?? 0,
            'pay_type'       => $sourceOrder['pay_type'] ?? '',
            'add_time'       => $sourceOrder['add_time'] ?? time(),
        ];
    }

    /**
     * 根据订单号获取订单详情
     * @param string $orderNo 订单号
     * @return array 返回订单详情数据
     * @throws \RuntimeException 订单不存在时抛出异常
     */
    public function getDetailByOrderNo(string $orderNo): array
    {
        $detail = $this->dao->get(['order_no' => $orderNo], ['id']);
        if (!$detail) {
            throw new \RuntimeException('订单不存在');
        }
        return $this->getDetail((int)$detail['id']);
    }

    /**
     * 获取订单详情
     * 通过事件机制允许扩展模块注入详情数据
     * @param int $id 订单中台记录ID
     * @return array 返回订单详情数据
     * @throws \RuntimeException 订单不存在时抛出异常
     */
    public function getDetail(int $id): array
    {
        $detail = $this->dao->get(['id' => $id]);
        if (!$detail) {
            throw new \RuntimeException('订单不存在');
        }
        $detail = $detail->toArray();
        $detail['extra_info'] = $this->parseExtraInfo($detail['extra_info']);
        // 下单用户昵称/绑定电话（eb_user），供详情"用户信息"区展示
        $detailUser = User::where('uid', (int)$detail['uid'])->field('nickname,phone')->find();
        $detail['nickname'] = $detailUser['nickname'] ?? '';
        $detail['user_phone'] = $detailUser['phone'] ?? '';
        // 解析 JSON 字段
        if (!empty($detail['custom_form'])) {
            $detail['custom_form'] = $this->parseExtraInfo($detail['custom_form']);
            $detail['custom_form'] = $this->transformCustomForm($detail['custom_form']);
        }
        // 解析发票信息：中台 invoice 仅是下单时抬头快照，不含开票状态；
        // 开票状态（is_invoice/票号/开票时间）以申请表 eb_order_invoice 为真源，后台开票只写该表
        $invoiceSnapshot = [];
        if (!empty($detail['invoice'])) {
            // 中台订单创建时保存的是用户发票原始字段（name/duty_number），
            // 详情契约统一使用 title/tax_number，避免前端因数据来源不同而漏显示。
            $invoiceSnapshot = $this->normalizeInvoice($this->parseExtraInfo($detail['invoice']));
        }
        // 支付方式从 eb_pay_channel 读取中文名称
        $detail['pay_type_name'] = $this->getPayTypeName($detail['pay_type']);

        // 从源订单表加载发票、备注、自定义表单等信息，并重新推导最新状态码和扩展状态
        // 必须在构建时间线、按钮、进度条之前调用，确保这些字段基于实时状态
        $this->loadSourceOrderInfo($detail);

        // 发票信息：优先取申请表（含开票状态），无申请记录时回退下单快照
        $invoiceLive = $this->loadOrderInvoice((int)$detail['id']);
        if (!empty($invoiceLive)) {
            // 快照可补齐申请表未存的展示字段，但状态类字段必须以申请表为准
            $detail['invoice'] = array_merge($invoiceSnapshot, $invoiceLive);
        } else {
            $detail['invoice'] = $invoiceSnapshot ?: null;
        }

        // loadSourceOrderInfo 可能重算 status_code，需同步刷新 status_text
        // 确保 buildGroupedData 和前端引擎都能拿到正确的状态文案
        $detail['status_text'] = OrderCenter::$statusMap[$detail['status_code'] ?? ''] ?? ($detail['status_code'] ?? '');

        // 构建操作按钮（委托给产品类型 Handler）
        $detail['order_buttons'] = $this->getOrderButtonsFromHandler($detail);
        // 构建订单生命周期进度条（不同订单类型节点不同，委托给产品类型 Handler）
        $detail['progress'] = $this->getProgressFromHandler($detail);

        // 构建订单时间线（从 eb_order_log 表读取操作记录，最新事件在前）
        $detail['timeline'] = $this->buildTimeline($detail);

        // 前端统一的到店自提标识
        $detail['is_pickup'] = ($detail['shipping_type'] ?? 0) == 2;
        // 前端统一的上门服务标识
        $detail['is_door_service'] = ($detail['shipping_type'] ?? 0) == 3;
        // 到店自提订单：从 order_center 表 store_id 列加载门店信息
        $storeId = (int)($detail['store_id'] ?? 0);
        if ($detail['is_pickup'] && $storeId > 0) {
            try {
                /** @var \app\services\system\store\SystemStoreServices $storeServices */
                $storeServices = app()->make(\app\services\system\store\SystemStoreServices::class);
                $storeInfo = $storeServices->getStoreDispose($storeId);
                if ($storeInfo) {
                    $detail['store'] = is_object($storeInfo) ? $storeInfo->toArray() : $storeInfo;
                }
            } catch (\Throwable $e) {
                Log::warning('[OrderCenter] load store info failed: ' . $e->getMessage());
            }
        }
        // 非实物商品（虚拟/卡密）无物流配送概念，清除 store 信息，前端无需展示
        // contact/delivery/service 等扩展独有字段由各产品类型 OrderCenterDetailExtendListener 注入
        $typeHandler = null;
        try { $typeHandler = app()->make(ProductTypeRegistry::class)->get((string)($detail['product_type'] ?? '')); } catch (\Throwable $e) {}
        $capabilities = $typeHandler ? $typeHandler->getCapabilities() : [];
        $isVirtual = (($capabilities['order']['virtual'] ?? false) === true);
        if ($isVirtual || !$typeHandler) {
            unset($detail['store']);
            $detail['is_pickup'] = false;
            $detail['is_door_service'] = false;
        }
        // 从实体列读取核心金额字段，旧数据 fallback 到 extra_info
        // 新订单：6 个金额字段写入实体列（coupon_price/deduction_price/use_integral_num/
        //   member_discount/vip_discount/gift_price），extra_info 中不再双写
        // 旧订单：extra_info 中留有旧数据，实体列值为 0.00，需 fallback
        $priceFields = [
            'coupon_price'     => 'coupon_price',
            'deduction_price'  => 'deduction_price',
            'use_integral_num' => 'use_integral_num',
            'member_discount'  => 'member_level_discount', // 列名 ≠ extra_info key
            'vip_discount'     => 'vip_discount',
            'gift_price'       => 'gift_price',
        ];
        foreach ($priceFields as $entityField => $extraInfoKey) {
            $entityVal = $detail[$entityField] ?? null;
            // 实体列值为默认值（新增列后旧订单为 0.00/0）且 extra_info 有旧数据 → 使用旧数据
            $isDefault = ($entityVal === null || $entityVal === 0 || $entityVal === '0' || $entityVal === '0.00');
            if ($isDefault && isset($detail['extra_info'][$extraInfoKey])) {
                $detail[$entityField] = $detail['extra_info'][$extraInfoKey];
            }
            unset($detail['extra_info'][$extraInfoKey]);
        }
        // 从 extra_info 中提取业务状态到顶层
        // loadSourceOrderInfo 已按扩展表实时推导 business_status（优先采用），
        // 仅当推导结果为空（如已取消/已退款无业务子状态）时才回退 extra_info 中的快照，
        // 避免残留的旧快照覆盖实时推导值（如门票 issued 后应显示"待使用"而非旧"已完成"）
        if (empty($detail['business_status']) && isset($detail['extra_info']['business_status'])) {
            $detail['business_status'] = $detail['extra_info']['business_status'];
        }
        unset($detail['extra_info']['business_status']);

        // 加载商品明细（order_items）
        $detail['order_items'] = $this->loadOrderItems($detail);

        // 补充 extra_info 中缺失的 product_id
        // 旧订单创建时可能未存储 product_id，从源订单表或 order_items 获取
        if (!isset($detail['extra_info']['product_id']) || empty($detail['extra_info']['product_id'])) {
            $detail['extra_info']['product_id'] = $this->resolveProductId($detail);
        }

        // 根据产品类型生成后台详情路由，供前端 iframe 嵌入
        $detail['admin_detail_url'] = $this->getAdminDetailUrl($detail);

        // 初始化扩展容器，供插件注入数据
        // extend：扩展数据（合并进 page_config.extensions）
        // extend_sections：插件自声明的渲染 section（追加进 page_config.sections）
        $detail['extend'] = [];
        $detail['extend_sections'] = [];

        // 通过事件机制，允许扩展模块注入详情数据
        $eventData = [
            'detail'       => &$detail,
            'source_type'  => $detail['source_type'],
            'source_id'    => $detail['source_id'],
        ];
        // 触发事件，允许插件注入数据
        event('OrderCenterDetailExtend', $eventData);

        // 加载售后进度扩展数据（含 type_text/status_text/timeline）
        // 前端 AfterSaleProgress 组件据此渲染，不再硬编码售后类型映射表
        $this->loadAfterSaleExtension($detail);

        // 加载物流轨迹扩展数据（已发货订单调用物流查询驱动获取 tracks）
        // 前端 LogisticsTimeline 组件据此渲染物流轨迹卡片，无需点"查看物流"单独查询
        $this->loadLogisticsExtension($detail);

        // 加载订单完成态权益扩展数据（积分奖励/领券入口，系统内置能力，中台自行组装）
        $this->loadOrderRewardExtension($detail);

        // 如果没有任何插件注入数据，移除 extend 字段
        if (empty($detail['extend'])) {
            unset($detail['extend']);
        }
        // 如果没有任何插件声明渲染 section，移除 extend_sections 字段
        if (empty($detail['extend_sections'])) {
            unset($detail['extend_sections']);
        }

        return $detail;
    }

    /**
     * 组装价格明细数组
     * 将金额实体列转为 {label, value, type} 数组格式
     * 金额字段为中台实体列（价格链规范固定）；插件可通过 OrderPricingExtend 事件追加金额项
     * @param array $detail 订单详情数据
     * @return array 返回 {items: [...], pay_price: string}
     */
    protected function buildPricingItems(array $detail): array
    {
        $items = [];
        // 商品总额（始终存在）
        $items[] = [
            'label' => '商品总额',
            'value' => number_format((float)($detail['total_price'] ?? 0), 2, '.', ''),
            'type'  => 'subtotal',
        ];

        // 运费（>0 才显示）
        $postage = (float)($detail['pay_postage'] ?? 0);
        if ($postage > 0) {
            $items[] = [
                'label' => '运费',
                'value' => '+' . number_format($postage, 2, '.', ''),
                'type'  => 'shipping',
            ];
        }
        // 优惠券抵扣
        if (!empty($detail['coupon_price']) && (float)$detail['coupon_price'] > 0) {
            $items[] = [
                'label' => '优惠券抵扣',
                'value' => '-' . number_format((float)$detail['coupon_price'], 2, '.', ''),
                'type'  => 'coupon',
            ];
        }
        // 积分抵扣
        if (!empty($detail['deduction_price']) && (float)$detail['deduction_price'] > 0) {
            $items[] = [
                'label' => '积分抵扣',
                'value' => '-' . number_format((float)$detail['deduction_price'], 2, '.', ''),
                'type'  => 'integral',
            ];
        }
        // 等级会员优惠
        if (!empty($detail['member_discount']) && (float)$detail['member_discount'] > 0) {
            $items[] = [
                'label' => '会员折扣',
                'value' => '-' . number_format((float)$detail['member_discount'], 2, '.', ''),
                'type'  => 'member',
            ];
        }
        // 付费会员优惠
        if (!empty($detail['vip_discount']) && (float)$detail['vip_discount'] > 0) {
            $items[] = [
                'label' => '付费会员优惠',
                'value' => '-' . number_format((float)$detail['vip_discount'], 2, '.', ''),
                'type'  => 'vip',
            ];
        }

        // 价格明细扩展缝口：交易模式/产品类型插件经事件追加金额项（如服务费/上门费）
        $pricingEvent = ['items' => &$items, 'detail' => $detail];
        event('OrderPricingExtend', $pricingEvent);

        return [
            'items'     => $items,
            'pay_price' => number_format((float)($detail['pay_price'] ?? 0), 2, '.', ''),
        ];
    }

    /**
     * 组装默认 page_config sections 声明式渲染配置
     * 只组装中台自身职责的 section（状态/商品/价格/配送/核销/表单/发票/售后等）；
     * 插件专属 section 由插件在 OrderCenterDetailExtend 监听器中通过 extend_sections 自声明，
     * 中台不感知插件业务，新增插件零修改中台代码（架构铁律：中台不处理扩展层独有字段）
     * @param array $detail 订单详情数据（已组装完成）
     * @param array $extendSections 插件自声明的渲染 section（key => section 配置）
     * @return array 返回 {sections: [...]}
     */
    protected function buildDefaultPageConfig(array $detail, array $extendSections = []): array
    {
        // $detail 为 buildGroupedData 的分组结构，从各功能域分组读取
        $statusCode = $detail['status']['code'] ?? '';
        $isPickup = ($detail['delivery']['shipping_type'] ?? 0) == 2;
        $productType = (string)($detail['order_info']['product_type'] ?? '');
        $sections = [];

        // 10. 状态头部（始终存在）
        // props 直填渲染值（v8.1 简化契约：去掉 JSONPath，前端引擎无需二次解析）
        // code/pay_status 供前端引擎逻辑（isRefund/payStatus）读取
        $sections[] = [
            'key'       => 'status',
            'component' => 'status_bar',
            'props'     => [
                'text'       => $detail['status']['text'] ?? '',
                'icon'       => $detail['status']['icon'] ?? '',
                'subtitle'   => $detail['status']['subtitle'] ?? '',
                'add_time'   => $detail['order_info']['add_time'] ?? 0,
                'code'       => $detail['status']['code'] ?? '',
                'pay_status' => $detail['status']['pay_status'] ?? 0,
            ],
            'visible'   => true,
            'order'     => 10,
        ];

        // 15. 待支付倒计时（仅 pending_payment）
        $countdown = $detail['countdown'] ?? null;
        $sections[] = [
            'key'       => 'countdown',
            'component' => 'countdown',
            'props'     => [
                'end_time'  => $countdown['end_time'] ?? 0,
                'remaining' => $countdown['remaining'] ?? 0,
                'text'      => $countdown['text'] ?? '',
            ],
            'visible'   => $statusCode === 'pending_payment',
            'order'     => 15,
        ];

        // 20. 进度条（非售后/退款/取消）
        $progressVisible = !in_array($statusCode, ['after_sale', 'refunded', 'canceled']);
        $progress = $detail['status']['progress'] ?? [];
        $sections[] = [
            'key'       => 'progress',
            'component' => 'progress_bar',
            'props'     => [
                'nodes'   => $progress['nodes'] ?? [],
                'current' => $progress['current'] ?? 0,
            ],
            'visible'   => $progressVisible,
            'order'     => 20,
        ];

        // 25. 提示条
        $sections[] = [
            'key'       => 'notice',
            'component' => 'notice_bar',
            'props'     => ['notices' => $detail['notice'] ?? []],
            'visible'   => !empty($detail['notice']),
            'order'     => 25,
        ];

        // 30. 活动进度（拼团/砍价/秒杀等）
        // title 由后端动态生成：活动扩展自带 title 优先，否则用交易模式短标签 + "进度" 兜底
        $activityExt = $detail['extensions']['activity'] ?? null;
        $activityTitle = '';
        if (!empty($activityExt)) {
            $activityTitle = (string)($activityExt['title'] ?? '');
            if ($activityTitle === '') {
                $activityType = $activityExt['type'] ?? ($detail['order_info']['activity_type'] ?? '');
                if ($activityType && $activityType !== 'normal') {
                    try {
                        $tradeModeRegistry = app()->make(\crmeb\trade\mode\TradeModeRegistry::class);
                        if ($tradeModeRegistry->has($activityType)) {
                            $activityTitle = $tradeModeRegistry->get($activityType)->getLabel() . '进度';
                        }
                    } catch (\Throwable $e) {
                        // 交易模式未注册时静默降级，标题留空由前端兜底
                    }
                }
            }
        }
        $sections[] = [
            'key'       => 'activity',
            'component' => 'activity_card',
            'props'     => [
                'activity' => $activityExt,
                'title'    => $activityTitle,
            ],
            'visible'   => !empty($activityExt),
            'order'     => 30,
        ];

        // 40. 配送/自提卡片（仅实物商品）
        // 上门服务订单不渲染地址卡片，预约信息由 service_appointment section（118）展示
        if ($productType === 'normal' && !($detail['delivery']['is_door_service'] ?? false)) {
            $deliveryComponent = $isPickup ? 'store_card' : 'address_card';
            $deliveryProps = $isPickup
                ? [
                    'store'       => $detail['delivery']['store'] ?? null,
                    'verify_code' => $detail['verification']['code'] ?? '',
                ]
                : [
                    'contact'      => $detail['delivery']['contact'] ?? null,
                    'user_address' => $detail['delivery']['user_address'] ?? '',
                ];
            $sections[] = [
                'key'       => 'delivery',
                'component' => $deliveryComponent,
                'props'     => $deliveryProps,
                'visible'   => true,
                'order'     => 40,
            ];
        }

        // 50. 商品卡片（始终存在）
        // product_id 供前端引擎跳转商品详情（goProductDetail）使用
        $product = $detail['product'] ?? [];
        $productId = $detail['extensions']['extra_info']['product_id'] ?? ($product['items'][0]['product_id'] ?? 0);
        $sections[] = [
            'key'       => 'product',
            'component' => 'product_card',
            'props'     => [
                'product'    => $product,
                'product_id' => (int)$productId,
            ],
            'visible'   => true,
            'order'     => 50,
        ];

        // 65. 核销码（有核销码的订单：自提订单 或 已支付的非自提订单如酒店预订）
        // 可见性由 buildGroupedData 中 verification 对象是否构造决定，此处只需检查 code 是否存在
        $verificationVisible = !empty($detail['verification']['code']);
        $verification = $detail['verification'] ?? [];
        $sections[] = [
            'key'       => 'verification',
            'component' => 'qrcode',
            'props'     => [
                'code'       => $verification['code'] ?? '',
                'qrcode_url' => $verification['qrcode_url'] ?? '',
                'status'     => $verification['status'] ?? 'pending',
            ],
            'visible'   => $verificationVisible,
            'order'     => 65,
        ];

        // 70. 自定义表单
        $customForm = $detail['extensions']['custom_form'] ?? null;
        $sections[] = [
            'key'       => 'custom_form',
            'component' => 'custom_form',
            'props'     => ['items' => $customForm ?? []],
            'visible'   => !empty($customForm),
            'order'     => 70,
        ];

        // 75. 物流轨迹
        $logisticsExt = $detail['extensions']['logistics'] ?? null;
        $sections[] = [
            'key'       => 'logistics',
            'component' => 'timeline',
            'props'     => [
                'tracks'        => $logisticsExt['tracks'] ?? [],
                'delivery_name' => $logisticsExt['delivery_name'] ?? '',
                'delivery_id'   => $logisticsExt['delivery_id'] ?? '',
            ],
            'visible'   => !empty($logisticsExt),
            'order'     => 75,
        ];

        // 80. 价格明细（始终存在）
        $sections[] = [
            'key'       => 'pricing',
            'component' => 'price_detail',
            'props'     => [
                'items'     => $detail['pricing']['items'] ?? [],
                'pay_price' => $detail['pricing']['pay_price'] ?? '0.00',
            ],
            'visible'   => true,
            'order'     => 80,
        ];

        // 85. 订单信息（始终存在）
        $sections[] = [
            'key'       => 'order_info',
            'component' => 'info_list',
            'props'     => ['fields' => $detail['order_info'] ?? [], 'title' => '订单信息'],
            'visible'   => true,
            'order'     => 85,
        ];

        // 90. 发票信息（从 extensions.invoice 取值，由 loadOrderInvoice 组装）
        $invoiceExt = $detail['extensions']['invoice'] ?? null;
        $sections[] = [
            'key'       => 'invoice',
            'component' => 'info_list',
            'props'     => ['fields' => $invoiceExt ?? [], 'title' => '发票信息'],
            'visible'   => !empty($invoiceExt),
            'order'     => 90,
        ];

        // 93. 支付详情（已支付订单）
        $payment = $detail['payment'] ?? null;
        $sections[] = [
            'key'       => 'payment',
            'component' => 'info_list',
            'props'     => ['fields' => $payment ?? [], 'title' => '支付详情'],
            'visible'   => !empty($payment),
            'order'     => 93,
        ];

        // 100. 售后进度
        $afterSaleExt = $detail['extensions']['after_sale'] ?? null;
        $sections[] = [
            'key'       => 'after_sale',
            'component' => 'after_sale_progress',
            'props'     => ['after_sale' => $afterSaleExt ?? []],
            'visible'   => !empty($afterSaleExt),
            'order'     => 100,
        ];

        // 105. 关联订单（拆单/换货）
        $relatedOrders = $detail['related_orders'] ?? null;
        $sections[] = [
            'key'       => 'related_orders',
            'component' => 'related_order_list',
            'props'     => ['items' => $relatedOrders ?? []],
            'visible'   => !empty($relatedOrders),
            'order'     => 105,
        ];

        // 122. 领券入口（订单完成，系统内置权益，跳转领券中心）
        $couponGiftExt = $detail['extensions']['coupon_gift'] ?? null;
        $sections[] = [
            'key'       => 'coupon_gift',
            'component' => 'action_card',
            'props'     => [
                'title'  => $couponGiftExt['title'] ?? '领取优惠券',
                'icon'   => 'icon-youhuiquan',
                'action' => 'goCouponGift',
                'link'   => $couponGiftExt['link'] ?? '',
            ],
            'visible'   => !empty($couponGiftExt),
            'order'     => 122,
        ];

        // 126. 积分奖励（订单完成且本单有积分入账，系统内置权益）
        $pointsRewardExt = $detail['extensions']['points_reward'] ?? null;
        $sections[] = [
            'key'       => 'points_reward',
            'component' => 'action_card',
            'props'     => [
                'title'  => $pointsRewardExt['text'] ?? '',
                'icon'   => 'icon-jifen',
                'action' => 'goPointsReward',
                'link'   => $pointsRewardExt['link'] ?? '',
            ],
            'visible'   => !empty($pointsRewardExt),
            'order'     => 126,
        ];

        // 128. 客服联系（受中台系统配置 customer_service_switch 控制，默认显示）
        $sections[] = [
            'key'       => 'service_contact',
            'component' => 'action_card',
            'props'     => ['title' => '联系客服', 'icon' => 'icon-kefu', 'action' => 'goGoodCall'],
            'visible'   => (bool)sys_config('customer_service_switch', 1),
            'order'     => 128,
        ];

        // 500. 插件自声明 section（extend_sections）
        // 插件通过 OrderCenterDetailExtend 事件注入数据时同步声明渲染配置，
        // 中台只透传不感知插件业务；props 可直接内嵌插件数据（数据同时保留在 extensions 中）
        foreach ($extendSections as $sectionKey => $extendSection) {
            if (!is_array($extendSection) || empty($extendSection)) {
                continue;
            }
            $section = array_merge([
                'component' => 'info_list',
                'props'     => ['fields' => $detail['extensions'][$sectionKey] ?? []],
                'visible'   => true,
                'order'     => 500,
            ], $extendSection);
            // key 强制与声明键一致，保证 section 唯一性
            $section['key'] = (string)$sectionKey;
            $sections[] = $section;
        }

        // 按 order 升序排序（PHP 8 排序稳定，同 order 保持声明顺序）
        usort($sections, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        // 900. 时间线（移动端会被移除）
        $sections[] = [
            'key'       => 'timeline',
            'component' => 'timeline',
            'props'     => ['items' => $detail['timeline'] ?? []],
            'visible'   => true,
            'order'     => 900,
        ];

        // 999. 底部操作按钮
        $sections[] = [
            'key'       => 'buttons',
            'component' => 'button_group',
            'props'     => ['buttons' => $detail['buttons'] ?? []],
            'visible'   => !empty($detail['buttons']),
            'order'     => 999,
        ];

        return ['sections' => $sections];
    }

    /**
     * 将扁平详情数据组装为分组结构（data 层）
     * v8.1 全新设计：data 层只输出功能域分组，不保留扁平字段
     * @param array $detail getDetail() 返回的扁平详情数据
     * @return array 返回分组后的 data 结构
     */
    protected function buildGroupedData(array $detail): array
    {
        // 能力解析必须在本方法内完成，局部变量不能跨方法共享。
        $typeHandler = null;
        $capabilities = [];
        try {
            $productType = (string)($detail['product_type'] ?? '');
            if ($productType !== '') {
                $typeHandler = app()->make(ProductTypeRegistry::class)->get($productType);
                $capabilities = $typeHandler->getCapabilities();
            }
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] resolve product capabilities failed: ' . $e->getMessage());
        }
        $isVirtual = (($capabilities['order']['virtual'] ?? false) === true);
        // 状态映射图标（iconfont 项目 993865 中真实存在的图标，勿填不存在的字形名）
        $statusIcons = [
            'pending_payment' => 'icon-daifukuan',
            'processing'      => 'icon-daishouhuo',
            'completed'       => 'icon-yiwancheng',
            'canceled'        => 'icon-yiquxiao',
            'after_sale'      => 'icon-a-shouhoutuikuan',
            'refunding'       => 'icon-tuikuanzhong',
            'refunded'        => 'icon-yituikuan',
        ];
        $statusCode = $detail['status_code'] ?? '';
        $statusText = $detail['status_text'] ?? '';
        $businessStatus = $detail['business_status'] ?? [];

        // 状态对象
        $status = [
            'code'           => $statusCode,
            'text'           => $statusText,
            'icon'           => $statusIcons[$statusCode] ?? 'icon-daishouhuo',
            'subtitle'       => $businessStatus['text'] ?? '',
            'source_status'  => $detail['source_status'] ?? '',
            'business_status'=> $businessStatus,
            'pay_status'     => $detail['pay_status'] ?? 0,
            'refund_status'  => $detail['refund_status'] ?? 0,
            'progress'       => $detail['progress'] ?? ['current' => 0, 'status' => $statusCode, 'nodes' => []],
        ];

        // 商品对象
        $product = [
            'title'     => $detail['title'] ?? '',
            'image'     => $detail['image'] ?? '',
            'subtitle'  => $detail['subtitle'] ?? '',
            'total_num' => $detail['total_num'] ?? 0,
            'items'     => $detail['order_items'] ?? [],
        ];

        // 价格对象（含明细数组）
        $pricingData = $this->buildPricingItems($detail);
        $pricing = [
            'items'            => $pricingData['items'],
            'pay_price'        => $pricingData['pay_price'],
            'total_price'      => $detail['total_price'] ?? '0.00',
            'pay_postage'      => $detail['pay_postage'] ?? '0.00',
            'refund_price'     => $detail['refund_price'] ?? '0.00',
            'coupon_price'     => $detail['coupon_price'] ?? '0.00',
            'deduction_price'  => $detail['deduction_price'] ?? '0.00',
            'use_integral_num' => $detail['use_integral_num'] ?? 0,
            'member_discount'  => $detail['member_discount'] ?? '0.00',
            'vip_discount'     => $detail['vip_discount'] ?? '0.00',
            'gift_price'       => $detail['gift_price'] ?? '0.00',
        ];

        // 订单信息对象
        $orderInfo = [
            'id'              => $detail['id'] ?? 0,
            'order_no'        => $detail['order_no'] ?? '',
            'uid'             => $detail['uid'] ?? 0,
            'order_category'  => $detail['order_category'] ?? 'goods',
            'product_type'    => $detail['product_type'] ?? '',
            'activity_type'   => $detail['activity_type'] ?? '',
            'activity_id'     => $detail['activity_id'] ?? 0,
            'source_type'     => $detail['source_type'] ?? '',
            'source_id'       => $detail['source_id'] ?? 0,
            'pay_type'        => $detail['pay_type'] ?? '',
            'pay_type_name'   => $detail['pay_type_name'] ?? '',
            'mark'            => $detail['mark'] ?? '',
            'remark'          => $detail['remark'] ?? '',
            'add_time'        => $detail['add_time'] ?? 0,
            'pay_time'        => $detail['pay_time'] ?? 0,
            'delivery_time'   => $detail['delivery_time'] ?? 0,
            'take_time'       => $detail['take_time'] ?? 0,
            'refund_time'     => $detail['refund_time'] ?? 0,
            'cancel_time'     => $detail['cancel_time'] ?? 0,
            'is_del'          => $detail['is_del'] ?? 0,
            'detail_route'    => $detail['detail_route'] ?? '',
            'admin_detail_url'=> $detail['admin_detail_url'] ?? '',
            'activity_detail_url' => $detail['activity_detail_url'] ?? '',
        ];

        // 时间线
        $timeline = $detail['timeline'] ?? [];

        // 操作按钮
        $buttons = $detail['order_buttons'] ?? [];

        // 配送对象（仅实物商品）
        $delivery = null;
        if ($typeHandler && (($capabilities['delivery']['express'] ?? false)
            || ($capabilities['delivery']['pickup'] ?? false)
            || ($capabilities['delivery']['door_service'] ?? false))) {
            $delivery = [
                'shipping_type'    => $detail['shipping_type'] ?? 1,
                'is_pickup'        => $detail['is_pickup'] ?? false,
                'is_door_service'  => $detail['is_door_service'] ?? false,
                'store_id'         => $detail['store_id'] ?? 0,
            ];
            if (isset($detail['contact'])) {
                $delivery['contact'] = $detail['contact'];
            }
            if (isset($detail['user_address'])) {
                $delivery['user_address'] = $detail['user_address'];
            }
            if (isset($detail['store'])) {
                $delivery['store'] = $detail['store'];
            }
            // 上门服务订单：预约信息（联系人/电话/地址/预约时间）
            if (isset($detail['service'])) {
                $delivery['service'] = $detail['service'];
            }
        }

        // 核销对象（有核销码的订单：自提订单 或 已支付的非自提订单如酒店预订）
        $verification = null;
        if (!empty($detail['verify_code'])) {
            $isPickupOrder = ($detail['is_pickup'] ?? false);
            $isPaid = (int)($detail['pay_status'] ?? 0) === 1;
            // 自提订单：无论支付状态都展示核销码；非自提订单（如酒店预订）：仅已支付时展示
            if ($isPickupOrder || $isPaid) {
                $verification = [
                    'code'       => $detail['verify_code'],
                    'qrcode_url' => '/api/qrcode?text=' . urlencode($detail['verify_code']),
                    'status'     => $statusCode === 'completed' ? 'verified' : 'pending',
                    'verified_time' => $detail['take_time'] ?? 0,
                ];
            }
        }

        // 倒计时（待支付订单）
        $countdown = null;
        if ($statusCode === 'pending_payment') {
            $cancelHours = (int)sys_config('order_cancel_time', 0);
            if ($cancelHours > 0) {
                $endTime = (int)($detail['add_time'] ?? time()) + $cancelHours * 3600;
                $remaining = max(0, $endTime - time());
                $countdown = [
                    'end_time'  => $endTime,
                    'remaining' => $remaining,
                    'text'      => '请在 ' . gmdate('i:s', $remaining) . ' 内完成支付，超时订单将自动取消',
                ];
            }
        }

        // 提示条
        $notice = $this->buildNotice($statusCode, $detail);

        // 关联订单
        $relatedOrders = $this->buildRelatedOrders($detail);

        // 支付详情（已支付订单才组装）
        // trade_no/pay_method 优先从 extra_info 读取，缺失时留空
        $payment = null;
        if ((int)($detail['pay_status'] ?? 0) === 1) {
            $extraInfo = $detail['extra_info'] ?? [];
            $payment = [
                'trade_no'   => $extraInfo['trade_no'] ?? '',
                'pay_time'   => $detail['pay_time'] ?? 0,
                'pay_method' => $extraInfo['pay_method'] ?? '',
                'pay_price'  => $detail['pay_price'] ?? '0.00',
            ];
        }

        // 扩展模块容器
        $extensions = [];

        // 自定义表单
        if (!empty($detail['custom_form'])) {
            $extensions['custom_form'] = $detail['custom_form'];
        }
        // 发票
        if (!empty($detail['invoice'])) {
            $extensions['invoice'] = $detail['invoice'];
        }
        // 上门服务订单：预约信息（供 service_appointment section 渲染）
        if (!empty($detail['service'])) {
            $extensions['service'] = $detail['service'];
        }
        // 事件注入的扩展数据（extend → extensions）
        if (!empty($detail['extend'])) {
            foreach ($detail['extend'] as $key => $value) {
                $extensions[$key] = $value;
            }
        }
        // 原始 extra_info（供深度定制用）
        $extensions['extra_info'] = $detail['extra_info'] ?? [];

        // 返回分组结构：仅输出有值的分组，空分组（null/空数组）不下发，避免响应占位
        $result = [
            'status'     => $status,
            'product'    => $product,
            'pricing'    => $pricing,
            'order_info' => $orderInfo,
            'extensions' => $extensions,
        ];
        if (!empty($buttons)) {
            $result['buttons'] = $buttons;
        }
        if (!empty($notice)) {
            $result['notice'] = $notice;
        }
        if ($delivery !== null) {
            $result['delivery'] = $delivery;
        }
        if (!empty($timeline)) {
            $result['timeline'] = $timeline;
        }
        if ($countdown !== null) {
            $result['countdown'] = $countdown;
        }
        if ($verification !== null) {
            $result['verification'] = $verification;
        }
        if (!empty($relatedOrders)) {
            $result['related_orders'] = $relatedOrders;
        }
        if ($payment !== null) {
            $result['payment'] = $payment;
        }

        return $result;
    }

    /**
     * 从发票申请表加载订单发票信息
     * 查询 eb_order_invoice 表，组装发票抬头和开票状态
     * @param int $orderCenterId 订单中台ID（eb_order_center.id）
     * @return array|null 发票信息数组，无申请记录返回 null
     */
    protected function loadOrderInvoice(int $orderCenterId): ?array
    {
        try {
            /** @var OrderInvoiceDao $invoiceDao */
            $invoiceDao = app()->make(OrderInvoiceDao::class);
            $invoice = $invoiceDao->getOne(['order_id' => $orderCenterId, 'is_del' => 0]);
            if (!$invoice) {
                return null;
            }
            $invoice = is_object($invoice) ? $invoice->toArray() : $invoice;
            return [
                'title'          => $invoice['name'] ?? '',           // 发票抬头名称
                'header_type'    => (int)($invoice['header_type'] ?? 1), // 1=个人 2=企业
                'type'           => (int)($invoice['type'] ?? 1),       // 1=电子发票 2=纸质发票
                'tax_number'     => $invoice['duty_number'] ?? '',     // 税号（企业抬头才有）
                'email'          => $invoice['email'] ?? '',           // 接收邮箱
                'phone'          => $invoice['tell'] ?? '',            // 联系电话
                'address'        => $invoice['address'] ?? '',         // 地址
                'bank'           => $invoice['bank'] ?? '',            // 开户银行
                'bank_account'   => $invoice['card_number'] ?? '',     // 银行账号
                'is_invoice'     => (int)($invoice['is_invoice'] ?? 0),   // 0=未开票 1=已开票
                'invoice_number' => $invoice['invoice_number'] ?? '',  // 发票号码（已开票时才有）
                'invoice_time'   => (int)($invoice['invoice_time'] ?? 0), // 开票时间
                'is_refund'      => (int)($invoice['is_refund'] ?? 0),    // 0=正常 1=已红冲退款
            ];
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] 加载发票信息失败: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 规范化订单详情发票字段
     * 兼容中台历史订单保存的 name/duty_number 字段，并统一类型，供各端详情页使用。
     * @param mixed $invoice 发票信息
     * @return array
     */
    protected function normalizeInvoice($invoice): array
    {
        if (!is_array($invoice)) {
            return [];
        }

        $title = trim((string)($invoice['title'] ?? ''));
        if ($title === '') {
            $title = trim((string)($invoice['name'] ?? ''));
        }
        $taxNumber = trim((string)($invoice['tax_number'] ?? ''));
        if ($taxNumber === '') {
            $taxNumber = trim((string)($invoice['duty_number'] ?? ''));
        }

        return array_merge($invoice, [
            'title'        => $title,
            'header_type'  => (int)($invoice['header_type'] ?? 1),
            'type'         => (int)($invoice['type'] ?? 1),
            'tax_number'   => $taxNumber,
            'is_invoice'   => (int)($invoice['is_invoice'] ?? 0),
            'invoice_time' => (int)($invoice['invoice_time'] ?? 0),
        ]);
    }

    /**
     * 按订单状态生成运营提示条
     * @param string $statusCode 订单状态码
     * @param array $detail 订单详情
     * @return array 返回提示条数组
     */
    protected function buildNotice(string $statusCode, array $detail): array
    {
        $notices = [];
        switch ($statusCode) {
            case 'pending_payment':
                $notices[] = ['type' => 'warning', 'text' => '请尽快完成支付，超时订单将自动取消'];
                break;
            case 'processing':
                if (!empty($detail['delivery_time'])) {
                    $notices[] = ['type' => 'info', 'text' => '您的订单已发货，请留意收货'];
                }
                $notices[] = ['type' => 'info', 'text' => '签收后请在7天内确认收货'];
                break;
            case 'completed':
                $notices[] = ['type' => 'info', 'text' => '订单已完成，感谢您的购买'];
                break;
            case 'after_sale':
            case 'refunding':
                $notices[] = ['type' => 'warning', 'text' => '退款处理中，请耐心等待'];
                break;
            case 'refunded':
                $notices[] = ['type' => 'info', 'text' => '退款已完成'];
                break;
            case 'canceled':
                $notices[] = ['type' => 'info', 'text' => '订单已取消'];
                break;
        }
        return $notices;
    }

    /**
     * 查询关联订单（拆单/换货）
     * @param array $detail 订单详情
     * @return array|null 返回关联订单数组或null
     */
    protected function buildRelatedOrders(array $detail): ?array
    {
        // 当前暂无拆单/换货功能，返回 null
        // 后续实现拆单时，按 order_no 前缀或 parent_order_no 查询关联订单
        return null;
    }

    /**
     * 获取带声明式渲染配置的订单详情
     * v8.1 最终形态：接口只返回 page_config，不再返回 data 分组
     * - page_config.sections：声明式渲染清单，props 直填渲染值（前端零解析）
     *   由中台基础 section + 插件 extend_sections 自声明 section 组成
     * - page_config.extensions：扩展数据（含 extra_info 深度定制数据）
     * @param int $id 订单中台记录ID
     * @return array 返回 {page_config: {sections: [...], extensions: {...}}}
     */
    public function getDetailWithConfig(int $id): array
    {
        // 1. 获取扁平详情数据（复用现有 getDetail 逻辑）
        $flatDetail = $this->getDetail($id);

        // 2. 组装分组数据（内部使用：buildDefaultPageConfig 依赖全量分组生成 sections）
        $data = $this->buildGroupedData($flatDetail);

        // 3. 组装 page_config：中台基础 section + 插件自声明 section（extend_sections）
        $pageConfig = $this->buildDefaultPageConfig($data, $flatDetail['extend_sections'] ?? []);

        // 4. 只下发可见 section（visible=true）
        // 业态通用配置（隐藏项）不随接口下发，前端无需再过滤，显著减小响应体积
        $pageConfig['sections'] = array_values(array_filter($pageConfig['sections'] ?? [], function ($s) {
            return ($s['visible'] ?? false) === true;
        }));

        // 5. 最终返回：page_config = {sections, extensions}
        // extensions 含 extra_info（深度定制数据源）及事件注入的扩展数据
        $result = [
            'page_config' => [
                'sections'   => $pageConfig['sections'],
                'extensions' => $data['extensions'] ?? [],
            ],
        ];

        // 透传扩展模块注入的 redirect 指令（如拼团未成团时跳转拼团进度页）
        if (!empty($flatDetail['redirect'])) {
            $result['redirect'] = $flatDetail['redirect'];
        }

        return $result;
    }

    /**
     * 根据订单号获取带声明式渲染配置的订单详情
     * @param string $orderNo 订单号
     * @return array 返回 {page_config, data}
     * @throws \RuntimeException 订单不存在时抛出异常
     */
    public function getDetailWithConfigByOrderNo(string $orderNo): array
    {
        $detail = $this->dao->get(['order_no' => $orderNo], ['id']);
        if (!$detail) {
            throw new \RuntimeException('订单不存在');
        }
        return $this->getDetailWithConfig((int)$detail['id']);
    }

    /**
     * 加载商品明细
     * 统一从 extra_info.items 快照读取（旧 eb_store_order_cart_info 表已删除）
     * @param array $detail 订单详情数据
     * @return array 返回商品明细列表
     */
    public function loadOrderItems(array $detail): array
    {
        if (!empty($detail['extra_info']['items'])) {
            return $detail['extra_info']['items'];
        }
        return [];
    }

    /**
     * 获取订单商品快照列表（中台化读取基础方法）
     * 支持中台主键或订单号两种标识符，优先按 order_no 匹配（订单号含字母前缀，不与自增主键冲突）
     * @param int|string $id 中台订单主键或订单号
     * @return array extra_info.items 商品快照列表
     */
    protected function getOrderCartItems($id): array
    {
        if (empty($id)) return [];
        $order = $this->dao->get(['order_no' => (string)$id]);
        if (!$order) {
            $order = $this->dao->get(['id' => (int)$id]);
        }
        if (!$order) return [];
        $detail = $order->toArray();
        $detail['extra_info'] = $this->parseExtraInfo($detail['extra_info'] ?? null);
        return $this->loadOrderItems($detail);
    }

    /**
     * 获取订单商品详情（中台化读取）
     * 基于 extra_info.items 快照构建，兼容旧订单购物车明细 getOrderCartInfo 返回结构
     * [cart_id => ['cart_info' => [...]]]，缺失字段（costPrice/price_type 等）由调用方兜底
     * @param int|string $id 中台订单主键或订单号
     * @return array
     */
    public function getOrderCartInfo($id): array
    {
        $info = [];
        foreach ($this->getOrderCartItems($id) as $item) {
            $productInfo = $item['attr_info'] ?? [];
            $productInfo['store_name'] = $item['store_name'] ?? '';
            $productInfo['image'] = $item['image'] ?? '';
            $productInfo['price'] = $item['price'] ?? '0.00';
            $info[$item['cart_id'] ?? 0] = [
                'cart_info' => [
                    'id'          => $item['cart_id'] ?? 0,
                    'product_id'  => $item['product_id'] ?? 0,
                    'cart_num'    => $item['cart_num'] ?? 1,
                    'truePrice'   => $item['true_price'] ?? ($item['price'] ?? '0.00'),
                    'productInfo' => get_thumb_water($productInfo),
                ],
            ];
        }
        return $info;
    }

    /**
     * 获取订单商品标题（中台化读取）
     * 基于 extra_info.items 拼接商品名称，兼容旧 getCarIdByProductTitle 输出格式
     * @param int|string $id 中台订单主键或订单号
     * @param bool $goodsNum 是否附带购买数量
     * @return string
     */
    public function getCarIdByProductTitle($id, bool $goodsNum = false): string
    {
        $title = '';
        foreach ($this->getOrderCartItems($id) as $item) {
            if (empty($item['store_name'])) continue;
            if ($goodsNum) {
                $title .= $item['store_name'] . ' * ' . ($item['cart_num'] ?? 1) . ' | ';
            } else {
                $title .= $item['store_name'] . '|';
            }
        }
        return $title ? substr($title, 0, strlen($title) - 1) : '';
    }

    /**
     * 获取订单商品ID列表（中台化读取）
     * 基于 extra_info.items 提取 product_id，替代旧 getColumn(['oid'=>...], 'product_id')
     * @param int|string $id 中台订单主键或订单号
     * @return array
     */
    public function getCartProductIds($id): array
    {
        return array_values(array_unique(array_filter(array_column($this->getOrderCartItems($id), 'product_id'))));
    }

    /**
     * 从扩展订单表加载补充信息并重新推导状态码
     * 通过状态注册器从扩展订单表实时读取数据，确保订单状态显示正确
     * @param array $detail 订单详情数据（引用传递）
     */
    protected function loadSourceOrderInfo(array &$detail): void
    {
        $sourceType = $detail['source_type'] ?? '';
        $sourceId = $detail['source_id'] ?? 0;

        if (empty($sourceType) || $sourceId <= 0) {
            return;
        }

        $adapter = $this->resolveOrderAdapter($sourceType);
        if (!$adapter) {
            return;
        }

        try {
            $sourceOrder = $this->getSourceOrderData($sourceType, $sourceId);

            if ($sourceOrder) {
                $centerStatus = (string)($detail['status_code'] ?? '');
                $sourceStatus = $adapter->resolveStatusCode($sourceOrder);
                if (!$this->isAuthoritativeCenterStatus($centerStatus) || $sourceStatus === $centerStatus) {
                    $detail['status_code'] = $sourceStatus;
                    $detail['business_status'] = $adapter->resolveBusinessStatus($sourceOrder);
                }
                // 将扩展表状态透传到详情，供按钮、进度条按子状态差异化处理
                $detail['source_status'] = $sourceOrder['status'] ?? '';

                // 配送方式/核销码已归属中台表实体列，直接从 $detail 读取
                // 扩展表不再需要回退这些字段

                // 从扩展表提取物流配送信息（delivery_name/delivery_id/delivery_type）
                // 这些字段仅存在于扩展表 eb_pt_normal_order，中台表无对应实体列
                if (!empty($sourceOrder['delivery_name'])) {
                    $detail['delivery_name'] = $sourceOrder['delivery_name'];
                }
                if (!empty($sourceOrder['delivery_id'])) {
                    $detail['delivery_id'] = $sourceOrder['delivery_id'];
                }
                if (!empty($sourceOrder['delivery_type'])) {
                    $detail['delivery_type'] = $sourceOrder['delivery_type'];
                }

                // 同步出票/核销时间到详情（中台表无 send_time 列，供门票进度条"已出票"节点取时间）
                if (!empty($sourceOrder['send_time'])) {
                    $detail['send_time'] = (int)$sourceOrder['send_time'];
                }

                // 旧订单的收货地址存在 extension 表（real_name/user_phone/user_address），
                // 新订单已写入 extra_info.contact；如果 extra_info 中无 contact，从 source 回退
                if (!isset($detail['extra_info']['contact']) && !empty($sourceOrder['real_name'])) {
                    $detail['contact'] = [
                        'name'    => $sourceOrder['real_name'],
                        'phone'   => $sourceOrder['user_phone'] ?? '',
                        'address' => $sourceOrder['user_address'] ?? '',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] loadSourceOrderInfo failed: ' . $e->getMessage());
        }
    }

    protected function isAuthoritativeCenterStatus(string $statusCode): bool
    {
        return in_array($statusCode, [
            OrderCenter::STATUS_COMPLETED,
            OrderCenter::STATUS_CANCELED,
            OrderCenter::STATUS_AFTER_SALE,
            OrderCenter::STATUS_REFUNDING,
            OrderCenter::STATUS_REFUNDED,
            OrderCenter::STATUS_DELETED,
        ], true);
    }

    /**
     * 加载售后进度扩展数据
     * 查询订单最新售后单，构建含 type_text/status_text/timeline 的摘要，注入 extend.after_sale
     * 前端 AfterSaleProgress 组件据此渲染，无需硬编码售后类型映射表
     * @param array $detail 订单详情数据（引用传递）
     * @return void
     */
    protected function loadAfterSaleExtension(array &$detail): void
    {
        $orderNo = $detail['order_no'] ?? '';
        if (empty($orderNo)) {
            return;
        }

        try {
            // 查询订单最新售后单号
            /** @var AfterSaleDao $afterSaleDao */
            $afterSaleDao = $this->app->make(AfterSaleDao::class);
            $afterSaleNo = $afterSaleDao->getLatestNoByOrderNo($orderNo);
            if (empty($afterSaleNo)) {
                return;
            }

            // 调用售后服务获取完整详情（含 type_text/status_text/timeline）
            /** @var AfterSaleServices $afterSaleServices */
            $afterSaleServices = $this->app->make(AfterSaleServices::class);
            $afterSaleDetail = $afterSaleServices->getDetail($afterSaleNo);
            if (empty($afterSaleDetail)) {
                return;
            }

            // timeline 格式转换：后端 {key,label,time,done} → 前端 {title,time,desc}
            // 仅保留已发生的节点（time > 0），过滤掉未发生的"待处理"节点
            // 订单记录时间轴只展示已发生的事件，避免未发生节点污染时间顺序
            $rawTimeline = $afterSaleDetail['timeline'] ?? [];
            $timeline = [];
            foreach ($rawTimeline as $node) {
                $ts = (int)($node['time'] ?? 0);
                // 时间戳为 0 表示该节点尚未发生，不纳入订单记录时间轴
                if ($ts <= 0) {
                    continue;
                }
                $timeline[] = [
                    'title' => $node['label'] ?? '',
                    'time'  => date('Y-m-d H:i', $ts),
                    'desc'  => '',
                ];
            }

            // 仅提取详情页展示所需字段，避免下发冗余数据
            $detail['extend']['after_sale'] = [
                'after_sale_no' => $afterSaleDetail['after_sale_no'] ?? '',
                'type'          => $afterSaleDetail['type'] ?? '',
                'type_text'     => $afterSaleDetail['type_text'] ?? '',
                'status'        => $afterSaleDetail['status'] ?? '',
                'status_text'   => $afterSaleDetail['status_text'] ?? '',
                'amount'        => $afterSaleDetail['refund_price'] ?? 0,
                'reason'        => $afterSaleDetail['reason'] ?? '',
                'timeline'      => $timeline,
            ];
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] loadAfterSaleExtension failed: ' . $e->getMessage());
        }
    }

    /**
     * 加载物流轨迹扩展数据
     * 已发货且填了快递单号的订单，调用物流查询驱动获取 tracks，注入 extend.logistics
     * 前端 LogisticsTimeline 组件据此渲染物流轨迹卡片，用户无需点"查看物流"单独查询
     * 数据流：本方法写入 $detail['extend']['logistics']
     *        → buildGroupedData() 合并到 extensions['logistics']
     *        → page_config 中 component='timeline' + key='logistics' → 复合 key 'timeline:logistics'
     *        → 前端 LogisticsTimeline 渲染 delivery_name/delivery_id + OrderTimeline 遍历 tracks
     * @param array $detail 订单详情数据（引用传递）
     * @return void
     */
    protected function loadLogisticsExtension(array &$detail): void
    {
        // 仅处理有快递单号的订单（已发货且填写了物流单号）
        $deliveryId = (string)($detail['delivery_id'] ?? '');
        if ($deliveryId === '') {
            return;
        }

        // 到店自提 / 上门服务订单无物流轨迹，仅处理快递配送
        $deliveryType = (string)($detail['delivery_type'] ?? '');
        if ($deliveryType !== '' && $deliveryType !== 'express') {
            return;
        }

        $orderNo      = (string)($detail['order_no'] ?? '');
        $deliveryName = (string)($detail['delivery_name'] ?? '');
        // delivery_code 传空字符串而非 null：一号通驱动 query() 第二参数声明为 string，null 会触发 TypeError
        $deliveryCode = (string)($detail['delivery_code'] ?? '');

        // 物流轨迹查询（失败时 tracks 置空，仍展示快递公司 + 单号，不阻断详情渲染）
        $tracks = [];
        try {
            // 收件人手机号（顺丰等物流查询需要）
            $phone   = '';
            $contact = $detail['contact'] ?? ($detail['extra_info']['contact'] ?? []);
            if (is_array($contact) && !empty($contact['phone'])) {
                $phone = (string)$contact['phone'];
            }

            // 缓存键名：订单号 + 快递单号（ExpressServices 内部按此缓存，已签收缓存 0 秒，未签收缓存 1800 秒）
            $cacheName = $orderNo . $deliveryId;

            /** @var ExpressServices $expressServices */
            $expressServices = $this->app->make(ExpressServices::class);
            $rawTracks = $expressServices->query($cacheName, $deliveryId, $deliveryCode, $phone);

            // 归一化轨迹字段为前端统一格式 {title, time, desc}
            // 驱动返回格式差异：一号通 {time,status} / 阿里云 {time,context} / 其他兼容
            foreach ($rawTracks ?? [] as $track) {
                if (!is_array($track)) {
                    continue;
                }
                $tracks[] = [
                    'title' => (string)($track['title'] ?? $track['status'] ?? $track['context'] ?? $track['name'] ?? ''),
                    'time'  => (string)($track['time'] ?? $track['ftime'] ?? ''),
                    'desc'  => (string)($track['desc'] ?? ''),
                ];
            }
        } catch (\Throwable $e) {
            // 物流查询失败不阻断订单详情渲染（驱动未配置 / 网络异常等场景降级为仅展示快递公司+单号）
            Log::warning('[OrderCenter] loadLogisticsExtension failed: ' . $e->getMessage());
        }

        // 始终写入 extend.logistics：即使 tracks 查询失败，也展示快递公司 + 单号
        $detail['extend']['logistics'] = [
            'delivery_name' => $deliveryName,
            'delivery_id'   => $deliveryId,
            'tracks'        => $tracks,
        ];
    }

    /**
     * 加载订单完成态权益扩展数据（系统内置能力，非插件扩展）
     * 积分奖励：聚合本单积分入账流水（收货时由 OrderTakeServices 发放），有入账才展示；
     * 领券入口：订单完成后展示领券中心入口卡片
     * 前端 ActionCard 引擎对带 link 的入口统一跳转，无需前端硬编码 action 处理
     * @param array $detail 订单详情数据（引用传递）
     * @return void
     */
    protected function loadOrderRewardExtension(array &$detail): void
    {
        // 仅已完成订单展示完成态权益
        if (((string)($detail['status_code'] ?? '')) !== OrderCenter::STATUS_COMPLETED) {
            return;
        }

        // 领券入口：固定卡片，跳转领券中心
        $detail['extend']['coupon_gift'] = [
            'title' => '领取优惠券',
            'link'  => '/pages/users/user_get_coupon/index',
        ];

        // 积分奖励：查询本单积分入账流水，无入账不展示
        $uid = (int)($detail['uid'] ?? 0);
        $orderId = (int)($detail['id'] ?? 0);
        if ($uid <= 0 || $orderId <= 0) {
            return;
        }
        try {
            /** @var UserBillDao $userBillDao */
            $userBillDao = $this->app->make(UserBillDao::class);
            $integral = $userBillDao->getOrderGainIntegralSum($uid, $orderId);
            if ($integral > 0) {
                $detail['extend']['points_reward'] = [
                    'text' => '本单奖励 ' . (int)$integral . ' 积分',
                    'link' => '/pages/users/user_integral/index',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] loadOrderRewardExtension failed: ' . $e->getMessage());
        }
    }

    /**
     * 根据源类型获取扩展订单表数据
     * @param string $sourceType 源类型标识
     * @param int $sourceId 源订单ID
     * @return array|null 返回扩展订单数据或null
     */
    protected function getSourceOrderData(string $sourceType, int $sourceId): ?array
    {
        $adapter = $this->resolveOrderAdapter($sourceType);
        if (!$adapter) {
            return null;
        }

        /** @var \crmeb\basic\BaseModel $model */
        $model = $this->app->make($adapter::getModelClass());
        $sourceOrder = $model->where('id', $sourceId)->find();

        return $sourceOrder ? $sourceOrder->toArray() : null;
    }

    /**
     * 将自定义表单数据从对象格式转换为数组格式
     * 兼容两种格式：对象格式 {"custom_field_0": "value"} 和数组格式 [{"label": "..."}]
     * @param mixed $customForm 自定义表单数据
     * @return array 返回数组格式的自定义表单数据
     */
    protected function transformCustomForm($customForm): array
    {
        if (!is_array($customForm)) {
            return [];
        }

        if (isset($customForm[0]) && is_array($customForm[0])) {
            return $customForm;
        }

        $result = [];
        foreach ($customForm as $key => $value) {
            $label = $key;
            $title = $key;
            
            if (strpos($key, 'custom_field_') === 0) {
                $index = (int) str_replace('custom_field_', '', $key);
                $title = '自定义字段' . ($index + 1);
            }

            $result[] = [
                'label' => $label,
                'title' => $title,
                'value' => $value,
            ];
        }

        return $result;
    }

    /**
     * 构建订单时间线
     * 从 order_log 订单状态日志表读取，按实际发生的每一步状态变更生成时间线
     * @param array $detail 订单详情数据
     * @return array 返回时间线数组（最新事件在前）
     */
    protected function buildTimeline(array $detail): array
    {
        $timeline = [];

        // 从订单号读取订单状态日志
        $orderNo = (string)($detail['order_no'] ?? '');
        if ($orderNo === '') {
            return $timeline;
        }

        try {
            /** @var \app\dao\order\OrderLogDao $logDao */
            $logDao = app()->make(\app\dao\order\OrderLogDao::class);
            $records = $logDao->getLogList(['order_no' => $orderNo]);

            foreach ($records as $record) {
                $fromStatus = $record['from_status'] ?? '';
                $toStatus = $record['to_status'] ?? '';
                $content = $record['content'] ?? '';
                $addTime = (int)($record['add_time'] ?? 0);

                if ($addTime <= 0) {
                    continue;
                }

                // 状态未变化时（如发货、改价、删除），使用content作为标题
                // 状态变化时，使用状态码映射标题
                $title = ($fromStatus === $toStatus && $fromStatus !== '')
                    ? $content
                    : $this->mapStatusToTitle($toStatus, $content);

                $timeline[] = [
                    'title' => $title,
                    'desc'  => $content,
                    'time'  => date('Y-m-d H:i:s', $addTime),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] buildTimeline failed: ' . $e->getMessage());
        }

        // 时间线倒序排列，最新事件在前
        return array_reverse($timeline);
    }

    /**
     * 将订单状态码映射为时间线标题
     * @param string $toStatus 变更后状态码
     * @param string $content 操作说明（作为回退标题）
     * @return string 返回标题
     */
    protected function mapStatusToTitle(string $toStatus, string $content): string
    {
        $statusMap = [
            OrderCenter::STATUS_PENDING_PAYMENT => '提交订单',
            OrderCenter::STATUS_PROCESSING      => '订单处理中',
            OrderCenter::STATUS_PENDING_REVIEW  => '待评价',
            OrderCenter::STATUS_COMPLETED       => '订单完成',
            OrderCenter::STATUS_CANCELED        => '订单取消',
            OrderCenter::STATUS_AFTER_SALE      => '售后处理中',
            OrderCenter::STATUS_REFUNDING       => '售后处理中',
            OrderCenter::STATUS_REFUNDED        => '退款完成',
        ];

        return $statusMap[$toStatus] ?? $content;
    }

    /**
     * 获取订单操作按钮
     * 委托给订单类型适配器（OrderTypeRegistry），由各产品类型根据自身流程决定按钮
     * 注：调用 getOrderButtons 而非 getStatusButtons，由基类统一注入 order_no 到 params；
     * 同时为 viewAfterSale 按钮额外注入 after_sale_no（按订单号查最新售后单），避免前端跳转售后详情时缺参
     * @param array $detail 订单详情数据
     * @return array 返回按钮数组
     */
    protected function getOrderButtonsFromHandler(array $detail): array
    {
        $adapter = $this->resolveOrderAdapter((string)($detail['source_type'] ?? ''));
        if (!$adapter) {
            return [];
        }

        try {
            // 调用 getOrderButtons（基类已统一注入 order_no 到每个按钮的 params）
            $buttons = $adapter->getOrderButtons($detail);
        } catch (\Throwable $e) {
            Log::warning('getOrderButtonsFromHandler failed: ' . $e->getMessage());
            // 回退：返回空数组
            return [];
        }

        // 为 viewAfterSale 按钮补充 after_sale_no（按订单号查最新售后单）
        // 前端 viewAfterSale action 依赖 params.after_sale_no 才能跳转到售后详情页
        $orderNo = $detail['order_no'] ?? '';
        if ($orderNo) {
            foreach ($buttons as &$button) {
                if (($button['action'] ?? '') === 'viewAfterSale') {
                    $afterSaleNo = $this->resolveLatestAfterSaleNo($orderNo);
                    if ($afterSaleNo !== '') {
                        $button['params'] = array_merge($button['params'] ?? [], ['after_sale_no' => $afterSaleNo]);
                    }
                }
            }
            unset($button);
        }

        return $buttons;
    }

    /**
     * 查询订单最新售后单号
     * 用于 viewAfterSale 按钮参数注入，取订单最近一条售后单（不限状态，含已取消/已完成）
     * @param string $orderNo 订单号
     * @return string 返回售后单号，无售后记录返回空字符串
     */
    protected function resolveLatestAfterSaleNo(string $orderNo): string
    {
        try {
            /** @var AfterSaleDao $afterSaleDao */
            $afterSaleDao = $this->app->make(AfterSaleDao::class);
            return $afterSaleDao->getLatestNoByOrderNo($orderNo);
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] resolveLatestAfterSaleNo failed: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * 获取订单生命周期进度条
     * 委托给订单类型适配器（OrderTypeRegistry），不同订单类型节点不同
     * @param array $detail 订单详情数据
     * @return array 返回进度条结构
     */
    protected function getProgressFromHandler(array $detail): array
    {
        $adapter = $this->resolveOrderAdapter((string)($detail['source_type'] ?? ''));
        if (!$adapter) {
            return [];
        }

        try {
            return $adapter->getProgress($detail);
        } catch (\Throwable $e) {
            Log::warning('getProgressFromHandler failed: ' . $e->getMessage());
        }
        return [];
    }


    /**
     * 根据产品类型生成后台详情路由
     * 按约定生成 /admin/product/{product_type}/order/detail/{order_no}，
     * 新增产品类型只要遵循该约定即自动生效，无需修改中台代码
     * @param array $detail 订单详情数据
     * @return string 返回相对路径路由
     */
    protected function getAdminDetailUrl(array $detail): string
    {
        $type    = (string)($detail['product_type'] ?? '');
        $orderNo = (string)($detail['order_no'] ?? '');
        if ($type === '' || $orderNo === '') {
            return '';
        }

        return '/admin/product/' . $type . '/order/detail/' . $orderNo;
    }



    /**
     * 使用雪花算法生成业务唯一单号
     * 统一收口订单号生成，便于活动、充值、抽奖等非订单业务复用
     * @param string $prefix 单号前缀，如 wx、cz、hb
     * @return string 返回带前缀的唯一单号
     * @throws \Exception
     */
    public function getNewOrderId(string $prefix = 'wx'): string
    {
        return SnowflakeIdGenerator::generate($prefix);
    }

    /**
     * 生成提货核销码（12 位数字，中台表查重保证唯一）
     * 核销码生成（中台统一收口）
     * @return string
     */
    public function getStoreCode(): string
    {
        // 时间戳 + 随机数 + 微秒片段拼接生成 12 位数字串
        list($msec, $sec) = explode(' ', microtime());
        $num = time() + mt_rand(10, 999999) . '' . substr($msec, 2, 3);
        if (strlen($num) < 12) {
            $num = str_pad((string)$num, 12, '0', STR_PAD_RIGHT);
        } else {
            $num = substr((string)$num, 0, 12);
        }
        // 与中台已有核销码查重，冲突则递归重生成
        if ($this->dao->count(['verify_code' => $num])) {
            return $this->getStoreCode();
        }
        return $num;
    }

    /**
     * 订单自动好评（定时任务）
     * 收货后超过 system_comment_time 天未评价的订单，按商品维度自动生成五星好评，
     * 全部商品评价完成后订单由待评价流转为已完成
     * @return void
     */
    public function autoComment(): void
    {
        // 自动评价天数（0 或未配置表示关闭）
        $days = (int)sys_config('system_comment_time', 0);
        if ($days <= 0) {
            return;
        }
        $deadline = time() - $days * 86400;

        // 待评价订单：待评价状态 + 收货时间超过自动评价期限
        $orderList = $this->dao->getDb()
            ->where('status_code', OrderCenter::STATUS_PENDING_REVIEW)
            ->where('is_del', 0)
            ->where('take_time', '>', 0)
            ->where('take_time', '<=', $deadline)
            ->field('id,order_no,source_type,uid,extra_info,take_time')
            ->select()->toArray();
        if (empty($orderList)) {
            return;
        }

        /** @var StoreProductReplyServices $replyServices */
        $replyServices = app()->make(StoreProductReplyServices::class);
        // 自动好评默认文案（后台可配）
        $defaultComment = sys_config('comment_content', '此用户未做评价');

        foreach ($orderList as $order) {
            // extra_info 可能被模型 JSON 类型自动转为数组，兼容两种形态
            $extra = $order['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            $items = $extra['items'] ?? [];
            // 无商品快照的订单直接完成状态流转，避免卡在待评价
            if (empty($items)) {
                $this->completeOrderStatus($order);
                continue;
            }
            // 用户信息（评价展示昵称/头像）
            $user = User::where('uid', $order['uid'])->field('uid,nickname,avatar')->find();
            $allReplied = true;
            foreach ($items as $item) {
                $unique = $item['attr_info']['unique'] ?? '';
                $productId = (int)($item['product_id'] ?? 0);
                if (!$productId || !$unique) {
                    continue;
                }
                // 防重：同订单同 SKU 只评价一次（oid 为中台订单 id，unique 为 SKU 快照唯一标识）
                if ($replyServices->count(['oid' => $order['id'], 'unique' => $unique]) > 0) {
                    continue;
                }
                $data = [
                    'uid' => $order['uid'],
                    'nickname' => $user['nickname'] ?? '',
                    'avatar' => $user['avatar'] ?? '',
                    'oid' => $order['id'],
                    'product_id' => $productId,
                    'unique' => $unique,
                    'reply_type' => 'product',
                    'product_score' => 5,
                    'service_score' => 5,
                    'comment' => $defaultComment,
                    'pics' => '[]',
                    'suk' => $item['attr_info']['suk'] ?? '',
                    'add_time' => time(),
                    'status' => 1,
                ];
                if (!$replyServices->save($data)) {
                    $allReplied = false;
                    continue;
                }
                // 商品评价事件（商品评分统计等扩展点）
                event('Product', ['reply', [
                    'uid' => $order['uid'],
                    'product_id' => $productId,
                    'order_id' => $order['id'],
                    'star' => 5,
                    'content' => $defaultComment,
                ]]);
            }
            // 全部商品评价完成：待评价 → 已完成
            if ($allReplied) {
                $this->completeOrderStatus($order);
            }
        }
    }

    /**
     * 扫描超时未支付订单并入队提醒任务
     * 定时任务 noticeUnpaidOrder 调用：与下单后 10 分钟的队列提醒（UnpaidOrderSend::dispatchSecs）互补，
     * 本扫描只处理下单超过 20 分钟、尚未到自动取消期限的待支付订单，配合缓存防重避免重复打扰
     * @return int 本次入队提醒的订单数
     */
    public function scanUnpaidOrders(): int
    {
        // 自动取消期限（小时），0 或未配置按 24 小时兜底，避免扫描窗口无界
        $cancelHours = (int)sys_config('order_cancel_time', 0);
        if ($cancelHours <= 0) {
            $cancelHours = 24;
        }
        $now = time();
        // 20 分钟前下单的才扫，避开下单后 10 分钟的队列提醒时间点
        $newest = $now - 1200;
        $oldest = $now - $cancelHours * 3600;

        $orderList = $this->dao->getDb()
            ->where('status_code', OrderCenter::STATUS_PENDING_PAYMENT)
            ->where('pay_status', OrderCenter::PAY_UNPAID)
            ->where('is_del', 0)
            ->where('add_time', '>=', $oldest)
            ->where('add_time', '<=', $newest)
            ->field('id,order_no,uid,add_time')
            ->select()->toArray();
        if (empty($orderList)) {
            return 0;
        }

        $dispatched = 0;
        foreach ($orderList as $order) {
            // 缓存防重：同一订单在提醒间隔内只入队一次（间隔 2 小时）
            $cacheKey = 'unpaid_order_remind_' . md5((string)$order['order_no']);
            if (CacheService::get($cacheKey)) {
                continue;
            }
            CacheService::set($cacheKey, 1, 7200);
            UnpaidOrderSend::dispatch([(int)$order['id']]);
            $dispatched++;
        }
        return $dispatched;
    }

    /**
     * 首页头部统计数据
     * 返回销售额、订单量、新增用户数、商品访问量四组统计数据的今日/昨日值、日环比增长率及总计
     * @return array
     */
    public function homeStatics(): array
    {
        $todayStart = strtotime(date('Y-m-d'));
        $todayEnd = $todayStart + 86400;
        $yesterdayStart = $todayStart - 86400;
        $yesterdayEnd = $todayStart;
        $date = date('Y-m-d');

        // 1. 已支付订单统计（销售额 + 订单量）
        $todayOrderStats = $this->dao->getDb()
            ->where('pay_status', 1)
            ->whereBetween('pay_time', [$todayStart, $todayEnd])
            ->field('COUNT(*) as count, COALESCE(SUM(pay_price), 0) as amount')
            ->find();

        $yesterdayOrderStats = $this->dao->getDb()
            ->where('pay_status', 1)
            ->whereBetween('pay_time', [$yesterdayStart, $yesterdayEnd])
            ->field('COUNT(*) as count, COALESCE(SUM(pay_price), 0) as amount')
            ->find();

        $totalOrderStats = $this->dao->getDb()
            ->where('pay_status', 1)
            ->field('COUNT(*) as count, COALESCE(SUM(pay_price), 0) as amount')
            ->find();

        // 2. 新增用户统计
        $todayNewUser = User::whereBetween('add_time', [$todayStart, $todayEnd])->count();
        $yesterdayNewUser = User::whereBetween('add_time', [$yesterdayStart, $yesterdayEnd])->count();
        $totalUser = User::count();

        // 3. 商品访问量统计
        $todayVisits = StoreVisit::whereBetween('add_time', [$todayStart, $todayEnd])->count();
        $yesterdayVisits = StoreVisit::whereBetween('add_time', [$yesterdayStart, $yesterdayEnd])->count();
        $totalVisits = StoreVisit::count();

        // 组装数据
        $info = [
            [
                'title'       => '销售额',
                'date'        => $date,
                'today'       => (float)($todayOrderStats['amount'] ?? 0),
                'yesterday'   => (float)($yesterdayOrderStats['amount'] ?? 0),
                'today_ratio' => 0,
                'total_name'  => '总金额',
                'total'       => (float)($totalOrderStats['amount'] ?? 0),
            ],
            [
                'title'       => '订单量',
                'date'        => $date,
                'today'       => (int)($todayOrderStats['count'] ?? 0),
                'yesterday'   => (int)($yesterdayOrderStats['count'] ?? 0),
                'today_ratio' => 0,
                'total_name'  => '总订单数',
                'total'       => (int)($totalOrderStats['count'] ?? 0),
            ],
            [
                'title'       => '新增用户',
                'date'        => $date,
                'today'       => $todayNewUser,
                'yesterday'   => $yesterdayNewUser,
                'today_ratio' => 0,
                'total_name'  => '总用户数',
                'total'       => $totalUser,
            ],
            [
                'title'       => '商品访问量',
                'date'        => $date,
                'today'       => $todayVisits,
                'yesterday'   => $yesterdayVisits,
                'today_ratio' => 0,
                'total_name'  => '总访问量',
                'total'       => $totalVisits,
            ],
        ];

        // 计算日环比增长率
        foreach ($info as &$item) {
            $nowValue = $item['today'];
            $lastValue = $item['yesterday'];
            if ($lastValue == 0 && $nowValue == 0) {
                $item['today_ratio'] = 0;
            } elseif ($lastValue == 0) {
                $item['today_ratio'] = round($nowValue, 2);
            } elseif ($nowValue == 0) {
                $item['today_ratio'] = -round($lastValue, 2);
            } else {
                $item['today_ratio'] = (float)bcmul(bcdiv((string)($nowValue - $lastValue), (string)$lastValue, 4), '100', 2);
            }
        }
        unset($item);

        return $info;
    }

    /**
     * 订单图表
     * 根据周期统计已支付订单的订单量与销售额，用于后台首页图表展示
     * @param string $cycle 统计周期：thirtyday=近30天、month=近12月、year=近1年
     * @return array 返回图表数据（legend/yAxis/xAxis/series）
     */
    public function orderCharts(string $cycle): array
    {
        // 根据周期计算起始日期
        $starday = $cycle == 'thirtyday' ? date('Y-m-d', strtotime('-30 day')) : ($cycle == 'month' ? date('Y-m-d', strtotime('-12 month')) : date('Y-m-d', strtotime('-1 year')));
        $yesterday = date('Y-m-d', strtotime('+1 day'));
        $startTime = strtotime($starday);
        $endTime = strtotime($yesterday);

        $orderList = $this->dao->orderCharts($startTime, $endTime, $cycle);

        $chartdata = [];
        $data = [];
        $chartdata['legend'] = ['订单量', '销售额'];//分类
        $chartdata['yAxis']['maxnum'] = 0;//最大值数量
        $chartdata['xAxis'] = [$starday];//X轴值
        $chartdata['series'] = [0, 0];//分类1值
        if (!empty($orderList)) {
            foreach ($orderList as $v) {
                $data['day'][] = $v['day'];
                $data['count'][] = $v['count'];
                $data['price'][] = (float)$v['price'];
                if ($chartdata['yAxis']['maxnum'] < $v['count'])
                    $chartdata['yAxis']['maxnum'] = $v['count'];
            }
            $chartdata['xAxis'] = $data['day'];//X轴值
            $chartdata['series'][0] = $data['count'];//分类1值
            $chartdata['series'][1] = $data['price'];//分类2值
        }
        return $chartdata;
    }

    /**
     * 后台订单列表（带完整筛选和售后状态合并）
     * 支持多维度筛选和搜索，返回分页数据
     * @param array $where 查询条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array 返回分页数据（total、list）
     */
    public function getAdminList(array $where, int $page, int $limit): array
    {
        $searchWhere = [];

        // 通用：is_deleted=1 时为「已删除」tab，只查用户已删且后台未删的订单；
        // 常规 tab 用户删除与后台删除均不显示
        if (!empty($where['is_deleted'])) {
            $searchWhere[] = ['is_del', '=', 1];
            $searchWhere[] = ['is_system_del', '=', 0];
        } else {
            $searchWhere[] = ['is_del', '=', 0];
            $searchWhere[] = ['is_system_del', '=', 0];
        }

        // 订单大类筛选
        if (!empty($where['order_category'])) {
            $searchWhere[] = ['order_category', '=', $where['order_category']];
        }
        // 产品类型筛选
        if (!empty($where['product_type'])) {
            $searchWhere[] = ['product_type', '=', $where['product_type']];
        }
        // 活动类型筛选
        if (!empty($where['activity_type'])) {
            $searchWhere[] = ['activity_type', '=', $where['activity_type']];
        }
        // 统一状态码筛选
        if (!empty($where['status_code'])) {
            $searchWhere[] = ['status_code', '=', $where['status_code']];
        }
        // 支付方式筛选
        if (!empty($where['pay_type'])) {
            $searchWhere[] = ['pay_type', '=', $where['pay_type']];
        }
        // 用户ID筛选
        if (!empty($where['uid'])) {
            $searchWhere[] = ['uid', '=', $where['uid']];
        }
        // 关键词搜索（订单号、收货人姓名、联系电话，联系信息走 extra_info JSON 路径）
        if (!empty($where['keyword'])) {
            $searchWhere[] = function ($query) use ($where) {
                $keyword = addcslashes($where['keyword'], '%_');
                $query->whereOr('order_no', 'LIKE', "{$keyword}%")
                      ->whereOrRaw("extra_info->>'$.contact.name' LIKE ?", ["%{$keyword}%"])
                      ->whereOrRaw("extra_info->>'$.contact.phone' LIKE ?", ["%{$keyword}%"]);
            };
        }
        // 日期范围筛选
        if (!empty($where['date_range'])) {
            [$start, $end] = $this->parseDateRange($where['date_range']);
            $searchWhere[] = ['add_time', 'BETWEEN', [$start, $end]];
        }

        // 售后状态筛选：all=全部，processing=售后中，refunded=已退款完成，none=无售后
        $afterSaleFilter = $where['after_sale'] ?? '';

        // 扩展条件：闭包不能直接放入 where 数组（ThinkPHP 数组模式不解析闭包），需单独应用
        $extraConditions = $where['extra_conditions'] ?? [];

        // 查询字段
        $fields = 'id, order_no, source_type, source_id, uid, order_category, product_type,
             activity_type, status_code, pay_status, refund_status,
             total_price, pay_price, pay_postage, refund_price, pay_type,
             total_num, title, image, subtitle, shipping_type,
             extra_info,
             remark, detail_route,
             add_time, pay_time, delivery_time, take_time, refund_time, cancel_time, is_del, is_system_del';

        // 售后状态筛选：需要先按其他条件筛选出候选订单号，再按售后状态过滤，保证分页准确
        $afterSaleOrderNos = [];
        // all=所有有售后记录，processing=售后中，refunded=已退款完成，none=无售后
        $needAfterSaleFilter = in_array($afterSaleFilter, ['processing', 'refunded', 'none', 'all']);
        if ($needAfterSaleFilter) {
            $candidateQuery = $this->dao->getDb()->where($searchWhere);
            $orderNos = $where['_order_nos'] ?? [];
            if (!empty($orderNos)) {
                $candidateQuery->whereIn('order_no', $orderNos);
            }
            foreach ($extraConditions as $closure) {
                $candidateQuery->where($closure);
            }
            $candidateOrderNos = $candidateQuery->column('order_no');
            $matchedOrderNos = $this->getAfterSaleOrderNos($afterSaleFilter, $candidateOrderNos);

            if ($afterSaleFilter === 'none') {
                $afterSaleOrderNos = array_values(array_diff($candidateOrderNos, $matchedOrderNos));
            } else {
                $afterSaleOrderNos = $matchedOrderNos;
            }

            if (empty($afterSaleOrderNos)) {
                return ['total' => 0, 'list' => []];
            }
        }

        if (empty($extraConditions)) {
            // 无扩展条件：直接走 DAO 方法
            $finalWhere = $searchWhere;
            if (!empty($afterSaleOrderNos)) {
                $finalWhere[] = ['order_no', 'IN', $afterSaleOrderNos];
            }
            $list = $this->dao->selectList($finalWhere, $fields, $page, $limit, 'id DESC');
            $total = $this->dao->count($finalWhere);
        } else {
            // 有扩展条件：用 DAO 提供的查询构建器，避免直接 Db::table
            $query = $this->dao->getDb()->where($searchWhere);

            // 扩展表条件用两步查询（先查 order_no，再 IN），不 JOIN
            $orderNos = $where['_order_nos'] ?? [];
            if (!empty($orderNos)) {
                $query->whereIn('order_no', $orderNos);
            }
            if (!empty($afterSaleOrderNos)) {
                $query->whereIn('order_no', $afterSaleOrderNos);
            }

            foreach ($extraConditions as $closure) {
                $query->where($closure);
            }
            $list = $query->field($fields)->order('id DESC')->page($page, $limit)->select()->toArray();

            // count 需另起查询
            $countQuery = $this->dao->getDb()->where($searchWhere);
            if (!empty($orderNos)) {
                $countQuery->whereIn('order_no', $orderNos);
            }
            if (!empty($afterSaleOrderNos)) {
                $countQuery->whereIn('order_no', $afterSaleOrderNos);
            }
            foreach ($extraConditions as $closure) {
                $countQuery->where($closure);
            }
            $total = $countQuery->count();
        }

        // 转换状态码为中文文本
        foreach ($list as &$item) {
            // 订单状态
            $item['status_text'] = OrderCenter::$statusMap[$item['status_code']] ?? '未知';
            // 支付状态
            $item['pay_status_text'] = OrderCenter::$payStatusMap[$item['pay_status']] ?? '未知';
            // 退款状态
            $item['refund_status_text'] = OrderCenter::$refundStatusMap[$item['refund_status']] ?? '未知';
            // 支付方式
            $item['pay_type_name'] = $this->getPayTypeName($item['pay_type']);
            // 活动类型含义文案：normal 非活动显示空，其他从交易模式注册中心取标签，未注册的兜底原始值
            $activityType = (string)($item['activity_type'] ?? '');
            $item['activity_type_text'] = '';
            if ($activityType && $activityType !== 'normal') {
                try {
                    $tradeModeRegistry = app()->make(TradeModeRegistry::class);
                    $item['activity_type_text'] = $tradeModeRegistry->has($activityType)
                        ? $tradeModeRegistry->get($activityType)->getLabel()
                        : $activityType;
                } catch (\Throwable $e) {
                    $item['activity_type_text'] = $activityType;
                }
            }
            // 订单扩展信息
            $item['extra_info'] = $this->parseExtraInfo($item['extra_info']);
            $item['business_status'] = $item['extra_info']['business_status'] ?? null;
            // 提取联系人信息到顶层，供列表页展示
            $item['contact_name'] = $item['extra_info']['contact']['name'] ?? '';
            $item['contact_phone'] = $item['extra_info']['contact']['phone'] ?? '';
            // 提取地址到顶层
            $addr = $item['extra_info']['contact']['address'] ?? '';
            $item['user_address'] = is_array($addr)
                ? implode('', array_filter([
                    $addr['province'] ?? '',
                    $addr['city'] ?? '',
                    $addr['district'] ?? '',
                    $addr['detail'] ?? '',
                ]))
                : ($addr ?: '');
            unset($item['extra_info']['business_status']);
        }

        // 批量注入下单用户昵称/电话，供列表页"用户信息"列展示
        $uids = array_values(array_unique(array_filter(array_column($list, 'uid'))));
        if (!empty($uids)) {
            $userMap = User::where('uid', 'in', $uids)->column('nickname,phone', 'uid');
            foreach ($list as &$item) {
                $user = $userMap[$item['uid']] ?? [];
                $item['nickname'] = $user['nickname'] ?? '';
                $item['user_phone'] = $user['phone'] ?? '';
            }
            unset($item);
        }

        // 批量查询并合并售后状态到列表
        $orderNos = array_column($list, 'order_no');
        $afterSaleSummary = $this->getAfterSaleSummary($orderNos);
        foreach ($list as &$item) {
            $summary = $afterSaleSummary[$item['order_no']] ?? null;
            $item['after_sale'] = $summary ?: [
                'has_after_sale' => false,
                'count'          => 0,
                'types'          => [],
                'statuses'       => [],
                'is_processing'  => false,
            ];
            // 兼容旧字段，列表页可直接判断 refund_status / is_after_sale
            $item['is_after_sale'] = $item['after_sale']['has_after_sale'];
            $item['is_after_sale_processing'] = $item['after_sale']['is_processing'];
        }

        // 通过事件机制，允许扩展模块注入额外数据
        $eventData = ['list' => &$list];
        event('OrderCenterListExtend', $eventData);
        $list = $eventData['list'];

        return compact('total', 'list');
    }

    /**
     * 批量获取订单售后概要
     * @param array $orderNos 订单号数组
     * @return array 返回以 order_no 为键的售后概要
     */
    protected function getAfterSaleSummary(array $orderNos): array
    {
        if (empty($orderNos)) {
            return [];
        }

        try {
            /** @var AfterSaleDao $afterSaleDao */
            $afterSaleDao = $this->app->make(AfterSaleDao::class);
            return $afterSaleDao->getBatchSummary($orderNos);
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] 批量查询售后概要失败: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 根据售后状态获取订单号列表
     * @param string $mode 筛选模式：processing=售后中，refunded=已退款完成，none=无售后（此处返回有售后的订单号供 none 取差集）
     * @param array $candidateOrderNos 候选订单号范围
     * @return array 返回符合条件的订单号数组
     */
    protected function getAfterSaleOrderNos(string $mode, array $candidateOrderNos): array
    {
        if (empty($candidateOrderNos)) {
            return [];
        }

        try {
            /** @var AfterSaleDao $afterSaleDao */
            $afterSaleDao = $this->app->make(AfterSaleDao::class);
            return $afterSaleDao->getOrderNosByAfterSaleStatus($mode, $candidateOrderNos);
        } catch (\Throwable $e) {
            Log::warning('[OrderCenter] 按售后状态查询订单号失败: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 获取订单统计数据
     * 包含订单总数、金额统计、状态分布、分类统计，支持缓存
     * @param array $where 查询条件
     * @return array 返回统计数据
     */
    public function getStatistics(array $where): array
    {
        $cacheKey = $this->statCacheKey($where);
        $ttl = empty($where['date_range']) ? 300 : 60;

        return app()->cache->remember($cacheKey, function () use ($where) {
            $baseWhere = [['is_del', '=', 0], ['is_system_del', '=', 0]];
            // 按订单大类筛选
            if (!empty($where['order_category'])) {
                $baseWhere[] = ['order_category', '=', $where['order_category']];
            }
            // 按产品类型筛选
            if (!empty($where['product_type'])) {
                $baseWhere[] = ['product_type', '=', $where['product_type']];
            }
            // 按活动类型筛选
            if (!empty($where['activity_type'])) {
                $baseWhere[] = ['activity_type', '=', $where['activity_type']];
            }
            // 按日期范围筛选
            if (!empty($where['date_range'])) {
                [$start, $end] = $this->parseDateRange($where['date_range']);
                $baseWhere[] = ['add_time', 'BETWEEN', [$start, $end]];
            }
            // 按支付方式筛选
            if (!empty($where['pay_type'])) {
                $baseWhere[] = ['pay_type', '=', $where['pay_type']];
            }
            // 按关键词搜索（订单号、联系人姓名、联系电话）
            if (!empty($where['keyword'])) {
                $keyword = addcslashes($where['keyword'], '%_');
                $baseWhere[] = function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->whereOr('order_no', 'LIKE', "{$keyword}%")
                          ->whereOrRaw("extra_info->>'$.contact.name' LIKE ?", ["%{$keyword}%"])
                          ->whereOrRaw("extra_info->>'$.contact.phone' LIKE ?", ["%{$keyword}%"]);
                    });
                };
            }

            // 按状态统计订单数和金额
            $statusStats = $this->dao->getDb()
                ->where($baseWhere)
                ->field('status_code, COUNT(*) as count, SUM(pay_price) as amount')
                ->group('status_code')
                ->select()
                ->toArray();

            // 按分类统计订单数和金额
            $categoryStats = $this->dao->getDb()
                ->where($baseWhere)
                ->field('order_category, COUNT(*) as count, SUM(pay_price) as amount')
                ->group('order_category')
                ->select()
                ->toArray();

            // 统计总数和总金额
            $all = $this->dao->getDb()
                ->where($baseWhere)
                ->field('COUNT(*) as count, SUM(pay_price) as amount')
                ->find();

            // 「已删除」tab 计数：用户删除（is_del=1）且后台未删（is_system_del=0），与列表体口径一致，
            // 随大类/日期筛选联动
            $deletedWhere = [['is_del', '=', 1], ['is_system_del', '=', 0]];
            if (!empty($where['order_category'])) {
                $deletedWhere[] = ['order_category', '=', $where['order_category']];
            }
            if (!empty($where['date_range'])) {
                [$start, $end] = $this->parseDateRange($where['date_range']);
                $deletedWhere[] = ['add_time', 'BETWEEN', [$start, $end]];
            }
            $deletedCount = $this->dao->getDb()->where($deletedWhere)->count();

            // 补全所有状态码，缺值填0
            $statusCounts = [];
            foreach (OrderCenter::$statusMap as $code => $label) {
                $hit = array_values(array_filter($statusStats, fn($s) => $s['status_code'] === $code));
                $statusCounts[$code] = [
                    'count'  => (int) ($hit[0]['count'] ?? 0),
                    'amount' => (string) ($hit[0]['amount'] ?? '0.00'),
                ];
            }

            // 补全所有订单大类，缺值填0
            $categoryMap = [];
            foreach ($categoryStats as $row) {
                $categoryMap[$row['order_category']] = $row;
            }
            $categoryResult = [];
            foreach (OrderTypeRegistry::getCategoryMap() as $cat => $label) {
                $hit = $categoryMap[$cat] ?? null;
                $categoryResult[] = [
                    'order_category' => $cat,
                    'count'     => (int) ($hit['count'] ?? 0),
                    'amount'    => (string) ($hit['amount'] ?? '0.00'),
                ];
            }

            return [
                'all' => [
                    'count'  => (int) ($all['count'] ?? 0),
                    'amount' => (string) ($all['amount'] ?? '0.00'),
                ],
                'status_counts' => $statusCounts,
                'by_category'   => $categoryResult,
                'deleted_count' => $deletedCount,
            ];
        }, $ttl);
    }

    /**
     * 更新订单备注
     * @param int $id 订单中台记录ID
     * @param string $remark 备注内容
     * @return bool 返回是否更新成功
     */
    public function updateRemark(int $id, string $remark): bool
    {
        return $this->dao->update(['id' => $id], ['remark' => $remark]) !== false;
    }

    /**
     * 软删除订单（管理端）
     * 写 is_system_del 仅隐藏后台列表，不影响用户端；仅允许后台删除用户端已删除的订单
     * @param int $id 订单中台记录ID
     * @return bool 返回是否删除成功
     * @throws ApiException 订单不存在或用户未删除时抛出
     */
    public function delete(int $id): bool
    {
        $orderInfo = $this->dao->get(['id' => $id]);
        if (!$orderInfo) {
            throw new ApiException('订单不存在');
        }
        if (!(int)($orderInfo['is_del'] ?? 0)) {
            throw new ApiException('订单未被用户删除，不能删除');
        }
        $res = $this->dao->update(['id' => $id], ['is_system_del' => 1]) !== false;
        $this->clearStatCache();
        return $res;
    }

    /**
     * 设置线下支付方式
     * 将订单支付方式改为线下支付，可选去除邮费
     * @param string $orderNo 订单号
     * @return bool 返回操作结果
     */
    public function setOrderTypePayOffline(string $orderNo)
    {
        if (($count = strpos($orderNo, '_')) !== false) {
            $orderNo = substr($orderNo, $count + 1);
        }
        // 从中台表查询订单
        $orderInfo = $this->dao->get(['order_no' => $orderNo]);
        if (!$orderInfo) {
            return false;
        }
        $orderInfo = $orderInfo->toArray();

        if (sys_config('offline_postage', 0) == 1) {
            $res = $this->dao->update(['order_no' => $orderNo], [
                'pay_type'   => 'offline',
                'pay_price'  => bcsub((string)($orderInfo['pay_price'] ?? '0.00'), (string)($orderInfo['pay_postage'] ?? '0.00'), 2),
                'pay_postage' => 0,
            ]);
        } else {
            $res = $this->dao->update(['order_no' => $orderNo], ['pay_type' => 'offline']);
        }
        // 线下支付仅切换支付方式，不重复触发下单后置事件（订单创建时已触发 OrderCreateAfter）
        return $res;
    }

    /**
     * 获取用户订单列表（用户详情-订单tab）
     * @param int $uid 用户ID
     * @param array $where 额外查询条件
     * @param int $page 页码
     * @param int $limit 每页条数
     * @return array ['list' => [...], 'count' => int]
     */
    public function getUserList(int $uid, array $where = [], int $page = 1, int $limit = 20): array
    {
        $where['uid'] = $uid;
        $count = $this->dao->count($where, false);
        $list = $this->dao->selectList($where, '*', $page, $limit, 'id desc', [], false);
        foreach ($list as &$item) {
            $item['pay_type_name'] = $this->getPayTypeName($item['pay_type'] ?? '');
            $item['status_text'] = \app\model\order\OrderCenter::$statusMap[$item['status_code'] ?? ''] ?? '未知';
            $item['pay_status_text'] = \app\model\order\OrderCenter::$payStatusMap[$item['pay_status'] ?? 0] ?? '未知';
            $item['refund_status_text'] = \app\model\order\OrderCenter::$refundStatusMap[$item['refund_status'] ?? 0] ?? '未知';
        }
        return compact('list', 'count');
    }

}
