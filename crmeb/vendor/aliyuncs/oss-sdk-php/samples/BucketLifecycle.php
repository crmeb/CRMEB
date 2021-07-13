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

//******************************* 简单使用 *******************************************************

//设置lifecycle规则
$lifecycleConfig = new LifecycleConfig();
$actions = array();
$actions[] = new LifecycleAction("Expiration", "Days", 3);
$lifecycleRule = new LifecycleRule("delete obsoleted files", "obsoleted/", "Enabled", $actions);
$lifecycleConfig->addRule($lifecycleRule);
$ossClient->putBucketLifecycle($bucket, $lifecycleConfig);
Common::println("bucket $bucket lifecycleConfig created:" . $lifecycleConfig->serializeToXml());

//获取lifecycle规则
$lifecycleConfig = $ossClient->getBucketLifecycle($bucket);
Common::println("bucket $bucket lifecycleConfig fetched:" . $lifecycleConfig->serializeToXml());

//删除bucket的lifecycle配置
$ossClient->deleteBucketLifecycle($bucket);
Common::println("bucket $bucket lifecycleConfig deleted");


//***************************** 完整用法参考下面函数  ***********************************************

putBucketLifecycle($ossClient, $bucket);
getBucketLifecycle($ossClient, $bucket);
deleteBucketLifecycle($ossClient, $bucket);
getBucketLifecycle($ossClient, $bucket);

/**
 * 设置bucket的生命周期配置
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 获取bucket的生命周期配置
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 删除bucket的生命周期配置
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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


