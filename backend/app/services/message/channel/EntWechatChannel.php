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
use crmeb\services\notice\AbstractNoticeChannel;
use crmeb\services\notice\NoticeMessage;
use crmeb\utils\Http;

/**
 * 企业微信群机器人消息渠道
 *
 * 调用 crmeb\utils\Http 向机器人 webhook 发送 markdown 消息（app 可调 crmeb，合法）。
 * Webhook 地址来自 eb_notice_template.extra.url 字段。
 */
class EntWechatChannel extends AbstractNoticeChannel
{
    public static function channelKey(): string
    {
        return 'is_ent_wechat';
    }

    public static function channelName(): string
    {
        return '企业微信群机器人';
    }

    /**
     * 本渠道额外配置字段：机器人 Webhook URL
     * @return array
     */
    public static function getExtraFields(): array
    {
        return ['url' => ''];
    }

    /**
     * 仅支持平台端消息：群机器人只能发到企业微信群，无法向具体用户发消息
     * @return array
     */
    public static function getSupportScope(): array
    {
        return ['support_user' => 0, 'support_platform' => 1];
    }

    /**
     * @param bool|string $response 企业微信 HTTP 响应
     * @return bool
     */
    public static function responseSucceeded($response): bool
    {
        if (!is_string($response)) return false;
        $result = json_decode($response, true);
        return is_array($result) && (($result['errcode'] ?? -1) === 0 || ($result['code'] ?? -1) === 0);
    }

    /**
     * 发送企业微信群机器人消息
     *
     * @param NoticeMessage $message 待投递消息（此渠道为平台通知，uid 固定为 0）
     * @throws NoticeSendException webhook 未配置、模板内容为空或机器人响应失败
     */
    public function send(NoticeMessage $message): void
    {
        $url = (string)($message->extra['url'] ?? '');
        if ($url === '') {
            throw new NoticeSendException('企业微信机器人 webhook 未配置');
        }
        if ($message->content === '') {
            throw new NoticeSendException('企业微信模板内容为空');
        }

        // 替换模板变量后转企业微信 markdown 格式（引用块）
        $content = $this->renderPlaceholders($message->content, $message->variables);
        $content = str_replace('\n', "\n>", $content);
        $content = "> {$content}";

        $response = Http::postRequest($url, json_encode([
            'msgtype' => 'markdown',
            'markdown' => ['content' => $content],
        ], JSON_UNESCAPED_UNICODE));

        if (!self::responseSucceeded($response)) {
            throw new NoticeSendException('企业微信机器人响应失败：' . (is_string($response) ? $response : '空响应'));
        }
    }
}
