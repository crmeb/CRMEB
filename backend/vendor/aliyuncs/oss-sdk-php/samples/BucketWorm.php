<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;

$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
$bucket = Common::getBucketName();

//******************************* Simple Usage****************************************************************

// Create a new compliance retention policy:
// The specified object protection days are 30 days.
$wormId = $ossClient->initiateBucketWorm($bucket, 30);
Common::println("bucket $bucket wormId: " . $wormId.PHP_EOL);

// Cancel an unlocked compliance retention policy
$ossClient->abortBucketWorm($bucket);

//Lock compliant retention policy
$wormId = $ossClient->initiateBucketWorm($bucket, 30);
$ossClient->completeBucketWorm($bucket, $wormId);

// Get compliant retention policy
$config = $ossClient->getBucketWorm($bucket);
Common::println("WormId:".$config->getWormId().PHP_EOL);
Common::println("State:". $config->getState().PHP_EOL);
Common::println("Day:". $config->getDay().PHP_EOL);

// Extend the retention days of objects
$wormId = "<yourWormId>";
// Extend the retention days of objects in the locked compliance retention policy to 120 days.
$ossClient->extendBucketWorm($bucket, $wormId, 120);

//******************************* For complete usage, see the following functions ****************************************************

initiateBucketWorm($ossClient, $bucket);
abortBucketWorm($ossClient, $bucket);
completeBucketWorm($ossClient, $bucket);
getBucketWorm($ossClient, $bucket);
extendBucketWorm($ossClient, $bucket);

/**
 * Set Bucket Worm Ploicy
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function initiateBucketWorm($ossClient, $bucket)
{
	try {
		$wormId = $ossClient->initiateBucketWorm($bucket,30);
		print("bucket $bucket wormId: " . $wormId.PHP_EOL);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * Cancel an unlocked compliance retention policy
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function abortBucketWorm($ossClient, $bucket)
{
	try {
		$ossClient->abortBucketWorm($bucket);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * Complete Bucket Worm
 * @param $ossClient $ossClient OssClient instance
 * @param $bucket $bucket Name of the bucket to create
 */
function completeBucketWorm($ossClient, $bucket)
{
	try {
		$wormId = $ossClient->initiateBucketWorm($bucket, 30);
		$ossClient->completeBucketWorm($bucket, $wormId);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Get Bucket Worm
 * @param $ossClient $ossClient OssClient instance
 * @param $bucket $bucket Name of the bucket to create
 */
function getBucketWorm($ossClient, $bucket)
{
	try {
		$config = $ossClient->getBucketWorm($bucket);
		
		printf("WormId:%s\n", $config->getWormId());
		printf("State:%s\n", $config->getState());
		printf("Day:%d\n", $config->getDay());
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Extend the retention days of objects
 * @param $ossClient $ossClient OssClient instance
 * @param $bucket $bucket Name of the bucket to create
 */
function extendBucketWorm($ossClient, $bucket)
{
	$wormId = "<yourWormId>";
	try {
		$ossClient->ExtendBucketWorm($bucket, $wormId, 120);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}