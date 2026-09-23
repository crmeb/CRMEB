<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\WebsiteConfig;

$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);

//******************************* Simple Usage ***************************************************************

// Set bucket static website configuration
$websiteConfig = new WebsiteConfig("index.html", "error.html");
$ossClient->putBucketWebsite($bucket, $websiteConfig);
Common::println("bucket $bucket websiteConfig created:" . $websiteConfig->serializeToXml());

// Get bucket static website configuration
$websiteConfig = $ossClient->getBucketWebsite($bucket);
Common::println("bucket $bucket websiteConfig fetched:" . $websiteConfig->serializeToXml());

// Delete bucket static website configuration
$ossClient->deleteBucketWebsite($bucket);
Common::println("bucket $bucket websiteConfig deleted");

//******************************* For complete usage, see the following functions  ****************************************************

putBucketWebsite($ossClient, $bucket);
getBucketWebsite($ossClient, $bucket);
deleteBucketWebsite($ossClient, $bucket);
getBucketWebsite($ossClient, $bucket);

/**
 * Sets bucket static website configuration
 *
 * @param $ossClient OssClient
 * @param  $bucket string bucket name
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
 * Get bucket static website configuration
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
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
 * Delete bucket static website configuration
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
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
