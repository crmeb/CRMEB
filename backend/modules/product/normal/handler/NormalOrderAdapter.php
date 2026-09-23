<?php
declare(strict_types=1);

namespace modules\product\normal\handler;

use crmeb\trade\order\type\BaseOrderTypeAdapter;
use crmeb\trade\order\type\Button;
use crmeb\trade\order\type\OrderFlowNode;
use app\dao\order\ServiceFulfillmentDao;
use modules\product\normal\dao\NormalOrderDao;
use modules\product\normal\model\NormalOrderModel;
use think\facade\Event as EventFacade;

/**
 * 实物商品订单流转定义 — 唯一的订单行为数据源
 *
 * ═════════════════════════════ 二开必读 ═════════════════════════════
 *
 * 打开这个文件就能看懂实物商品订单的完整逻辑：
 *
 *   1. 有哪些状态？        → defineStatusTree()  每行一个状态
 *   2. 状态间怎么流转？    → onEnter 回调方法名
 *   3. 每个状态有什么按钮？ → admin_buttons / user_buttons
 *   4. 中台状态码怎么来的？→ center_status 直接标注
 *   5. 列表筛选条件？      → filter 字段
 *
 * ============================== 完整状态流转图 ==============================
 *
 *   pending_payment  待支付 ──────→ 中台 pending_payment
 *     │ 支付成功 → Event → OrderPaidListener → Flow::advance('pending_shipped')
 *     ▼
 *   pending_shipped  待发货 ──────→ 中台 processing
 *     │ 发货 → Event → OrderDeliveryListener → Flow::advance('pending_receive')
 *     ▼
 *   pending_receive  待收货 ──────→ 中台 processing
 *     │ 收货 → Event → OrderTakeListener
 *     ├─ 无需安装 → Flow::advance('pending_review')
 *     └─ 需安装   → Flow::advance('pending_installation')
 *        │ 安装完成 → pending_review
 *        ▼
 *   pending_review  待评价   ────→ 中台 pending_review
 *     │ 评价 → Event → OrderCompleteListener → Flow::advance('completed')
 *     ▼
 *   completed       已完成   ────→ 中台 completed
 *
 *   任意节点可触发：
 *     → canceled    已取消  ────→ 中台 canceled
 *     → after_sale  售后中  ────→ 中台 after_sale
 *     → refunded    已退款  ────→ 中台 refunded
 *
 * ========================== 扩展状态 → 中台状态码映射 ==========================
 *
 * 扩展状态常量                    │ 对应中台 status_code    │ 前端Tab标签
 * ───────────────────────────────┼────────────────────────┼─────────────
 * EXT_STATUS_PENDING_PAYMENT     │ pending_payment        │ 待支付
 * EXT_STATUS_PENDING_SHIPPED     │ processing             │ 待发货
 * EXT_STATUS_PENDING_RECEIVE     │ processing             │ 待收货
 * EXT_STATUS_PENDING_INSTALL     │ processing             │ 待安装
 * EXT_STATUS_PENDING_REVIEW      │ pending_review         │ 待评价
 * EXT_STATUS_COMPLETED           │ completed              │ 已完成
 * EXT_STATUS_CANCELED            │ canceled               │ 已取消
 * ── 以下由 resolveStatusCode 直接处理，不进 defineStatusTree ──
 * refund_status=1                │ after_sale             │ 退换/售后
 * refund_status=2                │ refunded               │ 已退款
 */
class NormalOrderAdapter extends BaseOrderTypeAdapter
{
    // ======================== 扩展状态常量（向后兼容） ========================

    /** 待支付 — 订单刚创建 */
    public const EXT_STATUS_PENDING_PAYMENT = 'pending_payment';

    /** 待发货 — 支付成功，等待管理员发货 */
    public const EXT_STATUS_PENDING_SHIPPED = 'pending_shipped';

    /** 待收货 — 管理员已发货，等待用户确认收货 */
    public const EXT_STATUS_PENDING_RECEIVE = 'pending_receive';

    /** 待安装 — 用户已收货但需要上门安装（特殊流程） */
    public const EXT_STATUS_PENDING_INSTALL = 'pending_installation';

    /** 待评价 — 用户已收货 / 安装已完成，等待用户评价 */
    public const EXT_STATUS_PENDING_REVIEW = 'pending_review';

