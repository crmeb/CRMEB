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

use crmeb\utils\Http;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;
use think\facade\Log;

/**
 * MCP 工具：企业微信群机器人消息推送
 *
 * Webhook 地址由系统配置 aiagent_qywechat_webhook 统一提供，入参刻意不包含
 * 推送目标——模型无法指定任意 Webhook（杜绝 SSRF 与消息外劫持），只需提供内容。
 * 供 AI 对话（确认后推送）与自动化任务（无人值守直推）共用。
 */
class EntWechatSendTool implements McpToolInterface
{
    /** 机器人 Webhook 所在的系统配置变量名 */
    public const CONFIG_KEY = 'aiagent_qywechat_webhook';

    /** 企业微信 markdown 消息正文长度上限（字符），官方上限 4096 字节，留冗余取 3000 字符 */
    private const MAX_CONTENT_LENGTH = 3000;

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_qywechat_send';
    }

    /**
     * 交互声明：推送为对外副作用动作，对话场景需用户确认后才真实执行；
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
        return '向企业微信群机器人推送 Markdown 消息；机器人地址由系统统一配置，无需也无法指定。'
            . '需要传入中文标题与 Markdown 格式内容。发送失败时会返回失败原因，请勿使用相同参数原样重试';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '企微机器人推送';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'required'   => ['title', 'content'],
            'properties' => [
                'title'   => ['type' => 'string', 'description' => '消息标题，用中文概括消息主题'],
                'content' => ['type' => 'string', 'description' => '消息内容，Markdown 格式（支持标题/加粗/列表/换行）'],
            ],
        ];
    }

    /**
     * 执行推送
     * @param array $args 参数（title/content）
     * @return array 推送结果
     * @throws McpException 参数缺失、Webhook 未配置或推送失败
     */
    public function execute(array $args): array
    {
        $title = trim((string)($args['title'] ?? ''));
        $content = (string)($args['content'] ?? '');
        if ($title === '') {
            throw new McpException('消息标题不能为空');
        }
        if (trim($content) === '') {
            throw new McpException('消息内容不能为空');
        }
        $webhook = trim((string)sys_config(self::CONFIG_KEY, ''));
        if ($webhook === '' || !filter_var($webhook, FILTER_VALIDATE_URL)) {
            throw new McpException('企业微信机器人 Webhook 未配置，请先在后台系统配置「AI 自动化任务」中填写推送地址');
        }
        if (mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
            $content = mb_substr($content, 0, self::MAX_CONTENT_LENGTH) . "\n…（内容过长已截断）";
        }

        $markdown = "## {$title}\n" . $content;
        try {
            $response = Http::postRequest($webhook, json_encode([
                'msgtype'  => 'markdown',
                'markdown' => ['content' => $markdown],
            ], JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            Log::error('[MCP EntWechatSendTool] 推送异常', ['exception' => get_class($e)]);
            throw new McpException('企业微信消息推送失败，请检查机器人配置与服务状态');
        }
        // 企业微信返回体 errcode 非 0 视为失败（如 webhook 失效、频控限制）
        $body = is_string($response) ? json_decode($response, true) : (array)$response;
        if (isset($body['errcode']) && (int)$body['errcode'] !== 0) {
            throw new McpException('企业微信消息推送失败，请检查机器人配置（errcode=' . (int)$body['errcode'] . '）');
        }
        return [
            'success' => true,
            'title'   => $title,
            'message' => '企业微信消息推送成功',
        ];
    }
}
