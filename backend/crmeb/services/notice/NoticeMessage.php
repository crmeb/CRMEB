<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\services\notice;

/**
 * 一条待投递的通知消息（不可变值对象）
 *
 * 渠道接口 NoticeChannelInterface::send() 的唯一入参。
 * 扩展字段只需追加带默认值的 readonly 属性并在构造点（Notice::selectTargets）补参，
 * 所有渠道实现零改动；readonly 保证消息构造后不可被任何渠道篡改。
 */
final class NoticeMessage
{
    /**
     * 消息标识（对应 eb_notice_config.mark，如 order_pay_success）
     * @var string
     */
    public readonly string $mark;

    /**
     * 接收用户ID，平台/客服通知为 0（接收者即类型：>0 用户通知、0 平台通知）
     * @var int
     */
    public readonly int $uid;

    /**
     * 该通知在该渠道配置的模板标题
     * @var string
     */
    public readonly string $title;

    /**
     * 该通知在该渠道配置的模板内容（含 {key} 占位符，由渠道自行渲染）
     * @var string
     */
    public readonly string $content;

    /**
     * 扁平业务变量（如 ['订单编号' => 'wx123', '支付金额' => '99.00']）
     * @var array
     */
    public readonly array $variables;

    /**
     * 该通知在该渠道的附加配置（来自 eb_notice_template.extra JSON，如 tempid / webhook / key_list / sms_id）
     * @var array
     */
    public readonly array $extra;

    /**
     * @param string $mark      消息标识
     * @param int    $uid       接收用户ID，平台通知为 0
     * @param string $title     模板标题
     * @param string $content   模板内容
     * @param array  $variables 扁平业务变量
     * @param array  $extra     渠道附加配置
     */
    public function __construct(
        string $mark,
        int $uid,
        string $title = '',
        string $content = '',
        array $variables = [],
        array $extra = []
    ) {
        $this->mark = $mark;
        $this->uid = $uid;
        $this->title = $title;
        $this->content = $content;
        $this->variables = $variables;
        $this->extra = $extra;
    }
}
