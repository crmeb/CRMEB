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

namespace modules\service\mcp\controller;

use crmeb\basic\BaseController;
use crmeb\services\CacheService;
use modules\service\mcp\service\McpUploadTicketServices;
use think\facade\Log;
use think\file\UploadedFile;
use think\Request;
use think\response\Json;

/**
 * MCP 图片直传端点
 *
 * 跨机场景（AI 客户端与商城服务器不同机）下，把客户端本机图片字节带外
 * 写入服务器暂存区的 HTTP 入口：POST /api/mcp/upload，multipart/form-data，
 * 表单字段 ticket（crmeb_upload_ticket 签发）+ files[]（可多张）。
 *
 * 职责边界：本端点只做「收字节 + 暂存 + 过期清理」，不做正式入库；正式
 * 入库统一由 MCP 工具 crmeb_upload_image 完成（去重/素材库/限流/审计口径
 * 全部收敛在该工具内），端点返回的 ref 直接作为该工具的 source 即可。
 * 这样入库失败时只需重试工具调用，无需客户端重传文件。
 *
 * 鉴权：仅凭一次性票据（10 分钟，服务端只存摘要），不要求 X-Mcp-Key——
 * 避免 AI 客户端把长期密钥写进命令、进入对话上下文与命令历史；同时受
 * MCP 服务总开关 mcp_enabled 约束。
 */
class McpUploadController extends BaseController
{
    /**
     * 单次最多接收张数（与 crmeb_upload_image 单次上限一致）
     */
    private const MAX_BATCH = 20;

    /**
     * 单张字节上限（与 crmeb_upload_image 外链/本地口径一致）
     */
    private const MAX_FILE_SIZE = 10485760;

    /**
     * 单账号每分钟接收张数上限
     */
    private const RATE_LIMIT_PER_MINUTE = 60;

    /**
     * 暂存区保留时长（秒），超时文件由本端点机会式清理
     */
    private const STAGING_TTL = 3600;

    /**
     * 暂存区（项目根相对路径，位于本地路径白名单目录 runtime/upload 内）
     */
    private const STAGING_DIR = 'runtime/upload/mcp_recv';

    /**
     * 单次清理扫描的文件数上限（避免大目录拖慢请求）
     */
    private const GC_SCAN_LIMIT = 200;

    /**
     * 允许接收的图片 mime → 扩展名（落盘扩展名以内容为准）
     */
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    /**
     * 初始化（直传端点不使用后台/用户 Token 体系，鉴权在 index 内按票据处理）
     */
    protected function initialize()
    {
    }

    /**
     * 接收直传图片字节：写入暂存区并返回暂存引用
     *
     * @param Request $request HTTP 请求对象
     * @return Json {code, msg, data:{total, count, received[], failed[]}}
     */
    public function index(Request $request): Json
    {
        if (!intval(sys_config('mcp_enabled', 1))) {
            return $this->fail(403, 'MCP 服务未开启');
        }

        $ticket = trim((string)$request->post('ticket', ''));
        if ($ticket === '') {
            return $this->fail(401, '缺少上传票据：请先调用 MCP 工具 crmeb_upload_ticket 获取'
                . '（若表单较大，请确认服务端 post_max_size / upload_max_filesize 足够）');
        }
        /** @var McpUploadTicketServices $ticketServices */
        $ticketServices = app()->make(McpUploadTicketServices::class);
        $payload = $ticketServices->verify($ticket);
        if ($payload === null) {
            return $this->fail(401, '上传票据无效或已过期，请重新调用 MCP 工具 crmeb_upload_ticket 获取');
        }

        // PHP 在 multipart 解析阶段即按 php.ini max_file_uploads（默认 20）截断超量文件，
        // 控制器只能看到被截断后的集合；识别解析期告警，避免「调用方以为全部成功、实际
        // 少收」的静默丢件（下方 count 校验只在 ini 上限高于本端点上限时才可能触发）
        $parseError = error_get_last();
        if (is_array($parseError) && str_contains((string)($parseError['message'] ?? ''), 'Maximum number of allowable file uploads')) {
            return $this->fail(400, '上传张数超过服务端限制（php.ini max_file_uploads=' . (int)ini_get('max_file_uploads')
                . '），请拆分后重试，单次最多 ' . self::MAX_BATCH . ' 张');
        }

        $files = $request->file('files') ?? $request->file('file');
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }
        if (!is_array($files) || $files === []) {
            return $this->fail(400, '未收到上传文件：请使用 multipart/form-data，文件字段名 files[]（单张可用 file）');
        }
        if (count($files) > self::MAX_BATCH) {
            return $this->fail(400, '单次最多上传 ' . self::MAX_BATCH . ' 张');
        }

