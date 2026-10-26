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
 * 消息渠道适配器抽象基类
 *
 * 提供渠道声明（key/名称/额外字段/支持范围）与占位符渲染等通用能力，
 * 具体渠道只需继承本类并实现 send(NoticeMessage)：发送成功直接返回，
 * 失败抛 NoticeSendException（由 Notice::deliver() 捕获记日志）。
 *
 * 依赖边界（架构铁律：crmeb/ 不可调用 app/）：
 * - 本类位于 crmeb/ 核心层，不依赖任何 app/ 层服务。
 * - 所有渠道适配器统一放在 app/services/message/channel/ 下继承本类：
 *   有 app 依赖的（System/Sms/Email）通过 app()->make() 调用 app 层服务；
 *   纯调用 driver/ 的（DingTalk/EntWechat）直接调用 crmeb 层 Bot/Http。
 */
abstract class AbstractNoticeChannel implements NoticeChannelInterface
{
    /**
     * 渠道标识（对应 eb_notice_channel.channel_key）
     * 子类必须覆盖此方法，返回如 'is_system'、'is_sms' 等
     * @return string
     */
    public static function channelKey(): string
    {
        return '';
    }

    /**
     * 渠道显示名称（如 系统站内信、短信），子类覆写
     * @return string
     */
    public static function channelName(): string
    {
        return static::channelKey();
    }

    /**
     * 本渠道需要额外存储的配置字段，格式 [字段名 => 默认值]。
     * 通用字段 template_title / template_content / status 由框架统一处理，无需在此声明。
     * @return array
     */
    public static function getExtraFields(): array
    {
        return [];
    }

    /**
     * 渠道支持的消息范围，默认用户端/平台端均支持。
     * 仅平台通知的渠道（如钉钉/企业微信群机器人）覆写为仅平台端。
     * @return array ['support_user' => 0|1, 'support_platform' => 0|1]
     */
    public static function getSupportScope(): array
    {
        return ['support_user' => 1, 'support_platform' => 1];
    }

    /**
     * 将扁平业务变量渲染进模板文本：替换全部 {key} 占位符
     *
     * 值统一转 string，避免 int 参数触发 str_replace TypeError；
     * 非标量值（数组/对象）跳过不参与替换。
     * 静态入口供写发送日志等非渠道场景复用，与实例方法行为一致。
     *
     * @param string $text      模板文本（含 {key} 占位符）
     * @param array  $variables 扁平业务变量
     * @return string
     */
    public static function renderTemplate(string $text, array $variables): string
    {
        if ($text === '' || empty($variables)) {
            return $text;
        }
        foreach ($variables as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            $text = str_replace('{' . $key . '}', (string)$value, $text);
        }
        return $text;
    }

    /**
     * 实例侧占位符渲染（委托静态实现，保持子类调用习惯）
     *
     * @param string $text      模板文本（含 {key} 占位符）
     * @param array  $variables 扁平业务变量
     * @return string
     */
    protected function renderPlaceholders(string $text, array $variables): string
    {
        return static::renderTemplate($text, $variables);
    }
}
