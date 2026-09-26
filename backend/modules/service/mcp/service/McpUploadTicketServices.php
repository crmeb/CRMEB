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

namespace modules\service\mcp\service;

use crmeb\services\CacheService;
use modules\service\mcp\McpException;

/**
 * MCP 图片直传票据服务
 *
 * 跨机场景（AI 客户端与商城服务器不同机）下，图片字节既无法通过工具参数
 * 传递（base64 会占用大量对话上下文），服务器也读不到客户端磁盘。票据是
 * 带外直传的授权凭据：MCP 工具 crmeb_upload_ticket 签发，AI 客户端在自己
 * 所在机器用 HTTP 把文件直传到 /api/mcp/upload，命令中无需出现 MCP 密钥。
 *
 * 安全口径：32 位随机 hex，服务端只存 sha256 摘要（明文不落缓存键与日志）；
 * 10 分钟有效，仅授予「写入暂存区」能力，正式入库仍由 crmeb_upload_image
 * 按原口径完成（去重/素材库/限流/审计）。票据不做单次消费，重试友好：
 * 重复提交的内容按 md5 去重收敛，暂存文件由接收端点按 1 小时 TTL 清理。
 */
class McpUploadTicketServices
{
    /**
     * 票据有效期（秒）
     */
    public const TTL = 600;

    /**
     * 票据缓存键前缀（键内为票据摘要）
     */
    private const CACHE_PREFIX = 'mcp:upload_ticket:';

    /**
     * 签发票据（返回明文，服务端仅存摘要）
     *
     * @param string $key 调用账号的 MCP 密钥
     * @return string 32 位 hex 票据
     * @throws McpException 缓存写入失败时抛出
     */
    public function issue(string $key): string
    {
        $ticket = bin2hex(random_bytes(16));
        $cacheKey = self::CACHE_PREFIX . hash('sha256', $ticket);
        $payload = ['key_md5' => md5($key), 'issued_at' => time()];
        // 写入后读回确认，避免发放无法校验的票据
        if (CacheService::set($cacheKey, $payload, self::TTL) !== true || !is_array(CacheService::get($cacheKey))) {
            throw new McpException('上传票据签发失败，请稍后重试');
        }
        return $ticket;
    }

    /**
     * 校验票据并返回载荷
     *
     * @param string $ticket 明文票据
     * @return array|null {key_md5, issued_at}；无效或已过期返回 null
     */
    public function verify(string $ticket): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $ticket)) {
            return null;
        }
        $payload = CacheService::get(self::CACHE_PREFIX . hash('sha256', $ticket));
        return is_array($payload) && isset($payload['key_md5']) ? $payload : null;
    }
}