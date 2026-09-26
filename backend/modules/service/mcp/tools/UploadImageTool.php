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

use app\services\driver\upload\UploadService;
use app\services\system\attachment\SystemAttachmentServices;
use crmeb\interfaces\McpToolInterface;
use crmeb\services\CacheService;
use crmeb\utils\DownloadImage;
use modules\service\mcp\McpException;
use think\facade\Config;
use think\facade\Log;

/**
 * MCP 工具：图片上传转存
 *
 * 把 AI 客户端产出的图片写入系统当前配置的存储方式（eb_driver 启用驱动：
 * 本地/OSS/COS/七牛/OBS 等），并按需写入后台素材库，返回可直接用于
 * 商品资料等字段的站点资产地址与元数据。
 *
 * 来源按上下文与部署形态适配：直传暂存引用（crmeb_upload_ticket 签发票据
 * + /api/mcp/upload 带外直传，本机图片上传的默认方式）> 生图接口 http(s) URL >
 * 站点相对路径 > 本地白名单路径（仅 AI 与服务器同机部署时可用）>
 * base64 data URI 内联（仅作无法发起本机 HTTP 上传时的最后应急，≤512KB）。
 */
class UploadImageTool implements McpToolInterface
{
    /**
     * 允许入库的图片 mime → 扩展名映射
     */
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    /**
     * 本地文件后缀白名单（上传内容仍以真实 mime 二次校验）
     */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * 单张图片字节上限（外链下载 / 本地路径）
     */
    private const MAX_IMAGE_SIZE = 10485760;

    /**
     * base64 内联单张字节上限（512KB，解码后）
     *
     * 阈值故意收紧：base64 以文本形式经过对话上下文与工具调用参数（体积约为
     * 原图的 4/3），引导 AI 客户端优先走 crmeb_upload_ticket 票据直传，
     * 不要把对话上下文当成图片传输通道。
     */
    private const MAX_INLINE_IMAGE_SIZE = 524288;

    /**
     * 单次请求最多上传张数
     */
    private const MAX_BATCH = 20;

    /**
     * 单密钥每分钟上传张数上限
     */
    private const RATE_LIMIT_PER_MINUTE = 60;

