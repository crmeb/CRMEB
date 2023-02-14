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
namespace crmeb\services\upload\storage;

use crmeb\services\upload\BaseUpload;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\UploadException;
use Guzzle\Http\EntityBody;
use OSS\Core\OssException;
use OSS\Model\CorsConfig;
use OSS\Model\CorsRule;
use OSS\OssClient;


/**
 * 阿里云OSS上传
 * Class OSS
 */
class Oss extends BaseUpload
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
     * COS使用  所属地域
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
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config)
    {
        parent::initialize($config);
        $this->accessKey = $config['accessKey'] ?? null;
        $this->secretKey = $config['secretKey'] ?? null;
        $this->uploadUrl = $this->checkUploadUrl($config['uploadUrl'] ?? '');
        $this->storageName = $config['storageName'] ?? null;
        $this->storageRegion = $config['storageRegion'] ?? null;
    }

    /**
     * 初始化oss
     * @return OssClient
     * @throws OssException
     */
    protected function app()
    {
        if (!$this->accessKey || !$this->secretKey) {
            throw new UploadException(400721);
        }
        $this->handle = new OssClient($this->accessKey, $this->secretKey, $this->storageRegion);
        //不再自动创建
//        if (!$this->handle->doesBucketExist($this->storageName)) {
//            $this->handle->createBucket($this->storageName, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
//        }
        return $this->handle;
    }

    /**
     * 上传文件
     * @param string $file
     * @param bool $realName
     * @return array|bool|mixed|\StdClass
     */
    public function move(string $file = 'file', $realName = false)
    {
        $fileHandle = app()->request->file($file);
        if (!$fileHandle) {
            return $this->setError('Upload file does not exist');
        }
        if ($this->validate) {
            if (!in_array(pathinfo($fileHandle->getOriginalName(), PATHINFO_EXTENSION), $this->validate['fileExt'])) {
                return $this->setError('Upload fileExt error');
            }
            if (filesize($fileHandle) > $this->validate['filesize']) {
                return $this->setError('Upload filesize error');
            }
            if (!in_array($fileHandle->getOriginalMime(), $this->validate['fileMime'])) {
                return $this->setError('Upload fileMine error');
            }
        }
        $key = $this->saveFileName($fileHandle->getRealPath(), $fileHandle->getOriginalExtension());
        $key = $this->getUploadPath($key);
        try {
            $uploadInfo = $this->app()->uploadFile($this->storageName, $key, $fileHandle->getRealPath());
            if (!isset($uploadInfo['info']['url'])) {
                return $this->setError('Upload failure');
            }
            $this->fileInfo->uploadInfo = $uploadInfo;
            $this->fileInfo->realName = $fileHandle->getOriginalName();
            $this->fileInfo->filePath = $this->uploadUrl . '/' . $key;
            $this->fileInfo->fileName = $key;
            $this->fileInfo->filePathWater = $this->water($this->fileInfo->filePath);
            $this->authThumb && $this->thumb($this->fileInfo->filePath);
            return $this->fileInfo;
        } catch (UploadException $e) {
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
    public function stream($fileContent, string $key = null)
    {
        try {
            if (!$key) {
                $key = $this->saveFileName();
            }
            $key = $this->getUploadPath($key);
            $fileContent = (string)EntityBody::factory($fileContent);
            $uploadInfo = $this->app()->putObject($this->storageName, $key, $fileContent);
            if (!isset($uploadInfo['info']['url'])) {
                return $this->setError('Upload failure');
            }
            $this->fileInfo->uploadInfo = $uploadInfo;
            $this->fileInfo->realName = $key;
            $this->fileInfo->filePath = $this->uploadUrl . '/' . $key;
            $this->fileInfo->fileName = $key;
            $this->fileInfo->filePathWater = $this->water($this->fileInfo->filePath);
            $this->thumb($this->fileInfo->filePath);
            return $this->fileInfo;
        } catch (UploadException $e) {
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
        $this->fileInfo->filePathBig = $this->fileInfo->filePathMid = $this->fileInfo->filePathSmall = $this->fileInfo->filePathWater = $filePath;
        if ($filePath) {
            $config = $this->thumbConfig;
            foreach ($this->thumb as $v) {
                if ($type == 'all' || $type == $v) {
                    $height = 'thumb_' . $v . '_height';
                    $width = 'thumb_' . $v . '_width';
                    $key = 'filePath' . ucfirst($v);
                    if (sys_config('image_thumbnail_status', 1) && isset($config[$height]) && isset($config[$width]) && $config[$height] && $config[$width]) {
                        $this->fileInfo->$key = $filePath . '?x-oss-process=image/resize,h_' . $config[$height] . ',w_' . $config[$width];
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
        if ($waterConfig['image_watermark_status'] && $filePath) {
            if (strpos($filePath, '?x-oss-process') === false) {
                $filePath .= '?x-oss-process=image';
            }
            switch ($waterConfig['watermark_type']) {
                case 1://图片
                    if (!$waterConfig['watermark_image']) {
                        throw new AdminException(400722);
                    }
                    $waterPath = $filePath .= '/watermark,image_' . base64_encode($waterConfig['watermark_image']) . ',t_' . $waterConfig['watermark_opacity'] . ',g_' . ($this->position[$waterConfig['watermark_position']] ?? 'nw') . ',x_' . $waterConfig['watermark_x'] . ',y_' . $waterConfig['watermark_y'];
                    break;
                case 2://文字
                    if (!$waterConfig['watermark_text']) {
                        throw new AdminException(400723);
                    }
                    $waterConfig['watermark_text_color'] = str_replace('#', '', $waterConfig['watermark_text_color']);
                    $waterPath = $filePath .= '/watermark,text_' . base64_encode($waterConfig['watermark_text']) . ',color_' . $waterConfig['watermark_text_color'] . ',size_' . $waterConfig['watermark_text_size'] . ',g_' . ($this->position[$waterConfig['watermark_position']] ?? 'nw') . ',x_' . $waterConfig['watermark_x'] . ',y_' . $waterConfig['watermark_y'];
                    break;
            }
        }
        return $waterPath;
    }

    /**
     * 删除资源
     * @param $key
     * @return mixed
     */
    public function delete(string $key)
    {
        try {
            return $this->app()->deleteObject($this->storageName, $key);
        } catch (OssException $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 获取OSS上传密钥
     * @return mixed|void
     */
    public function getTempKeys($callbackUrl = '', $dir = '')
    {
        // TODO: Implement getTempKeys() method.
        $base64CallbackBody = base64_encode(json_encode([
            'callbackUrl' => $callbackUrl,
            'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType' => "application/x-www-form-urlencoded"
        ]));

        $policy = json_encode([
            'expiration' => $this->gmtIso8601(time() + 30),
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
            'policy' => $base64Policy,
            'signature' => $signature,
            'expire' => time() + 30,
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
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
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
        $handle = new OssClient($this->accessKey, $this->secretKey, $region);
        $response = $handle->listBuckets();
        $data = $response->getBucketList();
        $list = [];
        foreach ($data as $item) {
            $list[] = [
                'location' => $item->getLocation(),
                'name' => $item->getName(),
                'createTime' => $item->getCreateDate()
            ];
        }
        return $list;
    }

    /**
     * @param string $name
     * @param string $region
     * @param string $acl
     * @return mixed|void
     */
    public function createBucket(string $name, string $region = '', string $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ)
    {
        $regionData = $this->getRegion();
        if (!in_array($region, array_column($regionData, 'value'))) {
            return $this->setError('OSS:无效的区域');
        }
        try {
            $handle = new OssClient($this->accessKey, $this->secretKey, $region);
            if ($handle->doesBucketExist($name)) {
                return $this->setError('OSS:空间已经存在，请更换空间名');
            }
            return $handle->createBucket($name, $acl);
        } catch (\Throwable $e) {
            if (strstr('The bucket you access does not belong to you', $e->getMessage())) {
                return $this->setError('OSS:空间已被使用，请更换空间名');
            }
            return $this->setError('OSS:' . $e->getMessage());
        }
    }

    /**
     * @param string $name
     * @param string $region
     * @return bool|mixed|null
     */
    public function deleteBucket(string $name, string $region = '')
    {
        try {
            $handle = new OssClient($this->accessKey, $this->secretKey, $region);
            return $handle->deleteBucket($name);
        } catch (\Throwable $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * @param string $name
     * @param string|null $region
     * @return array|bool
     */
    public function getDomian(string $name, string $region = null)
    {
        try {
            $handle = new OssClient($this->accessKey, $this->secretKey, $region);
            $res = $handle->getBucketCname($name);
            $data = $res->getCnames();
            return array_column($data, 'Domain');
        } catch (\Throwable $e) {
        }
        return [];
    }

    /**
     * 绑定域名
     * @param string $name
     * @param string $domain
     * @param string|null $region
     * @return bool|mixed
     */
    public function bindDomian(string $name, string $domain, string $region = null)
    {
        $parseDomin = parse_url($domain);
        try {
            $handle = new OssClient($this->accessKey, $this->secretKey, $region);
            $res = $handle->getBucketCname($name);
            $data = $res->getCnames();
            if (in_array($parseDomin['host'], array_column($data, 'Domain'))) {
                return true;
            }
            return $handle->addBucketCname($name, $parseDomin['host']);
        } catch (\Throwable $e) {
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
        try {
            $handle = new OssClient($this->accessKey, $this->secretKey, $region);
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
        } catch (\Throwable $e) {
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