    /** 已完成 — 用户已评价，订单终结 */
    public const EXT_STATUS_COMPLETED = 'completed';

    /** 已取消 — 订单已取消 */
    public const EXT_STATUS_CANCELED = 'canceled';

    // ======================== 1. 身份标识 ========================

    public static function getSourceType(): string
    {
        return 'normal_order';
    }

    public static function getOrderCategory(): string
    {
        return 'goods';
    }

    public static function getOrderCategoryLabel(): string
    {
        return '商品订单';
    }

    public static function getModelClass(): string
    {
        return NormalOrderModel::class;
    }

    // ======================== 2. 状态树（唯一数据源） ========================

    /**
     * 状态树定义
     *
     * 每个节点：
     * - ext_status:    扩展表 eb_pt_normal_order.status 取值
     * - center_status: 映射到中台 eb_order_center.status_code
     * - label:         中文标签（管理端 Tab / 用户端列表）
     * - badge:         列表页业务状态标签（code / text / detail），可为空
     * - admin_buttons: 管理端详情页操作按钮
     * - user_buttons:  用户端操作按钮
     * - filter:        管理端列表按此状态筛选时的 extra 条件
     * - on_enter:      进入该状态时调用的本类方法名（可选，不填则仅执行中台同步）
     */
    protected function defineStatusTree(): array
    {
        return [
            // ───── 待支付 ─────
            OrderFlowNode::make(self::EXT_STATUS_PENDING_PAYMENT)
                ->centerStatus(self::STATUS_PENDING_PAYMENT)
                ->label('待支付')
                ->adminButtons([
                    Button::make('取消订单', 'cancelOrder'),
                    Button::make('订单改价', 'editPrice'),
                ])
                ->userButtons([
                    Button::make('取消订单', 'cancelOrder'),
                    Button::primary('去支付', 'payOrder'),
                ]),

            // ───── 待发货（进入 processing） ─────
            OrderFlowNode::make(self::EXT_STATUS_PENDING_SHIPPED)
                ->centerStatus(self::STATUS_PROCESSING)
                ->label('待发货')
                ->badge('pending_delivery', '待发货')
                ->adminButtons([
                    Button::primary('去发货', 'goDelivery'),
                    Button::make('订单改价', 'editPrice'),
                ])
                ->userButtons([
                    Button::make('申请退款', 'applyRefund'),
                ])
                ->filter([['pt_normal_order.status', '=', self::EXT_STATUS_PENDING_SHIPPED]]),

            // ───── 待收货（processing） ─────
            OrderFlowNode::make(self::EXT_STATUS_PENDING_RECEIVE)
                ->centerStatus(self::STATUS_PROCESSING)
                ->label('待收货')
                ->badge('pending_receipt', '待收货', '快递运输中')
                ->adminButtons([
                    Button::make('查看物流', 'viewLogistics'),
                    Button::make('确认收货', 'confirmReceive'),
                ])
                ->userButtons([
                    Button::primary('确认收货', 'confirmReceive'),
                    Button::make('查看物流', 'viewLogistics'),
                    Button::make('申请退款', 'applyRefund'),
                ])
                ->filter([['pt_normal_order.status', '=', self::EXT_STATUS_PENDING_RECEIVE]]),

            // ───── 待安装（特殊流程，processing） ─────
            OrderFlowNode::make(self::EXT_STATUS_PENDING_INSTALL)
                ->centerStatus(self::STATUS_PROCESSING)
                ->label('待安装')
                ->badge('pending_installation', '待安装', '等待师傅上门安装')
                ->adminButtons([
                    Button::primary('安装完成', 'installComplete'),
                ])
                ->userButtons([])
                ->filter([['pt_normal_order.status', '=', self::EXT_STATUS_PENDING_INSTALL]])
                ->onEnter('onTakenForInstall'),

            // ───── 待评价（pending_review） ─────
            OrderFlowNode::make(self::EXT_STATUS_PENDING_REVIEW)
                ->centerStatus(self::STATUS_PENDING_REVIEW)
                ->label('待评价')
                ->adminButtons([
                    Button::make('查看物流', 'viewLogistics'),
                ])
                ->userButtons([
                    Button::primary('去评价', 'goComment'),
                    Button::make('申请售后', 'applyAfterSale'),
                ])
                ->filter([['pt_normal_order.status', '=', self::EXT_STATUS_PENDING_REVIEW]]),

            // ───── 已完成（终态） ─────
            OrderFlowNode::make(self::EXT_STATUS_COMPLETED)
                ->centerStatus(self::STATUS_COMPLETED)
                ->label('已完成')
                ->adminButtons([])
                ->userButtons([
                    Button::make('申请售后', 'applyAfterSale'),
                    Button::primary('再次购买', 'repurchase'),
                ])
                ->filter([['pt_normal_order.status', '=', self::EXT_STATUS_COMPLETED]]),

            // ───── 已取消（终态） ─────
            OrderFlowNode::make(self::EXT_STATUS_CANCELED)
                ->centerStatus(self::STATUS_CANCELED)
                ->label('已取消')
                ->adminButtons([])
                ->userButtons([
                    Button::make('删除订单', 'deleteOrder'),
                    Button::primary('再次购买', 'repurchase'),
                ]),

            // ───── 售后中（由 resolveStatusCode 直接处理） ─────
            OrderFlowNode::make(self::STATUS_AFTER_SALE)
                ->centerStatus(self::STATUS_AFTER_SALE)
                ->label('退换/售后')
                ->adminButtons([
                    Button::primary('处理售后', 'handleAfterSale'),
                ])
                ->userButtons([
                    Button::primary('查看售后', 'viewAfterSale'),
                ]),

            // ───── 已退款（终态） ─────
            OrderFlowNode::make(self::STATUS_REFUNDED)
                ->centerStatus(self::STATUS_REFUNDED)
                ->label('已退款')
                ->adminButtons([])
                ->userButtons([
                    Button::primary('查看售后', 'viewAfterSale'),
                ]),
        ];
    }