    /**
     * options.dir 允许的存储子目录
     */
    private const ALLOWED_STORAGE_DIRS = ['attach', 'store', 'product', 'comment'];

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_upload_image';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '把图片写入站点存储（系统当前配置的存储方式：本地/OSS/COS/七牛/OBS 等）并登记后台素材库，'
            . '返回可直接用于 crmeb_product_normal_create 等工具图片字段的站点资产地址（url）与元数据（att_id/宽高/大小/md5）。'
            . '【本工具不接收本机文件字节：要上传 AI 本机生成或下载的图片，必须先调 crmeb_upload_ticket 拿票据、'
            . '在本机执行上传命令，再把返回的 ref 作为 source 传入；不要把图片内容转成 base64 内联，'
            . 'base64 只作无法发起本机 HTTP 上传时的最后应急】'
            . '【来源优先级——请从上到下选用】'
            . '1）直传暂存引用（如 runtime/upload/mcp_recv/up_x.png）：本机图片上传的默认方式'
            . '（先用 crmeb_upload_ticket 获取），图片字节不经过对话上下文；'
            . '2）图片生成接口返回的 http(s) URL：服务端自动下载落站，单张上限10MB；'
            . '3）站点已有图片相对路径（/uploads/...）：校验存在后原样返回并补全元数据，不重复上传；'
            . '4）本地绝对路径或 file:// 路径：仅当 AI 与服务器同机部署（自托管/脚本）时可用，'
            . '白名单目录默认 runtime/upload，可用系统配置 mcp_upload_local_dirs 追加；'
            . '5）base64 data URI（最后应急）：仅当确实无法发起本机 HTTP 上传时才用，'
            . '以纯文本占用大量对话上下文，解码后超过 512KB 拒绝。'
            . '统一限制：仅支持 jpg/jpeg/png/gif/webp；相同内容按 md5 去重复用；'
            . '单次最多20张，单密钥每分钟最多60张；批量中个别失败不影响其他图片，失败原因在 failed 数组逐条给出。'
            . '默认写入后台素材库（write_attachment=true，可在 options 关闭），便于运营在「附件管理」中查看与复用。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '图片上传';
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
     * 写操作声明：上传会产生新站点资产，执行前需客户端确认
     * @return string
     */
    public function interaction(): string
    {
        return 'execute';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'images' => [
                    'type'        => 'array',
                    'maxItems'    => 20,
                    'description' => '图片列表，必填；每项可为字符串（旧格式），'
                        . '或对象 {source 必填, filename 可选(落盘文件名不含扩展名，仅字母数字下划线中划线), real_name 可选(素材库显示名), pid 可选(素材分类ID,默认0)}。'
                        . '上传 AI 本机生成/下载的图片时，必须先调 crmeb_upload_ticket 直传并把返回的 ref 作为 source 传入，不要转成 base64 内联',
                    'items'       => [
                        'type'                 => 'object',
                        'required'             => ['source'],
                        'properties'           => [
                            'source'    => ['type' => 'string', 'description' => '图片来源。上传 AI 本机生成/下载的图片时，先调 crmeb_upload_ticket 拿票据、在本机执行上传命令，再把返回的 ref 传到这里（如 runtime/upload/mcp_recv/up_x.png），不要转 base64；也支持 http(s) URL、站点相对路径（/uploads/...）、file:// 或本地白名单路径（仅 AI 与服务器同机时可用）；base64 data URI 仅作无法发起本机 HTTP 上传时的最后应急'],
                            'filename'  => ['type' => 'string', 'description' => '期望落盘文件名（不含扩展名），缺省用内容 md5'],
                            'real_name' => ['type' => 'string', 'description' => '素材库显示名，缺省取原文件名'],
                            'pid'       => ['type' => 'number', 'description' => '素材分类ID，默认 0（未分类）'],
                        ],
                    ],
                ],
                'options' => [
                    'type'       => 'object',
                    'properties' => [
                        'dir'              => ['type' => 'string', 'description' => '存储子目录：attach(默认)/store/product/comment'],
                        'write_attachment' => ['type' => 'boolean', 'description' => '是否写入后台素材库，默认 true'],
                        'return_absolute'  => ['type' => 'boolean', 'description' => '是否返回带站点域名的绝对地址 absolute，默认 true'],
                    ],
                    'description' => '可选上传选项',
                ],
            ],
            'required'   => ['images'],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（images, options）
     * @return array count：成功数；total：总数；uploaded：成功明细；failed：失败明细（含原因）
     * @throws McpException 参数级错误或触发限流
     */
    public function execute(array $args): array
    {
        $items = $this->normalizeItems($args['images'] ?? null);
        $options = $this->parseOptions($args['options'] ?? []);
        $this->checkRateLimit(count($items));

        $uploaded = [];
        $failed = [];
        foreach (array_values($items) as $index => $item) {
            try {
                $uploaded[] = ['index' => $index, 'ok' => true] + $this->storeOne($item, $options);
            } catch (\Throwable $e) {
                $failed[] = [
                    'index'  => $index,
                    'ok'     => false,
                    'source' => mb_substr((string)($item['source'] ?? ''), 0, 200),
                    'reason' => $e->getMessage() ?: '未知错误',
                ];
            }
        }

        return [
            'count'    => count($uploaded),
            'total'    => count($items),
            'uploaded' => $uploaded,
            'failed'   => $failed,
        ];
    }

