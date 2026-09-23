<?php
declare(strict_types=1);

namespace app\model\order;

use crmeb\basic\BaseModel;
use crmeb\trade\order\type\OrderTypeAdapterInterface;
use crmeb\traits\ModelTrait;

/**
 * 订单中台模型
 * 统一管理所有类型订单的元数据
 *
 * 状态常量已委托给 crmeb\trade\order\type\OrderTypeAdapterInterface
 * 本模型保留常量别名以兼容已有代码，新代码请直接引用接口常量或从 Adapter 继承
 *
 * @method static $this where($field, $operator = null, $value = null)
 * @property int $pay_counted 购买次数已记账标记，与用户计数同事务提交
 * @property int $take_rewards_given 收货奖励已记账标记，防止重复发奖
 * @property array|string|null $refund_reward_accounted 退款奖励累计已处理目标，含已核对短缺
 */
class OrderCenter extends BaseModel
{
    use ModelTrait;

    /**
     * 表名
     * @var string
     */
    protected $name = 'order_center';

    /**
     * 主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 统一状态码常量（委托给 OrderTypeAdapterInterface）
     * 数据库字段 status_code
     */
    const STATUS_PENDING_PAYMENT = OrderTypeAdapterInterface::STATUS_PENDING_PAYMENT;
    const STATUS_PROCESSING      = OrderTypeAdapterInterface::STATUS_PROCESSING;
    const STATUS_PENDING_REVIEW  = OrderTypeAdapterInterface::STATUS_PENDING_REVIEW;
    const STATUS_COMPLETED       = OrderTypeAdapterInterface::STATUS_COMPLETED;
    const STATUS_CANCELED        = OrderTypeAdapterInterface::STATUS_CANCELED;
    const STATUS_AFTER_SALE      = OrderTypeAdapterInterface::STATUS_AFTER_SALE;
    const STATUS_REFUNDING       = OrderTypeAdapterInterface::STATUS_REFUNDING;
    const STATUS_REFUNDED        = OrderTypeAdapterInterface::STATUS_REFUNDED;
    const STATUS_DELETED         = OrderTypeAdapterInterface::STATUS_DELETED;

    /**
     * 支付状态常量（委托给 OrderTypeAdapterInterface）
     * 数据库字段 pay_status
     */
    const PAY_UNPAID         = OrderTypeAdapterInterface::PAY_UNPAID;
    const PAY_PAID           = OrderTypeAdapterInterface::PAY_PAID;
    const PAY_PARTIAL_REFUND = OrderTypeAdapterInterface::PAY_PARTIAL_REFUND;
    const PAY_FULL_REFUND    = OrderTypeAdapterInterface::PAY_FULL_REFUND;

    /**
     * 退款状态常量（委托给 OrderTypeAdapterInterface）
     * 数据库字段 refund_status
     */
    const REFUND_NONE     = OrderTypeAdapterInterface::REFUND_NONE;
    const REFUND_APPLYING = OrderTypeAdapterInterface::REFUND_APPLYING;
    const REFUND_SUCCESS  = OrderTypeAdapterInterface::REFUND_SUCCESS;

    /**
     * 状态码映射表
     * @var array<string, string>
     */
    public static array $statusMap = OrderTypeAdapterInterface::STATUS_MAP;

    /**
     * 支付状态映射表
     * @var array<int, string>
     */
    public static array $payStatusMap = [
        self::PAY_UNPAID         => '未支付',
        self::PAY_PAID           => '已支付',
        self::PAY_PARTIAL_REFUND => '部分退款',
        self::PAY_FULL_REFUND    => '全额退款',
    ];

    /**
     * 退款状态映射表
     * @var array<int, string>
     */
    public static array $refundStatusMap = [
        self::REFUND_NONE     => '无退款',
        self::REFUND_APPLYING => '退款中',
        self::REFUND_SUCCESS  => '已退款',
    ];
}
