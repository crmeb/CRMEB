<?php

namespace Qiniu\Storage;

use Qiniu\Config;
use Qiniu\Http\Client;
use Qiniu\Http\Error;
use Qiniu\Enum\SplitUploadVersion;
use Qiniu\Http\RequestOptions;

/**
 * 断点续上传类, 该类主要实现了断点续上传中的分块上传,
 * 以及相应地创建块和创建文件过程.
 *
 * @link http://developer.qiniu.com/docs/v6/api/reference/up/mkblk.html
 * @link http://developer.qiniu.com/docs/v6/api/reference/up/mkfile.html
 */
final class ResumeUploader
{
    private $upToken;
    private $key;
    private $inputStream;
    private $size;
    private $params;
    private $mime;
    private $contexts;
    private $finishedEtags;
    private $host;
    private $bucket;
    private $currentUrl;
    private $config;
    private $resumeRecordFile;
    private $version;
    private $partSize;
    /**
     * @var RequestOptions
     */
    private $reqOpt;

    /**
     * 上传二进制流到七牛
     *
     * @param string $upToken 上传凭证
     * @param string $key 上传文件名
     * @param resource $inputStream 上传二进制流
     * @param int $size 上传流的大小
     * @param array<string, string> $params 自定义变量
     * @param string $mime 上传数据的mimeType
     * @param Config $config
     * @param string $resumeRecordFile 断点续传的已上传的部分信息记录文件
     * @param string $version 分片上传版本 目前支持v1/v2版本 默认v1
     * @param int $partSize 分片上传v2字段 默认大小为4MB 分片大小范围为1 MB - 1 GB
     * @param RequestOptions $reqOpt 分片上传v2字段 默认大小为4MB 分片大小范围为1 MB - 1 GB
     * @throws \Exception
     *
     * @link http://developer.qiniu.com/docs/v6/api/overview/up/response/vars.html#xvar
     */
    public function __construct(
        $upToken,
        $key,
        $inputStream,
        $size,
        $params,
        $mime,
        $config,
        $resumeRecordFile = null,
        $version = 'v1',
        $partSize = config::BLOCK_SIZE,
        $reqOpt = null
    ) {

        $this->upToken = $upToken;
        $this->key = $key;
        $this->inputStream = $inputStream;
        $this->size = $size;
        $this->params = $params;
        $this->mime = $mime;
        $this->contexts = array();
        $this->finishedEtags = array("etags" => array(), "uploadId" => "", "expiredAt" => 0, "uploaded" => 0);
        $this->config = $config;
        $this->resumeRecordFile = $resumeRecordFile ? $resumeRecordFile : null;
        $this->partSize = $partSize ? $partSize : config::BLOCK_SIZE;

        if ($reqOpt === null) {
            $reqOpt = new RequestOptions();
        }
        $this->reqOpt = $reqOpt;

        try {
            $this->version = SplitUploadVersion::from($version ? $version : 'v1');
        } catch (\Exception $e) {
            throw new \Exception("only support v1/v2 now!", 0, $e);
        }

        list($accessKey, $bucket, $err) = \Qiniu\explodeUpToken($upToken);
        $this->bucket = $bucket;
        if ($err != null) {
            return array(null, $err);
        }

        list($upHost, $err) = $config->getUpHostV2($accessKey, $bucket, $reqOpt);
        if ($err != null) {
            throw new \Exception($err->message(), 1);
        }
        $this->host = $upHost;
    }

    /**
     * 上传操作
     * @param $fname string 文件名
     *
     * @throws \Exception
     */
    public function upload($fname)
    {
        $blkputRets = null;
        // get upload record from resumeRecordFile
        if ($this->resumeRecordFile != null) {
            if (file_exists($this->resumeRecordFile)) {
                $stream = fopen($this->resumeRecordFile, 'r');
                if ($stream) {
                    $streamLen = filesize($this->resumeRecordFile);
                    if ($streamLen > 0) {
                        $contents = fread($stream, $streamLen);
                        fclose($stream);
                        if ($contents) {
                            $blkputRets = json_decode($contents, true);
                            if ($blkputRets === null) {
                                error_log("resumeFile contents decode error");
                            }
                        } else {
                            error_log("read resumeFile failed");
                        }
                    } else {
                        error_log("resumeFile is empty");
                    }
                } else {
                    error_log("resumeFile open failed");
                }
            } else {
                error_log("resumeFile not exists");
            }
        }

        if ($this->version == SplitUploadVersion::V1) {
            return $this->uploadV1($fname, $blkputRets);
        } elseif ($this->version == SplitUploadVersion::V2) {
            return $this->uploadV2($fname, $blkputRets);
        } else {
            throw new \Exception("only support v1/v2 now!");
        }
    }

