<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;

$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
$bucket = Common::getBucketName();

//******************************* Simple Usage****************************************************************

// Set Bucket Policy
// Authorization strategy.
$policy = <<< BBBB
{
  "Version":"1",
  "Statement":[
  {
    "Action":[
    "oss:PutObject",
    "oss:GetObject"
  ],
    "Effect":"Allow",
    "Resource":["acs:oss:*:*:*/user1/*"]
  }
  ]
}
BBBB;
$ossClient->putBucketPolicy($bucket, $policy);

// Get bucket pllicy
$policy = $ossClient->getBucketPolicy($bucket);
Common::println("bucket $bucket policy: " . $policy);


// Delete bucket pllicy
$policy = $ossClient->deleteBucketPolicy($bucket);

//******************************* For complete usage, see the following functions ****************************************************

putBucketPolicy($ossClient, $bucket);
getBucketPolicy($ossClient, $bucket);
deleteBucketPolicy($ossClient, $bucket);

/**
 * Set Bucket Policy
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function putBucketPolicy($ossClient, $bucket)
{
	$policy = <<< BBBB
{
  "Version":"1",
  "Statement":[
  {
    "Action":[
    "oss:PutObject",
    "oss:GetObject"
  ],
    "Effect":"Allow",
    "Resource":["acs:oss:*:*:*/user1/*"]
  }
  ]
}
BBBB;
	
	try {
		$ossClient->putBucketPolicy($bucket, $policy);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * Get Bucket Policy
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function getBucketPolicy($ossClient, $bucket)
{
	try {
		$policy = $ossClient->getBucketPolicy($bucket);
		print($policy);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * Delete Bucket Policy
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function deleteBucketPolicy($ossClient, $bucket)
{
	try {
		$ossClient->deleteBucketPolicy($bucket);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}
