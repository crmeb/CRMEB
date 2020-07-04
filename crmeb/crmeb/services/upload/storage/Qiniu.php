<?php

namespace crmeb\services\upload\storage;

use crmeb\basic\BaseUpload;
use crmeb\exceptions\UploadException;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Qiniu\Config;
use think\exception\ValidateException;


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
            throw new UploadException('Please configure accessKey and secretKey');
        }
        $this->handle = new Auth($this->accessKey, $this->secretKey);
        return $this->handle;
    }

    /**
     * 上传文件
     * @param string $file
     * @return array|bool|mixed|\StdClass|string
     */
    public function move(string $file = 'file')
    {
        $fileHandle = app()->request->file($file);
        if (!$fileHandle) {
            return $this->setError('Upload file does not exist');
        }
        if ($this->validate) {
            try {
                validate([$file => $this->validate])->check([$file => $fileHandle]);
            } catch (ValidateException $e) {
                return $this->setError($e->getMessage());
            }
        }
        $key = $this->saveFileName($fileHandle->getRealPath(), $fileHandle->getOriginalExtension());
        $token = $this->app()->uploadToken($this->storageName);
        try {
            $uploadMgr = new UploadManager();
            [$result, $error] = $uploadMgr->putFile($token, $key, $fileHandle->getRealPath());
            if ($error !== null) {
                return $this->setError($error->message());
            }
            $this->fileInfo->uploadInfo = $result;
            $this->fileInfo->filePath = $this->uploadUrl . '/' . $key;
            $this->fileInfo->fileName = $key;
            return $this->fileInfo;
        } catch (UploadException $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 文件流上传
     * @param string $fileContent
     * @param string|null $key
     * @return array|bool|mixed|\StdClass
     */
    public function stream(string $fileContent, string $key = null)
    {
        $token = $this->app()->uploadToken($this->storageName);
        if (!$key) {
            $key = $this->saveFileName();
        }
        try {
            $uploadMgr = new UploadManager();
            [$result, $error] = $uploadMgr->put($token, $key, $fileContent);
            if ($error !== null) {
                return $this->setError($error->message());
            }
            $this->fileInfo->uploadInfo = $result;
            $this->fileInfo->filePath = $this->uploadUrl . '/' . $key;
            $this->fileInfo->fileName = $key;
            return $this->fileInfo;
        } catch (UploadException $e) {
            return $this->setError($e->getMessage());
        }
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
}