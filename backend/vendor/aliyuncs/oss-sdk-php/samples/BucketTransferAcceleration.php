<?php

require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;

$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
$bucket = Common::getBucketName();

//******************************* Simple Usage****************************************************************

// set <tran></tran>sfer acceleration
$enabled = true; // set true to enable transfer acceleration; set false to disalbe transfer acceleration
$ossClient->putBucketTransferAcceleration($bucket, $enabled);
printf('putBucketTransferAcceleration SUCCESS' . "\n");


// get transfer acceleration
$result  = $ossClient->getBucketTransferAcceleration($bucket);
printf('getBucketTransferAcceleration Status:%s'."\n",$result);


//******************************* For complete usage, see the following functions ****************************************************
putBucketTransferAcceleration($ossClient,$bucket);
getBucketTransferAcceleration($bucket);

/**
 * @param $ossClient OssClient
 * @param $bucket bucket_name string
 * @param $enabled string
 */
function putBucketTransferAcceleration($ossClient, $bucket, $enabled)
{
	try{
		$enabled = true; // set true to enable transfer acceleration; set false to disalbe transfer acceleration
		$ossClient->putBucketTransferAcceleration($bucket,$enabled);
		printf('putBucketTransferAcceleration SUCCESS' . "\n");
	} catch(OssException $e) {
		printf($e->getMessage() . "\n");
		return;
	}
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * @param $ossClient OssClient
 * @param $bucket bucket_name string
 */
function getBucketTransferAcceleration($ossClient, $bucket)
{
	try{
		$result = $ossClient->getBucketTransferAcceleration($bucket);
		printf('getBucketTransferAcceleration Status:%s'."\n",$result);
	} catch(OssException $e) {
		printf($e->getMessage() . "\n");
		return;
	}
	print(__FUNCTION__ . ": OK" . "\n");
}
