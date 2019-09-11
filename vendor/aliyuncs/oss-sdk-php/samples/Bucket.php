<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;

$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
$bucket = Common::getBucketName();

//******************************* 简单使用 ****************************************************************

//创建bucket
$ossClient->createBucket($bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
Common::println("bucket $bucket created");

// 判断Bucket是否存在
$doesExist = $ossClient->doesBucketExist($bucket);
Common::println("bucket $bucket exist? " . ($doesExist ? "yes" : "no"));

// 获取Bucket列表
$bucketListInfo = $ossClient->listBuckets();

// 设置bucket的ACL
$ossClient->putBucketAcl($bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
Common::println("bucket $bucket acl put");
// 获取bucket的ACL
$acl = $ossClient->getBucketAcl($bucket);
Common::println("bucket $bucket acl get: " . $acl);


//******************************* 完整用法参考下面函数 ****************************************************

createBucket($ossClient, $bucket);
doesBucketExist($ossClient, $bucket);
deleteBucket($ossClient, $bucket);
putBucketAcl($ossClient, $bucket);
getBucketAcl($ossClient, $bucket);
listBuckets($ossClient);

/**
 * 创建一个存储空间
 * acl 指的是bucket的访问控制权限，有三种，私有读写，公共读私有写，公共读写。
 * 私有读写就是只有bucket的拥有者或授权用户才有权限操作
 * 三种权限分别对应 (OssClient::OSS_ACL_TYPE_PRIVATE，OssClient::OSS_ACL_TYPE_PUBLIC_READ, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE)
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 要创建的存储空间名称
 * @return null
 */
function createBucket($ossClient, $bucket)
{
    try {
        $ossClient->createBucket($bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

/**
 *  判断Bucket是否存在
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
 */
function doesBucketExist($ossClient, $bucket)
{
    try {
        $res = $ossClient->doesBucketExist($bucket);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    if ($res === true) {
        print(__FUNCTION__ . ": OK" . "\n");
    } else {
        print(__FUNCTION__ . ": FAILED" . "\n");
    }
}

/**
 * 删除bucket，如果bucket不为空则bucket无法删除成功， 不为空表示bucket既没有object，也没有未完成的multipart上传时的parts
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 待删除的存储空间名称
 * @return null
 */
function deleteBucket($ossClient, $bucket)
{
    try {
        $ossClient->deleteBucket($bucket);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * 设置bucket的acl配置
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
 * @return null
 */
function putBucketAcl($ossClient, $bucket)
{
    $acl = OssClient::OSS_ACL_TYPE_PRIVATE;
    try {
        $ossClient->putBucketAcl($bucket, $acl);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * 获取bucket的acl配置
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
 * @return null
 */
function getBucketAcl($ossClient, $bucket)
{
    try {
        $res = $ossClient->getBucketAcl($bucket);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
    print('acl: ' . $res);
}


/**
 * 列出用户所有的Bucket
 *
 * @param OssClient $ossClient OssClient实例
 * @return null
 */
function listBuckets($ossClient)
{
    $bucketList = null;
    try {
        $bucketListInfo = $ossClient->listBuckets();
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
    $bucketList = $bucketListInfo->getBucketList();
    foreach ($bucketList as $bucket) {
        print($bucket->getLocation() . "\t" . $bucket->getName() . "\t" . $bucket->getCreatedate() . "\n");
    }
}
