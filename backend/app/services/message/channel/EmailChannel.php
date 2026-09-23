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

use app\services\driver\email\EmailDriverServices;
use crmeb\exceptions\NoticeSendException;
use crmeb\services\email\Email;
use crmeb\services\notice\AbstractNoticeChannel;
use crmeb\services\notice\NoticeMessage;
use think\facade\Log;

/**
 * 邮件渠道
 *
 * 依赖 app 层 EmailDriverServices 读取启用的邮件驱动配置，故置于 app/services/message/channel/ 下。
 * sendEmail() 方法同时供外部直接调用（AI 助手任务、MCP 邮件工具）。
 */
class EmailChannel extends AbstractNoticeChannel
{
    public static function channelKey(): string
    {
        return 'is_email';
    }

    public static function channelName(): string
    {
        return '邮件';
    }

    /**
     * 发送邮件（通用入口，可被外部直接调用）
     * @param $to
     * @param string $subject
     * @param string $content
     * @param array $options
     * @return bool
     */
    public function sendEmail($to, string $subject, string $content, array $options = []): bool
    {
        try {
            $this->dispatchEmail($to, $subject, $content, $options);
            return true;
        } catch (\Throwable $e) {
            Log::error('发送邮件失败,失败原因:' . $e->getMessage());
            return false;
        }
    }

    /**
     * 邮件发送核心流程（经启用的邮件驱动投递）
     *
     * @param string $to      收件邮箱
     * @param string $subject 主题
     * @param string $content  内容
     * @param array  $options  附加选项（透传驱动）
     * @throws NoticeSendException 驱动未配置、收件人为空或网关拒绝
     */
    protected function dispatchEmail(string $to, string $subject, string $content, array $options = []): void
    {
        if (!$to) {
            throw new NoticeSendException('收件邮箱为空');
        }

        /** @var EmailDriverServices $driverServices */
        $driverServices = app()->make(EmailDriverServices::class);
        $enabledDriver = $driverServices->getEnabledDriver();

        if (!$enabledDriver) {
            throw new NoticeSendException('未配置启用的邮件驱动');
        }

        $type = $enabledDriver['driver_key'];
        $config = $this->getDriverConfig($enabledDriver);

        $emailMake = app()->make(Email::class, [$type, $config]);
        $res = $emailMake->send($to, $subject, $content, $options);

        if ($res === false) {
            throw new NoticeSendException((string)$emailMake->getError() ?: '邮件网关拒绝');
        }
    }

    /**
     * 从驱动配置中提取配置数组
     * @param array $driver
     * @return array
     */
    protected function getDriverConfig(array $driver): array
    {
        $config = [];
        $configFields = is_array($driver['config_fields']) ? $driver['config_fields'] : [];
        foreach ($configFields as $field) {
            if (isset($field['field']) && isset($field['value'])) {
                $config[$field['field']] = $field['value'];
            }
        }
        return $config;
    }

    /**
     * 发送邮件通知（通知管线入口）
     *
     * 收件邮箱优先取业务变量（user_email / email）；
     * 用户通知（uid>0）未显式传入时，按 uid 从用户资料自动解析。
     *
     * @param NoticeMessage $message 待投递消息
     * @throws NoticeSendException 收件邮箱缺失、模板为空或投递失败
     */
    public function send(NoticeMessage $message): void
    {
        $to = (string)($message->variables['user_email'] ?? $message->variables['email'] ?? '');
        if ($to === '' && $message->uid > 0) {
            $to = (string)app()->make(\app\services\user\UserServices::class)->value(['uid' => $message->uid], 'email');
        }
        if ($to === '') {
            throw new NoticeSendException('邮件发送缺少收件邮箱（变量 user_email/email 及用户资料均未提供）');
        }

        if ($message->title === '' || $message->content === '') {
            throw new NoticeSendException('邮件主题或内容为空');
        }

        $subject = $this->renderPlaceholders($message->title, $message->variables);
        $content = $this->renderPlaceholders($message->content, $message->variables);

        $this->dispatchEmail($to, $subject, $content, $message->variables);
    }
}
