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

namespace crmeb\utils;


use crmeb\exceptions\AdminException;
use crmeb\exceptions\UploadException;
use crmeb\services\upload\Upload;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\FnStream;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use GuzzleHttp\Psr7\Utils;
use think\facade\Config;
use think\Image;

/**
 * 下载图片到本地
 * Class DownloadImage
 * @package crmeb\utils
 * @method $this thumb(bool $thumb) 是否生成缩略图
 * @method $this thumbWidth(int $thumbWidth) 缩略图宽度
 * @method $this thumHeight(int $thumHeight) 缩略图宽度
 * @method $this path(int $path) 存储位置
 */
#[AllowDynamicProperties]
class DownloadImage
{
    //是否生成缩略图
    protected $thumb = false;
    //缩略图宽度
    protected $thumbWidth = 300;
    //缩略图高度
    protected $thumHeight = 300;
    //存储位置
    protected $path = 'attach';

    /**
     * @var string[]
     */
    protected $rules = ['thumb', 'thumbWidth', 'thumHeight', 'path'];

    /**
     * 获取即将要下载的图片扩展名
     * @param string $url
     * @param string $ex
     * @return array|string[]
     */
    public function getImageExtname($url = '', $ex = 'jpg')
    {
        $path = parse_url($url, PHP_URL_PATH);
        $ext = strtolower(pathinfo(is_string($path) ? $path : '', PATHINFO_EXTENSION) ?: $ex);
        return ['file_name' => $url ? md5($url) . '.' . $ext : '', 'ext_name' => $ext];
    }

