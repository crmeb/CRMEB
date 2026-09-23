<?php
declare(strict_types=1);

namespace crmeb\trade\order\type;

use think\facade\Log;

/**
 * 订单类型适配器基类 — 为每种订单类型提供统一的「状态推进」基础设施
 *
 * 设计目标：
 *   让二开开发者只需阅读一个文件就能理解一个订单类型的完整流转逻辑：
 *   - 有哪些状态（defineStatusTree）
 *   - 每个状态的按钮（adminButtons/userButtons）
 *   - 状态变化时做什么（onEnter 回调）
 *
 * 子类只需实现：
 *   1. defineStatusTree()  — 定义状态树（唯一数据源）
 *   2. resolveStatusCode() — 扩展状态 → 中台状态码推导
 *   3. resolvePayStatus / resolveRefundStatus / resolveBusinessStatus
 *   4. getSourceType / getOrderCategory / getOrderCategoryLabel / getModelClass
 *   5. resolveExtensionFilter — 扩展表筛选条件映射（供管理端列表 Tab 查询）
 *   6. getStatusButtons — 按钮获取（支持动态）
 *   7. onEnter 回调方法（如 onPaid / onDelivered / etc）
 *
 * 不在本基类中的内容（属于事件监听层的职责）：
 *   - OrderLifecycleServices 调用（日志/通知/优惠券/流水）
 *   - 这些由 listener/ 目录处理
 */
abstract class BaseOrderTypeAdapter implements OrderTypeAdapterInterface
{
    /**
     * 状态树解析缓存
     * @var array<string, array>
     */
    protected array $statusTree = [];

    /**
     * 扩展状态 → 节点映射缓存
     * @var array<string, array>
     */
    protected array $extStatusMap = [];

    /**
     * 中台状态 → 节点列表映射缓存
     * @var array<string, array>
     */
    protected array $centerStatusMap = [];

    /**
     * 单例缓存（每个 source_type 一个实例）
     * @var array<string, static>
     */
    protected static array $instances = [];

    /**
     * 获取单例（每个 source_type 一个实例）
     * @return static
     */
    public static function instance(): static
    {
        $class = static::class;
        if (!isset(static::$instances[$class])) {
            static::$instances[$class] = new static();
        }
        return static::$instances[$class];
    }

    /**
     * 初始化：解析 defineStatusTree 到内部索引
     */
    protected function __construct()
    {
        $this->parseStatusTree();
    }

    /**
     * 定义状态树（子类实现）
     *
     * 每个节点是一个关联数组：
     * [
     *   'ext_status'    => 'pending_shipped',       // 扩展表 status 值
     *   'center_status' => 'processing',             // 映射的中台状态码
     *   'label'         => '待发货',                  // 中文标签
     *   'badge'         => ['code'=>'pending_delivery', 'text'=>'待发货', 'detail'=>''],
     *   'admin_buttons' => [...],                    // 管理端操作按钮
     *   'user_buttons'  => [...],                    // 用户端操作按钮
     *   'filter'        => [['pt_normal_order.status', '=', 'pending_shipped']],
     *   'on_enter'      => 'onPaid',                 // 进入该状态时的回调方法名
     * ]
     *
     * @return array
     */
    abstract protected function defineStatusTree(): array;

    /**
     * 解析状态树到内部索引
     * 兼容数组格式和 OrderFlowNode DSL 格式
     */
    protected function parseStatusTree(): void
    {
        $this->extStatusMap    = [];
        $this->centerStatusMap = [];
        $this->statusTree      = [];

        foreach ($this->defineStatusTree() as $node) {
            if ($node instanceof OrderFlowNode) {
                $node = $node->toArray();
            }
            $this->statusTree[] = $node;
            $extStatus    = $node['ext_status'] ?? '';
            $centerStatus = $node['center_status'] ?? '';
            if ($extStatus) {
                $this->extStatusMap[$extStatus] = $node;
            }
            if ($centerStatus) {
                $this->centerStatusMap[$centerStatus][] = $node;
            }
        }
    }

    // ======================== 核心能力：状态推进 ========================

    /**
     * 推进订单状态到指定扩展状态
     *
     * 一站式完成：
     *   1. 写入扩展表 status 字段
     *   2. 调用 onEnter 回调
     *   3. 同步中台 status_code + pay_status + extra_info.business_status
     *
     * @param string $orderNo  订单号
     * @param string $extStatus 目标扩展状态名
     * @param array $extra  额外数据（传递给 onEnter 回调）
     * @return void
     * @throws \RuntimeException 当 extStatus 未在 STATUS_TREE 中定义时
     */
    public function advance(string $orderNo, string $extStatus, array $extra = []): void
    {
        $node = $this->extStatusMap[$extStatus] ?? null;
        if (!$node) {
            throw new \RuntimeException("[OrderFlow] 未定义的状态节点: {$extStatus}");
        }

        // 1. 写入扩展表 status
        $this->updateExtStatus($orderNo, $extStatus, $extra);

        // 2. 调用 onEnter 回调
        $onEnter = $node['on_enter'] ?? '';
        if ($onEnter && method_exists($this, $onEnter)) {
            $this->{$onEnter}($orderNo, $extra);
        }

        // 3. 同步中台
        $this->syncCenterAfterAdvance($orderNo, $extStatus);
    }