    /**
     * @param string $fname 文件名
     * @param null|array $blkputRets
     *
     * @throws \Exception
     */
    private function uploadV1($fname, $blkputRets = null)
    {
        // 尝试恢复恢复已上传的数据
        $isResumeUpload = $blkputRets !== null;
        $this->contexts = array();

        if ($blkputRets) {
            if (isset($blkputRets['contexts']) && isset($blkputRets['uploaded']) &&
                is_array($blkputRets['contexts']) && is_int($blkputRets['uploaded'])
            ) {
                $this->contexts = array_map(function ($ctx) {
                    if (is_array($ctx)) {
                        return $ctx;
                    } else {
                        // 兼容旧版本（旧版本没有存储 expireAt）
                        return array(
                            "ctx" => $ctx,
                            "expiredAt" => 0,
                        );
                    }
                }, $blkputRets['contexts']);
            }
        }

        // 上传分片
        $uploaded = 0;
        while ($uploaded < $this->size) {
            $blockSize = $this->blockSize($uploaded);
            $blockIndex = $uploaded / $this->partSize;
            if (!is_int($blockIndex)) {
                throw new \Exception("v1 part size changed");
            }
            // 如果已上传该分片且没有过期
            if (isset($this->contexts[$blockIndex]) && $this->contexts[$blockIndex]["expiredAt"] >= time()) {
                $uploaded += $blockSize;
                fseek($this->inputStream, $blockSize, SEEK_CUR);
                continue;
            }
            $data = fread($this->inputStream, $blockSize);
            if ($data === false) {
                throw new \Exception("file read failed", 1);
            }
            $crc = \Qiniu\crc32_data($data);
            $response = $this->makeBlock($data, $blockSize);


            $ret = null;
            if ($response->ok() && $response->json() != null) {
                $ret = $response->json();
            }
            if ($response->statusCode < 0) {
                list($accessKey, $bucket, $err) = \Qiniu\explodeUpToken($this->upToken);
                if ($err != null) {
                    return array(null, $err);
                }
                list($upHostBackup, $err) = $this->config->getUpBackupHostV2($accessKey, $bucket, $this->reqOpt);
                if ($err != null) {
                    return array(null, $err);
                }
                $this->host = $upHostBackup;
            }

            if ($response->needRetry() || !isset($ret['crc32']) || $crc != $ret['crc32']) {
                $response = $this->makeBlock($data, $blockSize);
                $ret = $response->json();
            }
            if (!$response->ok() || !isset($ret['crc32']) || $crc != $ret['crc32']) {
                return array(null, new Error($this->currentUrl, $response));
            }

            // 如果可以在已上传取到说明是过期分片直接修改已上传信息，否则是新的片添加到已上传分片尾部
            if (isset($this->contexts[$blockIndex])) {
                $this->contexts[$blockIndex] = array(
                    'ctx' => $ret['ctx'],
                    'expiredAt' => $ret['expired_at'],
                );
            } else {
                array_push($this->contexts, array(
                    'ctx' => $ret['ctx'],
                    'expiredAt' => $ret['expired_at'],
                ));
            }
            $uploaded += $blockSize;

            // 记录断点
            if ($this->resumeRecordFile !== null) {
                $recordData = array(
                    'contexts' => $this->contexts,
                    'uploaded' => $uploaded
                );
                $recordData = json_encode($recordData);

                if ($recordData) {
                    $isWritten = file_put_contents($this->resumeRecordFile, $recordData);
                    if ($isWritten === false) {
                        error_log("write resumeRecordFile failed");
                    }
                } else {
                    error_log('resumeRecordData encode failed');
                }
            }
        }

        // 完成上传
        list($ret, $err) = $this->makeFile($fname);
        if ($err !== null) {
            $response = $err->getResponse();
            if ($isResumeUpload && $response->statusCode === 701) {
                fseek($this->inputStream, 0);
                return $this->uploadV1($fname);
            }
        }
        return array($ret, $err);
    }

