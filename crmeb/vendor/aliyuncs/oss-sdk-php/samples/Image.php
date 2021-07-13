<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;

$bucketName = Common::getBucketName();
$object = "example.jpg";
$ossClient = Common::getOssClient();
$download_file = "download.jpg";
if (is_null($ossClient)) exit(1);

//*******************************简单使用***************************************************************

// 先把本地的example.jpg上传到指定$bucket, 命名为$object
$ossClient->uploadFile($bucketName, $object, "example.jpg");

// 图片缩放
$options = array(
    OssClient::OSS_FILE_DOWNLOAD => $download_file,
    OssClient::OSS_PROCESS => "image/resize,m_fixed,h_100,w_100", );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageResize",$download_file);

// 图片裁剪
$options = array(
    OssClient::OSS_FILE_DOWNLOAD => $download_file,
    OssClient::OSS_PROCESS => "image/crop,w_100,h_100,x_100,y_100,r_1", );
$ossClient->getObject($bucketName, $object, $options);
printImage("iamgeCrop", $download_file);

// 图片旋转
$options = array(
    OssClient::OSS_FILE_DOWNLOAD => $download_file,
    OssClient::OSS_PROCESS => "image/rotate,90", );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageRotate", $download_file);

// 图片锐化
$options = array(
    OssClient::OSS_FILE_DOWNLOAD => $download_file,
    OssClient::OSS_PROCESS => "image/sharpen,100", );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageSharpen", $download_file);

// 图片水印
$options = array(
    OssClient::OSS_FILE_DOWNLOAD => $download_file,
    OssClient::OSS_PROCESS => "image/watermark,text_SGVsbG8g5Zu-54mH5pyN5YqhIQ", );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageWatermark", $download_file);

// 图片格式转换
$options = array(
    OssClient::OSS_FILE_DOWNLOAD => $download_file,
    OssClient::OSS_PROCESS => "image/format,png", );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageFormat", $download_file);

// 获取图片信息
$options = array(
    OssClient::OSS_FILE_DOWNLOAD => $download_file,
    OssClient::OSS_PROCESS => "image/info", );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageInfo", $download_file);


/**
 *  生成一个带签名的可用于浏览器直接打开的url, URL的有效期是3600秒
 */
 $timeout = 3600;
$options = array(
    OssClient::OSS_PROCESS => "image/resize,m_lfit,h_100,w_100",
    );
$signedUrl = $ossClient->signUrl($bucketName, $object, $timeout, "GET", $options);
Common::println("rtmp url: \n" . $signedUrl);

//最后删除上传的$object
$ossClient->deleteObject($bucketName, $object);     

function printImage($func, $imageFile)
{
    $array = getimagesize($imageFile);
    Common::println("$func, image width: " . $array[0]);
    Common::println("$func, image height: " . $array[1]);
    Common::println("$func, image type: " . ($array[2] === 2 ? 'jpg' : 'png'));
    Common::println("$func, image size: " . ceil(filesize($imageFile)));
}
