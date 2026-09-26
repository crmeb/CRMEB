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

use crmeb\interfaces\McpToolInterface;
use crmeb\services\CacheService;
use modules\service\mcp\McpException;
use modules\service\mcp\service\McpUploadTicketServices;

/**
 * MCP 工具：图片直传票据
 *
 * 跨机场景（AI 客户端与商城服务器不同机）下，AI 本机生成的图片文件无法
 * 通过工具参数传递（base64 会占用大量对话上下文），服务器也读不到客户端
 * 磁盘。本工具签发一次性 HTTP 直传票据：AI 客户端在自己所在机器用 curl
 * 等把文件直传到 /api/mcp/upload，字节不经过对话上下文、命令中也无需
 * 出现 MCP 密钥；随后用端点返回的 ref 调 crmeb_upload_image 完成入库。
 */
class UploadTicketTool implements McpToolInterface
{
    /**
     * 单账号每分钟票据签发上限
     */
    private const RATE_LIMIT_PER_MINUTE = 10;

    /**
     * 与接收端点一致的接收口径（回给调用方，便于一次成型）
     */
    private const MAX_BATCH = 20;
    private const MAX_FILE_SIZE = 10485760;

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_upload_ticket';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '获取图片直传票据：只要需要上传「AI 运行所在机器」上的图片文件（本机生成或下载的商品图一律属于此类），'
            . '必须先调用本工具拿票据与 upload_url（这是本机图片上传的默认方式，不要改用 base64 内联替代），'
            . '然后在你所在机器执行返回的 curl 命令（或等价 HTTP 表单请求）把文件直传服务器——'
            . '图片字节不经过对话上下文，命令中也不需要 MCP 密钥，是跨机场景（AI 客户端与服务器不同机）的推荐上传入口。'
            . '【完整链路】1）本工具签发票据；2）本机执行上传命令（multipart/form-data，字段 ticket 与 files[]，'
            . '单次最多 20 张、单张 10MB、仅 jpg/png/gif/webp）；3）把响应 data.received[].ref（如 runtime/upload/mcp_recv/up_x.png）'
            . '作为 source 调用 crmeb_upload_image 换取正式站点地址（自动去重/登记素材库/返回元数据），再写入商品等字段。'
            . '【口径】票据 10 分钟内有效、仅签发账号可用、可重试；暂存文件保留 1 小时后自动清理，入库失败可只重试第 3 步不必重传。'
            . '若所在环境无法发起 HTTP 上传（无 shell/curl），改用 crmeb_upload_image 的 http(s) URL 来源，'
            . '或 base64（仅 ≤512KB 的极小图应急）。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '图片直传票据';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '系统设置';
    }

    /**
     * 入参 Schema（无参数：票据只与调用账号绑定）
     * @return array
     */
    public function inputSchema(): array
    {
        return ['type' => 'object'];
    }

    /**
     * 执行工具：签发直传票据并返回可直接执行的命令
     *
     * @param array $args 参数（本工具无参数）
     * @return array ticket/upload_url/expires_in/command 等
     * @throws McpException 无法识别账号或触发限流时抛出
     */
    public function execute(array $args): array
    {
        $key = $this->clientKey();
        if ($key === '') {
            throw new McpException('无法识别调用账号：缺少 X-Mcp-Key 或 Authorization 请求头');
        }
        $this->checkRateLimit($key);

        /** @var McpUploadTicketServices $services */
        $services = app()->make(McpUploadTicketServices::class);
        $ticket = $services->issue($key);

        // 客户端只用站点公网地址，与服务端部署形态（容器/物理机/路径）无关
        $base = rtrim((string)sys_config('site_url'), '/');
        if ($base === '') {
            // site_url 未配置时退回客户端实际访问的域名，保证返回地址对客户端可达
            $base = rtrim((string)request()->domain(), '/');
        }
        $uploadUrl = $base . '/api/mcp/upload';

        return [
            'ticket'     => $ticket,
            'upload_url' => $uploadUrl,
            'expires_in' => McpUploadTicketServices::TTL,
            'max_files'  => self::MAX_BATCH,
            'max_size'   => self::MAX_FILE_SIZE,
            'accept'     => 'jpg/jpeg/png/gif/webp',
            'command'    => 'curl -s -F "ticket=' . $ticket . '" -F "files[]=@/本机图片路径.png" ' . $uploadUrl,
            'next_step'  => '把上传响应 data.received[].ref 作为 source 调用 crmeb_upload_image 完成正式入库',
        ];
    }

    /**
     * 取调用账号密钥（与 McpController 认证口径一致：X-Mcp-Key 或 Bearer）
     * @return string
     */
    private function clientKey(): string
    {
        try {
            $key = (string)request()->header('x-mcp-key', '');
            if ($key === '') {
                $authorization = (string)request()->header('authorization', '');
                if (stripos($authorization, 'bearer ') === 0) {
                    $key = trim(substr($authorization, 7));
                }
            }
            return $key;
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * 按密钥限流签发：每分钟最多 RATE_LIMIT_PER_MINUTE 张票据
     *
     * @param string $key 调用账号密钥
     * @throws McpException 超限时抛出
     */
    private function checkRateLimit(string $key): void
    {
        $windowKey = 'mcp_upload_ticket_rl_' . md5($key);
        $used = (int)CacheService::get($windowKey, 0);
        if ($used + 1 > self::RATE_LIMIT_PER_MINUTE) {
            throw new McpException('票据签发频率超限：每分钟最多 ' . self::RATE_LIMIT_PER_MINUTE . ' 次，请稍后重试');
        }
        CacheService::set($windowKey, $used + 1, 60);
    }
}