    /**
     * 归一化 images 入参：兼容旧格式字符串数组与新格式对象数组
     * @param mixed $images 原始入参
     * @return array 每项 ['source'=>, 'filename'=>, 'real_name'=>, 'pid'=>0]
     * @throws McpException 参数缺失或格式错误
     */
    private function normalizeItems($images): array
    {
        if (is_string($images)) {
            $images = [$images];
        }
        if (!is_array($images) || $images === []) {
            throw new McpException('参数错误：images 必须为非空图片列表');
        }
        if (count($images) > self::MAX_BATCH) {
            throw new McpException('参数错误：单次最多上传 ' . self::MAX_BATCH . ' 张');
        }
        $items = [];
        foreach ($images as $image) {
            if (is_string($image)) {
                $image = ['source' => $image];
            }
            $source = trim((string)($image['source'] ?? ''));
            if ($source === '') {
                throw new McpException('参数错误：images 中存在空的 source');
            }
            $items[] = [
                'source'    => $source,
                'filename'  => preg_replace('/[^A-Za-z0-9_-]/', '', (string)($image['filename'] ?? '')),
                'real_name' => trim((string)($image['real_name'] ?? '')),
                'pid'       => max(0, (int)($image['pid'] ?? 0)),
            ];
        }
        return $items;
    }

    /**
     * 解析上传选项
     * @param array $options 原始入参
     * @return array write_attachment / return_absolute / dir
     */
    private function parseOptions(array $options): array
    {
        $dir = strtolower(trim((string)($options['dir'] ?? 'attach')));
        return [
            'write_attachment' => !isset($options['write_attachment']) || (bool)$options['write_attachment'],
            'return_absolute'  => !isset($options['return_absolute']) || (bool)$options['return_absolute'],
            'dir'              => in_array($dir, self::ALLOWED_STORAGE_DIRS, true) ? $dir : 'attach',
        ];
    }

    /**
     * 按密钥限流：每分钟最多 RATE_LIMIT_PER_MINUTE 张
     * @param int $count 本次请求数量
     * @throws McpException 超限时抛出
     */
    private function checkRateLimit(int $count): void
    {
        try {
            $key = (string)request()->header('X-Mcp-Key', '');
        } catch (\Throwable $e) {
            $key = '';
        }
        $windowKey = 'mcp_upload_rl_' . md5($key ?: 'unknown');
        $used = (int)CacheService::get($windowKey, 0);
        if ($used + $count > self::RATE_LIMIT_PER_MINUTE) {
            throw new McpException('上传频率超限：单密钥每分钟最多 ' . self::RATE_LIMIT_PER_MINUTE . ' 张，请稍后重试');
        }
        CacheService::set($windowKey, $used + $count, 60);
    }

