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
namespace driver\upload\Oss;

use crmeb\services\upload\BaseUpload;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\UploadException;
use OSS\Core\OssException;
use OSS\Model\CorsConfig;
use OSS\Model\CorsRule;
use OSS\OssClient;


/**
 * 阿里云OSS上传
 * Class Driver
 * @package driver\upload\Oss
 */
class Driver extends BaseUpload
{
    /**
     * accessKey
     * @var mixed
     */
    protected $accessKey;

    /**
     * secretKey
     * @var mixed
     */
    protected $secretKey;

    /**
     * 句柄
     * @var OssClient
     */
    protected $handle;

    /**
     * 空间域名 Domain
     * @var mixed
     */
    protected $uploadUrl;

    /**
     * 存储空间名称  公开空间
     * @var mixed
     */
    protected $storageName;

    /**
     * OSS 服务端点
     * @var mixed|null
     */
    protected $storageRegion;

    /**
     * 水印位置
     * @var string[]
     */
    protected $position = [
        '1' => 'nw',//：左上
        '2' => 'north',//：中上
        '3' => 'ne',//：右上
        '4' => 'west',//：左中
        '5' => 'center',//：中部
        '6' => 'east',//：右中
        '7' => 'sw',//：左下
        '8' => 'south',//：中下
        '9' => 'se',//：右下
    ];