    // ======================== 3. 状态推导 ========================

    /**
     * 推导统一状态码
     * 优先级：取消 > 退款完成 > 售后中 > 未支付 > 扩展状态映射
     */
    public function resolveStatusCode(array $sourceOrder): string
    {
        $isCancel     = (bool) ($sourceOrder['is_cancel'] ?? 0);
        $refundStatus = (int) ($sourceOrder['refund_status'] ?? 0);
        $paid         = (bool) ($sourceOrder['paid'] ?? 0);
        $status       = (string) ($sourceOrder['status'] ?? '');

        if ($isCancel)           return self::STATUS_CANCELED;
        if ($refundStatus === 2) {
            $refundPrice = (float)($sourceOrder['refund_price'] ?? 0);
            $payPrice = (float)($sourceOrder['pay_price'] ?? 0);
            if ($payPrice > 0 && $refundPrice >= $payPrice) {
                return self::STATUS_REFUNDED;
            }
            // 部分退款不终止订单原有履约状态。
            return $this->mapPaidStatusToCenter($status);
        }
        if ($refundStatus === 1) return self::STATUS_AFTER_SALE;
        if (!$paid)              return self::STATUS_PENDING_PAYMENT;

        return $this->mapPaidStatusToCenter($status);
    }

    /**
     * 已支付订单的扩展状态 → 中台状态映射
     */
    private function mapPaidStatusToCenter(string $status): string
    {
        $node = $this->extStatusMap[$status] ?? null;
        return $node['center_status'] ?? self::STATUS_PROCESSING;
    }

    /**
     * 推导支付状态
     */
    public function resolvePayStatus(array $sourceOrder): int
    {
        $refundStatus = (int) ($sourceOrder['refund_status'] ?? 0);
        $refundPrice  = isset($sourceOrder['refund_price']) ? (float) $sourceOrder['refund_price'] : 0.0;
        $payPrice     = isset($sourceOrder['pay_price']) ? (float) $sourceOrder['pay_price'] : 0.0;
        $paid         = (bool) ($sourceOrder['paid'] ?? 0);

        if ($refundStatus === 2) {
            return $refundPrice >= $payPrice
                ? self::PAY_FULL_REFUND
                : self::PAY_PARTIAL_REFUND;
        }
        return $paid ? self::PAY_PAID : self::PAY_UNPAID;
    }