    /**
     * 转存单个图片，返回结构化元数据
     * @param array $item 归一化后的图片项
     * @param array $options 上传选项
     * @return array url/absolute/att_id/filename/real_name/size/width/height/mime/md5/dedupe_hit
     * @throws \RuntimeException 该张失败时抛出（由调用方隔离进 failed）
     */
    private function storeOne(array $item, array $options): array
    {
        $source = $item['source'];
        $content = null;
        $dedupeHit = false;
        $relative = '';
        $md5 = '';
        $width = 0;
        $height = 0;
        $mime = '';
        $size = 0;

        // 站点相对路径：校验文件真实存在，补全元数据后原样返回（不重复上传）
        $localPath = $this->resolveLocalPath($source);
        if ($localPath !== null) {
            $content = $this->readLocalFile($localPath);
        } elseif (str_starts_with($source, '/')) {
            return $this->existingSiteAsset($source, $item, $options);
        } elseif (str_starts_with($source, 'data:image/')) {
            $content = $this->decodeBase64($source);
        } elseif (preg_match('~^https?://~i', $source)) {
            if ($this->isLocalAsset($source)) {
                $relative = (string)parse_url($source, PHP_URL_PATH);
                return $this->existingSiteAsset($relative, $item, $options);
            }
            $content = $this->fetchRemote($source);
        } else {
            throw new \RuntimeException('不支持的来源：仅支持本地白名单路径、file:// 路径、data URI、http(s) URL 或站点相对路径');
        }

        $size = strlen($content);
        $md5 = md5($content);
        if ($size > ($localPath !== null ? self::MAX_IMAGE_SIZE : self::MAX_INLINE_IMAGE_SIZE)) {
            throw new \RuntimeException($localPath !== null || !str_starts_with($source, 'data:')
                ? '图片大小超过 10MB 限制'
                : 'base64 内联图片解码后超过 512KB，会占用大量对话上下文；请改用 crmeb_upload_ticket 票据直传，或改用 http(s) URL / 站点相对路径 / 本地白名单路径');
        }
        $image = @getimagesizefromstring($content);
        $mime = (string)($image['mime'] ?? '');
        $ext = self::MIME_EXTENSIONS[$mime] ?? '';
        if ($ext === '') {
            throw new \RuntimeException('仅支持 jpg/png/gif/webp 格式图片');
        }
        $width = (int)($image[0] ?? 0);
        $height = (int)($image[1] ?? 0);

        // 落盘名：优先自定义文件名，默认内容 md5（同内容天然幂等）
        $filename = ($item['filename'] !== '' ? $item['filename'] : $md5) . '.' . $ext;
        $storageDir = $options['dir'] . '/' . date('Y/m/d');
        $dedupeHit = $this->targetExists($storageDir, $filename, $md5);
        if (!$dedupeHit) {
            $this->streamUpload($storageDir, $filename, $content);
        }
        $relative = '/' . trim(Config::get('filesystem.disks.public.url', '/uploads'), '/') . '/' . $storageDir . '/' . $filename;

        $attId = 0;
        if ($options['write_attachment']) {
            $attId = $this->writeAttachment($filename, $relative, $size, $ext, $item, $dedupeHit);
        }
        // 审计：记录来源与落站结果，便于追溯 AI 生成资产（来源截断，避免大 payload 入日志）
        try {
            Log::info('MCP图片上传', [
                'att_id'   => $attId,
                'url'      => $relative,
                'md5'      => $md5,
                'size'     => $size,
                'source'   => mb_substr(str_starts_with($source, 'data:') ? '[base64内联]' : $source, 0, 200),
                'mcp_key'  => md5((string)request()->header('X-Mcp-Key', '')),
            ]);
        } catch (\Throwable $e) {
        }

        $result = [
            'url'        => $relative,
            'att_id'     => $attId,
            'filename'   => $filename,
            'real_name'  => $item['real_name'] !== '' ? $item['real_name'] : $filename,
            'size'       => $size,
            'width'      => $width,
            'height'     => $height,
            'mime'       => $mime,
            'md5'        => $md5,
            'dedupe_hit' => $dedupeHit,
        ];
        if ($options['return_absolute']) {
            $result['absolute'] = rtrim((string)sys_config('site_url'), '/') . $relative;
        }
        return $result;
    }

    /**
     * 解析本地文件路径（file:// 前缀、绝对路径或项目根相对路径），不属于本地路径来源时返回 null
     * @param string $source 图片来源
     * @return string|null 白名单内的真实文件路径；非本地路径来源返回 null
     * @throws \RuntimeException 明确是本地路径但不在白名单/不存在时抛出
     */
    private function resolveLocalPath(string $source): ?string
    {
        $path = $source;
        if (str_starts_with($source, 'file://')) {
            $path = substr($source, 7);
        } elseif (preg_match('~^(?:https?|data):~i', $source)) {
            // 外链与 base64 走各自来源分支
            return null;
        } elseif (str_starts_with($source, '/')) {
            // /uploads/ 开头视为站点资产；其余以 / 开头的按本地绝对路径处理
            if (str_starts_with($source, '/uploads/')) {
                return null;
            }
        } else {
            // 项目根相对路径：直传端点返回的暂存引用（runtime/upload/mcp_recv/...）等。
            // 注意不能用 root_path()：其实现会在路径尾部追加 DIRECTORY_SEPARATOR，
            // 拼文件路径时生成 ".../up_x.png/" 导致 realpath/is_file 失败。
            $path = app()->getRootPath() . ltrim($source, '/');
        }

        $realPath = realpath($path);
        if ($realPath === false || !is_file($realPath)) {
            throw new \RuntimeException('本地文件不存在：请传直传端点返回的暂存引用（ref），或确认文件在白名单目录（默认 runtime/upload）内');
        }
        foreach ($this->allowedLocalDirs() as $dir) {
            if ($realPath === $dir || str_starts_with($realPath, $dir . DIRECTORY_SEPARATOR)) {
                if (!in_array(strtolower(pathinfo($realPath, PATHINFO_EXTENSION)), self::ALLOWED_EXTENSIONS, true)) {
                    throw new \RuntimeException('仅支持 jpg/jpeg/png/gif/webp 格式图片');
                }
                return $realPath;
            }
        }
        throw new \RuntimeException('本地路径不在白名单目录内（默认 runtime/upload，可配置 mcp_upload_local_dirs），拒绝读取');
    }

