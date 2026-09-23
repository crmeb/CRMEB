<?php
declare(strict_types=1);

namespace crmeb\trade\order\type;

/**
 * 订单状态节点 DSL
 * 用于 defineStatusTree() 中声明式定义每个状态节点
 *
 * ═══════════ 使用示例 ═══════════
 *
 * OrderFlowNode::make('pending_shipped')
 *     ->code(10)
 *     ->centerStatus(self::STATUS_PROCESSING)
 *     ->label('待发货')
 *     ->badge('pending_delivery', '待发货')
 *     ->adminButtons([
 *         Button::primary('去发货', 'goDelivery'),
 *         Button::make('订单改价', 'editPrice'),
 *     ])
 *     ->userButtons([
 *         Button::make('申请退款', 'applyRefund'),
 *     ])
 *     ->filter([['pt_normal_order.status', '=', 'pending_shipped']])
 *     ->onEnter('onPaid'),
 */
class OrderFlowNode
{
    private string $extStatus;
    private int $code = 0;
    private string $centerStatus = '';
    private string $label = '';
    private ?array $badge = null;
    private array $adminButtons = [];
    private array $userButtons = [];
    private ?array $filter = null;
    private ?string $onEnter = null;

    private function __construct(string $extStatus)
    {
        $this->extStatus = $extStatus;
    }

    /**
     * 创建一个状态节点
     * @param string $extStatus 扩展表 status 字段值
     * @return static
     */
    public static function make(string $extStatus): self
    {
        return new self($extStatus);
    }

    /**
     * 扩展状态码（存扩展表 status 字段）
     */
    public function code(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * 映射到中台 status_code
     */
    public function centerStatus(string $status): self
    {
        $this->centerStatus = $status;
        return $this;
    }

    /**
     * 前端 Tab / 中文标签
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * 列表页业务标签（code, text, detail）
     */
    public function badge(string $code, string $text, string $detail = ''): self
    {
        $this->badge = [
            'code'   => $code,
            'text'   => $text,
            'detail' => $detail,
        ];
        return $this;
    }

    /**
     * 管理端详情页操作按钮
     * @param Button[] $buttons
     */
    public function adminButtons(array $buttons): self
    {
        $this->adminButtons = $buttons;
        return $this;
    }

    /**
     * 用户端操作按钮
     * @param Button[] $buttons
     */
    public function userButtons(array $buttons): self
    {
        $this->userButtons = $buttons;
        return $this;
    }

    /**
     * 列表页按此状态筛选时的额外条件
     * @param array|null $filter [['field', 'op', 'value'], ...]
     */
    public function filter(?array $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * 进入该状态时调用的本类方法名
     */
    public function onEnter(?string $method): self
    {
        $this->onEnter = $method;
        return $this;
    }

    /**
     * 转为内部数组格式（供 BaseOrderTypeAdapter 解析）
     * @return array
     */
    public function toArray(): array
    {
        $normalizeButtons = function (array $buttons): array {
            return array_map(function ($b) {
                return $b instanceof Button ? $b->toArray() : $b;
            }, $buttons);
        };

        return [
            'ext_status'    => $this->extStatus,// 扩展表 status 字段值
            'code'          => $this->code,// 扩展状态码（存扩展表 status 字段）
            'center_status' => $this->centerStatus,// 映射到中台 status_code
            'label'         => $this->label,// 前端 Tab / 中文标签
            'badge'         => $this->badge,// 列表页业务标签（code, text, detail）
            'admin_buttons' => $normalizeButtons($this->adminButtons), //管理端详情页操作按钮
            'user_buttons'  => $normalizeButtons($this->userButtons),// 用户端操作按钮
            'filter'        => $this->filter,// 列表页按此状态筛选时的额外条件
            'on_enter'      => $this->onEnter,// 进入该状态时调用的本类方法名   
        ];
    }
}