    /**
     * 推导退款状态
     */
    public function resolveRefundStatus(array $sourceOrder): int
    {
        return (int) ($sourceOrder['refund_status'] ?? 0);
    }

    /**
     * 推导业务状态（前端 Badge 展示）
     */
    public function resolveBusinessStatus(array $sourceOrder): ?array
    {
        $isCancel     = (bool) ($sourceOrder['is_cancel'] ?? 0);
        $refundStatus = (int) ($sourceOrder['refund_status'] ?? 0);
        $paid         = (bool) ($sourceOrder['paid'] ?? 0);
        $status       = (string) ($sourceOrder['status'] ?? '');

        if ($isCancel || $refundStatus >= 2 || !$paid) {
            return null;
        }

        // 通过事件允许其他模块覆盖
        $businessStatus = EventFacade::until('OrderCenterBusinessStatusResolve', [
            'source_type' => self::getSourceType(),
            'order'       => $sourceOrder,
        ]);
        if ($businessStatus) {
            return $businessStatus;
        }

        // 从 STATUS_TREE 查找 badge
        return $this->getBadge($status);
    }

    // ======================== 4. 按钮逻辑 ========================

    /**
     * 获取指定状态码的操作按钮
     *
     * PROCESSING 状态需要根据 delivery_time 区分按钮：
     *   - 已发货 → 查看物流 + 确认收货
     *   - 未发货 → 申请退款
     *
     * @param string $statusCode 中台统一状态码
     * @param array $detail 订单详情（含 delivery_time）
     * @return array
     */
    public function getStatusButtons(string $statusCode, array $detail = []): array
    {
        if ($statusCode === self::STATUS_PROCESSING) {
            return $this->getProcessingButtons($detail);
        }

        // 其他状态：从 STATUS_TREE 静态读取
        $node = $this->centerStatusMap[$statusCode][0] ?? null;
        if ($node) {
            return $node['user_buttons'] ?? [];
        }

        // 回退：纯扩展状态节点（after_sale / refunded）
        return $this->extStatusMap[$statusCode]['user_buttons'] ?? [];
    }

    /**
     * PROCESSING 状态动态按钮
     */
    private function getProcessingButtons(array $detail): array
    {
        $extStatus  = $detail['source_status'] ?? '';
        $orderNo    = $detail['order_no'] ?? '';

        // 根据扩展子状态返回对应按钮
        $node = $this->extStatusMap[$extStatus] ?? null;
        if ($node) {
            $buttons = $node['user_buttons'] ?? [];
        } else {
            // 兜底：根据 delivery_time 判断
            $hasShipped = (int)($detail['delivery_time'] ?? 0) > 0;
            $buttons = $hasShipped
                ? [
                    ['text' => '查看物流', 'action' => 'viewLogistics'],
                    ['text' => '确认收货', 'action' => 'confirmReceive', 'primary' => true],
                ]
                : [
                    ['text' => '申请退款', 'action' => 'applyRefund'],
                ];
        }

        // 注入订单号
        foreach ($buttons as &$btn) {
            $btn['params'] = array_merge($btn['params'] ?? [], ['order_no' => $orderNo]);
        }
        unset($btn);
        return $buttons;
    }

    // ======================== 5. 进度条 ========================