    /**
     * 本地路径白名单：默认 runtime/upload，可用系统配置 mcp_upload_local_dirs 追加（逗号分隔）
     * @return array 规范化后的绝对目录列表
     */
    private function allowedLocalDirs(): array
    {
        $dirs = [realpath(root_path('runtime/upload')) ?: root_path('runtime/upload')];
        foreach (explode(',', trim((string)sys_config('mcp_upload_local_dirs'))) as $extra) {
            $extra = trim($extra);
            if ($extra !== '') {
                $dirs[] = realpath($extra) ?: $extra;
            }
        }
        return array_values(array_unique(array_filter($dirs)));
    }

    /**
     * 读取本地白名单文件字节
     * @param string $path 白名单内的真实路径
     * @return string 图片字节
     * @throws \RuntimeException 不可读或超限时抛出
     */
    private function readLocalFile(string $path): string
    {
        if (!is_readable($path)) {
            throw new \RuntimeException('本地文件不可读');
        }
        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException('本地文件读取失败');
        }
        return $content;
    }

    /**
     * 站点已有资产：校验文件真实存在后补全元数据原样返回
     * @param string $relative 站点相对路径（/uploads/...）
     * @param array $item 归一化图片项
     * @param array $options 上传选项
     * @return array 元数据
     * @throws \RuntimeException 文件不存在时抛出
     */
    private function existingSiteAsset(string $relative, array $item, array $options): array
    {
        $relative = '/' . ltrim($relative, '/');
        $diskPath = public_path() . ltrim(str_replace('\\', '/', $relative), '/');
        if (!is_file($diskPath)) {
            throw new \RuntimeException('站点相对路径对应的文件不存在：' . $relative);
        }
        $content = (string)file_get_contents($diskPath);
        $image = @getimagesizefromstring($content);
        $filename = basename($relative);
        $attId = 0;
        if ($options['write_attachment']) {
            $attId = $this->findAttachmentId($relative);
        }
        $result = [
            'url'        => $relative,
            'att_id'     => $attId,
            'filename'   => $filename,
            'real_name'  => $item['real_name'] !== '' ? $item['real_name'] : $filename,
            'size'       => filesize($diskPath) ?: strlen($content),
            'width'      => (int)($image[0] ?? 0),
            'height'     => (int)($image[1] ?? 0),
            'mime'       => (string)($image['mime'] ?? ''),
            'md5'        => md5($content),
            'dedupe_hit' => true,
        ];
        if ($options['return_absolute']) {
            $result['absolute'] = rtrim((string)sys_config('site_url'), '/') . $relative;
        }
        return $result;
    }

    /**
     * 解码并校验 base64 data URI 图片
     * @param string $dataUri data:image/xxx;base64,... 形态
     * @return string 图片字节
     * @throws \RuntimeException 格式或解码失败时抛出
     */
    private function decodeBase64(string $dataUri): string
    {
        if (!preg_match('~^data:image/(\w+);base64,([A-Za-z0-9+/=\r\n]+)$~', $dataUri, $matched)) {
            throw new \RuntimeException('base64 图片格式不正确');
        }
        $content = base64_decode(str_replace(["\r", "\n"], '', $matched[2]), true);
        if ($content === false || $content === '') {
            throw new \RuntimeException('base64 图片解码失败');
        }
        return $content;
    }

    /**
     * 下载外链图片（协议/内网/DNS 重绑定/大小防护由核心 DownloadImage 提供）
     * @param string $url 图片地址
     * @return string 图片字节
     * @throws \RuntimeException 下载失败时抛出
     */
    private function fetchRemote(string $url): string
    {
        try {
            /** @var DownloadImage $down */
            $down = app()->make(DownloadImage::class);
            return $down->readImage($url, [], 20);
        } catch (\Throwable $e) {
            throw new \RuntimeException('远程图片下载失败：' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 判断目标文件在本地盘是否已存在（同内容 md5 命名天然幂等）；非本地驱动返回 false
     * @param string $storageDir 存储子目录
     * @param string $filename 落盘文件名
     * @param string $md5 内容指纹
     * @return bool
     */
    private function targetExists(string $storageDir, string $filename, string $md5): bool
    {
        try {
            // uploadDir 经 BaseManager 魔术转发到驱动，method_exists 检测不到，直接调用并捕获
            $diskPath = UploadService::init('local')->uploadDir($storageDir . '/' . $filename);
            return is_string($diskPath) && is_file($diskPath) && md5((string)file_get_contents($diskPath)) === $md5;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 流式上传到系统当前配置的存储方式
     * @param string $storageDir 存储子目录（attach/日期）
     * @param string $filename 落盘文件名
     * @param string $content 图片字节
     * @throws \RuntimeException 上传失败时抛出
     */
    private function streamUpload(string $storageDir, string $filename, string $content): void
    {
        $upload = UploadService::init();
        if ($upload->to($storageDir)->validate()->setAuthThumb(false)->stream($content, $filename) === false) {
            throw new \RuntimeException((string)$upload->getError() ?: '图片保存失败');
        }
        if ((string)($upload->getUploadInfo()['dir'] ?? '') === '') {
            throw new \RuntimeException('图片保存失败：未返回存储地址');
        }
    }

    /**
     * 写入后台素材库并返回 att_id（已有同路径记录时复用）
     * @param string $filename 落盘文件名
     * @param string $relative 站点相对路径
     * @param int $size 文件大小
     * @param string $ext 扩展名
     * @param array $item 归一化图片项（real_name/pid）
     * @param bool $dedupeHit 是否为重复内容
     * @return int att_id，写入失败返回 0
     */
    private function writeAttachment(string $filename, string $relative, int $size, string $ext, array $item, bool $dedupeHit): int
    {
        try {
            $attId = $this->findAttachmentId($relative);
            if ($attId > 0) {
                return $attId;
            }
            /** @var SystemAttachmentServices $services */
            $services = app()->make(SystemAttachmentServices::class);
            $services->attachmentAdd(
                $filename,
                $size,
                $ext,
                $relative,
                $relative,
                (int)$item['pid'],
                UploadService::getEnabledImageType(),
                0,
                1,
                0,
                $item['real_name'] !== '' ? $item['real_name'] : ''
            );
            return $this->findAttachmentId($relative);
        } catch (\Throwable $e) {
            // 素材库登记失败不影响图片已落站，att_id 返回 0 供调用方感知
            return 0;
        }
    }

    /**
     * 按站点相对路径查找素材库记录
     * @param string $relative 站点相对路径
     * @return int att_id，不存在返回 0
     */
    private function findAttachmentId(string $relative): int
    {
        try {
            /** @var SystemAttachmentServices $services */
            $services = app()->make(SystemAttachmentServices::class);
            $record = $services->getOne(['att_dir' => $relative]);
            return $record ? (int)($record['att_id'] ?? 0) : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * 判断 URL 是否已指向本站资产（站点域名或当前请求域名一致）
     * @param string $url 图片地址
     * @return bool
     */
    private function isLocalAsset(string $url): bool
    {
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        if ($host === '') {
            return false;
        }
        $siteHost = strtolower((string)parse_url((string)sys_config('site_url'), PHP_URL_HOST));
        if ($siteHost !== '' && $host === $siteHost) {
            return true;
        }
        try {
            $requestHost = strtolower((string)request()->host());
        } catch (\Throwable $e) {
            $requestHost = '';
        }
        return $requestHost !== '' && $host === $requestHost;
    }
}
