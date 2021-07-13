<?php
require_once __DIR__ . '/Common.php';

use OSS\Http\RequestCore;
use OSS\Http\ResponseCore;
use OSS\OssClient;
use OSS\Core\OssException;

$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);

//******************************* 简单使用 ***************************************************************

$ossClient->uploadFile($bucket, "a.file", __FILE__);

// 生成GetObject的签名url，用户可以使用这个url直接在浏览器下载
$signedUrl = $ossClient->signUrl($bucket, "a.file", 3600);
Common::println($signedUrl);

// 生成用于putObject的签名URL，用户可以直接用put方法使用这个url上传文件到 "a.file"
$signedUrl = $ossClient->signUrl($bucket, "a.file", "3600", "PUT");
Common::println($signedUrl);

// 生成从本地文件上传PutObject的签名url, 用户可以直接使用这个url把本地文件上传到　"a.file"
$signedUrl = $ossClient->signUrl($bucket, "a.file", 3600, "PUT", array('Content-Type' => 'txt'));
Common::println($signedUrl);

//******************************* 完整用法参考下面函数 ****************************************************

getSignedUrlForPuttingObject($ossClient, $bucket);
getSignedUrlForPuttingObjectFromFile($ossClient, $bucket);
getSignedUrlForGettingObject($ossClient, $bucket);

/**
 * 生成GetObject的签名url,主要用于私有权限下的读访问控制
 *
 * @param $ossClient OssClient OssClient实例
 * @param $bucket string 存储空间名称
 * @return null
 */
function getSignedUrlForGettingObject($ossClient, $bucket)
{
    $object = "test/test-signature-test-upload-and-download.txt";
    $timeout = 3600;
    try {
        $signedUrl = $ossClient->signUrl($bucket, $object, $timeout);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": signedUrl: " . $signedUrl . "\n");
    /**
     * 可以类似的代码来访问签名的URL，也可以输入到浏览器中去访问
     */
    $request = new RequestCore($signedUrl);
    $request->set_method('GET');
    $request->add_header('Content-Type', '');
    $request->send_request();
    $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
    if ($res->isOK()) {
        print(__FUNCTION__ . ": OK" . "\n");
    } else {
        print(__FUNCTION__ . ": FAILED" . "\n");
    };
}

/**
 * 生成PutObject的签名url,主要用于私有权限下的写访问控制
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
 * @return null
 * @throws OssException
 */
function getSignedUrlForPuttingObject($ossClient, $bucket)
{
    $object = "test/test-signature-test-upload-and-download.txt";
    $timeout = 3600;
    $options = NULL;
    try {
        $signedUrl = $ossClient->signUrl($bucket, $object, $timeout, "PUT");
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": signedUrl: " . $signedUrl . "\n");
    $content = file_get_contents(__FILE__);

    $request = new RequestCore($signedUrl);
    $request->set_method('PUT');
    $request->add_header('Content-Type', '');
    $request->add_header('Content-Length', strlen($content));
    $request->set_body($content);
    $request->send_request();
    $res = new ResponseCore($request->get_response_header(),
        $request->get_response_body(), $request->get_response_code());
    if ($res->isOK()) {
        print(__FUNCTION__ . ": OK" . "\n");
    } else {
        print(__FUNCTION__ . ": FAILED" . "\n");
    };
}

/**
 * 生成PutObject的签名url,主要用于私有权限下的写访问控制， 用户可以利用生成的signedUrl
 * 从文件上传文件
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
 * @throws OssException
 */
function getSignedUrlForPuttingObjectFromFile($ossClient, $bucket)
{
    $file = __FILE__;
    $object = "test/test-signature-test-upload-and-download.txt";
    $timeout = 3600;
    $options = array('Content-Type' => 'txt');
    try {
        $signedUrl = $ossClient->signUrl($bucket, $object, $timeout, "PUT", $options);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": signedUrl: " . $signedUrl . "\n");

    $request = new RequestCore($signedUrl);
    $request->set_method('PUT');
    $request->add_header('Content-Type', 'txt');
    $request->set_read_file($file);
    $request->set_read_stream_size(filesize($file));
    $request->send_request();
    $res = new ResponseCore($request->get_response_header(),
        $request->get_response_body(), $request->get_response_code());
    if ($res->isOK()) {
        print(__FUNCTION__ . ": OK" . "\n");
    } else {
        print(__FUNCTION__ . ": FAILED" . "\n");
    };
}