        // 频率限制：按票据所属账号，防止单个账号高频灌入暂存区
        $windowKey = 'mcp_direct_upload_rl_' . (string)$payload['key_md5'];
        $used = (int)CacheService::get($windowKey, 0);
        if ($used + count($files) > self::RATE_LIMIT_PER_MINUTE) {
            return $this->fail(429, '直传频率超限：单账号每分钟最多 ' . self::RATE_LIMIT_PER_MINUTE . ' 张，请稍后重试');
        }
        CacheService::set($windowKey, $used + count($files), 60);

        $stagingDir = root_path(self::STAGING_DIR);
        if (!is_dir($stagingDir) && !@mkdir($stagingDir, 0755, true) && !is_dir($stagingDir)) {
            return $this->fail(500, '暂存目录创建失败，请检查 runtime 目录权限');
        }
        $this->gcStaging($stagingDir);

        $received = [];
        $failed = [];
        foreach (array_values($files) as $index => $file) {
            try {
                if (!$file instanceof UploadedFile) {
                    throw new \RuntimeException('文件上传失败（可能超过 php.ini 上传限制或表单缺少 enctype）');
                }
                $originalName = (string)$file->getOriginalName();
                if ((int)$file->getSize() > self::MAX_FILE_SIZE) {
                    throw new \RuntimeException('图片大小超过 10MB 限制');
                }
                $content = (string)file_get_contents($file->getRealPath());
                $image = @getimagesizefromstring($content);
                $mime = (string)($image['mime'] ?? '');
                $ext = self::MIME_EXTENSIONS[$mime] ?? '';
                if ($ext === '') {
                    throw new \RuntimeException('仅支持 jpg/png/gif/webp 格式图片');
                }
                $filename = 'up_' . bin2hex(random_bytes(6)) . '.' . $ext;
                if (@file_put_contents($stagingDir . '/' . $filename, $content) === false) {
                    throw new \RuntimeException('暂存写入失败');
                }
                $received[] = [
                    'index' => $index,
                    'ok'    => true,
                    'ref'   => self::STAGING_DIR . '/' . $filename,
                    'name'  => $originalName,
                    'size'  => strlen($content),
                    'mime'  => $mime,
                ];
            } catch (\Throwable $e) {
                $failed[] = [
                    'index'  => $index,
                    'ok'     => false,
                    'name'   => $file instanceof UploadedFile ? (string)$file->getOriginalName() : '',
                    'reason' => $e->getMessage() ?: '未知错误',
                ];
            }
        }

        // 审计：只记录票据归属摘要、结果计数与暂存引用，不记录文件内容
        try {
            Log::info('MCP图片直传', [
                'key_md5'  => (string)$payload['key_md5'],
                'total'    => count($files),
                'received' => count($received),
                'failed'   => count($failed),
                'refs'     => array_column($received, 'ref'),
            ]);
        } catch (\Throwable $e) {
        }

        return json([
            'code' => 200,
            'msg'  => 'success',
            'data' => [
                'total'     => count($files),
                'count'     => count($received),
                'received'  => $received,
                'failed'    => $failed,
                'next_step' => '把 received[].ref 作为 source 调用 MCP 工具 crmeb_upload_image 完成正式入库',
            ],
        ]);
    }

    /**
     * 失败响应（HTTP 状态码与 code 一致，便于 curl 侧判断）
     *
     * @param int $code HTTP 状态码
     * @param string $message 失败原因
     * @return Json
     */
    private function fail(int $code, string $message): Json
    {
        return json(['code' => $code, 'msg' => $message, 'data' => null])->code($code);
    }

    /**
     * 机会式清理暂存区过期文件（1 小时），扫描量有上限
     *
     * @param string $stagingDir 暂存目录绝对路径
     * @return void
     */
    private function gcStaging(string $stagingDir): void
    {
        try {
            $deadline = time() - self::STAGING_TTL;
            $scanned = 0;
            foreach ((array)glob($stagingDir . '/up_*') as $path) {
                if (++$scanned > self::GC_SCAN_LIMIT) {
                    break;
                }
                if (is_file($path) && filemtime($path) < $deadline) {
                    @unlink($path);
                }
            }
        } catch (\Throwable $e) {
        }
    }
}