    /**
     * 检查订单是否可以推进到目标状态（幂等检查）
     * @param string $orderNo 订单号
     * @param string $extStatus 目标扩展状态名
     * @return bool 可以推进返回 true，已经是该状态或无法推进返回 false
     */
    public function canAdvanceTo(string $orderNo, string $extStatus): bool
    {
        $currentStatus = $this->getCurrentExtStatus($orderNo);
        if ($currentStatus === $extStatus) {
            return false; // 已经是目标状态，幂等跳过
        }

        $node = $this->extStatusMap[$extStatus] ?? null;
        return $node !== null;
    }

    /**
     * 获取订单当前扩展表状态
     * @param string $orderNo
     * @return string|null
     */
    protected function getCurrentExtStatus(string $orderNo): ?string
    {
        try {
            $modelClass = static::getModelClass();
            $record = app()->make($modelClass)->db()
                ->where('order_no', $orderNo)->field('status')->find();
            return $record ? ($record['status'] ?? null) : null;
        } catch (\Throwable $e) {
            Log::warning("[OrderFlow] getCurrentExtStatus failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * 更新扩展表 status
     * @param string $orderNo
     * @param string $extStatus
     * @param array $extra 额外更新字段
     */
    protected function updateExtStatus(string $orderNo, string $extStatus, array $extra = []): void
    {
        $modelClass = static::getModelClass();
        $data = array_merge(['status' => $extStatus], $extra);
        app()->make($modelClass)->db()
            ->where('order_no', $orderNo)->update($data);
    }

    /**
     * 推进后同步中台状态
     * @param string $orderNo
     * @param string $extStatus
     */
    protected function syncCenterAfterAdvance(string $orderNo, string $extStatus): void
    {
        $node       = $this->extStatusMap[$extStatus];
        $centerCode = $node['center_status'] ?? '';
        if (!$centerCode) return;

        // 通过接口契约反转依赖，避免核心层反向依赖 app 层
        /** @var OrderCenterSyncInterface $centerServices */
        $centerServices = app()->make(OrderCenterSyncInterface::class);
        $centerServices->syncStatus($orderNo, $centerCode);
    }

    // ======================== 按钮能力 ========================

    /**
     * 获取指定状态码的操作按钮
     * 子类可覆写以支持动态按钮（如 PROCESSING 状态根据发货状态区分）
     *
     * @param string $statusCode 中台统一状态码
     * @param array $detail 订单详情（动态按钮场景需要传）
     * @return array
     */
    public function getStatusButtons(string $statusCode, array $detail = []): array
    {
        $nodes = $this->centerStatusMap[$statusCode] ?? [];
        if (empty($nodes)) {
            // 回退：查找全局节点（如 after_sale / refunded / canceled）
            foreach ($this->statusTree as $node) {
                if (($node['ext_status'] ?? '') === $statusCode) {
                    return $node['user_buttons'] ?? [];
                }
            }
            return [];
        }

        // PROCESSING 状态可能有多个子状态节点，取所有 user_buttons 的并集
        // 或按子类覆写的逻辑来决定
        return $this->resolveProcessingButtons($nodes, $detail);
    }

    /**
     * 处理 PROCESSING 状态的按钮（子类可覆写）
     */
    protected function resolveProcessingButtons(array $nodes, array $detail): array
    {
        $buttons = [];
        foreach ($nodes as $node) {
            $nodeButtons = $node['user_buttons'] ?? [];
            $buttons = array_merge($buttons, $nodeButtons);
        }
        return $buttons;
    }

    /**
     * 获取订单操作按钮（默认实现：从 STATUS_TREE 读 + 注入 order_no）
     *
     * @param array $detail 订单详情
     * @return array
     */
    public function getOrderButtons(array $detail): array
    {
        $statusCode = $detail['status_code'] ?? '';
        $orderNo    = $detail['order_no'] ?? '';
        $buttons    = $this->getStatusButtons($statusCode, $detail);

        foreach ($buttons as &$button) {
            $button['params'] = array_merge(
                $button['params'] ?? [],
                ['order_no' => $orderNo]
            );
        }
        unset($button);
        return $buttons;
    }

    // ======================== 进度条能力 ========================

    /**
     * 获取订单生命周期进度条
     * 子类覆写以定义不同订单类型的生命周期节点
     * 默认返回空数组，表示该订单类型未定义进度条
     *
     * @param array $detail 订单详情（含 status_code、source_status、pay_time、delivery_time、take_time 等）
     * @return array 返回进度条结构：
     *   [
     *     'current' => 2,  // 当前激活节点索引，-1 表示未开始
     *     'nodes'   => [
     *       ['title' => '提交订单', 'completed' => true,  'active' => false, 'time' => '2026-07-23 11:12:02'],
     *       ['title' => '支付成功', 'completed' => true,  'active' => false, 'time' => '2026-07-23 11:12:07'],
     *       ['title' => '商品发货', 'completed' => false, 'active' => true,  'time' => ''],
     *       ...
     *     ],
     *   ]
     */
    public function getProgress(array $detail): array
    {
        return [];
    }

    // ======================== 筛选辅助 ========================

    /**
     * 根据扩展状态名获取扩展表 filter 条件
     * @param string $extStatus
     * @return array|null
     */
    public function getExtStatusFilter(string $extStatus): ?array
    {
        $node = $this->extStatusMap[$extStatus] ?? null;
        return $node['filter'] ?? null;
    }

    /**
     * 根据扩展状态名获取 badge 配置
     * @param string $extStatus
     * @return array|null
     */
    public function getBadge(string $extStatus): ?array
    {
        $node = $this->extStatusMap[$extStatus] ?? null;
        return $node['badge'] ?? null;
    }
}
