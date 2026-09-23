<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\message\channel;

use app\services\message\NoticeServices;
use crmeb\exceptions\NoticeSendException;
use crmeb\services\notice\AbstractNoticeChannel;
use crmeb\services\notice\NoticeMessage;

/**
 * 系统站内信渠道
 *
 * 写入 eb_notice + notice_read 表，依赖 app 层 NoticeServices，
 * 故置于 app/services/message/channel/ 下。
 * uid > 0 发给对应用户，uid = 0（平台通知）发给客服端（收件人由事件解析）。
 */
class SystemChannel extends AbstractNoticeChannel
{
    public static function channelKey(): string
    {
        return 'is_system';
    }

    public static function channelName(): string
    {
        return '系统站内信';
    }

    /**
     * 发送站内信
     *
     * @param NoticeMessage $message 待投递消息
     * @throws NoticeSendException 写入失败或平台通知无客服收件人
     */
    public function send(NoticeMessage $message): void
    {
        // 平台通知：收件人由客服插件监听 AdminNoticeRecipientResolve 事件返回，避免 app 层引用客服服务
        if ($message->uid === 0) {
            $this->kefuSystemSend($message);
            return;
        }

        // 模板为空时降级：标题回退为消息标识，内容拼接变量值
        $title = $message->title !== '' ? $this->renderPlaceholders($message->title, $message->variables) : $message->mark;
        $str = $message->content !== '' ? $this->renderPlaceholders($message->content, $message->variables) : $this->buildFallbackContent($message->variables);

        try {
            /** @var NoticeServices $noticeService */
            $noticeService = app()->make(NoticeServices::class);
            $noticeService->createNoticeForUser($message->uid, $title, $str, $message->mark, $message->variables);
        } catch (NoticeSendException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new NoticeSendException('站内信写入失败：' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 给客服端发站内信（写入 eb_notice + notice_read）
     *
     * @param NoticeMessage $message 待投递消息
     * @throws NoticeSendException 无客服收件人或写入失败
     */
    protected function kefuSystemSend(NoticeMessage $message): void
    {
        $adminUids = [];
        $results = event('AdminNoticeRecipientResolve', []);
        if (is_array($results)) {
            foreach ($results as $result) {
                if (is_array($result)) {
                    $adminUids = $result;
                    break;
                }
            }
        }
        if (empty($adminUids)) {
            throw new NoticeSendException('站内信平台通知无客服收件人');
        }

        // 模板为空时降级
        $title = $message->title !== '' ? $this->renderPlaceholders($message->title, $message->variables) : $message->mark;
        $str = $message->content !== '' ? $this->renderPlaceholders($message->content, $message->variables) : $this->buildFallbackContent($message->variables);

        try {
            /** @var NoticeServices $noticeService */
            $noticeService = app()->make(NoticeServices::class);
            foreach ($adminUids as $uid) {
                $noticeService->createNoticeForUser((int)$uid, $title, $str, $message->mark, $message->variables);
            }
        } catch (\Throwable $e) {
            throw new NoticeSendException('站内信客服通知写入失败：' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 构建降级内容：模板为空时，将数据值拼接为可读文本
     * @param array $data 模板变量
     * @return string
     */
    private function buildFallbackContent(array $data): string
    {
        if (empty($data)) {
            return '';
        }
        // 过滤掉非标量值，拼接为 "值1、值2" 格式
        $parts = [];
        foreach ($data as $value) {
            if (is_scalar($value) && $value !== '' && $value !== null) {
                $parts[] = (string)$value;
            }
        }
        return implode('，', $parts);
    }
}
