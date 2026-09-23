<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\TaggingConfig;
use OSS\Model\Tag;

$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
$bucket = Common::getBucketName();

//******************************* Simple Usage****************************************************************

// Set bucket tag
$config = new TaggingConfig();
$config->addTag(new Tag("key1", "value1"));
$config->addTag(new Tag("key2", "value2"));
$ossClient->putBucketTags($bucket, $config);

// Get bucket tags
$config = $ossClient->getBucketTags($bucket);
Common::println("bucket $bucket tags: ".$config->serializeToXml());

// Delete bucket tags

// Delete the specified tag of the bucket.
$tags = array();
$tags[] = new Tag("key1", "value1");
$tags[] = new Tag("key2", "value2");
$ossClient->deleteBucketTags($bucket, $tags);

// Delete all tags in the bucket.
$ossClient->deleteBucketTags($bucket);

//******************************* For complete usage, see the following functions ****************************************************

putBucketTags($ossClient, $bucket);
getBucketTags($ossClient, $bucket);
deleteBucketTags($ossClient, $bucket);


/**
 * Create bucket tag
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function putBucketTags($ossClient, $bucket)
{
	try {
		// 设置Bucket标签。
		$config = new TaggingConfig();
		$config->addTag(new Tag("key1", "value1"));
		$config->addTag(new Tag("key2", "value2"));
		$ossClient->putBucketTags($bucket, $config);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * get bucket tag
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function getBucketTags($ossClient, $bucket)
{
	try {
		$config = $ossClient->getBucketTags($bucket);
		print_r($config->getTags());
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * delete bucket tag
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function deleteBucketTags($ossClient, $bucket)
{
	try {
		// Delete the specified tag of the bucket.
		$tags = array();
		$tags[] = new Tag("key1", "value1");
		$tags[] = new Tag("key2", "value2");
		$ossClient->deleteBucketTags($bucket, $tags);
		
		// Delete all tags in the bucket.
		//$ossClient->deleteBucketTags($bucket);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}

