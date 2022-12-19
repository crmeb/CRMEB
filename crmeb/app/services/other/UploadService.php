<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\other;

use app\services\system\config\SystemStorageServices;
use crmeb\exceptions\UploadException;
use crmeb\services\SystemConfigService;
use crmeb\services\upload\Upload;
use crmeb\utils\DownloadImage;

/**
 * Class UploadService
 * @package crmeb\services
 */
class UploadService
{

    /**
     * @var array
     */
    protected static $upload = [];

    /**
     * @param null $type
     * @return Upload|mixed
     */
    public static function init($type = null)
    {
        if (is_null($type)) {
            $type = (int)sys_config('upload_type', 1);
        }
        if (isset(self::$upload['upload_' . $type])) {
            return self::$upload['upload_' . $type];
        }
        $type = (int)$type;
        $config = [];
        switch ($type) {
            case 2://七牛
                $config = [
                    'accessKey' => sys_config('qiniu_accessKey'),
                    'secretKey' => sys_config('qiniu_secretKey'),
                ];
                break;
            case 3:// oss 阿里云
                $config = [
                    'accessKey' => sys_config('accessKey'),
                    'secretKey' => sys_config('secretKey'),
                ];
                break;
            case 4:// cos 腾讯云
                $config = [
                    'accessKey' => sys_config('tengxun_accessKey'),
                    'secretKey' => sys_config('tengxun_secretKey'),
                    'appid' => sys_config('tengxun_appid'),
                ];
                break;
            case 1:
                break;
            default:
                throw new UploadException(400733);
        }

        //除了本地存储其他都去获取配置信息
        if (1 !== $type) {
            /** @var SystemStorageServices $make */
            $make = app()->make(SystemStorageServices::class);
            $res = $make->getConfig($type);
            $config['uploadUrl'] = $res['domain'];
            $config['storageName'] = $res['name'];
            $config['storageRegion'] = $res['region'];
        }

        $thumb = SystemConfigService::more(['thumb_big_height', 'thumb_big_width', 'thumb_mid_height', 'thumb_mid_width', 'thumb_small_height', 'thumb_small_width',]);
        $water = SystemConfigService::more([
            'image_watermark_status',
            'watermark_type',
            'watermark_image',
            'watermark_opacity',
            'watermark_position',
            'watermark_rotate',
            'watermark_text',
            'watermark_text_angle',
            'watermark_text_color',
            'watermark_text_size',
            'watermark_x',
            'watermark_y']);
        $config = array_merge($config, ['thumb' => $thumb], ['water' => $water]);
        return self::$upload['upload_' . $type] = new Upload($type, $config);
    }

    /**
     * 生辰缩略图水印实例化
     * @param string $filePath
     * @param bool $is_remote_down
     * @return Upload
     */
    public static function getOssInit(string $filePath, bool $is_remote_down = false)
    {
        //本地
        $uploadUrl = sys_config('site_url');
        if ($uploadUrl && strpos($filePath, $uploadUrl) !== false) {
            $filePath = explode($uploadUrl, $filePath)[1] ?? '';
            return self::init(1)->setFilepath($filePath);
        }
        $fileArr = parse_url($filePath);
        $fileHost = $fileArr['scheme'] . '://' . $fileArr['host'];
        /** @var SystemStorageServices $storageServices */
        $storageServices = app()->make(SystemStorageServices::class);
        $storageArr = $storageServices->selectList([])->toArray();
        foreach ($storageArr as $item) {
            if ($fileHost == $item['domain']) {
                return self::init($item['type'])->setFilepath($filePath);
            }
        }
        //远程图片 下载到本地处理
        if ($is_remote_down) {
            try {
                /** @var DownloadImage $down */
                $down = app()->make(DownloadImage::class);
                $data = $down->path('thumb_water')->downloadImage($filePath);
                $filePath = $data['path'] ?? '';
            } catch (\Throwable $e) {
                //下载失败 传入原地址
            }
        }
        return self::init(1)->setFilepath($filePath);
    }
}
