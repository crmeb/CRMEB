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
namespace driver\upload\Local;

use crmeb\services\upload\BaseUpload;
use crmeb\exceptions\AdminException;
use crmeb\utils\DownloadImage;
use think\facade\Config;
use think\facade\Filesystem;
use think\File;
use think\Image;

/**
 * 本地上传
 * Class Driver
 * @package driver\upload\Local
 */
class Driver extends BaseUpload
{

    /**
     * 本地存储可用的访问域名
     * @var array
     */
    protected $localUrlBases = [];

    /**
     * 缩略图、水印图存放位置
     * @var string
     */
    public $thumbWaterPath = 'thumb_water';

    /**
     * 初始化本地图片处理配置
     * @param array $config
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->localUrlBases = array_filter([$config['uploadUrl'] ?? '', $config['cdn'] ?? '']);
        $this->waterConfig['watermark_text_font'] = app()->getRootPath() . 'public' . '/statics/font/simsunb.ttf';
    }

    /**
     * 设置上传路径
     * @param string $path
     * @return $this
     */
    public function to(string $path)
    {
        $this->path = $this->normalizeLocalPath(ltrim(str_replace('\\', '/', $path), '/'));
        $this->beginLocalOperation();
        return $this;
    }

    /**
     * 开启验证
     * @param array|null $rules
     * @return $this
     */
    public function validate(?array $rules = null)
    {
        $this->validate = $rules ?? Config::get('upload', []);
        return $this;
    }

    /**
     * 本地驱动不需要第三方客户端
     * @return null
     */
    protected function app()
    {
        // TODO: Implement app() method.
    }

    /**
     * 本地存储不支持云端临时凭据
     * @return bool
     */
    public function getTempKeys()
    {
        // TODO: Implement getTempKeys() method.
        return $this->setError('请检查您的上传配置，视频默认oss上传');
    }

    /**
     * 生成上传文件目录
     * @param $path
     * @param null $root
     * @return string
     */
    public function uploadDir($path, $root = null)
    {
        $path = $this->normalizeLocalPath(ltrim(str_replace('\\', '/', (string)$path), '/'));
        if ($root === null) return $this->localDiskPath($path);
        return rtrim(str_replace('\\', '/', $root), '/') . '/uploads' . ($path !== '' ? '/' . $path : '');
    }

    /**
     * 检查上传目录不存在则生成
     * @param $dir
     * @return bool
     */
    protected function validDir($dir)
    {
        return is_dir($dir) || @mkdir($dir, 0755, true) || is_dir($dir);
    }

    /**
     * 清理上一次操作的结果，避免缓存驱动泄漏文件信息
     * @return void
     */
    protected function beginLocalOperation()
    {
        $this->fileInfo = new \StdClass();
        $this->downFileInfo = new \StdClass();
        $this->error = null;
    }

