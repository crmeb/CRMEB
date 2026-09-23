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

namespace app\services\driver\upload;

use crmeb\exceptions\UploadException;
use crmeb\services\CacheService;
use crmeb\services\upload\Upload;
use crmeb\utils\DownloadImage;
use think\facade\Config;

/**
 * 上传服务类
 *
 * 统一管理文件上传逻辑，支持多种存储驱动（本地、OSS、COS、七牛等）
 * 所有配置从 eb_driver 表动态读取，不硬编码扩展内容
 *
 * 使用方式：
 * <code>
 * // 使用默认启用的驱动上传
 * $upload = UploadService::init();
 * $result = $upload->to('image')->validate()->move();
 *
 * // 指定驱动上传
 * $upload = UploadService::init('local');
 * $result = $upload->to('image')->validate()->move();
 *
 * // 根据文件路径自动识别驱动
 * $upload = UploadService::getOssInit($filePath);
 * </code>
 *
 * Class UploadService
 * @package app\services\driver\upload
 */
class UploadService
{

    /**
     * 根据 type 或 driver_key 初始化上传服务
     *
     * 当不传参数时，自动从 eb_driver 表获取当前启用的驱动
     *
     * @param null|int|string $typeOrKey 支持 type（int）或 driver_key（string）
     * @return Upload|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function init($typeOrKey = null)
    {
        if (is_null($typeOrKey)) {
            $driverKey = self::getEnabledDriverKey();
        } else {
            $driverKey = self::resolveDriverKey($typeOrKey);
        }

        return self::createUpload($driverKey);
    }

    /**
     * 创建独立上传实例，避免配置、校验规则与文件状态跨操作残留
     * @param string $driverKey
     * @param array|null $storage 指定文件所属空间，不使用当前启用空间替代
     * @return Upload
     */
    protected static function createUpload(string $driverKey, ?array $storage = null): Upload
    {
        $config = self::loadDriverConfig($driverKey, $storage);
        return new Upload($driverKey, array_merge($config, self::getCommonConfig($driverKey)));
    }

    /**
     * 获取当前启用的驱动 key
     *
     * 从数据库 eb_driver 表中读取 status=1 的驱动，按 sort 降序排列
     * 如果没有启用的驱动，默认返回 'local'
     *
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected static function getEnabledDriverKey(): string
    {
        /** @var UploadDriverServices $driverServices */
        $driverServices = app()->make(UploadDriverServices::class);
        $enabledDrivers = $driverServices->getEnabledDrivers();

        if (!empty($enabledDrivers)) {
            return $enabledDrivers[0]['driver_key'];
        }

