<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\LifecycleAction;
use OSS\Model\LifecycleConfig;
use OSS\Model\LifecycleRule;

$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);

//******************************* Simple Usage *******************************************************

// Set lifecycle configuration
$lifecycleConfig = new LifecycleConfig();
$actions = array();
$actions[] = new LifecycleAction("Expiration", "Days", 3);
$lifecycleRule = new LifecycleRule("delete obsoleted files", "obsoleted/", "Enabled", $actions);
$lifecycleConfig->addRule($lifecycleRule);
$ossClient->putBucketLifecycle($bucket, $lifecycleConfig);
Common::println("bucket $bucket lifecycleConfig created:" . $lifecycleConfig->serializeToXml());

// Get lifecycle configuration
$lifecycleConfig = $ossClient->getBucketLifecycle($bucket);
Common::println("bucket $bucket lifecycleConfig fetched:" . $lifecycleConfig->serializeToXml());

// Delete bucket lifecycle configuration
$ossClient->deleteBucketLifecycle($bucket);
Common::println("bucket $bucket lifecycleConfig deleted");


//***************************** For complete usage, see the following functions  ***********************************************

putBucketLifecycle($ossClient, $bucket);
getBucketLifecycle($ossClient, $bucket);
deleteBucketLifecycle($ossClient, $bucket);
getBucketLifecycle($ossClient, $bucket);

/**
 * Set bucket lifecycle configuration
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function putBucketLifecycle($ossClient, $bucket)
{
    $lifecycleConfig = new LifecycleConfig();
    $actions = array();
    $actions[] = new LifecycleAction(OssClient::OSS_LIFECYCLE_EXPIRATION, OssClient::OSS_LIFECYCLE_TIMING_DAYS, 3);
    $lifecycleRule = new LifecycleRule("delete obsoleted files", "obsoleted/", "Enabled", $actions);
    $lifecycleConfig->addRule($lifecycleRule);
    $actions = array();
    $actions[] = new LifecycleAction(OssClient::OSS_LIFECYCLE_EXPIRATION, OssClient::OSS_LIFECYCLE_TIMING_DATE, '2022-10-12T00:00:00.000Z');
    $lifecycleRule = new LifecycleRule("delete temporary files", "temporary/", "Enabled", $actions);
    $lifecycleConfig->addRule($lifecycleRule);
    try {
        $ossClient->putBucketLifecycle($bucket, $lifecycleConfig);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Get bucket lifecycle configuration
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function getBucketLifecycle($ossClient, $bucket)
{
    $lifecycleConfig = null;
    try {
        $lifecycleConfig = $ossClient->getBucketLifecycle($bucket);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
    print($lifecycleConfig->serializeToXml() . "\n");
}

/**
 * Delete bucket lifecycle configuration
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function deleteBucketLifecycle($ossClient, $bucket)
{
    try {
        $ossClient->deleteBucketLifecycle($bucket);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}


