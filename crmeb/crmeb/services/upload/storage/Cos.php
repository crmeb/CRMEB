<?php

namespace crmeb\services\upload\storage;

use crmeb\basic\BaseUpload;
use crmeb\exceptions\UploadException;
use Guzzle\Http\EntityBody;
use Qcloud\Cos\Client;
use think\exception\ValidateException;

/**
 * 腾讯云COS文件上传
 * Class COS
 * @package crmeb\services\upload\storage
 */
class Cos extends BaseUpload
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
     * @var Client
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
     * 实例化cos
     * @return Client
     */
    protected function app()
    {
        if (!$this->accessKey || !$this->secretKey) {
            throw new UploadException('Please configure accessKey and secretKey');
        }
        $this->handle = new Client(['region' => $this->storageRegion, 'credentials' => [
            'secretId' => $this->accessKey, 'secretKey' => $this->secretKey
        ]]);
        return $this->handle;
    }

    /**
     * 上传文件
     * @param string|null $file
     * @param bool $isStream 是否为流上传
     * @param string|null $fileContent 流内容
     * @return array|bool|\StdClass
     */
    protected function upload(string $file = null, bool $isStream = false, string $fileContent = null)
    {
        if (!$isStream) {
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
            $body = fopen($fileHandle->getRealPath(), 'rb');
        } else {
            $key = $file;
            $body = $fileContent;
        }
        try {
            $this->fileInfo->uploadInfo = $this->app()->putObject([
                'Bucket' => $this->storageName,
                'Key' => $key,
                'Body' => $body
            ]);
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
        if (!$key) {
            $key = $this->saveFileName();
        }
        return $this->upload($key, true, $fileContent);
    }

    /**
     * 文件上传
     * @param string $file
     * @return array|bool|mixed|\StdClass
     */
    public function move(string $file = 'file')
    {
        return $this->upload($file);
    }

    /**
     * TODO 删除资源
     * @param $key
     * @return mixed
     */
    public function delete(string $filePath)
    {
        try {
            return $this->app()->deleteObject(['Bucket' => $this->storageName, 'Key' => $filePath]);
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }
    }
}