    /**
     * @var string
     */
    protected $cdn;

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config)
    {
        parent::initialize($config);
        $this->accessKey = trim($config['accessKey'] ?? '');
        $this->secretKey = trim($config['secretKey'] ?? '');
        $this->uploadUrl = rtrim(trim($config['uploadUrl'] ?? ''), '/');
        $this->storageName = trim($config['storageName'] ?? '');
        $this->cdn = rtrim(trim($config['cdn'] ?? ''), '/');
        $this->storageRegion = $this->normalizeEndpoint($config['storageRegion'] ?? '');
        // 未配置自定义域名时，使用当前空间的公共访问域名。
        if (!$this->uploadUrl && $this->storageName && $this->storageRegion) {
            $this->uploadUrl = 'https://' . $this->storageName . '.' . parse_url($this->storageRegion, PHP_URL_HOST);
        }
    }

    /**
     * 设置上传路径
     * @param string $path
     * @return $this
     */
    public function to(string $path)
    {
        $this->resetOssUploadState();
        $this->path = $path;
        return $this;
    }

    /**
     * 开启验证
     * @param array|null $rules
     * @return $this
     */
    public function validate(?array $rules = null)
    {
        $this->validate = $rules ?? \think\facade\Config::get('upload', []);
        return $this;
    }

    /**
     * 初始化oss
     * @return OssClient
     * @throws OssException
     */
    protected function app()
    {
        if (!$this->storageName) {
            throw new UploadException('请先配置存储空间');
        }
        $this->handle = $this->createClient();
        return $this->handle;
    }

    /**
     * 将区域代码转换为 SDK 所需的服务端点
     * @param string $region 区域代码或服务端点
     * @return string
     */
    protected function normalizeEndpoint(string $region): string
    {
        $region = rtrim(trim($region), '/');
        if (!$region) {
            return '';
        }
        // 清单使用区域短名，SDK 第三个参数要求完整 endpoint。
        if (preg_match('/^oss-[a-z0-9-]+$/', $region)) {
            $region .= '.aliyuncs.com';
        }
        if (strpos($region, '://') === false) {
            $region = 'https://' . $region;
        }
        $parts = parse_url($region);
        if (!$parts || !in_array($parts['scheme'] ?? '', ['http', 'https'], true)
            || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])
            || !empty($parts['path']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new UploadException('OSS:无效的服务端点');
        }
        return $region;
    }

    /**
     * 创建 OSS 客户端并统一区域和凭据检查
     * @param string|null $region 区域代码或服务端点
     * @return OssClient
     */
    protected function createClient(?string $region = null): OssClient
    {
        if (!$this->accessKey || !$this->secretKey) {
            throw new UploadException('请先配置accessKey和secretKey');
        }
        $endpoint = $this->normalizeEndpoint($region ?: $this->storageRegion);
        if (!$endpoint) {
            throw new UploadException('请先配置存储区域');
        }
        $handle = $this->newOssClient($endpoint);
        $handle->setConnectTimeout(10);
        $handle->setTimeout(60);
        return $handle;
    }

    /**
     * 实例化 SDK 客户端
     * @param string $endpoint 完整服务端点
     * @return OssClient
     */
    protected function newOssClient(string $endpoint): OssClient
    {
        return new OssClient($this->accessKey, $this->secretKey, $endpoint);
    }

    /**
     * 清理上次上传的信息与错误
     * @return void
     */
    protected function resetOssUploadState(): void
    {
        $this->fileInfo = new \StdClass();
        $this->error = null;
    }

    /**
     * 校验真实文件内容和当前验证规则
     * @param string $key 文件名
     * @param int $size 实际字节数
     * @param string $mimeType 实际 MIME
     * @param array|false $imageInfo 图片内容信息
     * @return void
     */
    protected function validateOssContent(string $key, int $size, string $mimeType, $imageInfo): void
    {
        $extension = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        $imageTypes = ['jpg' => IMAGETYPE_JPEG, 'jpeg' => IMAGETYPE_JPEG, 'png' => IMAGETYPE_PNG,
            'gif' => IMAGETYPE_GIF, 'webp' => IMAGETYPE_WEBP, 'ico' => IMAGETYPE_ICO, 'bmp' => IMAGETYPE_BMP];
        // 图片后缀必须和可解析的实际图片格式一致，不能只信任客户端 MIME。
        if (isset($imageTypes[$extension]) && (!$imageInfo || $imageInfo[2] !== $imageTypes[$extension])) {
            throw new UploadException('文件内容不合法');
        }
        if ($this->validate) {
            if ($extension && !in_array($extension, $this->validate['fileExt'] ?? [], true)) {
                throw new UploadException('不合法的文件后缀');
            }
            if (isset($this->validate['filesize']) && $size > $this->validate['filesize']) {
                throw new UploadException('文件过大');
            }
            if (!in_array($mimeType, $this->validate['fileMime'] ?? [], true)) {
                throw new UploadException('不合法的文件类型');
            }
        }
    }

    /**
     * 获取源文件内容信息并校验
     * @param string $filePath 源文件路径
     * @param string $key 文件名
     * @return array
     */
    protected function inspectOssFile(string $filePath, string $key): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new UploadException('上传的文件不存在或不可读');
        }
        $size = filesize($filePath);
        if ($size === false) {
            throw new UploadException('无法读取文件大小');
        }
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($filePath) ?: 'application/octet-stream';
        $imageInfo = @getimagesize($filePath);
        $this->validateOssContent($key, $size, $mimeType, $imageInfo);
        if ($imageInfo) {
            $this->validateOssImageBytes(file_get_contents($filePath), $imageInfo);
        }
        return [$size, $mimeType, $this->isOssProcessableImage($imageInfo)];
    }

    /**
     * 判断解析出的格式是否支持 OSS 图片处理
     * @param array|false $imageInfo 图片信息
     * @return bool
     */
    protected function isOssProcessableImage($imageInfo): bool
    {
        return $imageInfo && in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP, IMAGETYPE_BMP], true);
    }

    /**
     * 校验常用图片完整内容，拒绝仅有合法文件头的损坏图片
     * @param string|false $content 图片字节
     * @param array $imageInfo 图片格式信息
     * @return void
     */
    protected function validateOssImageBytes($content, array $imageInfo): void
    {
        $maxPixels = filter_var($this->validate['imageMaxPixels'] ?? 20000000, FILTER_VALIDATE_INT);
        if ($maxPixels === false || $maxPixels <= 0) {
            throw new UploadException('图片像素限制配置错误');
        }
        $width = (int)($imageInfo[0] ?? 0);
        $height = (int)($imageInfo[1] ?? 0);
        if ($width <= 0 || $height <= 0) {
            throw new UploadException('文件内容不合法');
        }
        // 先限制展开后的像素数量，不能让小文件的大尺寸头进入 GD 分配。
        if ($width > intdiv($maxPixels, $height)) {
            throw new UploadException('图片像素过大');
        }
        // ICO 等格式不受 OSS 图片处理支持，原样保存即可。
        if (!$this->isOssProcessableImage($imageInfo)) {
            return;
        }
        if (!function_exists('imagecreatefromstring') || $content === false) {
            throw new UploadException('文件内容不合法');
        }
        set_error_handler(static function () {
            throw new UploadException('文件内容不合法');
        }, E_WARNING | E_NOTICE);
        try {
            $image = imagecreatefromstring($content);
            if ($image === false) {
                throw new UploadException('文件内容不合法');
            }
        } finally {
            unset($image);
            restore_error_handler();
        }
        // 提前校验图片水印配置，避免上传成功后因处理链接错误遗留对象。
        if (($this->waterConfig['image_watermark_status'] ?? 0)) {
            if (($this->waterConfig['watermark_type'] ?? 2) == 1) {
                if (empty($this->waterConfig['watermark_image'])) {
                    throw new AdminException('请先配置水印图片');
                }
                $this->getOssWatermarkKey($this->waterConfig['watermark_image']);
                $this->normalizeOssWatermarkAngle($this->waterConfig['watermark_rotate'] ?? '');
            } elseif (($this->waterConfig['watermark_type'] ?? 2) == 2) {
                if (empty($this->waterConfig['watermark_text'])) {
                    throw new AdminException('请先配置水印文字');
                }
                $this->normalizeOssWatermarkAngle($this->waterConfig['watermark_text_angle'] ?? '');
            }
        }
    }

    /**
     * 记录实际上传内容信息，仅为图片生成处理链接
     * @param array $uploadInfo SDK 响应
     * @param string $key 实际对象 key
     * @param string $realName 原始名称
     * @param int $size 实际字节数
     * @param string $mimeType 实际 MIME
     * @param bool $isImage 是否为可解析图片
     * @return object
     */
    protected function finishOssUpload(array $uploadInfo, string $key, string $realName, int $size, string $mimeType, bool $isImage): object
    {
        $this->fileInfo->uploadInfo = $uploadInfo;
        $this->fileInfo->realName = $realName;
        $this->fileInfo->filePath = $this->getFilePath($key);
        $this->fileInfo->fileName = $key;
        $this->fileInfo->size = $size;
        $this->fileInfo->mimeType = $mimeType;
        $this->fileInfo->isImage = $isImage;
        $this->fileInfo->filePathWater = $isImage ? $this->water($this->fileInfo->filePath) : $this->fileInfo->filePath;
        if ($isImage && $this->authThumb) {
            $this->thumb($this->fileInfo->filePath);
        }
        return $this->fileInfo;
    }

    /**
     * 上传文件
     * @param string $file
     * @param bool $realName
     * @return array|bool|mixed|\StdClass
     */
    public function move(string $file = 'file', $realName = false)
    {
        $this->resetOssUploadState();
        try {
            $fileHandle = app()->request->file($file);
            if (!$fileHandle) {
                return $this->setError('上传的文件不存在');
            }
            $originalName = $fileHandle->getOriginalName();
            [$size, $mimeType, $isImage] = $this->inspectOssFile($fileHandle->getRealPath(), $originalName);
            $key = $realName ? basename(str_replace('\\', '/', $originalName))
                : $this->saveFileName($fileHandle->getRealPath(), strtolower($fileHandle->getOriginalExtension()));
            $key = $this->getUploadPath($key);
            $uploadInfo = $this->app()->uploadFile($this->storageName, $key, $fileHandle->getRealPath());
            if (!isset($uploadInfo['info']['url'])) {
                return $this->setError('Upload failure');
            }
            return $this->finishOssUpload($uploadInfo, $key, $originalName, $size, $mimeType, $isImage);
        } catch (\Exception $e) {
            $this->resetOssUploadState();
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 文件流上传
     * @param $fileContent
     * @param string|null $key
     * @return array|bool|mixed
     * @throws OssException
     */
    public function stream($fileContent, ?string $key = null)
    {
        $this->resetOssUploadState();
        try {
            if (!$key) {
                $key = $this->saveFileName();
            }
            $realName = $key;
            $fileContent = (string)\GuzzleHttp\Psr7\Utils::streamFor($fileContent);
            $size = strlen($fileContent);
            $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($fileContent) ?: 'application/octet-stream';
            $imageInfo = @getimagesizefromstring($fileContent);
            $this->validateOssContent($key, $size, $mimeType, $imageInfo);
            if ($imageInfo) {
                $this->validateOssImageBytes($fileContent, $imageInfo);
            }
            $key = $this->getUploadPath($key);
            $uploadInfo = $this->app()->putObject($this->storageName, $key, $fileContent);
            if (!isset($uploadInfo['info']['url'])) {
                return $this->setError('Upload failure');
            }
            return $this->finishOssUpload($uploadInfo, $key, $realName, $size, $mimeType, $this->isOssProcessableImage($imageInfo));
        } catch (\Exception $e) {
            $this->resetOssUploadState();
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 缩略图
     * @param string $filePath
     * @param string $fileName
     * @param string $type
     * @return array|mixed
     */
    public function thumb(string $filePath = '', string $fileName = '', string $type = 'all')
    {
        $filePath = $this->getFilePath($filePath);
        $data = ['big' => $filePath, 'mid' => $filePath, 'small' => $filePath];
        $this->fileInfo->filePathBig = $this->fileInfo->filePathMid = $this->fileInfo->filePathSmall = $filePath;
        $this->fileInfo->filePathWater = $this->water($filePath);
        if ($filePath && $this->isOssImagePath($filePath)) {
            $config = $this->thumbConfig;
            // 每个尺寸独立生成处理链接，不覆盖原图的水印链接。
            foreach ($this->thumb as $v) {
                if ($type == 'all' || $type == $v) {
                    $height = 'thumb_' . $v . '_height';
                    $width = 'thumb_' . $v . '_width';
                    $key = 'filePath' . ucfirst($v);
                    if (($config['image_thumb_status'] ?? 1) && isset($config[$height]) && isset($config[$width]) && $config[$height] && $config[$width]) {
                        $this->fileInfo->$key = $this->appendOssImageProcess($filePath, 'resize,h_' . (int)$config[$height] . ',w_' . (int)$config[$width]);
                        $this->fileInfo->$key = $this->water($this->fileInfo->$key);
                        $data[$v] = $this->fileInfo->$key;
                    } else {
                        $this->fileInfo->$key = $this->water($this->fileInfo->$key);
                        $data[$v] = $this->fileInfo->$key;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 水印
     * @param string $filePath
     * @return mixed|string
     */
    public function water(string $filePath = '')
    {
        $filePath = $this->getFilePath($filePath);
        $waterConfig = $this->waterConfig;
        $waterPath = $filePath;
        if (($waterConfig['image_watermark_status'] ?? 0) && $filePath && $this->isOssImagePath($filePath)) {
            $query = [];
            parse_str(parse_url($filePath, PHP_URL_QUERY) ?? '', $query);
            // 已带水印的链接重复调用时保持原样，避免叠加水印。
            if (is_string($query['x-oss-process'] ?? null) && strpos($query['x-oss-process'], '/watermark,') !== false) {
                return $filePath;
            }
            $position = $this->position[$waterConfig['watermark_position'] ?? 1] ?? 'nw';
            $offset = ',g_' . $position . ',x_' . (int)($waterConfig['watermark_x'] ?? 0) . ',y_' . (int)($waterConfig['watermark_y'] ?? 0);
            switch ($waterConfig['watermark_type'] ?? 2) {
                case 1://图片
                    if (empty($waterConfig['watermark_image'])) {
                        throw new AdminException('请先配置水印图片');
                    }
                    $image = $this->getOssWatermarkKey($waterConfig['watermark_image']);
                    $rotation = $this->normalizeOssWatermarkAngle($waterConfig['watermark_rotate'] ?? '');
                    // OSS 预处理参数编码在水印对象内，不能旋转主图。
                    if ($rotation) $image .= '?x-oss-process=image/rotate,' . $rotation;
                    $encoded = rtrim(strtr(base64_encode($image), '+/', '-_'), '=');
                    $opacity = ($waterConfig['watermark_opacity'] ?? '') === '' ? 100 : (int)$waterConfig['watermark_opacity'];
                    $waterPath = $this->appendOssImageProcess($filePath, 'watermark,image_' . $encoded . ',t_' . $opacity . $offset);
                    break;
                case 2://文字
                    if (empty($waterConfig['watermark_text'])) {
                        throw new AdminException('请先配置水印文字');
                    }
                    $color = ltrim($waterConfig['watermark_text_color'] ?? '#ffffff', '#');
                    $encoded = rtrim(strtr(base64_encode($waterConfig['watermark_text']), '+/', '-_'), '=');
                    $rotation = $this->normalizeOssWatermarkAngle($waterConfig['watermark_text_angle'] ?? '');
                    $waterPath = $this->appendOssImageProcess($filePath, 'watermark,text_' . $encoded . ',color_' . $color
                        . ',size_' . (int)($waterConfig['watermark_text_size'] ?? 28) . ($rotation ? ',rotate_' . $rotation : '') . $offset);
                    break;
            }
        }
        return $waterPath;
    }

    /**
     * 转换配置角度为 OSS 支持的顺时针角度
     * @param mixed $value 角度或空值
     * @return int
     */
    protected function normalizeOssWatermarkAngle($value): int
    {
        $angle = filter_var($value === '' ? 0 : $value, FILTER_VALIDATE_INT);
        if ($angle === false || $angle < -360 || $angle > 360) {
            throw new AdminException('水印旋转角度无效');
        }
        return ($angle % 360 + 360) % 360;
    }

    /**
     * 判断链接是否对应可进行图片处理的资源
     * @param string $filePath 资源链接
     * @return bool
     */
    protected function isOssImagePath(string $filePath): bool
    {
        $parts = parse_url($filePath);
        $current = parse_url($this->fileInfo->filePath ?? '');
        // 上传后以真实内容为准，允许无扩展名图片，排除伪装成图片的文件。
        if ($parts && $current && array_intersect_key($parts, array_flip(['scheme', 'host', 'port', 'path']))
            === array_intersect_key($current, array_flip(['scheme', 'host', 'port', 'path']))
            && isset($this->fileInfo->isImage)) {
            return $this->fileInfo->isImage;
        }
        return in_array(strtolower(pathinfo($parts['path'] ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
    }

    /**
     * 合并图片处理查询参数，保留其他参数及片段
     * @param string $filePath 资源链接
     * @param string $operation 图片处理指令
     * @return string
     */
    protected function appendOssImageProcess(string $filePath, string $operation): string
    {
        $parts = parse_url($filePath);
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        $process = is_string($query['x-oss-process'] ?? null) ? $query['x-oss-process'] : 'image';
        $query['x-oss-process'] = $process . '/' . $operation;
        $url = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') . ($parts['path'] ?? '');
        return $url . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986)
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    /**
     * 将同空间水印附件链接转换为 OSS 对象 key
     * @param string $image 水印图片 key 或公开链接
     * @return string
     */
    protected function getOssWatermarkKey(string $image): string
    {
        $parts = parse_url($image);
        if (!isset($parts['scheme']) && !isset($parts['host'])) {
            return ltrim($image, '/');
        }
        // OSS 图片水印只读取对象 key；公开域名与 CDN 路径前缀都需要精确匹配。
        foreach ([$this->uploadUrl, $this->cdn] as $base) {
            $baseParts = parse_url($base);
            if (!$baseParts || !in_array($parts['scheme'] ?? '', ['http', 'https'], true)
                || isset($parts['user']) || isset($parts['pass'])
                || ($parts['scheme'] ?? '') !== ($baseParts['scheme'] ?? '')
                || strtolower($parts['host'] ?? '') !== strtolower($baseParts['host'] ?? '')
                || ($parts['port'] ?? (($parts['scheme'] ?? '') === 'https' ? 443 : 80))
                    !== ($baseParts['port'] ?? (($baseParts['scheme'] ?? '') === 'https' ? 443 : 80))) {
                continue;
            }
            $prefix = rtrim($baseParts['path'] ?? '', '/') . '/';
            if (strpos($parts['path'] ?? '', $prefix) === 0) {
                $key = rawurldecode(substr($parts['path'], strlen($prefix)));
                if ($key !== '') {
                    return $key;
                }
            }
        }
        throw new UploadException('水印图片必须位于当前OSS空间');
    }

    /**
     * 删除资源
     * @param $key
     * @return mixed
     */
    public function delete(string $key)
    {
        $this->error = null;
        try {
            return $this->app()->deleteObject($this->storageName, $key);
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 获取OSS上传密钥
     * @return mixed|void
     */
    public function getTempKeys($callbackUrl = '', $dir = '')
    {
        $this->error = null;
        if (!$this->accessKey || !$this->secretKey || !$this->uploadUrl) {
            return $this->setError('请先配置accessKey、secretKey和空间域名');
        }
        $expire = time() + 30;
        $base64CallbackBody = base64_encode(json_encode([
            'callbackUrl' => $callbackUrl,
            'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType' => "application/x-www-form-urlencoded"
        ]));

        $policy = json_encode([
            'expiration' => $this->gmtIso8601($expire),
            'conditions' =>
                [
                    [0 => 'content-length-range', 1 => 0, 2 => 1048576000],
                    [0 => 'starts-with', 1 => '$key', 2 => $dir]
                ]
        ]);
        $base64Policy = base64_encode($policy);
        $signature = base64_encode(hash_hmac('sha1', $base64Policy, $this->secretKey, true));
        return [
            'accessid' => $this->accessKey,
            'host' => $this->uploadUrl,
            'cdn' => $this->cdn,
            'policy' => $base64Policy,
            'signature' => $signature,
            'expire' => $expire,
            'callback' => $base64CallbackBody,
            'type' => 'OSS'
        ];
    }

    /**
     * 获取ISO时间格式
     * @param $time
     * @return string
     */
    protected function gmtIso8601($time)
    {
        return gmdate('Y-m-d\TH:i:s\Z', $time);
    }

    /**
     * 文件路径上传
     * @param string $filePath
     * @param string $key
     * @return array|bool|mixed|\StdClass
     */
    public function upload(string $filePath, string $key)
    {
        $this->resetOssUploadState();
        try {
            $realName = $key;
            [$size, $mimeType, $isImage] = $this->inspectOssFile($filePath, $key);
            $key = $this->getUploadPath($key);
            $uploadInfo = $this->app()->uploadFile($this->storageName, $key, $filePath);
            if (!isset($uploadInfo['info']['url'])) {
                return $this->setError('Upload failure');
            }
            return $this->finishOssUpload($uploadInfo, $key, $realName, $size, $mimeType, $isImage);
        } catch (\Exception $e) {
            $this->resetOssUploadState();
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 获取文件路径
     * @param string $key
     * @return string
     */
    public function getFilePath(string $key)
    {
        if ($key === '') {
            return '';
        }
        $parts = parse_url($key);
        if (in_array($parts['scheme'] ?? '', ['http', 'https'], true) && !empty($parts['host'])) {
            return $key;
        }
        $base = $this->cdn ?: $this->uploadUrl;
        if (!$base) {
            throw new UploadException('请先配置空间域名');
        }
        // 对象 key 保持原样上传，访问链接逐段编码，防止空格和查询分隔符改变资源路径。
        return $base . '/' . implode('/', array_map('rawurlencode', explode('/', ltrim($key, '/'))));
    }

    /**
     * 获取文件信息
     * @return mixed
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * 获取当前管理下的所有桶
     * @param string|null $region
     * @param bool $line
     * @param bool $shared
     * @return mixed|\OSS\Model\BucketListInfo
     * @throws OssException
     */
    public function listbuckets(string $region = 'oss-cn-hangzhou.aliyuncs.com', bool $line = false, bool $shared = false)
    {
        $this->error = null;
        try {
            $response = $this->createClient($region)->listBuckets();
            $list = [];
            // 保持存储空间同步服务需要的厂商字段。
            foreach ($response->getBucketList() as $item) {
                $list[] = [
                    'location' => $item->getLocation(),
                    'name' => $item->getName(),
                    'createTime' => $item->getCreateDate()
                ];
            }
            return $list;
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 创建空间
     * @param string $name
     * @param string $region
     * @param string $acl
     * @return mixed|void
     */
    public function createBucket(string $name, string $region = '', string $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ)
    {
        $this->error = null;
        try {
            $endpoint = $this->normalizeEndpoint($region);
            $endpoints = array_map([$this, 'normalizeEndpoint'], array_column($this->getRegion(), 'value'));
            if (!in_array($endpoint, $endpoints, true)) {
                return $this->setError('OSS:无效的区域');
            }
            if (!in_array($acl, [OssClient::OSS_ACL_TYPE_PRIVATE, OssClient::OSS_ACL_TYPE_PUBLIC_READ, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE], true)) {
                return $this->setError('OSS:无效的读写权限');
            }
            $handle = $this->createClient($region);
            if ($handle->doesBucketExist($name)) {
                return $this->setError('OSS:空间已经存在，请更换空间名');
            }
            return $handle->createBucket($name, $acl);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'The bucket you access does not belong to you') !== false) {
                return $this->setError('OSS:空间已被使用，请更换空间名');
            }
            return $this->setError('OSS:' . $e->getMessage());
        }
    }

    /**
     * 删除空间
     * @param string $name
     * @param string $region
     * @return bool|mixed|null
     */
    public function deleteBucket(string $name, string $region = '')
    {
        $this->error = null;
        try {
            $handle = $this->createClient($region);
            return $handle->deleteBucket($name);
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 获取绑定域名
     * @param string $name
     * @param string|null $region
     * @return array|bool
     */
    public function getDomian(string $name, ?string $region = null)
    {
        $this->error = null;
        try {
            $handle = $this->createClient($region);
            $res = $handle->getBucketCname($name);
            $data = $res->getCnames();
            return array_column($data, 'Domain');
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 绑定域名
     * @param string $name
     * @param string $domain
     * @param string|null $region
     * @return bool|mixed
     */
    public function bindDomian(string $name, string $domain, ?string $region = null)
    {
        $this->error = null;
        try {
            $domain = trim($domain);
            $parseDomin = parse_url(strpos($domain, '://') === false ? 'https://' . $domain : $domain);
            if (!$parseDomin || !in_array($parseDomin['scheme'] ?? '', ['http', 'https'], true)
                || isset($parseDomin['user']) || isset($parseDomin['pass'])
                || empty($parseDomin['host']) || !filter_var($parseDomin['host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                return $this->setError('OSS:无效的域名');
            }
            $host = strtolower($parseDomin['host']);
            $handle = $this->createClient($region);
            $res = $handle->getBucketCname($name);
            $data = $res->getCnames();
            if (in_array($host, array_map('strtolower', array_column($data, 'Domain')), true)) {
                return true;
            }
            return $handle->addBucketCname($name, $host);
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 设置跨域
     * @param string $name
     * @param string $region
     * @return bool|null
     */
    public function setBucketCors(string $name, string $region)
    {
        $this->error = null;
        try {
            $handle = $this->createClient($region);
            $corsConfig = new CorsConfig();
            $rule = new CorsRule();
            // 设置允许跨域请求的响应头。AllowedHeader可以设置多个，每个AllowedHeader中最多只能使用一个通配符星号（*）。
            // 建议无特殊需求时设置AllowedHeader为星号（*）。
            $rule->addAllowedHeader("*");
            // 设置允许用户从应用程序中访问的响应头。ExposeHeader可以设置多个，ExposeHeader中不支持使用通配符星号（*）。
            $rule->addExposeHeader("ETag");
            // 设置允许的跨域请求的来源。AllowedOrigin可以设置多个，每个AllowedOrigin中最多只能使用一个通配符星号（*）。
            // 设置AllowedOrigin为星号（*）时，表示允许所有域的来源。
            $rule->addAllowedOrigin("*");
            // 设置允许的跨域请求方法。
            $rule->addAllowedMethod("POST");
            $rule->addAllowedMethod("GET");
            $rule->addAllowedMethod("DELETE");
            $rule->addAllowedMethod("PUT");
            $rule->addAllowedMethod("HEAD");
            // 设置浏览器对特定资源的预取（OPTIONS）请求返回结果的缓存时间，单位为秒。
            $rule->setMaxAgeSeconds(600);
            // 每个Bucket最多支持添加10条规则。
            $corsConfig->addRule($rule);
            return $handle->putBucketCors($name, $corsConfig);
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 数据中心
     * @return mixed|\string[][]
     */
    public function getRegion()
    {
        return [
            [
                'value' => 'oss-cn-hangzhou.aliyuncs.com',
                'label' => '华东1（杭州）'
            ],
            [
                'value' => 'oss-cn-shanghai.aliyuncs.com',
                'label' => '华东2（上海）'
            ],
            [
                'value' => 'oss-cn-qingdao.aliyuncs.com',
                'label' => '华北1（青岛）'
            ],
            [
                'value' => 'oss-cn-beijing.aliyuncs.com',
                'label' => '华北2（北京）'
            ],
            [
                'value' => 'oss-cn-zhangjiakou.aliyuncs.com',
                'label' => '华北 3（张家口）'
            ],
            [
                'value' => 'oss-cn-huhehaote.aliyuncs.com',
                'label' => '华北5（呼和浩特）'
            ],
            [
                'value' => 'oss-cn-wulanchabu.aliyuncs.com',
                'label' => '华北6（乌兰察布）'
            ],
            [
                'value' => 'oss-cn-shenzhen.aliyuncs.com',
                'label' => '华南1（深圳）'
            ],
            [
                'value' => 'oss-cn-heyuan.aliyuncs.com',
                'label' => '华南2（河源）'
            ],
            [
                'value' => 'oss-cn-guangzhou.aliyuncs.com',
                'label' => '华南3（广州）'
            ],
            [
                'value' => 'oss-cn-chengdu.aliyuncs.com',
                'label' => '西南1（成都）'
            ],
            [
                'value' => 'oss-cn-hongkong.aliyuncs.com',
                'label' => '中国（香港）'
            ],
            [
                'value' => 'oss-us-west-1.aliyuncs.com',
                'label' => '美国（硅谷）*'
            ],
            [
                'value' => 'oss-us-east-1.aliyuncs.com',
                'label' => '美国（弗吉尼亚）*'
            ],
            [
                'value' => 'oss-ap-southeast-1.aliyuncs.com',
                'label' => '新加坡*'
            ],
            [
                'value' => 'oss-ap-southeast-2.aliyuncs.com',
                'label' => '澳大利亚（悉尼）*'
            ],
            [
                'value' => 'oss-ap-southeast-3.aliyuncs.com',
                'label' => '马来西亚（吉隆坡）*'
            ],
            [
                'value' => 'oss-ap-southeast-5.aliyuncs.com',
                'label' => '印度尼西亚（雅加达）*'
            ],
            [
                'value' => 'oss-ap-northeast-1.aliyuncs.com',
                'label' => '日本（东京）*'
            ],
            [
                'value' => 'oss-ap-south-1.aliyuncs.com',
                'label' => '印度（孟买）*'
            ],
            [
                'value' => 'oss-eu-central-1.aliyuncs.com',
                'label' => '德国（法兰克福）*'
            ],
            [
                'value' => 'oss-eu-west-1.aliyuncs.com',
                'label' => '英国（伦敦）'
            ],
            [
                'value' => 'oss-me-east-1.aliyuncs.com',
                'label' => '阿联酋（迪拜）*'
            ],
            [
                'value' => 'oss-ap-southeast-6.aliyuncs.com',
                'label' => '菲律宾（马尼拉）'
            ]
        ];
    }
}