    /**
     * 校验相对目录和对象名，不允许跳出存储根目录
     * @param string $path
     * @param bool $allowEmpty
     * @return string
     */
    protected function normalizeLocalPath(string $path, bool $allowEmpty = true)
    {
        $path = str_replace('\\', '/', $path);
        if (preg_match('/[\x00-\x1f\x7f:]/', $path) || str_starts_with($path, '/')) {
            throw new \InvalidArgumentException('不合法的文件路径');
        }
        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '..') throw new \InvalidArgumentException('不合法的文件路径');
            if ($part !== '' && $part !== '.') $parts[] = $part;
        }
        $path = implode('/', $parts);
        if (!$allowEmpty && $path === '') throw new \InvalidArgumentException('文件名称不能为空');
        return $path;
    }

    /**
     * 获取磁盘根目录并检查实际父目录，防止符号链接逃逸
     * @param string $path
     * @param string $disk
     * @return string
     */
    protected function localDiskPath(string $path, string $disk = 'public')
    {
        $path = $this->normalizeLocalPath($path);
        $root = rtrim(str_replace('\\', '/', (string)Config::get('filesystem.disks.' . $disk . '.root', '')), '/');
        if ($root === '' || !str_starts_with($root, '/')) throw new \InvalidArgumentException('本地存储根目录配置错误');
        $target = $root . ($path !== '' ? '/' . $path : '');
        $realRoot = $this->resolveLocalPath($root);
        $realTarget = $this->resolveLocalPath($target);
        if (is_link($target) || ($realTarget !== $realRoot && !str_starts_with($realTarget, $realRoot . '/'))) {
            throw new \InvalidArgumentException('文件路径超出本地存储目录');
        }
        return $target;
    }

    /**
     * 解析尚未创建的目录，同时校验其已有父目录
     * @param string $path
     * @return string
     */
    protected function resolveLocalPath(string $path)
    {
        $existing = $path;
        $suffix = '';
        while (!file_exists($existing) && !is_link($existing)) {
            $parent = dirname($existing);
            if ($parent === $existing) throw new \InvalidArgumentException('无法解析本地存储目录');
            $suffix = '/' . basename($existing) . $suffix;
            $existing = $parent;
        }
        $resolved = realpath($existing);
        if ($resolved === false) throw new \InvalidArgumentException('无法解析本地存储目录');
        return rtrim(str_replace('\\', '/', $resolved), '/') . $suffix;
    }

    /**
     * 根据实际内容校验扩展名、大小和 MIME，忽略客户端声明类型
     * @param string $name
     * @param string $source
     * @param bool $isFile
     * @return bool
     */
    protected function validateLocalFile(string $name, string $source, bool $isFile = true)
    {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (in_array($extension, ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phar', 'shtml'], true)
            || in_array(strtolower(basename($name)), ['.htaccess', '.user.ini'], true)) {
            return $this->setError('不合法的文件后缀');
        }
        if ($isFile && (!is_file($source) || !is_readable($source))) return $this->setError('上传的文件不存在或不可读');
        $size = $isFile ? filesize($source) : strlen($source);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $isFile ? $finfo->file($source) : $finfo->buffer($source);
        if ($this->validate) {
            if (isset($this->validate['fileExt']) && !in_array($extension, array_map('strtolower', $this->validate['fileExt']), true)) {
                return $this->setError('不合法的文件后缀');
            }
            if (isset($this->validate['filesize']) && $size > (int)$this->validate['filesize']) return $this->setError('文件过大');
            if (isset($this->validate['fileMime']) && !in_array($mime, $this->validate['fileMime'], true)) return $this->setError('不合法的文件类型');
        }
        $imageMimes = ['png' => ['image/png'], 'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'gif' => ['image/gif'],
            'webp' => ['image/webp'], 'bmp' => ['image/bmp', 'image/x-ms-bmp'], 'ico' => ['image/x-icon', 'image/vnd.microsoft.icon']];
        if (isset($imageMimes[$extension]) && !in_array($mime, $imageMimes[$extension], true)) {
            return $this->setError('文件内容与图片后缀不符');
        }
        if (isset($imageMimes[$extension]) || in_array($mime, array_merge(...array_values($imageMimes)), true)) {
            return $this->validateLocalImage($source, $isFile);
        }
        return true;
    }

    /**
     * 图片头只用于尺寸预检，完整解码独立于缩略图和水印开关。
     * @param string $source
     * @param bool $isFile
     * @return bool
     */
    protected function validateLocalImage(string $source, bool $isFile = true)
    {
        $info = $isFile ? @getimagesize($source) : @getimagesizefromstring($source);
        if (!$info) return $this->setError('图片内容不合法');
        if (!$this->validateLocalImageDimensions((int)($info[0] ?? 0), (int)($info[1] ?? 0))) return false;
        // GD 不支持 ICO，且本地驱动不会解码或变换此格式。
        if (($info[2] ?? null) === IMAGETYPE_ICO) return true;
        if (!function_exists('imagecreatefromstring')) return $this->setError('图片解码组件不可用');
        $content = $isFile ? @file_get_contents($source) : $source;
        if (!is_string($content)) return $this->setError('图片内容不可读');
        $image = null;
        $warning = false;
        set_error_handler(static function (int $level) use (&$warning): bool {
            if ($level & (E_WARNING | E_NOTICE)) { $warning = true; return true; }
            return false;
        });
        try {
            $image = imagecreatefromstring($content);
            if (!$image || $warning) return $this->setError('图片内容损坏');
            return true;
        } catch (\Throwable $e) {
            return $this->setError('图片内容无法解码');
        } finally {
            if ($image instanceof \GdImage) imagedestroy($image);
            restore_error_handler();
        }
    }

    /**
     * 在解码和旋转分配内存前限制像素，使用除法避免维度乘法溢出。
     * @param int $width
     * @param int $height
     * @return bool
     */
    protected function validateLocalImageDimensions(int $width, int $height)
    {
        $maxPixels = filter_var($this->validate['imageMaxPixels'] ?? 20000000, FILTER_VALIDATE_INT);
        if ($maxPixels === false || $maxPixels <= 0) return $this->setError('图片像素限制配置不合法');
        if ($width <= 0 || $height <= 0 || $width > intdiv($maxPixels, $height)) return $this->setError('图片像素超过限制');
        return true;
    }

    /**
     * 写入前检查所有约定的衍生文件，避免覆盖后复用旧图或触碰符号链接。
     * @param string $relative
     * @return array
     */
    protected function localThumbnailPaths(string $relative)
    {
        $directory = dirname($relative);
        $paths = [];
        foreach ($this->thumb as $size) {
            $path = $this->localDiskPath(($directory !== '.' ? $directory . '/' : '') . $size . '_' . basename($relative));
            if (file_exists($path) && !is_file($path)) throw new \InvalidArgumentException('缩略图路径不是文件');
            $paths[] = $path;
        }
        return $paths;
    }

    /**
     * 每次成功写入后失效旧缩略图，即使本次关闭变换也不能保留旧缓存。
     * @param array $paths
     * @return void
     */
    protected function clearLocalThumbnails(array $paths)
    {
        foreach ($paths as $path) {
            if (is_file($path) && !@unlink($path)) throw new \RuntimeException('旧缩略图删除失败');
        }
    }

    /**
     * 水印只在写入原图时应用一次，读取缩略图不能再次修改原图。
     * @param string $target
     * @param string $url
     * @return void
     */
    protected function transformLocalFile(string $target, string $url)
    {
        if (!$this->authThumb || !$this->checkImage($target) || in_array(strtolower(pathinfo($target, PATHINFO_EXTENSION)), ['ico', 'gif'], true)) return;
        $errorLevel = error_reporting(E_ALL & ~E_DEPRECATED);
        try {
            $this->water($url);
            $this->thumb($url, basename($target));
        } finally {
            error_reporting($errorLevel);
        }
    }

    /**
     * 为未指定对象名的流根据实际 MIME 生成安全扩展名
     * @param string $content
     * @return string
     */
    protected function localStreamFileName(string $content)
    {
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
        $extensions = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/webp' => 'webp',
            'image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico', 'text/plain' => 'txt', 'application/zip' => 'zip'];
        return $this->saveFileName(null, $extensions[$mime] ?? null);
    }

    /**
     * 保存已校验文件，统一处理写入失败及返回元信息
     * @param string $key
     * @param callable $save
     * @param bool $download
     * @return bool|\StdClass
     */
    protected function saveLocalFile(string $key, callable $save, bool $download = false)
    {
        try {
            $key = $this->normalizeLocalPath($key, false);
            $relative = trim(($this->path ?? '') . '/' . $key, '/');
            $target = $this->localDiskPath($relative);
            $thumbnailPaths = $this->localThumbnailPaths($relative);
            if (!$this->validDir(dirname($target))) return $this->setError('无法创建上传目录，请检查目录权限');
            if (!$save($target)) return $this->setError('文件写入失败');
            $this->clearLocalThumbnails($thumbnailPaths);
            $file = new File($target);
            $url = Filesystem::disk('public')->url($relative);
            if ($download) {
                $this->downFileInfo->downloadInfo = $file;
                $this->downFileInfo->downloadRealName = $key;
                $this->downFileInfo->downloadFileName = basename($key);
                $this->downFileInfo->downloadFilePath = $url;
                return $this->downFileInfo;
            }
            $this->fileInfo->uploadInfo = $file;
            $this->fileInfo->realName = $key;
            $this->fileInfo->fileName = basename($key);
            $this->fileInfo->filePath = $url;
            $this->transformLocalFile($target, $url);
            return $this->fileInfo;
        } catch (\Throwable $e) {
            $this->beginLocalOperation();
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 检测filepath是否是远程地址
     * @param string $filePath
     * @return bool
     */
    public function checkFilePathIsRemote(string $filePath)
    {
        return (bool)preg_match('~^(?:https?:)?//~i', $filePath);
    }

    /**
     * 生成与配置相关的文件名称以及路径
     * @param string $filePath 原地址
     * @param string $toPath 保存目录
     * @param array $config 配置相关参数
     * @param string $root
     * @return string
     */
    public function createSaveFilePath(string $filePath, string $toPath, array $config = [], $root = '/')
    {
        [$path, $ext] = $this->getFileName($filePath);
        $fileName = md5(json_encode($config) . $filePath);
        return $this->uploadDir($toPath, $root) . '/' . $fileName . '.' . $ext;
    }

    /**
     * 文件上传
     * @param string $file
     * @param bool $realName
     * @return array|bool|mixed|\StdClass
     */
    public function move(string $file = 'file', $realName = false)
    {
        $this->beginLocalOperation();
        try {
            $fileHandle = app()->request->file($file);
            if (!$fileHandle || !$fileHandle->isValid()) return $this->setError('上传的文件不存在或不完整');
            $originalName = $fileHandle->getOriginalName();
            if (!$this->validateLocalFile($originalName, $fileHandle->getPathname())) return false;
            // 私钥和证书按扩展名进入私有磁盘，不能由客户端 MIME 决定目录。
            $private = in_array(strtolower(pathinfo($originalName, PATHINFO_EXTENSION)), ['pem', 'key', 'crt', 'cer'], true);
            $disk = $private ? 'pem' : 'public';
            $path = $private ? '' : ($this->path ?? '');
            if ($realName && (str_contains($originalName, '/') || str_contains($originalName, '\\'))) {
                return $this->setError('不合法的文件名称');
            }
            $key = $realName ? $originalName : $fileHandle->hashName($private ? function () { return md5(microtime(true)); } : null);
            $key = $this->normalizeLocalPath($key, false);
            $relative = trim($path . '/' . $key, '/');
            $target = $this->localDiskPath($relative, $disk);
            $thumbnailPaths = $private ? [] : $this->localThumbnailPaths($relative);
            $fileName = Filesystem::disk($disk)->putFileAs($path, $fileHandle, $key);
            if (!$fileName) return $this->setError('文件写入失败');
            $this->clearLocalThumbnails($thumbnailPaths);
            $this->fileInfo->uploadInfo = new File($target);
            $this->fileInfo->realName = $originalName;
            $this->fileInfo->fileName = basename($fileName);
            $this->fileInfo->filePath = Filesystem::disk($disk)->url($fileName);
            if (!$private) $this->transformLocalFile($target, $this->fileInfo->filePath);
            return $this->fileInfo;
        } catch (\Throwable $e) {
            $this->beginLocalOperation();
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 文件流上传
     * @param $fileContent
     * @param string|null $key
     * @return array|bool|mixed|\StdClass
     */
    public function stream($fileContent, ?string $key = null)
    {
        $this->beginLocalOperation();
        if (is_resource($fileContent)) $fileContent = stream_get_contents($fileContent);
        if (!is_string($fileContent)) return $this->setError('文件流内容不合法');
        $key = $key ?: $this->localStreamFileName($fileContent);
        if (!$this->validateLocalFile($key, $fileContent, false)) return false;
        return $this->saveLocalFile($key, function ($target) use ($fileContent) {
            return @file_put_contents($target, $fileContent, LOCK_EX) === strlen($fileContent);
        });
    }

    /**
     * 文件流下载保存图片
     * @param string $fileContent
     * @param string|null $key
     * @return array|bool|mixed|\StdClass
     */
    public function down(string $fileContent, ?string $key = null)
    {
        $this->beginLocalOperation();
        $key = $key ?: $this->localStreamFileName($fileContent);
        if (!$this->validateLocalFile($key, $fileContent, false)) return false;
        return $this->saveLocalFile($key, function ($target) use ($fileContent) {
            return @file_put_contents($target, $fileContent, LOCK_EX) === strlen($fileContent);
        }, true);
    }

    /**
     * 生成缩略图
     * @param string $filePath
     * @param string $fileName
     * @param string $type
     * @return array|mixed|string[]
     */
    public function thumb(string $filePath = '', string $fileName = '', string $type = 'all')
    {
        $config = $this->thumbConfig;
        $data = ['big' => $filePath, 'mid' => $filePath, 'small' => $filePath];
        $this->fileInfo->filePathBig = $this->fileInfo->filePathMid = $this->fileInfo->filePathSmall = $this->fileInfo->filePathWater = $filePath;
        if ($filePath === '') return $data;
        if ($type !== 'all' && !in_array($type, $this->thumb, true)) throw new \InvalidArgumentException('不支持的缩略图尺寸');
        $source = $this->getFilePath($filePath);
        if (!$this->authThumb) return $data;
        $imageInfo = @getimagesize($source);
        if (!$imageInfo || in_array(strtolower(pathinfo($source, PATHINFO_EXTENSION)), ['ico', 'gif'], true)) return $data;
        $fileName = basename($source);
        $root = $this->localDiskPath('');
        if (!str_starts_with($source, $root . '/')) throw new \InvalidArgumentException('图片不属于本地公开存储');
        $relative = substr($source, strlen($root) + 1);
        $filePath = Filesystem::disk('public')->url($relative);
        $this->fileInfo->filePathBig = $this->fileInfo->filePathMid = $this->fileInfo->filePathSmall = $this->fileInfo->filePathWater = $filePath;
        $data = ['big' => $filePath, 'mid' => $filePath, 'small' => $filePath];
        $errorLevel = error_reporting(E_ALL & ~E_DEPRECATED);
        try {
            if (!($config['image_thumb_status'] ?? 0)) return $data;
            if (!$this->validateLocalImageDimensions((int)$imageInfo[0], (int)$imageInfo[1])) throw new \InvalidArgumentException((string)$this->getError());
            $sourceValidated = false;
            foreach ($this->thumb as $size) {
                if ($type !== 'all' && $type !== $size) continue;
                $height = (int)($config['thumb_' . $size . '_height'] ?? 0);
                $width = (int)($config['thumb_' . $size . '_width'] ?? 0);
                if ($height <= 0 || $width <= 0) throw new \InvalidArgumentException('缩略图尺寸必须大于零');
                $directory = dirname($relative);
                $saveRelative = ($directory !== '.' ? $directory . '/' : '') . $size . '_' . $fileName;
                $savePath = $this->localDiskPath($saveRelative);
                // 已生成的缩略图也须同步返回元信息，避免依赖工作目录。
                if (!is_file($savePath)) {
                    if (!$sourceValidated) {
                        if (!$this->validateLocalImage($source)) throw new \InvalidArgumentException((string)$this->getError());
                        $sourceValidated = true;
                    }
                    Image::open($source)->thumb($width, $height)->save($savePath);
                }
                $key = 'filePath' . ucfirst($size);
                $data[$size] = $this->fileInfo->$key = Filesystem::disk('public')->url($saveRelative);
            }
        } catch (\Throwable $e) {
            throw new AdminException($e->getMessage());
        } finally {
            error_reporting($errorLevel);
        }
        return $data;
    }

    /**
     * 添加水印
     * @param string $filePath
     * @return mixed|string
     */
    public function water(string $filePath = '')
    {
        $waterConfig = $this->waterConfig;
        if (($waterConfig['image_watermark_status'] ?? 0) && $filePath) {

            switch ($waterConfig['watermark_type']) {
                case 1:
                    if (!empty($waterConfig['watermark_image'])) $filePath = $this->image($filePath, $waterConfig);
                    break;
                case 2:
                    $filePath = $this->text($filePath, $waterConfig);
                    break;
            }
        }
        return $filePath;
    }

    /**
     * 图片水印
     * @param string $filePath
     * @param array $waterConfig
     * @return string
     */
    public function image(string $filePath, array $waterConfig = [])
    {
        if (!$waterConfig) {
            $waterConfig = $this->waterConfig;
        }
        $watermark_image = $waterConfig['watermark_image'] ?? '';
        if (!$watermark_image) throw new AdminException('请先配置水印图片');
        $savePath = $this->getFilePath($filePath);
        try {
            $watermark_image = $this->getFilePath($watermark_image);
        } catch (\InvalidArgumentException $e) {
            if (!$this->checkFilePathIsRemote($watermark_image)) throw $e;
            // 外部水印只缓存到当前本地上传磁盘，不使用默认磁盘或当前工作目录。
            [, $extension] = $this->getFileName($watermark_image);
            $name = 'water_image_' . md5($watermark_image) . '.' . $extension;
            $watermark_image = $this->localDiskPath($this->thumbWaterPath . '/' . $name);
            if (!is_file($watermark_image)) {
                try {
                    /** @var DownloadImage $down */
                    $down = app()->make(DownloadImage::class);
                    $data = $down->path($this->thumbWaterPath)->downloadImage($waterConfig['watermark_image'], $name);
                    $watermark_image = $this->getFilePath($data['path'] ?? '');
                } catch (\Throwable $e) {
                    throw new AdminException('远程水印图片下载失败');
                }
            }
        }
        $temporaryWatermark = null;
        try {
            if (!$this->validateLocalImage($savePath) || !$this->validateLocalImage($watermark_image)) {
                throw new \InvalidArgumentException((string)$this->getError());
            }
            $rotate = $waterConfig['watermark_rotate'] ?? 0;
            if ($rotate !== '' && (!is_numeric($rotate) || !is_finite((float)$rotate) || abs((float)$rotate) > 360)) {
                throw new \InvalidArgumentException('水印旋转角度不合法');
            }
            $degrees = fmod((float)$rotate, 360);
            if ($degrees != 0) {
                $info = getimagesize($watermark_image);
                $radians = deg2rad($degrees);
                $quarterTurn = abs(fmod($degrees, 180));
                if ($quarterTurn == 90) {
                    $width = $info[1];
                    $height = $info[0];
                } elseif ($quarterTurn == 0) {
                    $width = $info[0];
                    $height = $info[1];
                } else {
                    $width = (int)ceil($info[0] * abs(cos($radians)) + $info[1] * abs(sin($radians)));
                    $height = (int)ceil($info[0] * abs(sin($radians)) + $info[1] * abs(cos($radians)));
                }
                if (!$this->validateLocalImageDimensions($width, $height)) throw new \InvalidArgumentException((string)$this->getError());
                $temporaryWatermark = $this->localDiskPath($this->thumbWaterPath . '/water_rotate_' . bin2hex(random_bytes(16)) . '.png');
                if (!$this->validDir(dirname($temporaryWatermark))) throw new \RuntimeException('无法创建临时水印目录');
                Image::open($watermark_image)->rotate($degrees)->save($temporaryWatermark, 'png');
                $watermark_image = $temporaryWatermark;
            }
            $Image = Image::open($savePath);
            $Image->water($watermark_image, $waterConfig['watermark_position'] ?: 1, (int)($waterConfig['watermark_opacity'] ?? 100))->save($savePath);
        } catch (\Throwable $e) {
            throw new AdminException($e->getMessage());
        } finally {
            if ($temporaryWatermark !== null && is_file($temporaryWatermark) && !@unlink($temporaryWatermark)) {
                throw new AdminException('临时水印文件清理失败');
            }
        }
        return $savePath;
    }

    /**
     * 文字水印
     * @param string $filePath
     * @param array $waterConfig
     * @return string
     */
    public function text(string $filePath, array $waterConfig = [])
    {
        if (!$waterConfig) {
            $waterConfig = $this->waterConfig;
        }
        if (!$waterConfig['watermark_text']) {
            throw new AdminException('请先配置水印文字');
        }
        $savePath = $this->getFilePath($filePath);
        try {
            $angle = $waterConfig['watermark_text_angle'] ?? '';
            $angle = filter_var($angle === '' ? 0 : $angle, FILTER_VALIDATE_INT);
            if ($angle === false || $angle < -360 || $angle > 360) throw new \InvalidArgumentException('水印旋转角度无效');
            // 配置为顺时针正角度，GD 文字角度为逆时针；完整一周无需旋转。
            $angle = -($angle % 360);
            if (!$this->validateLocalImage($savePath)) throw new \InvalidArgumentException((string)$this->getError());
            $Image = Image::open($savePath);
            $color = ltrim((string)$waterConfig['watermark_text_color'], '#');
            if (preg_match('/^[0-9a-f]{3}$/i', $color)) $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
            if (!preg_match('/^[0-9a-f]{6}$/i', $color)) throw new \InvalidArgumentException('水印文字颜色格式错误');
            $waterConfig['watermark_text_color'] = '#' . $color;
            $Image->text($waterConfig['watermark_text'], $waterConfig['watermark_text_font'], (float)$waterConfig['watermark_text_size'], $waterConfig['watermark_text_color'], $waterConfig['watermark_position'], [$waterConfig['watermark_x'], $waterConfig['watermark_y']], $angle)->save($savePath);
        } catch (\Throwable $e) {
            throw new AdminException($e->getMessage() . $e->getLine());
        }
        return $savePath;
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return bool|mixed
     */
    public function delete(string $filePath)
    {
        $this->beginLocalOperation();
        try {
            $path = $this->getFilePath($filePath);
            if (!is_file($path)) return false;
            $root = $this->localDiskPath('');
            $disk = str_starts_with($path, $root . '/') ? 'public' : 'pem';
            $root = $this->localDiskPath('', $disk);
            $relative = substr($path, strlen($root) + 1);
            $directory = dirname($relative);
            $paths = [];
            // 删除前校验全部衍生路径，缺少缩略图不是错误。
            foreach ($this->thumb as $size) {
                $paths[] = $this->localDiskPath(($directory !== '.' ? $directory . '/' : '') . $size . '_' . basename($path), $disk);
            }
            $paths[] = $path;
            foreach ($paths as $target) {
                if (is_file($target) && !@unlink($target)) return $this->setError('文件删除失败');
            }
            return true;
        } catch (\Throwable $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 文件路径上传
     * @param string $filePath
     * @param string $key
     * @return array|bool|mixed|\StdClass
     */
    public function upload(string $filePath, string $key)
    {
        $this->beginLocalOperation();
        if (!$this->validateLocalFile($key, $filePath)) return false;
        return $this->saveLocalFile($key, function ($target) use ($filePath) { return @copy($filePath, $target); });
    }

    /**
     * 获取文件路径
     * @param string $key
     * @return string
     */
    public function getFilePath(string $key)
    {
        if ($key === '' || preg_match('/[\x00-\x1f\x7f]/', $key)) throw new \InvalidArgumentException('不合法的文件路径');
        $key = str_replace('\\', '/', $key);
        foreach (['public', 'pem'] as $disk) {
            $root = $this->localDiskPath('', $disk);
            if ($key === $root || str_starts_with($key, $root . '/')) return $this->localDiskPath(ltrim(substr($key, strlen($root)), '/'), $disk);
        }
        $publicUrl = (string)Config::get('filesystem.disks.public.url', '/uploads');
        $prefix = trim((string)parse_url($publicUrl, PHP_URL_PATH), '/');
        if ($this->checkFilePathIsRemote($key)) {
            $url = parse_url(str_starts_with($key, '//') ? 'https:' . $key : $key);
            if (!$url || isset($url['user']) || isset($url['pass'])) throw new \InvalidArgumentException('不合法的本地文件地址');
            $bases = array_merge($this->localUrlBases, [(string)sys_config('site_url'), $publicUrl]);
            foreach ($bases as $base) {
                $allowed = parse_url($base);
                if (!$allowed || !isset($allowed['host']) || strcasecmp($url['host'] ?? '', $allowed['host']) !== 0
                    || strtolower($url['scheme'] ?? '') !== strtolower($allowed['scheme'] ?? '')
                    || ($url['port'] ?? ($url['scheme'] === 'https' ? 443 : 80)) !== ($allowed['port'] ?? (($allowed['scheme'] ?? '') === 'https' ? 443 : 80))) continue;
                $path = trim(rawurldecode($url['path'] ?? ''), '/');
                $basePath = trim(rawurldecode($allowed['path'] ?? ''), '/');
                if ($basePath !== '' && $path !== $basePath && !str_starts_with($path, $basePath . '/')) continue;
                $path = ltrim(substr($path, strlen($basePath)), '/');
                if ($prefix !== '' && ($path === $prefix || str_starts_with($path, $prefix . '/'))) {
                    $path = ltrim(substr($path, strlen($prefix)), '/');
                } elseif ($basePath === '') {
                    continue;
                }
                return $this->localDiskPath($path);
            }
            throw new \InvalidArgumentException('文件地址不属于本地存储');
        }
        $path = preg_replace('~^\./~', '', $key);
        $plainPath = ltrim($path, '/');
        if ($prefix !== '' && ($plainPath === $prefix || str_starts_with($plainPath, $prefix . '/'))) {
            return $this->localDiskPath(ltrim(substr($plainPath, strlen($prefix)), '/'));
        }
        if (str_starts_with($path, '/')) throw new \InvalidArgumentException('文件路径超出本地存储目录');
        return $this->localDiskPath($path);
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
     * 本地存储不提供云端存储桶列表
     * @param string $region
     * @param bool $line
     * @param bool $shared
     * @return array
     */
    public function listbuckets(string $region, bool $line = false, bool $shared = false)
    {
        return [];
    }

    /**
     * 本地存储不提供创建云端存储桶能力
     * @param string $name
     * @param string $region
     * @return null
     */
    public function createBucket(string $name, string $region)
    {
        return null;
    }

    /**
     * 本地存储不提供删除云端存储桶能力
     * @param string $name
     * @return null
     */
    public function deleteBucket(string $name)
    {
        return null;
    }

    /**
     * 本地存储无需设置云端跨域规则
     * @param string $name
     * @param string $region
     * @return bool
     */
    public function setBucketCors(string $name, string $region)
    {
        return true;
    }

    /**
     * 本地存储没有云端区域
     * @return array
     */
    public function getRegion()
    {
        return [];
    }

    /**
     * 本地存储无需绑定云端域名
     * @param string $name
     * @param string $domain
     * @param string|null $region
     * @return bool
     */
    public function bindDomian(string $name, string $domain, ?string $region = null)
    {
        return true;
    }

    /**
     * 本地存储没有云端域名列表
     * @param string $name
     * @param string|null $region
     * @return array
     */
    public function getDomian(string $name, ?string $region = null)
    {
        return [];
    }
}
