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
 * 消息渠道适配器接口
 *
 * 所有消息通知渠道（站内信/短信/邮件/钉钉/企业微信/公众号/小程序等）必须实现此接口。
 * 渠道适配器类名登记在 eb_notice_channel.adapter_class，由 Notice 门面按配置直接实例化调度。
 *
 * 新增渠道只需：
 * 1. 创建实现类 implements NoticeChannelInterface（通常继承 AbstractNoticeChannel）
 * 2. 在所属 Installer 的 getNoticeChannelDefinitions() 登记 channel_key 与 adapter_class（种子写入渠道表）
 */
interface NoticeChannelInterface
{
    /**
     * 渠道标识（对应 eb_notice_channel.channel_key，如 is_system、is_sms）
     * @return string
     */
    public static function channelKey(): string;

    /**
     * 渠道显示名称（如 系统站内信、短信），用于后台展示和发送日志
     * @return string
     */
    public static function channelName(): string;

    /**
     * 本渠道需要额外存储的配置字段，格式 [字段名 => 默认值]
     *
     * 这些字段来自前端表单的非通用字段（如 webhook、tempid、key_list 等），
     * 会被序列化存入 eb_notice_template.extra JSON 列，发送时经 NoticeMessage::$extra 传入。
     * 通用字段 template_title / template_content / status 由框架统一处理，无需声明。
     *
     * @return array
     */
    public static function getExtraFields(): array;

    /**
     * 渠道支持的消息范围（种子写入 eb_notice_channel 的初始值）
     *
     * 仅平台通知的渠道（如钉钉/企业微信群机器人，无法向具体用户发消息）
     * 应覆写为 ['support_user' => 0, 'support_platform' => 1]。
     *
     * @return array ['support_user' => 0|1, 'support_platform' => 0|1]
     */
    public static function getSupportScope(): array;

    /**
     * 发送一条通知
     *
     * @param NoticeMessage $message 待投递消息（接收者、模板、变量、附加配置）
     * @throws \crmeb\exceptions\NoticeSendException 任何失败（配置缺失/驱动报错/网关拒绝）都抛出，消息即原因；
     *                                               由 Notice::deliver() 捕获记录，不中断其他渠道
     */
    public function send(NoticeMessage $message): void;
}
