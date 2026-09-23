<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\services\message\channel;

use crmeb\exceptions\NoticeSendException;
use crmeb\services\bot\Bot;
use crmeb\services\notice\AbstractNoticeChannel;
use crmeb\services\notice\NoticeMessage;

/**
 * 钉钉机器人消息渠道
 *
 * 调用 crmeb\services\bot\Bot 驱动发送 markdown 消息（app 可调 crmeb，合法）。
 * Webhook 地址来自 eb_notice_template.extra.webhook 字段。
 */
class DingTalkChannel extends AbstractNoticeChannel
{
    public static function channelKey(): string
    {
        return 'is_dingtalk';
    }

    public static function channelName(): string
    {
        return '钉钉';
    }

    /**
     * 本渠道额外配置字段：Webhook 地址
     * @return array
     */
    public static function getExtraFields(): array
    {
        return ['webhook' => ''];
    }

    /**
     * 仅支持平台端消息：群机器人只能发到钉钉群，无法向具体用户发消息
     * @return array
     */
    public static function getSupportScope(): array
    {
        return ['support_user' => 0, 'support_platform' => 1];
    }

    /**
     * 发送钉钉机器人消息
     *
     * @param NoticeMessage $message 待投递消息（此渠道为平台通知，uid 固定为 0）
     * @throws NoticeSendException webhook 未配置、模板内容为空或机器人返回失败
     */
    public function send(NoticeMessage $message): void
    {
        // webhook 来自该通知在该渠道的 extra 配置
        $webhook = (string)($message->extra['webhook'] ?? '');
        if ($webhook === '') {
            throw new NoticeSendException('钉钉 webhook 未配置');
        }
        if ($message->content === '') {
            throw new NoticeSendException('钉钉模板内容为空');
        }

        // 模板变量替换
        $title = $this->renderPlaceholders($message->title !== '' ? $message->title : '系统通知', $message->variables);
        $content = $this->renderPlaceholders($message->content, $message->variables);

        if (!Bot::sendMarkdown('ding_talk', $webhook, $title, $content)) {
            throw new NoticeSendException('钉钉机器人返回发送失败');
        }
    }
}
