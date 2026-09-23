<?php

require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\ServerSideEncryptionConfig;

$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
$bucket = Common::getBucketName();

//******************************* Simple Usage****************************************************************

// Configure Bucket encryption
// Set Bucket's default server-side encryption method to OSS fully managed encryption (SSE-OSS).
$config = new ServerSideEncryptionConfig("AES256");
// Set Bucket's default server-side encryption method to KMS, and do not specify a CMK ID.
//$config = new ServerSideEncryptionConfig("KMS");
// Set Bucket's default server-side encryption method to KMS, and specify the CMK ID.
//$config = new ServerSideEncryptionConfig("KMS", "your kms id");
$ossClient->putBucketEncryption($bucket, $config);
Common::println("bucket $bucket encryoption created");

$config = $ossClient->getBucketEncryption($bucket);
Common::println("bucket $bucket encryoption:".$config->serializeToXml());

$config = $ossClient->deleteBucketEncryption($bucket);
Common::println("bucket $bucket encryoption has deleted");

//******************************* For complete usage, see the following functions ****************************************************
putBucketEncryption($ossClient, $bucket);
getBucketEncryption($ossClient, $bucket);
deleteBucketEncryption($ossClient, $bucket);

/**
 * Configure Bucket encryption
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */

function putBucketEncryption($ossClient,$bucket){
	try {
		// Set Bucket's default server-side encryption method to OSS fully managed encryption (SSE-OSS).
		$config = new ServerSideEncryptionConfig("AES256");
		// Set Bucket's default server-side encryption method to KMS, and do not specify a CMK ID.
		//$config = new ServerSideEncryptionConfig("KMS");
		// Set Bucket's default server-side encryption method to KMS, and specify the CMK ID.
		//$config = new ServerSideEncryptionConfig("KMS", "your kms id");
		$ossClient->putBucketEncryption($bucket, $config);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Get Bucket encryption
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */

function getBucketEncryption($ossClient,$bucket){
	try {
		$config = $ossClient->getBucketEncryption($bucket);
		print($config->getSSEAlgorithm());
		print($config->getKMSMasterKeyID());
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * Delete Bucket encryption
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */

function deleteBucketEncryption($ossClient,$bucket){
	try {
		$ossClient->deleteBucketEncryption($bucket);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	print(__FUNCTION__ . ": OK" . "\n");
}