    /**
     * 下载图片
     * @param string $url
     * @param string $name
     * @return array
     */
    public function downloadImage(string $url, $name = '')
    {
        $thumb = (bool)$this->thumb;
        $this->thumb = false;
        $url = str_starts_with($url, '//') ? 'https:' . $url : $url;
        $this->validateImageUrl($url);
        $autoName = !$name;
        $name = $name ?: $this->getImageExtname($url)['file_name'];
        if (!$name || basename($name) !== $name || str_contains($name, '\\') || preg_match('/[\x00-\x20\x7f?#]/', $name)) {
            throw new AdminException('图片文件名格式错误');
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $imageExtensions = array_intersect(Config::get('upload.fileExt', []), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico']);
        if (!in_array($ext, $imageExtensions, true)) {
            throw new AdminException('格式错误');
        }
        $toPath = $this->path === 'attach' ? $this->path . '/' . date('Y/m/d') : $this->path;
        $upload = new Upload('local');
        $upload->to($toPath)->validate()->authThumb(false);
        $diskPath = $upload->uploadDir($toPath . '/' . $name);
        $exists = is_file($diskPath);
        if ($exists && filesize($diskPath) > $this->getMaxImageSize()) {
            throw new UploadException('图片大小超过限制');
        }
        $content = $exists ? file_get_contents($diskPath, false, null, 0, $this->getMaxImageSize() + 1) : $this->fetchImage($url);
        if (!is_string($content)) throw new UploadException('图片读取失败');
        $image = $this->validateImageContent($content);
        $mimeExtensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp',
            'image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico'];
        $actualExt = $mimeExtensions[$image['mime'] ?? ''] ?? '';
        if (!$actualExt || !in_array($actualExt, $imageExtensions, true)) {
            throw new UploadException('图片内容或大小不符合要求');
        }
        if ($ext !== $actualExt && !($ext === 'jpeg' && $actualExt === 'jpg')) {
            if (!$autoName) {
                throw new AdminException('图片扩展名与内容不匹配');
            }
            $name = md5($url) . '.' . $actualExt;
            $diskPath = $upload->uploadDir($toPath . '/' . $name);
            $exists = false;
        }
        if (!$exists && $upload->down($content, $name) === false) {
            throw new UploadException('图片下载失败');
        }
        if ($thumb && $image[2] !== IMAGETYPE_ICO) {
            Image::open($diskPath)->thumb((int)$this->thumbWidth, (int)$this->thumHeight)->save($diskPath);
            clearstatcache(true, $diskPath);
        }
        $disk = Config::get('filesystem.disks.public.url', '/uploads');
        return [
            'path' => rtrim($disk, '/') . '/' . trim($toPath, '/') . '/' . $name,
            'name' => $name,
            'size' => filesize($diskPath),
            'mime' => $image['mime'],
            'image_type' => 1,
            'is_exists' => $exists,
        ];
    }

    /**
     * 读取经地址、内容与大小校验的图片字节，供其他存储驱动上传
     * @param string $url
     * @param array $headers HTTP 请求头
     * @param int $timeout 总超时时间
     * @return string
     */
    public function readImage(string $url, array $headers = [], int $timeout = 15): string
    {
        $url = str_starts_with($url, '//') ? 'https:' . $url : $url;
        $this->validateImageUrl($url);
        $content = $this->fetchImage($url, $headers, $timeout);
        $this->validateImageContent($content);
        return $content;
    }

    /**
     * 完整校验图片正文，先限制解码像素，缓存命中也不能跳过验证
     * @param string $content 图片字节
     * @return array
     */
    protected function validateImageContent(string $content): array
    {
        if ($content === '' || strlen($content) > $this->getMaxImageSize()) {
            throw new UploadException('图片内容或大小不符合要求');
        }
        $image = @getimagesizefromstring($content);
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon'];
        if (!$image || !in_array($image['mime'] ?? '', $allowed, true)) {
            throw new UploadException('图片内容或大小不符合要求');
        }
        $maxPixels = filter_var(Config::get('upload.imageMaxPixels', 20000000), FILTER_VALIDATE_INT);
        $width = (int)$image[0];
        $height = (int)$image[1];
        if ($maxPixels === false || $maxPixels <= 0) throw new UploadException('图片像素限制配置错误');
        if ($width <= 0 || $height <= 0 || $width > intdiv($maxPixels, $height)) {
            throw new UploadException('图片像素过大');
        }
        // ICO 不进入 GD 处理，与上传驱动的原样保存约定保持一致。
        if ($image[2] !== IMAGETYPE_ICO) {
            if (!function_exists('imagecreatefromstring')) throw new UploadException('图片解码组件不可用');
            set_error_handler(static function () {
                throw new UploadException('图片内容不合法');
            }, E_WARNING | E_NOTICE);
            try {
                $decoded = imagecreatefromstring($content);
                if ($decoded === false) throw new UploadException('图片内容不合法');
            } finally {
                unset($decoded);
                restore_error_handler();
            }
        }
        return $image;
    }

    /**
     * 获取有界图片下载大小
     * @return int
     */
    protected function getMaxImageSize(): int
    {
        return max(1, min((int)Config::get('upload.filesize', 52428800), 52428800));
    }

    /**
     * 验证远程图片地址，防止使用非 HTTP 协议或 URL 凭据
     * @param string $url
     * @return array
     */
    protected function validateImageUrl(string $url): array
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['host']) || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || isset($parts['user']) || isset($parts['pass']) || preg_match('/[\x00-\x20\x7f]/', $url)) {
            throw new AdminException('图片地址格式错误');
        }
        return $parts;
    }

    /**
     * 解析并限制到公网地址，实际请求固定解析结果以防 DNS 重绑定
     * @param string $host
     * @return array
     */
    protected function resolveImageHost(string $host): array
    {
        $host = trim($host, '[]');
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips = [$host];
        } else {
            $records = dns_get_record($host, DNS_A | DNS_AAAA);
            $ips = [];
            foreach ($records ?: [] as $record) {
                $ip = $record['ip'] ?? $record['ipv6'] ?? '';
                if ($ip) $ips[] = $ip;
            }
        }
        if (!$ips) throw new UploadException('图片服务器地址解析失败');
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE)) {
                throw new AdminException('不允许下载内网图片地址');
            }
        }
        return array_values(array_unique($ips));
    }

    /**
     * 创建支持超时和流式读取的 HTTP 客户端
     * @return Client
     */
    protected function createHttpClient(): Client
    {
        // 固定使用 cURL，确保流式选项不会绕过 CURLOPT_RESOLVE 的地址固定。
        return new Client(['handler' => new \GuzzleHttp\Handler\CurlHandler()]);
    }

    /**
     * 下载原始图片字节，每次跳转重新校验地址并限制响应体大小
     * @param string $url
     * @param array $headers
     * @param int $timeout
     * @return string
     */
    protected function fetchImage(string $url, array $headers = [], int $timeout = 15): string
    {
        $deadline = microtime(true) + max(1, min($timeout, 30));
        $client = $this->createHttpClient();
        try {
            for ($redirects = 0; $redirects <= 3; ++$redirects) {
                $parts = $this->validateImageUrl($url);
                $ips = $this->resolveImageHost($parts['host']);
                $port = $parts['port'] ?? (strtolower($parts['scheme']) === 'https' ? 443 : 80);
                $resolved = array_map(static fn(string $ip) => str_contains($ip, ':') ? '[' . $ip . ']' : $ip, $ips);
                $remaining = $deadline - microtime(true);
                if ($remaining <= 0) throw new UploadException('图片下载超时');
                $maxSize = $this->getMaxImageSize();
                $stream = Utils::streamFor(Utils::tryFopen('php://temp', 'w+'));
                $received = 0;
                // cURL 的进度是压缩传输量，必须在解压后写入前另行限制实际图片大小。
                $sink = FnStream::decorate($stream, ['write' => static function (string $chunk) use ($stream, &$received, $maxSize): int {
                    if (strlen($chunk) > $maxSize - $received) throw new UploadException('图片大小超过限制');
                    $written = $stream->write($chunk);
                    $received += $written;
                    return $written;
                }]);
                try {
                    $response = $client->get($url, [
                        'timeout' => $remaining, 'connect_timeout' => min(5, $remaining), 'stream' => true, 'allow_redirects' => false,
                        'http_errors' => false, 'proxy' => '', 'verify' => true, 'headers' => $headers,
                        'sink' => $sink,
                        'progress' => static function ($total, $downloaded) use ($maxSize): void {
                            if ($total > $maxSize || $downloaded > $maxSize) {
                                throw new UploadException('图片大小超过限制');
                            }
                        },
                        'curl' => [CURLOPT_RESOLVE => [$parts['host'] . ':' . $port . ':' . implode(',', $resolved)]],
                    ]);
                } catch (\Throwable $e) {
                    $sink->close();
                    throw $e;
                }
                if (!in_array($response->getStatusCode(), [301, 302, 303, 307, 308], true)) break;
                $response->getBody()->close();
                $sink->close();
                if ($redirects === 3 || !$response->hasHeader('Location')) throw new UploadException('图片服务器跳转失败');
                $next = (string)UriResolver::resolve(new Uri($url), new Uri($response->getHeaderLine('Location')));
                $nextParts = $this->validateImageUrl($next);
                $nextScheme = strtolower($nextParts['scheme']);
                if ($nextScheme !== strtolower($parts['scheme']) || strtolower($nextParts['host']) !== strtolower($parts['host'])
                    || ($nextParts['port'] ?? ($nextScheme === 'https' ? 443 : 80)) !== $port) {
                    // 自定义头可能携带凭据，不将调用方的头跨来源转发。
                    $headers = [];
                }
                $url = $next;
            }
            $body = $sink;
            try {
                if ($response->getStatusCode() !== 200 || (int)$response->getHeaderLine('Content-Length') > $this->getMaxImageSize()) {
                    throw new UploadException('图片服务器响应失败或图片过大');
                }
                $body->rewind();
                $content = '';
                while (!$body->eof()) {
                    $chunk = $body->read(8192);
                    if ($chunk === '' && !$body->eof()) throw new UploadException('图片流读取失败');
                    $content .= $chunk;
                    if (strlen($content) > $this->getMaxImageSize()) throw new UploadException('图片大小超过限制');
                }
                return $content;
            } finally {
                $body->close();
                $response->getBody()->close();
            }
        } catch (GuzzleException $e) {
            // 不透传含签名参数的 URL 或请求凭据。
            throw new UploadException('图片服务器请求失败');
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (in_array($name, $this->rules)) {
            if ($name === 'path') {
                $this->{$name} = $arguments[0] ?? 'attach';
            } else {
                $this->{$name} = $arguments[0] ?? null;
            }
            return $this;
        } else {
            throw new \RuntimeException('Method does not exist' . __CLASS__ . '->' . $name . '()');
        }
    }

}
