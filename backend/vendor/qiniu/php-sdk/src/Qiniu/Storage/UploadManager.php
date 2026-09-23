<?php
namespace Qiniu\Storage;

use Qiniu\Config;
use Qiniu\Http\HttpClient;
use Qiniu\Http\RequestOptions;
use Qiniu\Storage\ResumeUploader;
use Qiniu\Storage\FormUploader;

/**
 * 主要涉及了资源上传接口的实现
 *
 * @link http://developer.qiniu.com/docs/v6/api/reference/up/
 */
final class UploadManager
{
    private $config;
    /**
     * @var RequestOptions
     */
    private $reqOpt;

    /**
     * @param Config|null $config
     * @param RequestOptions|null $reqOpt
     */
    public function __construct(Config $config = null, RequestOptions $reqOpt = null)
    {
        if ($config === null) {
            $config = new Config();
        }
        $this->config = $config;

        if ($reqOpt === null) {
            $reqOpt = new RequestOptions();
        }

        $this->reqOpt = $reqOpt;
    }

    /**
     * 上传二进制流到七牛
     *
     * @param string $upToken 上传凭证
     * @param string $key 上传文件名
     * @param string $data 上传二进制流
     * @param array<string, string> $params 自定义变量，规格参考
     *                    http://developer.qiniu.com/docs/v6/api/overview/up/response/vars.html#xvar
     * @param string $mime 上传数据的mimeType
     * @param string $fname
     * @param RequestOptions $reqOpt
     * @return array    包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>"
     *                                              ]
     */
    public function put(
        $upToken,
        $key,
        $data,
        $params = null,
        $mime = 'application/octet-stream',
        $fname = "default_filename",
        $reqOpt = null
    ) {
        $reqOpt = $reqOpt === null ? $this->reqOpt : $reqOpt;

        $params = self::trimParams($params);
        return FormUploader::put(
            $upToken,
            $key,
            $data,
            $this->config,
            $params,
            $mime,
            $fname,
            $reqOpt
        );
    }


    /**
     * 上传文件到七牛
     *
     * @param string $upToken 上传凭证
     * @param string $key 上传文件名
     * @param string $filePath 上传文件的路径
     * @param array<string, mixed> $params 定义变量，规格参考
     *                                     http://developer.qiniu.com/docs/v6/api/overview/up/response/vars.html#xvar
     * @param boolean $mime 上传数据的mimeType
     * @param string $checkCrc 是否校验crc32
     * @param string $resumeRecordFile 断点续传文件路径 默认为null
     * @param string $version 分片上传版本 目前支持v1/v2版本 默认v1
     * @param int $partSize 分片上传v2字段 默认大小为4MB 分片大小范围为1 MB - 1 GB
     *
     * @return array<string, mixed> 包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>"
     *                                              ]
     * @throws \Exception
     */
    public function putFile(
        $upToken,
        $key,
        $filePath,
        $params = null,
        $mime = 'application/octet-stream',
        $checkCrc = false,
        $resumeRecordFile = null,
        $version = 'v1',
        $partSize = config::BLOCK_SIZE,
        $reqOpt = null
    ) {
        $reqOpt = $reqOpt === null ? $this->reqOpt : $reqOpt;

        $file = fopen($filePath, 'rb');
        if ($file === false) {
            throw new \Exception("file can not open", 1);
        }
        $params = self::trimParams($params);
        $stat = fstat($file);
        $size = $stat['size'];
        if ($size <= Config::BLOCK_SIZE) {
            $data = fread($file, $size);
            fclose($file);
            if ($data === false) {
                throw new \Exception("file can not read", 1);
            }
            return FormUploader::put(
                $upToken,
                $key,
                $data,
                $this->config,
                $params,
                $mime,
                basename($filePath),
                $reqOpt
            );
        }

        $up = new ResumeUploader(
            $upToken,
            $key,
            $file,
            $size,
            $params,
            $mime,
            $this->config,
            $resumeRecordFile,
            $version,
            $partSize,
            $reqOpt
        );
        $ret = $up->upload(basename($filePath));
        fclose($file);
        return $ret;
    }

    public static function trimParams($params)
    {
        if ($params === null) {
            return null;
        }
        $ret = array();
        foreach ($params as $k => $v) {
            $pos1 = strpos($k, 'x:');
            $pos2 = strpos($k, 'x-qn-meta-');
            if (($pos1 === 0 || $pos2 === 0) && !empty($v)) {
                $ret[$k] = $v;
            }
        }
        return $ret;
    }
}