    /**
     * @param string $fname 文件名
     * @param null|array $blkputRets
     *
     * @throws \Exception
     */
    private function uploadV2($fname, $blkputRets = null)
    {
        $uploaded = 0;
        $partNumber = 1;
        $encodedObjectName = $this->key ? \Qiniu\base64_urlSafeEncode($this->key) : '~';
        $isResumeUpload = $blkputRets !== null;

        // 初始化 upload id
        $err = null;
        if ($blkputRets) {
            if (isset($blkputRets["etags"]) && isset($blkputRets["uploadId"]) &&
                isset($blkputRets["expiredAt"]) && $blkputRets["expiredAt"] > time() &&
                $blkputRets["uploaded"] > 0 && is_array($blkputRets["etags"]) &&
                is_string($blkputRets["uploadId"]) && is_int($blkputRets["expiredAt"])
            ) {
                $this->finishedEtags['etags'] = $blkputRets["etags"];
                $this->finishedEtags["uploadId"] = $blkputRets["uploadId"];
                $this->finishedEtags["expiredAt"] = $blkputRets["expiredAt"];
                $this->finishedEtags["uploaded"] = $blkputRets["uploaded"];
                $uploaded = $blkputRets["uploaded"];
                $partNumber = count($this->finishedEtags["etags"]) + 1;
            } else {
                $err = $this->makeInitReq($encodedObjectName);
            }
        } else {
            $err = $this->makeInitReq($encodedObjectName);
        }
        if ($err != null) {
            return array(null, $err);
        }

        // 上传分片
        fseek($this->inputStream, $uploaded);
        while ($uploaded < $this->size) {
            $blockSize = $this->blockSize($uploaded);
            $data = fread($this->inputStream, $blockSize);
            if ($data === false) {
                throw new \Exception("file read failed", 1);
            }
            $md5 = md5($data);
            $response = $this->uploadPart(
                $data,
                $partNumber,
                $this->finishedEtags["uploadId"],
                $encodedObjectName,
                $md5
            );

            $ret = null;
            if ($response->ok() && $response->json() != null) {
                $ret = $response->json();
            }
            if ($response->statusCode < 0) {
                list($accessKey, $bucket, $err) = \Qiniu\explodeUpToken($this->upToken);
                if ($err != null) {
                    return array(null, $err);
                }
                list($upHostBackup, $err) = $this->config->getUpBackupHostV2($accessKey, $bucket, $this->reqOpt);
                if ($err != null) {
                    return array(null, $err);
                }
                $this->host = $upHostBackup;
            }

            if ($response->needRetry() || !isset($ret['md5']) || $md5 != $ret['md5']) {
                $response = $this->uploadPart(
                    $data,
                    $partNumber,
                    $this->finishedEtags["uploadId"],
                    $encodedObjectName,
                    $md5
                );
                $ret = $response->json();
            }
            if ($isResumeUpload && $response->statusCode === 612) {
                return $this->uploadV2($fname);
            }
            if (!$response->ok() || !isset($ret['md5']) || $md5 != $ret['md5']) {
                return array(null, new Error($this->currentUrl, $response));
            }
            $blockStatus = array('etag' => $ret['etag'], 'partNumber' => $partNumber);
            array_push($this->finishedEtags['etags'], $blockStatus);
            $partNumber += 1;

            $uploaded += $blockSize;
            $this->finishedEtags['uploaded'] = $uploaded;

            if ($this->resumeRecordFile !== null) {
                $recordData = json_encode($this->finishedEtags);
                if ($recordData) {
                    $isWritten = file_put_contents($this->resumeRecordFile, $recordData);
                    if ($isWritten === false) {
                        error_log("write resumeRecordFile failed");
                    }
                } else {
                    error_log('resumeRecordData encode failed');
                }
            }
        }

        list($ret, $err) = $this->completeParts($fname, $this->finishedEtags['uploadId'], $encodedObjectName);
        if ($err !== null) {
            $response = $err->getResponse();
            if ($isResumeUpload && $response->statusCode === 612) {
                return $this->uploadV2($fname);
            }
        }
        return array($ret, $err);
    }

    /**
     * 创建块
     */
    private function makeBlock($block, $blockSize)
    {
        $url = $this->host . '/mkblk/' . $blockSize;
        return $this->post($url, $block);
    }

    private function fileUrl($fname)
    {
        $url = $this->host . '/mkfile/' . $this->size;
        $url .= '/mimeType/' . \Qiniu\base64_urlSafeEncode($this->mime);
        if ($this->key != null) {
            $url .= '/key/' . \Qiniu\base64_urlSafeEncode($this->key);
        }
        $url .= '/fname/' . \Qiniu\base64_urlSafeEncode($fname);
        if (!empty($this->params)) {
            foreach ($this->params as $key => $value) {
                $val = \Qiniu\base64_urlSafeEncode($value);
                $url .= "/$key/$val";
            }
        }
        return $url;
    }

