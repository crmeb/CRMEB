<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;

$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
$bucket = Common::getBucketName();

//******************************* Simple Usage****************************************************************

//Set requester payment mode
$ossClient->putBucketRequestPayment($bucket, "Requester");

//Get requester payment mode configuration
$payer = $ossClient->getBucketRequestPayment($bucket);
Common::println("bucket $bucket Payer:".$payer.PHP_EOL);

//Third-party paid access to Object
$options = array(
	OssClient::OSS_HEADERS => array(
		OssClient::OSS_REQUEST_PAYER => 'requester',
	));

$content = "hello";
$object = "object";

//PutObject interface to specify the payer
$ossClient->putObject($bucket, $object, $content, $options);

// GetObject interface to specify the payer
$ossClient->getObject($bucket, $object, $options);

// DeleteObject interface to specify the payer
$ossClient->deleteObject($bucket, $object, $options);

//******************************* For complete usage, see the following functions ****************************************************

putBucketRequestPayment($ossClient,$bucket);
getBucketRequestPayment($ossClient,$bucket);
setObjectPayment($ossClient,$bucket);

/**
 * Set requester payment mode
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function putBucketRequestPayment($ossClient, $bucket)
{
	try {
		$ossClient->putBucketRequestPayment($bucket, "Requester");
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * Get payment mode of bucket
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function getBucketRequestPayment($ossClient, $bucket)
{
	try {
		$payer = $ossClient->getBucketRequestPayment($bucket);
		print("bucket $bucket Payer:".$payer.PHP_EOL);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Set payment mode of object
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function setObjectPayment($ossClient,$bucket){
	// Specify the payment model for the requester.
	$options = array(
		OssClient::OSS_HEADERS => array(
			OssClient::OSS_REQUEST_PAYER => 'requester',
		));
	
	try {
		
		$content = "hello";
		$object = "object";
		//PutObject interface to specify the payer
		$ossClient->putObject($bucket, $object, $content, $options);
		// GetObject interface to specify the payer
		$ossClient->getObject($bucket, $object, $options);
		// DeleteObject interface to specify the payer
		$ossClient->deleteObject($bucket, $object, $options);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}


