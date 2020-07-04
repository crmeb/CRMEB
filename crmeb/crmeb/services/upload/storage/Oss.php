<?php

namespace crmeb\services\upload\storage;

use crmeb\basic\BaseUpload;
use crmeb\exceptions\UploadException;
use Guzzle\Http\EntityBody;
use OSS\Core\OssException;
use OSS\OssClient;
use think\exception\ValidateException;


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
     * @var \OSS\OssClient
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
            throw new UploadException('Please configure accessKey and secretKey');
        }
        $this->handle = new OssClient($this->accessKey, $this->secretKey, $this->storageRegion);
        if (!$this->handle->doesBucketExist($this->storageName)) {
            $this->handle->createBucket($this->storageName, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
        }
        return $this->handle;
    }

    /**
     * 上传文件
     * @param string $file
     * @return array|bool|mixed|\StdClass
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
        try {
            $uploadInfo = $this->app()->uploadFile($this->storageName, $key, $fileHandle->getRealPath());
            if (!isset($uploadInfo['info']['url'])) {
                return $this->setError('Upload failure');
            }
            $this->fileInfo->uploadInfo = $uploadInfo;
            $this->fileInfo->filePath = $this->uploadUrl .'/'. $key;
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
     * @return bool|mixed
     */
    public function stream(string $fileContent, string $key = null)
    {
        try {
            if (!$key) {
                $key = $this->saveFileName();
            }
            $fileContent = (string)EntityBody::factory($fileContent);
            $uploadInfo = $this->app()->putObject($this->storageName, $key, $fileContent);
            if (!isset($uploadInfo['info']['url'])) {
                return $this->setError('Upload failure');
            }
            $this->fileInfo->uploadInfo = $uploadInfo;
            $this->fileInfo->filePath = $this->uploadUrl .'/'. $key;
            $this->fileInfo->fileName = $key;
            return $this->fileInfo;
        } catch (UploadException $e) {
            return $this->setError($e->getMessage());
        }
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

}