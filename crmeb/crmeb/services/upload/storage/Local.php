<?php

namespace crmeb\services\upload\storage;

use crmeb\basic\BaseUpload;
use crmeb\exceptions\UploadException;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Filesystem;
use think\File;

/**
 * 本地上传
 * Class Local
 * @package crmeb\services\upload\storage
 */
class Local extends BaseUpload
{

    /**
     * 默认存放路径
     * @var string
     */
    protected $defaultPath;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->defaultPath = Config::get('filesystem.disks.' . Config::get('filesystem.default') . '.url');
    }

    protected function app()
    {
        // TODO: Implement app() method.
    }

    /**
     * 生成上传文件目录
     * @param $path
     * @param null $root
     * @return string
     */
    protected function uploadDir($path, $root = null)
    {
        if ($root === null) $root = app()->getRootPath() . 'public' . DS;
        return str_replace('\\', '/', $root . 'uploads' . DS . $path);
    }

    /**
     * 检查上传目录不存在则生成
     * @param $dir
     * @return bool
     */
    protected function validDir($dir)
    {
        return is_dir($dir) == true || mkdir($dir, 0777, true) == true;
    }

    /**
     * 文件上传
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
        $fileName = Filesystem::putFile($this->path, $fileHandle);
        if (!$fileName)
            return $this->setError('Upload failure');
        $filePath = Filesystem::path($fileName);
        $this->fileInfo->uploadInfo = new File($filePath);
        $this->fileInfo->fileName = $this->fileInfo->uploadInfo->getFilename();
        $this->fileInfo->filePath = $this->defaultPath . '/' . str_replace('\\', '/', $fileName);
        return $this->fileInfo;
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
        $dir = $this->uploadDir($this->path);
        if (!$this->validDir($dir)) {
            return $this->setError('Failed to generate upload directory, please check the permission!');
        }
        $fileName = $dir . '/' . $key;
        file_put_contents($fileName, $fileContent);
        $this->fileInfo->uploadInfo = new File($fileName);
        $this->fileInfo->fileName = $key;
        $this->fileInfo->filePath = '/uploads/' . $this->path . '/' . $key;
        return $this->fileInfo;
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return bool|mixed
     */
    public function delete(string $filePath)
    {
        if (file_exists($filePath)) {
            try {
                unlink($filePath);
                return true;
            } catch (UploadException $e) {
                return $this->setError($e->getMessage());
            }
        }
        return false;
    }
}