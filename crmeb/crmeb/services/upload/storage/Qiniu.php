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
use Qiniu\Auth;
use Qiniu\Http\Client;
use Qiniu\Http\Error;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Qiniu\Config;


/**
 * TODO 七牛云上传
 * Class Qiniu
 */
class Qiniu extends BaseUpload
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
     * @var object
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
        '1' => 'NorthWest',//：左上
        '2' => 'North',//：中上
        '3' => 'NorthEast',//：右上
        '4' => 'West',//：左中
        '5' => 'Center',//：中部
        '6' => 'East',//：右中
        '7' => 'SouthWest',//：左下
        '8' => 'South',//：中下
        '9' => 'SouthEast',//：右下
    ];

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->accessKey = $config['accessKey'] ?? null;
        $this->secretKey = $config['secretKey'] ?? null;
        $this->uploadUrl = $this->checkUploadUrl($config['uploadUrl'] ?? '');
        $this->storageName = $config['storageName'] ?? null;
        $this->storageRegion = $config['storageRegion'] ?? null;
    }

    /**
     * 实例化七牛云
     * @return object|Auth
     */
    protected function app()
    {
        if (!$this->accessKey || !$this->secretKey) {
            throw new UploadException(400721);
        }
        $this->handle = new Auth($this->accessKey, $this->secretKey);
        return $this->handle;
    }

    /**
     * 上传文件
     * @param string $file
     * @param bool $realName
     * @return array|bool|mixed|\StdClass|string
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
        $token = $this->app()->uploadToken($this->storageName);
        try {
            $uploadMgr = new UploadManager();
            [$result, $error] = $uploadMgr->putFile($token, $key, $fileHandle->getRealPath());
            if ($error !== null) {
                return $this->setError($error->message());
            }
            $this->fileInfo->uploadInfo = $result;
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
     * @return array|bool|mixed|\StdClass
     */
    public function stream($fileContent, string $key = null)
    {
        if (!$key) {
            $key = $this->saveFileName();
        }
        $key = $this->getUploadPath($key);
        $token = $this->app()->uploadToken($this->storageName, $key);
        try {
            $uploadMgr = new UploadManager();
            [$result, $error] = $uploadMgr->put($token, $key, $fileContent);
            if ($error !== null) {
                return $this->setError($error->message());
            }
            $this->fileInfo->uploadInfo = $result;
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
                        $this->fileInfo->$key = $filePath . '?imageView2/2/w/' . $config[$width] . '/h/' . $config[$height];
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
            if (strpos($filePath, '?') === false) {
                $filePath .= '?watermark';
            } else {
                $filePath .= '&watermark';
            }
            switch ($waterConfig['watermark_type']) {
                case 1://图片
                    if (!$waterConfig['watermark_image']) {
                        throw new AdminException(400722);
                    }
                    $waterPath = $filePath .= '/1/image/' . base64_encode($waterConfig['watermark_image']) . '/gravity/' . ($this->position[$waterConfig['watermark_position']] ?? 'SouthEest') . '/dissolve/' . $waterConfig['watermark_opacity'] . '/dx/' . $waterConfig['watermark_x'] . '/dy/' . $waterConfig['watermark_y'];
                    break;
                case 2://文字
                    if (!$waterConfig['watermark_text']) {
                        throw new AdminException(400723);
                    }
                    $waterPath = $filePath .= '/2/text/' . base64_encode($waterConfig['watermark_text']) . '/fill/' . base64_encode($waterConfig['watermark_text_color']) . '/fontsize/' . $waterConfig['watermark_text_size'] . '/gravity/' . ($this->position[$waterConfig['watermark_position']] ?? 'SouthEest') . '/dx/' . $waterConfig['watermark_x'] . '/dy/' . $waterConfig['watermark_y'];
                    break;
            }
        }
        return $waterPath;
    }

    /**
     * 获取上传配置信息
     * @return array
     */
    public function getSystem()
    {
        $token = $this->app()->uploadToken($this->storageName);
        $domain = $this->uploadUrl;
        $key = $this->saveFileName();
        return compact('token', 'domain', 'key');
    }

    /**
     * TODO 删除资源
     * @param $key
     * @param $bucket
     * @return mixed
     */
    public function delete(string $key)
    {
        $bucketManager = new BucketManager($this->app(), new Config());
        return $bucketManager->delete($this->storageName, $key);
    }

    /**
     * 获取七牛云上传密钥
     * @return mixed|string
     */
    public function getTempKeys()
    {
        $token = $this->app()->uploadToken($this->storageName);
        $domain = $this->uploadUrl;
        $key = $this->saveFileName(NULL, 'mp4');
        $type = 'QINIU';
        return compact('token', 'domain', 'key', 'type');
    }

    /**
     * 获取当前所有桶列表
     * @param string|null $region
     * @param bool $line
     * @param bool $shared
     * @return bool|mixed
     */
    public function listbuckets(string $region = null, bool $line = false, bool $shared = false)
    {
        $bucket = new BucketManager($this->app());
        [$response, $error] = $bucket->listbuckets($region, $line ? 'true' : 'false', $shared ? 'true' : 'false');
        if ($error !== null) {
            return $this->setError($error->message());
        }
        return $response;
    }

    /**
     * @param string $name
     * @param string $region
     * @return bool|mixed
     */
    public function createBucket(string $name, string $region = 'z0')
    {
        $regionData = $this->getRegion();
        if (!in_array($region, array_column($regionData, 'value'))) {
            return $this->setError('七牛云:无效的区域');
        }
        $url = 'https://' . Config::UC_HOST . '/mkbucketv3/' . $name . '/region/' . $region;
        $body = null;
        $headers = $this->app()->authorizationV2($url, 'POST', $body, 'application/json');
        $headers["Content-Type"] = 'application/json';
        $ret = Client::post($url, $body, $headers);
        if (!$ret->ok()) {
            $error = new Error($url, $ret);
            if ('bucket exists' === $error->message()) {
                return $this->setError('七牛云：云空间已存在');
            }
            return $this->setError('七牛云：' . $error->message());
        }
        return ($ret->body === null) ? array() : $ret->json();
    }

    /**
     * 获取区域
     * @return mixed|\string[][]
     */
    public function getRegion()
    {
        return [
            [
                'value' => 'z0',
                'label' => '华东'
            ],
            [
                'value' => 'z1',
                'label' => '华北'
            ],
            [
                'value' => 'z2',
                'label' => '华南'
            ],
            [
                'value' => 'na0',
                'label' => '北美'
            ],
            [
                'value' => 'as0',
                'label' => '东南亚'
            ],
            [
                'value' => 'cn-east-2',
                'label' => '华东-浙江2'
            ],
        ];
    }

    /**
     * 删除空间
     * @param string $name
     * @return bool|mixed
     */
    public function deleteBucket(string $name)
    {
        $bucket = new BucketManager($this->app());
        [$response, $error] = $bucket->deleteBucket($name);
        if ($error !== null) {
            return $this->setError($error->message());
        }
        return $response;
    }

    /**
     * 获取七牛域名
     * @param string $name
     * @return array|bool|mixed|null
     */
    public function getDomian(string $name)
    {
        $url = 'https://' . Config::UC_HOST . '/v2/domains?tbl=' . $name;
        $body = null;
        $headers = $this->app()->authorizationV2($url, 'POST', $body, 'application/x-www-form-urlencoded');
        $headers["Content-Type"] = 'application/x-www-form-urlencoded';
        $ret = Client::post($url, $body, $headers);
        if (!$ret->ok()) {
            $error = new Error($url, $ret);
            return $this->setError('七牛云：' . $error->message());
        }
        return ($ret->body === null) ? array() : $ret->json();
    }

    public function getDomianInfo(string $host)
    {
        $url = 'https://' . Config::API_HOST . '/domain/' . $host;
        $headers = $this->app()->authorization($url, null, 'application/x-www-form-urlencoded');
        $headers["Content-Type"] = 'application/x-www-form-urlencoded';
        $ret = Client::get($url, $headers);
        if (!$ret->ok()) {
            $error = new Error($url, $ret);
            return $this->setError('七牛云：' . $error->message());
        }
        return ($ret->body === null) ? array() : $ret->json();
    }

    /**
     *
     * @param string $name
     * @param string $domain
     * @param string|null $region
     * @return array|bool|mixed|null
     */
    public function bindDomian(string $name, string $domain, string $region = null)
    {
        $parseDomin = parse_url($domain);
        $url = 'https://' . Config::API_HOST . '/domain/' . $parseDomin['host'];
        $body = [
            'type' => 'normal',
            'platform' => 'web',
            'geocover' => 'china',
            'source' => [
                'sourceType' => 'qiniuBucket',
                'sourceQiniuBucket' => $name,
                'TestURLPath' => 'qiniu_do_not_delete.gif',
            ],
            'protocol' => $parseDomin['scheme'],
            'cache' => [
                'cacheControls' => [
                    [
                        'time' => 1,
                        'timeunit' => 4,
                        'type' => 'all',
                        'rule' => '*'
                    ]
                ],
                'ignoreParam' => false
            ]
        ];
        $bodyJson = json_encode($body);
        $headers = $this->app()->authorization($url, $bodyJson, 'application/json');
        $headers["Content-Type"] = 'application/json';
        $ret = Client::post($url, $bodyJson, $headers);
        if (!$ret->ok()) {
            $error = new Error($url, $ret);
            return $this->setError('七牛云：' . $error->message());
        }
        return ($ret->body === null) ? array() : $ret->json();
    }

    /**
     * 跨域
     * @param string $name
     * @param string $region
     * @return bool
     */
    public function setBucketCors(string $name, string $region)
    {
        return true;
    }
}