    /**
     * 实物商品订单生命周期进度条
     *
     * 根据配送方式展示不同进度节点：
     * - 快递配送（delivery）：提交订单 → 支付成功 → 商品发货 → 确认收货 → 订单完成
     * - 到店自提（pickup）：  提交订单 → 支付成功 → 到店核销 → 订单完成
     *
     * @param array $detail 订单详情（含 status_code、source_status、add_time、pay_time、delivery_time、take_time、shipping_type 等）
     * @return array
     */
    public function getProgress(array $detail): array
    {
        $statusCode   = $detail['status_code'] ?? '';
        $sourceStatus = $detail['source_status'] ?? '';
        $isCancel     = $statusCode === self::STATUS_CANCELED;
        $isAfterSale  = $statusCode === self::STATUS_AFTER_SALE;
        $isRefunded   = $statusCode === self::STATUS_REFUNDED;
        $isCompleted  = $statusCode === self::STATUS_COMPLETED;

        // 判断是否为到店自提（shipping_type=2），默认走快递配送流程
        $isPickup = ($detail['shipping_type'] ?? 0) == 2;

        // 根据配送方式定义进度节点
        if ($isPickup) {
            // 到店自提：提交订单 → 支付成功 → 到店核销 → 订单完成
            $nodes = [
                ['title' => '提交订单', 'key' => 'submit', 'time_field' => 'add_time'],
                ['title' => '支付成功', 'key' => 'pay', 'time_field' => 'pay_time'],
                ['title' => '到店核销', 'key' => 'verify', 'time_field' => 'delivery_time'],
                ['title' => '订单完成', 'key' => 'complete', 'time_field' => ''],
            ];
            $completeIndex = 3;  // 订单完成节点索引
            $lastDeliveryIndex = 2; // 到店核销节点索引
        } else {
            // 快递配送：提交订单 → 支付成功 → 商品发货 → 确认收货 → 订单完成
            $nodes = [
                ['title' => '提交订单', 'key' => 'submit', 'time_field' => 'add_time'],
                ['title' => '支付成功', 'key' => 'pay', 'time_field' => 'pay_time'],
                ['title' => '商品发货', 'key' => 'delivery', 'time_field' => 'delivery_time'],
                ['title' => '确认收货', 'key' => 'receive', 'time_field' => 'take_time'],
                ['title' => '订单完成', 'key' => 'complete', 'time_field' => ''],
            ];
            $completeIndex = 4;
            $lastDeliveryIndex = 3;
        }

        // 计算每个节点的完成状态和发生时间
        foreach ($nodes as $i => &$node) {
            $time = 0;
            if ($node['time_field']) {
                $time = (int)($detail[$node['time_field']] ?? 0);
            }
            $node['time']      = $time ? date('Y-m-d H:i:s', $time) : '';
            $node['completed'] = $time > 0;
            $node['active']    = false;
        }
        unset($node);

        // 最后一个节点（订单完成）特殊处理：只有订单真正完成才算完成
        $completeNode = &$nodes[$completeIndex];
        $completeNode['completed'] = $isCompleted;
        if ($completeNode['completed']) {
            // 到店自提使用核销时间（delivery_time），快递配送使用收货时间（take_time）
            $completeNode['time'] = $isPickup
                ? ($detail['delivery_time'] ? date('Y-m-d H:i:s', (int)$detail['delivery_time']) : '')
                : ($detail['take_time'] ? date('Y-m-d H:i:s', (int)$detail['take_time']) : '');
        }
        unset($completeNode);

        // 重新计算最后一个已完成节点索引
        $lastCompleted = -1;
        foreach ($nodes as $i => $node) {
            if ($node['completed']) {
                $lastCompleted = $i;
            }
        }

        // 确定当前激活节点：
        // 按实际发生的时间字段判断最可靠；状态码作为兜底和终态判断
        $current = -1;
        if ($isCancel) {
            $current = 0; // 已取消：停留在提交订单节点
        } elseif ($isRefunded || $isAfterSale) {
            $current = 1; // 售后/退款：流程中断在支付成功后
        } elseif ($isCompleted) {
            $current = $completeIndex;
        } elseif ($sourceStatus === self::EXT_STATUS_PENDING_REVIEW) {
            $current = $completeIndex; // 待评价：激活"订单完成"作为当前待处理步骤
        } elseif ($isPickup) {
            // —— 到店自提 current 映射 ——
            if ($sourceStatus === self::EXT_STATUS_PENDING_RECEIVE) {
                $current = 2; // 已核销（pending_receive）：到店核销已完成，该节点即为当前步骤
            } elseif ($sourceStatus === self::EXT_STATUS_PENDING_SHIPPED) {
                $current = 2; // 待核销（pending_shipped）：等待到店核销
            } elseif ((int)($detail['delivery_time'] ?? 0) > 0) {
                $current = 2; // 已核销
            } elseif ((int)($detail['pay_time'] ?? 0) > 0) {
                $current = 1; // 已支付，等待核销
            } elseif ((int)($detail['add_time'] ?? 0) > 0) {
                $current = 0; // 刚提交，未支付
            }
        } else {
            // —— 快递配送 current 映射 ——
            if ($sourceStatus === self::EXT_STATUS_PENDING_RECEIVE || $sourceStatus === self::EXT_STATUS_PENDING_INSTALL) {
                $current = 3; // 待收货/待安装：发货已完成
            } elseif ($sourceStatus === self::EXT_STATUS_PENDING_SHIPPED) {
                $current = 2; // 待发货：支付已完成
            } elseif ((int)($detail['delivery_time'] ?? 0) > 0) {
                $current = 3; // 已发货，未收货
            } elseif ((int)($detail['pay_time'] ?? 0) > 0) {
                $current = 2; // 已支付，未发货
            } elseif ((int)($detail['add_time'] ?? 0) > 0) {
                $current = 0; // 刚提交，未支付
            }
        }

        // 兜底：按时间字段顺序取最后一个已完成节点的下一个
        if ($current < 0) {
            $current = min($lastCompleted + 1, count($nodes) - 1);
        }

        // 设置激活状态
        if (isset($nodes[$current])) {
            $nodes[$current]['active'] = true;
        }

        return [
            'current' => $current,
            'status'  => $statusCode,
            'nodes'   => $nodes,
        ];
    }

