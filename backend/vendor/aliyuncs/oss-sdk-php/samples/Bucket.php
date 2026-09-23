<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;

$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
$bucket = Common::getBucketName();

//******************************* Simple Usage****************************************************************

// Create a bucket
$ossClient->createBucket($bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
Common::println("bucket $bucket created");

// Check whether a bucket exists
$doesExist = $ossClient->doesBucketExist($bucket);
Common::println("bucket $bucket exist? " . ($doesExist ? "yes" : "no"));

// Get the region of bucket
$regions = $ossClient->getBucketLocation($bucket);
Common::println("bucket $bucket region: " .print_r($regions,true));

// Get the meta of a bucket
$metas  = $ossClient->getBucketMeta($bucket);
Common::println("bucket $bucket meta: " .print_r($metas,true));

// Get the info of bucket
$info = $ossClient->getBucketInfo($bucket);
Common::println("bucket name:".$info->getName()."\n");
Common::println("bucket location:". $info->getLocation()."\n");
Common::println("bucket creation time:".$info->getCreateDate()."\n");
Common::println("bucket storage class:".$info->getStorageClass()."\n");
Common::println("bucket extranet endpoint:".$info->getExtranetEndpoint()."\n");
Common::println("bucket intranet endpoint:".$info->getIntranetEndpoint()."\n");


// Get the bucket list
$bucketListInfo = $ossClient->listBuckets();

// Set bucket ACL
$ossClient->putBucketAcl($bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
Common::println("bucket $bucket acl put");
// Get bucket ACL
$acl = $ossClient->getBucketAcl($bucket);
Common::println("bucket $bucket acl get: " . $acl);


//******************************* For complete usage, see the following functions ****************************************************

createBucket($ossClient, $bucket);
doesBucketExist($ossClient, $bucket);
getBucketLocation($ossClient, $bucket);
getBucketMeta($ossClient,$bucket);
getBucketInfo($ossClient, $bucket);
deleteBucket($ossClient, $bucket);
putBucketAcl($ossClient, $bucket);
getBucketAcl($ossClient, $bucket);
listBuckets($ossClient);

/**
 * Create a new bucket
 * acl indicates the access permission of a bucket, including: private, public-read-only/private-read-write, and public read-write.
 * Private indicates that only the bucket owner or authorized users can access the data..
 * The three permissions are separately defined by (OssClient::OSS_ACL_TYPE_PRIVATE,OssClient::OSS_ACL_TYPE_PUBLIC_READ, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE)
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
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
 * Check whether a bucket exists.
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
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
 * Get the info of bucket
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 */
function getBucketInfo($ossClient, $bucket)
{
	try {
		$info = $ossClient->getBucketInfo($bucket);
		printf("bucket name:%s\n", $info->getName());
		printf("bucket location:%s\n", $info->getLocation());
		printf("bucket creation time:%s\n", $info->getCreateDate());
		printf("bucket storage class:%s\n", $info->getStorageClass());
		printf("bucket extranet endpoint:%s\n", $info->getExtranetEndpoint());
		printf("bucket intranet endpoint:%s\n", $info->getIntranetEndpoint());
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * Get the meta of a bucket
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 */
function getBucketLocation($ossClient, $bucket)
{
	try {
		$regions = $ossClient->getBucketLocation($bucket);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print("bucket $bucket region: " .print_r($regions,true));
	
}


/**
 *  Get the bucket's meta
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 */
function getBucketMeta($ossClient, $bucket)
{
	try {
		$metas = $ossClient->getBucketMeta($bucket);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	print(__FUNCTION__ . ": OK" . "\n");
	print("bucket $bucket meta: " .print_r($metas,true));
}

/**
 * Delete a bucket. If the bucket is not empty, the deletion fails.
 * A bucket which is not empty indicates that it does not contain any objects or parts that are not completely uploaded during multipart upload
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to delete
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
 * Set bucket ACL
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
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
 * Get bucket ACL
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
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
 * List all buckets
 *
 * @param OssClient $ossClient OssClient instance
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
