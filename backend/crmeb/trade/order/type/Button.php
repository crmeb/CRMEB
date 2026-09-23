<?php
declare(strict_types=1);

namespace crmeb\trade\order\type;

/**
 * 操作按钮 DSL
 * 用于 OrderFlowNode::adminButtons() / userButtons() 中声明式定义按钮
 *
 * ═══════════ 使用示例 ═══════════
 *
 * Button::make('取消订单', 'cancelOrder')
 * Button::primary('去支付', 'payOrder')
 * Button::danger('删除订单', 'deleteOrder')
 */
class Button
{
    private array $config;

    private function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * 普通按钮
     */
    public static function make(string $text, string $action): self
    {
        return new self(['text' => $text, 'action' => $action]);
    }

    /**
     * 主要按钮（高亮）
     */
    public static function primary(string $text, string $action): self
    {
        return new self(['text' => $text, 'action' => $action, 'primary' => true]);
    }

    /**
     * 危险按钮（红色警告）
     */
    public static function danger(string $text, string $action): self
    {
        return new self(['text' => $text, 'action' => $action, 'danger' => true]);
    }

    /**
     * 转为数组（供 OrderFlowNode::toArray() 调用）
     * @return array
     */
    public function toArray(): array
    {
        return $this->config;
    }
}