    /**
     * 创建文件
     *
     * @param string $fname 文件名
     * @return array{array | null, Error | null}
     */
    private function makeFile($fname)
    {
        $url = $this->fileUrl($fname);
        $body = implode(',', array_map(function ($ctx) {
            return $ctx['ctx'];
        }, $this->contexts));
        $response = $this->post($url, $body);
        if ($response->needRetry()) {
            $response = $this->post($url, $body);
        }
        if ($response->statusCode === 200 || $response->statusCode === 701) {
            if ($this->resumeRecordFile !== null) {
                @unlink($this->resumeRecordFile);
            }
        }
        if (!$response->ok()) {
            return array(null, new Error($this->currentUrl, $response));
        }
        return array($response->json(), null);
    }

    private function post($url, $data)
    {
        $this->currentUrl = $url;
        $headers = array('Authorization' => 'UpToken ' . $this->upToken);
        return Client::post($url, $data, $headers, $this->reqOpt);
    }

    private function blockSize($uploaded)
    {
        if ($this->size < $uploaded + $this->partSize) {
            return $this->size - $uploaded;
        }
        return $this->partSize;
    }

    private function makeInitReq($encodedObjectName)
    {
        list($ret, $err) = $this->initReq($encodedObjectName);

        if ($ret == null) {
            return $err;
        }

        $this->finishedEtags["uploadId"] = $ret['uploadId'];
        $this->finishedEtags["expiredAt"] = $ret['expireAt'];
        return $err;
    }

    /**
     * 初始化上传任务
     */
    private function initReq($encodedObjectName)
    {
        $url = $this->host . '/buckets/' . $this->bucket . '/objects/' . $encodedObjectName . '/uploads';
        $headers = array(
            'Authorization' => 'UpToken ' . $this->upToken,
            'Content-Type' => 'application/json'
        );
        $response = $this->postWithHeaders($url, null, $headers);
        $ret = $response->json();
        if ($response->ok() && $ret != null) {
            return array($ret, null);
        }

        return array(null, new Error($url, $response));
    }

    /**
     * 分块上传v2
     */
    private function uploadPart($block, $partNumber, $uploadId, $encodedObjectName, $md5)
    {
        $headers = array(
            'Authorization' => 'UpToken ' . $this->upToken,
            'Content-Type' => 'application/octet-stream',
            'Content-MD5' => $md5
        );
        $url = $this->host . '/buckets/' . $this->bucket . '/objects/' . $encodedObjectName .
            '/uploads/' . $uploadId . '/' . $partNumber;
        $response = $this->put($url, $block, $headers);
        if ($response->statusCode === 612) {
            if ($this->resumeRecordFile !== null) {
                @unlink($this->resumeRecordFile);
            }
        }
        return $response;
    }

    /**
     * 完成分片上传V2
     *
     * @param string $fname 文件名
     * @param int $uploadId 由 {@see initReq} 获取
     * @param string $encodedObjectName 经过编码的存储路径
     * @return array{array | null, Error | null}
     */
    private function completeParts($fname, $uploadId, $encodedObjectName)
    {
        $headers = array(
            'Authorization' => 'UpToken ' . $this->upToken,
            'Content-Type' => 'application/json'
        );
        $etags = $this->finishedEtags['etags'];
        $sortedEtags = \Qiniu\arraySort($etags, 'partNumber');
        $metadata = array();
        $customVars = array();
        if ($this->params) {
            foreach ($this->params as $k => $v) {
                if (strpos($k, 'x:') === 0) {
                    $customVars[$k] = $v;
                } elseif (strpos($k, 'x-qn-meta-') === 0) {
                    $metadata[$k] = $v;
                }
            }
        }
        if (empty($metadata)) {
            $metadata = null;
        }
        if (empty($customVars)) {
            $customVars = null;
        }
        $body = array(
            'fname' => $fname,
            'mimeType' => $this->mime,
            'metadata' => $metadata,
            'customVars' => $customVars,
            'parts' => $sortedEtags
        );
        $jsonBody = json_encode($body);
        $url = $this->host . '/buckets/' . $this->bucket . '/objects/' . $encodedObjectName . '/uploads/' . $uploadId;
        $response = $this->postWithHeaders($url, $jsonBody, $headers);
        if ($response->needRetry()) {
            $response = $this->postWithHeaders($url, $jsonBody, $headers);
        }
        if ($response->statusCode === 200 || $response->statusCode === 612) {
            if ($this->resumeRecordFile !== null) {
                @unlink($this->resumeRecordFile);
            }
        }
        if (!$response->ok()) {
            return array(null, new Error($this->currentUrl, $response));
        }
        return array($response->json(), null);
    }

    private function put($url, $data, $headers)
    {
        $this->currentUrl = $url;
        return Client::put($url, $data, $headers, $this->reqOpt);
    }

    private function postWithHeaders($url, $data, $headers)
    {
        $this->currentUrl = $url;
        return Client::post($url, $data, $headers, $this->reqOpt);
    }
}
