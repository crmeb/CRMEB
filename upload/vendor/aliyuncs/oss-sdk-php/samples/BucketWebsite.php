<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\WebsiteConfig;

$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);

//*******************************简单使用***************************************************************

// 设置Bucket的静态网站托管模式
$websiteConfig = new WebsiteConfig("index.html", "error.html");
$ossClient->putBucketWebsite($bucket, $websiteConfig);
Common::println("bucket $bucket websiteConfig created:" . $websiteConfig->serializeToXml());

// 查看Bucket的静态网站托管状态
$websiteConfig = $ossClient->getBucketWebsite($bucket);
Common::println("bucket $bucket websiteConfig fetched:" . $websiteConfig->serializeToXml());

// 删除Bucket的静态网站托管模式
$ossClient->deleteBucketWebsite($bucket);
Common::println("bucket $bucket websiteConfig deleted");

//******************************* 完整用法参考下面函数 ****************************************************

putBucketWebsite($ossClient, $bucket);
getBucketWebsite($ossClient, $bucket);
deleteBucketWebsite($ossClient, $bucket);
getBucketWebsite($ossClient, $bucket);

/**
 * 设置bucket的静态网站托管模式配置
 *
 * @param $ossClient OssClient
 * @param  $bucket string 存储空间名称
 * @return null
 */
function putBucketWebsite($ossClient, $bucket)
{
    $websiteConfig = new WebsiteConfig("index.html", "error.html");
    try {
        $ossClient->putBucketWebsite($bucket, $websiteConfig);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * 获取bucket的静态网站托管状态
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
 * @return null
 */
function getBucketWebsite($ossClient, $bucket)
{
    $websiteConfig = null;
    try {
        $websiteConfig = $ossClient->getBucketWebsite($bucket);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
    print($websiteConfig->serializeToXml() . "\n");
}

/**
 * 删除bucket的静态网站托管模式配置
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
 * @return null
 */
function deleteBucketWebsite($ossClient, $bucket)
{
    try {
        $ossClient->deleteBucketWebsite($bucket);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}