        return 'local';
    }

    /**
     * 获取当前启用的驱动类型值（image_type）
     *
     * 从 eb_driver 表获取当前启用的驱动，返回对应的 type 值
     * 用于写入素材库的 image_type 字段，统一替换旧的 sys_config('upload_type')
     *
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getEnabledImageType(): int
    {
        /** @var UploadDriverServices $driverServices */
        $driverServices = app()->make(UploadDriverServices::class);
        $enabledDrivers = $driverServices->getEnabledDrivers();
        $driverKey = !empty($enabledDrivers) ? $enabledDrivers[0]['driver_key'] : 'local';
        return $driverServices->getTypeByDriverKey($driverKey);
    }

    /**
     * 获取上传公共配置（缩略图和水印）
     *
     * 从 eb_driver 表的 thumb_watermark_config 字段获取配置
     *
     * @param string $driverKey 驱动标识
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getCommonConfig(string $driverKey): array
    {
        /** @var UploadDriverServices $driverServices */
        $driverServices = app()->make(UploadDriverServices::class);
        $config = $driverServices->getCommonConfig($driverKey);

        return [
            'thumb' => [
                'image_thumb_status' => $config['image_thumb_status'] ?? 1,
                'thumb_big_height' => $config['thumb_big_height'] ?? 800,
                'thumb_big_width' => $config['thumb_big_width'] ?? 800,
                'thumb_mid_height' => $config['thumb_mid_height'] ?? 300,
                'thumb_mid_width' => $config['thumb_mid_width'] ?? 300,
                'thumb_small_height' => $config['thumb_small_height'] ?? 150,
                'thumb_small_width' => $config['thumb_small_width'] ?? 150,
            ],
            'water' => [
                'image_watermark_status' => $config['image_watermark_status'] ?? 1,
                'watermark_type' => $config['watermark_type'] ?? 2,
                'watermark_image' => $config['watermark_image'] ?? '',
                'watermark_opacity' => $config['watermark_opacity'] ?? '',
                'watermark_position' => $config['watermark_position'] ?? 1,
                'watermark_rotate' => $config['watermark_rotate'] ?? '',
                'watermark_text' => $config['watermark_text'] ?? 'CRMEB',
                'watermark_text_angle' => $config['watermark_text_angle'] ?? '7',
                'watermark_text_color' => $config['watermark_text_color'] ?? '#ffffff',
                'watermark_text_size' => $config['watermark_text_size'] ?? '28',
                'watermark_x' => $config['watermark_x'] ?? '',
                'watermark_y' => $config['watermark_y'] ?? '',
            ],
        ];
    }

    /**
     * 解析 driver_key
     *
     * 如果传入的是字符串（非数字），直接返回
     * 如果传入的是数字（type），从数据库映射到 driver_key
     *
     * @param int|string $typeOrKey 类型或驱动标识
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected static function resolveDriverKey($typeOrKey)
    {
        if (is_string($typeOrKey) && !is_numeric($typeOrKey)) {
            return $typeOrKey;
        }

        /** @var UploadDriverServices $driverServices */
        $driverServices = app()->make(UploadDriverServices::class);
        return $driverServices->getDriverKeyByType((int)$typeOrKey);
    }

    /**
     * 加载驱动配置
     *
     * 从数据库 eb_driver 表读取配置字段定义，使用 config_fields 中的 default 值
     * 同时从 system_storage 表读取存储空间配置
     *
     * @param string $driverKey 驱动标识
     * @param array|null $storage
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected static function loadDriverConfig(string $driverKey, ?array $storage = null)
    {
        $config = [];

        /** @var UploadDriverServices $driverServices */
        $driverServices = app()->make(UploadDriverServices::class);

        $configFields = $driverServices->getConfigFields($driverKey);
        foreach ($configFields as $field) {
            $fieldName = $field['field'];
            $config[$fieldName] = $field['value'] ?? '';
        }

        // 映射带前缀的字段名：plugin.json 中可能定义了 {driverKey}_accessKey 等，
        // 但驱动 initialize() 期望 accessKey（无前缀）
        $prefix = $driverKey . '_';
        foreach ($config as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $unprefixed = substr($key, strlen($prefix));
                if (!isset($config[$unprefixed])) {
                    $config[$unprefixed] = $value;
                }
            }
        }

        /** @var UploadStorageServices $storageServices */
        $storageServices = app()->make(UploadStorageServices::class);
        $res = $storage ?? $storageServices->getConfigByDriverKey($driverKey);
        $config['uploadUrl'] = $res['domain'] ?? '';
        $config['storageName'] = $res['name'] ?? '';
        $config['storageRegion'] = $res['region'] ?? '';
        $config['cdn'] = $res['cdn'] ?? '';

        return $config;
    }

    /**
     * 根据文件路径判断所属渠道并初始化上传服务
     *
     * 用于处理已经上传的文件，根据文件URL自动识别存储驱动
     *
     * @param string $filePath 文件路径（支持本地路径和远程URL）
     * @param bool $is_remote_down 远程图片是否下载到本地处理
     * @return Upload
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getOssInit(string $filePath, bool $is_remote_down = false)
    {
        $fileArr = self::parseFileUrl($filePath);
        if (!isset($fileArr['host'])) {
            return self::init('local')->setFilePath($filePath);
        }
        /** @var UploadStorageServices $storageServices */
        $storageServices = app()->make(UploadStorageServices::class);
        $storageArr = CacheService::remember('storage_list', function () use ($storageServices) {
            return $storageServices->selectList([], 'name,region,domain,cdn,driver_key')->toArray();
        }, 300);
        if (!is_array($storageArr)) {
            $storageArr = $storageServices->selectList([], 'name,region,domain,cdn,driver_key')->toArray();
        }

        // 优先匹配最具体的路径，避免同域不同空间被根路径记录抢先匹配。
        $matched = null;
        $matchedLength = -1;
        $ambiguous = false;
        foreach ($storageArr as $item) {
            foreach (['domain', 'cdn'] as $field) {
                $base = $item[$field] ?? '';
                if ($base && self::matchesStorageUrl($fileArr, $base)) {
                    $length = strlen(rtrim(parse_url($base, PHP_URL_PATH) ?: '', '/'));
                    if ($length > $matchedLength && !empty($item['driver_key'])) {
                        $matched = $item;
                        $matchedLength = $length;
                        $ambiguous = false;
                    } elseif ($length === $matchedLength && !empty($item['driver_key']) && $matched
                        && [$matched['driver_key'], $matched['name'] ?? '', $matched['region'] ?? '']
                            !== [$item['driver_key'], $item['name'] ?? '', $item['region'] ?? '']) {
                        $ambiguous = true;
                    }
                }
            }
        }
        if ($ambiguous) throw new UploadException('文件地址匹配多个存储空间，无法安全操作');
        if ($matched) {
            return self::createUpload($matched['driver_key'], $matched)->setFilePath($filePath);
        }

        $localUrl = (string)Config::get('filesystem.disks.public.url', '/uploads');
        $siteUrl = rtrim((string)sys_config('site_url'), '/');
        $localUrl = str_starts_with($localUrl, '/') && $siteUrl ? $siteUrl . $localUrl : $localUrl;
        if ($localUrl && self::matchesStorageUrl($fileArr, $localUrl)) {
            return self::init('local')->setFilePath($filePath);
        }
        if ($is_remote_down) {
            /** @var DownloadImage $down */
            $down = app()->make(DownloadImage::class);
            $data = $down->path('thumb_water')->downloadImage($filePath);
            if (empty($data['path'])) {
                throw new UploadException('远程图片下载失败');
            }
            return self::init('local')->setFilePath($data['path']);
        }
        throw new UploadException('文件不属于已配置的存储空间');
    }

    /**
     * 解析路径并拒绝非 HTTP 协议及携带凭据的 URL
     * @param string $path
     * @return array
     */
    protected static function parseFileUrl(string $path): array
    {
        $url = str_starts_with($path, '//') ? 'https:' . $path : $path;
        $parts = parse_url($url);
        if ($parts === false || preg_match('/[\x00-\x20\x7f]/', $path)
            || isset($parts['user']) || isset($parts['pass'])
            || (isset($parts['scheme']) && (!in_array(strtolower($parts['scheme']), ['http', 'https'], true) || empty($parts['host'])))) {
            throw new UploadException('文件地址格式错误');
        }
        return $parts;
    }

    /**
     * 按协议、主机、端口和路径边界精确匹配存储地址
     * @param array $file
     * @param string $base
     * @return bool
     */
    protected static function matchesStorageUrl(array $file, string $base): bool
    {
        $storage = self::parseFileUrl($base);
        if (empty($storage['host']) || !isset($file['host'])) {
            return false;
        }
        $fileScheme = strtolower($file['scheme'] ?? 'https');
        $storageScheme = strtolower($storage['scheme'] ?? 'https');
        if ($fileScheme !== $storageScheme || strtolower($file['host']) !== strtolower($storage['host'])
            || ($file['port'] ?? ($fileScheme === 'https' ? 443 : 80)) !== ($storage['port'] ?? ($storageScheme === 'https' ? 443 : 80))) {
            return false;
        }
        $prefix = rtrim($storage['path'] ?? '', '/');
        $path = $file['path'] ?? '/';
        return $prefix === '' || $path === $prefix || str_starts_with($path, $prefix . '/');
    }
}