    // ======================== 6. 生命周期回调 ========================

    /**
     * 收货后 → 待安装（创建服务履约记录）
     */
    protected function onTakenForInstall(string $orderNo, array $extra = []): void
    {
        try {
            $order = app(NormalOrderDao::class)->getOne(['order_no' => $orderNo]);
            if (!$order) return;

            $centerServices = app(\app\services\order\OrderCenterServices::class);
            $center = $centerServices->getOne(['order_no' => $orderNo]);
            $centerId = $center ? ($center['id'] ?? 0) : 0;

            /** @var ServiceFulfillmentDao $fulfillmentDao */
            $fulfillmentDao = app(ServiceFulfillmentDao::class);
            $fulfillmentDao->save([
                'fulfillment_no'  => 'INS' . date('YmdHis') . mt_rand(100, 999),
                'order_center_id' => $centerId,
                'order_no'        => $orderNo,
                'uid'             => intval($order['uid'] ?? 0),
                'type'            => 'installation',
                'status'          => 'pending_assign',
                'add_time'        => time(),
            ]);
        } catch (\Throwable $e) {
            // 服务履约表创建失败不影响订单主流程
        }
    }

    // ======================== 6. 扩展表筛选 ========================

    /**
     * 根据扩展表 filter 条件查询匹配的 order_no 列表
     * 供管理端 OrderController 按扩展状态筛选时使用
     *
     * @param array $filter 筛选条件 [['status', '=', 'pending_review']]
     * @return array 匹配的订单号列表，无结果返回 ['__NONE__']
     */
    public static function resolveExtensionFilter(array $filter): array
    {
        /** @var NormalOrderDao $dao */
        $dao = app()->make(NormalOrderDao::class);
        return $dao->columnByFilters($filter, 'order_no') ?: ['__NONE__'];
    }

    /**
     * 获取状态树（向后兼容 NormalOrderAdapter::getStatusTree()）
     * 返回以前端 Tab 展示为维度的状态分组结构
     * 结构: { centerStatus: { label, children: { extStatus: { code, label, filter } }, buttons } }
     *
     * @return array
     */
    public static function getStatusTree(): array
    {
        $flow = self::instance();
        $tree = [];

        foreach ($flow->statusTree as $node) {
            $centerStatus = $node['center_status'] ?? '';
            $extStatus    = $node['ext_status'] ?? '';
            if (!$centerStatus || !$extStatus) continue;

            if (!isset($tree[$centerStatus])) {
                $tree[$centerStatus] = [
                    'label'    => $node['label'],
                    'children' => [],
                    'buttons'  => $node['user_buttons'] ?? [],
                ];
            }

            // 有 filter 条件的子节点才加入 children（用于 Tab 子筛选）
            if ($node['filter'] ?? null) {
                $tree[$centerStatus]['children'][$extStatus] = [
                    'code'   => $node['badge']['code'] ?? ($centerStatus === self::STATUS_COMPLETED ? 50 : 0),
                    'label'  => $node['badge']['text'] ?? $node['label'],
                    'filter' => $node['filter'],
                ];
            }

            // 合并 admin_buttons（管理端专用，不在 STATUS_TREE 中暴露）
            // 这里保持与旧 NormalOrderAdapter 的兼容性
        }

        return $tree;
    }
}
