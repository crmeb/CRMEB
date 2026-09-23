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
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\services\message\channel\EmailChannel;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;
use think\facade\Log;

/**
 * MCP 工具：发送邮件
 *
 * 通过系统邮件渠道（后台已启用的邮件驱动）向任意收件邮箱发送邮件。
 * 供 AI 对话（确认后发送）与自动化任务（无人值守直发）共用。
 */
class EmailSendTool implements McpToolInterface
{
    /** 正文长度上限（字符），超出截断保护下游 SMTP */
    private const MAX_CONTENT_LENGTH = 20000;

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_email_send';
    }

    /**
     * 交互声明：发送邮件为对外副作用动作，对话场景需用户确认后才真实执行；
     * 自动化任务无人值守场景由调用方按自主级别豁免确认
     * @return string
     */
    public function interaction(): string
    {
        return 'execute';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '通过系统邮件渠道向指定收件邮箱发送邮件。'
            . '需要传入收件邮箱、中文邮件主题与正文（正文按 HTML 渲染，必须传入邮件客户端兼容的内联样式 HTML，'
            . '不支持 Markdown，纯文本换行需用 <p>/<br> 标签）。'
            . '发送失败时会返回失败原因，请勿使用相同参数原样重试';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '发送邮件';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'required'   => ['to', 'subject', 'content'],
            'properties' => [
                'to'      => ['type' => 'string', 'description' => '收件邮箱'],
                'subject' => ['type' => 'string', 'description' => '邮件主题，用中文概括邮件内容'],
                'content' => ['type' => 'string', 'description' => '邮件正文，邮件客户端兼容的简单 HTML（内联样式），Markdown 源码会被原样显示禁止传入'],
            ],
        ];
    }

    /**
     * 执行发送
     * @param array $args 参数（to/subject/content）
     * @return array 发送结果
     * @throws McpException 参数缺失或邮箱不合法
     */
    public function execute(array $args): array
    {
        $to = strtolower(trim((string)($args['to'] ?? '')));
        $subject = trim((string)($args['subject'] ?? ''));
        $content = (string)($args['content'] ?? '');
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new McpException('收件邮箱不能为空且必须是合法邮箱地址');
        }
        if ($subject === '') {
            throw new McpException('邮件主题不能为空');
        }
        if (trim($content) === '') {
            throw new McpException('邮件正文不能为空');
        }
        if (mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
            $content = mb_substr($content, 0, self::MAX_CONTENT_LENGTH) . "\n…（正文过长已截断）";
        }

        try {
            $result = app()->make(EmailChannel::class)->sendEmail($to, $subject, $content);
        } catch (\Throwable $e) {
            Log::error('[MCP EmailSendTool] 发送异常', ['exception' => get_class($e)]);
            throw new McpException('邮件发送失败，请检查邮件驱动配置与服务状态');
        }
        if (!$result) {
            throw new McpException('邮件发送失败：请检查后台邮件驱动是否已配置并启用');
        }
        return [
            'success' => true,
            'to'      => $to,
            'subject' => $subject,
            'message' => '邮件发送成功',
        ];
    }
}
