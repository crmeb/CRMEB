<?php

require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Core\OssUtil;
use OSS\Model\TaggingConfig;
use OSS\Model\Tag;

$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
//******************************* Simple usage ***************************************************************

// Upload Object add tag
$object = "b.file";
$options = array(
	OssClient::OSS_HEADERS => array(
		'x-oss-tagging' => 'key1=value1&key2=value2&key3=value3',
	)
);
$result = $ossClient->putObject($bucket, $object, __FILE__,$options);
Common::println("b.file is created");
Common::println("tag is:".$result['oss-requestheaders']['x-oss-tagging']);

// Add object tags when uploading parts

$object = "b.file";
$file = __FILE__;
$options = array(
	OssClient::OSS_CHECK_MD5 => true,
	OssClient::OSS_PART_SIZE => 1,
	OssClient::OSS_HEADERS => array(
		'x-oss-tagging' => 'key1=value1&key2=value2&key3=value3',
	),
);
$result = $ossClient->multiuploadFile($bucket, $object, $file, $options);
Common::println("b.file is created");
Common::println("tag is:".$result['oss-requestheaders']['x-oss-tagging']);

// get tags from object

$object = "a.txt";
$result = $ossClient->getObjectTagging($bucket,$object);
printf($object.'tags is: '.$result->serializeToXml().PHP_EOL);


// Add or change object tags to uploaded objects
$config = new TaggingConfig();
$config->addTag(new Tag("key1", "value1"));
$config->addTag(new Tag("key2", "value2"));

$ossClient->putObjectTagging($bucket, $object, $config);
// Add object tags when uploading
$object = "a.txt";
$filePath = "D:\\localpath\\b.txt";
$filePath1 = "D:\\localpath\\c.txt";
$options = array(
	OssClient::OSS_HEADERS => array(
		'x-oss-tagging' => 'key1=value1&key2=value2',
	)
);
$position = $ossClient->appendObject($bucket, $object,'content one',0,$options);
printf('Content one append object Success'.PHP_EOL);
$position = $ossClient->appendObject($bucket, $object, 'content two',$position,$options);
printf('Content two append object Success'.PHP_EOL);

// delete tags
$object = "g.file";
$ossClient->deleteObjectTagging($bucket, $object);
printf($object.' tags has deleted'.PHP_EOL);

// Copy a small file
$fromBucket = $bucket;
$fromObject = "a.file";
$toBucket = $bucket;
$toObject = $fromObject . '.copy';
$options = array(
	OssClient::OSS_HEADERS => array(
		'x-oss-tagging-directive' => 'Replace',
		'x-oss-tagging'=>'key1=value1&key2=value2&key3=value3',
	));
$ossClient->copyObject($fromBucket, $fromObject, $toBucket, $toObject, $options);
$config = $ossClient->getObjectTagging($bucket, $toObject);
Common::println('object tags is:'.$config->serializeToXml());

// Copy a large file
$fromBucket = $bucket;
$fromObject = "a.file";
$toBucket = $bucket;
$toObject = $fromObject . '.copy';
$options = array(
	OssClient::OSS_HEADERS => array(
		'x-oss-tagging'=>'key1=value1&key2=value2&key3=value3',
	));

$part_size = 256*1024*1024;
$objectMeta = $ossClient->getObjectMeta($fromBucket, $fromObject);
$length = $objectMeta['content-length'];
$upload_id = $ossClient->initiateMultipartUpload($toBucket, $toObject,$options);
$pieces = $ossClient->generateMultiuploadParts($length, $part_size);
$response_upload_part = array();
$copyId = 1;
$upload_position = 0;
foreach ($pieces as $i => $piece) {
	$from_pos = $upload_position + (integer)$piece['seekTo'];
	$to_pos = (integer)$piece['length'] + $from_pos - 1;
	$up_options = array(
		'start' => $from_pos,
		'end' => $to_pos,
	);
	$response_upload_part[] = $ossClient->uploadPartCopy( $fromBucket, $fromObject, $toBucket, $toObject, $copyId, $upload_id, $up_options);
	printf("initiateMultipartUpload, uploadPartCopy - part#{$copyId} OK\n");
	$copyId = $copyId + 1;
}
$upload_parts = array();
foreach ($response_upload_part as $i => $etag) {
	$upload_parts[] = array(
		'PartNumber' => ($i + 1),
		'ETag' => $etag,
	);
}
$result = $ossClient->completeMultipartUpload($toBucket, $toObject, $upload_id, $upload_parts);
$config = $ossClient->getObjectTagging($bucket, $toObject);
Common::println($toObject.' tags is:'.$config->serializeToXml());



//******************************* For complete usage, see the following functions ****************************************************

putObject($ossClient,$bucket);
multiuploadFile($ossClient,$bucket);
appendObject($ossClient,$bucket);
putObjectTagging($ossClient,$bucket);
getObjectTagging($ossClient,$bucket);
deleteObjectTagging($ossClient,$bucket);
copyObjectSmall($ossClient,$bucket);
copyObjectLarge($ossClient,$bucket);
/**
 *  Upload Object add tag
 * @param $ossClient OssClient
 * @param $bucket bucket_name
 */
function putObject($ossClient,$bucket){
	$object = "b.file";
	$options = array(
		OssClient::OSS_HEADERS => array(
			'x-oss-tagging' => 'key1=value1&key2=value2&key3=value3',
		));
	try {
		// 通过简单上传的方式上传Object。
		$result = $ossClient->putObject($bucket, $object, __FILE__,$options);
		Common::println("b.file is created".PHP_EOL);
		Common::println("tag is:".$result['oss-requestheaders']['x-oss-tagging'].PHP_EOL);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Add object tags when uploading parts
 * @param $ossClient OssClient
 * @param $bucket bucket_name
 */
function multiuploadFile($ossClient,$bucket){
	$object = "b.file";
	$file = __FILE__;
	$options = array(
		OssClient::OSS_CHECK_MD5 => true,
		OssClient::OSS_PART_SIZE => 1,
		OssClient::OSS_HEADERS => array(
			'x-oss-tagging' => 'key1=value1&key2=value2&key3=value3',
		),
	);
	
	try {
		$result = $ossClient->multiuploadFile($bucket, $object, $file, $options);
		Common::println("b.file is created".PHP_EOL);
		Common::println("tag is:".$result['oss-requestheaders']['x-oss-tagging'].PHP_EOL);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Add object tags when uploading
 * @param $ossClient OssClient
 * @param $bucket bucket_name string
 */
function appendObject($ossClient,$bucket){
	$object = "g.file";
	$content_array = array('Hello OSS', 'Hi OSS');
	$options = array(
		OssClient::OSS_HEADERS => array(
			'x-oss-tagging' => 'key1=value1&key2=value2',
		));
	
	try {
		$position = $ossClient->appendObject($bucket, $object, $content_array[0], 0, $options);
		printf($content_array[0].' append object Success'.PHP_EOL);
		$position = $ossClient->appendObject($bucket, $object, $content_array[1], $position);
		printf($content_array[1].' append object Success'.PHP_EOL);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * @param $ossClient OssClient
 * @param $bucket bucket_name string
 * @throws OssException
 */
function putObjectTagging($ossClient,$bucket){
	$object = "g.file";
	$config = new TaggingConfig();
	$config->addTag(new Tag("key1", "value1"));
	$config->addTag(new Tag("key2", "value2"));
	
	try {
		$ossClient->putObjectTagging($bucket, $object, $config);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * get object tags
 * @param $ossClient OssClient
 * @param $bucket bucket_name string
 */
function getObjectTagging($ossClient,$bucket){
	$object = "g.file";
	try {
		$config = $ossClient->getObjectTagging($bucket, $object);
		printf($object." tags is:".$config->serializeToXml().PHP_EOL);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * get object tags
 * @param $ossClient OssClient
 * @param $bucket bucket_name string
 */
function deleteObjectTagging($ossClient,$bucket){
	$object = "g.file";
	try {
		$ossClient->deleteObjectTagging($bucket, $object);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Copy small files
 * @param $ossClient OssClient
 * @param $bucket bucket_name string
 */
function copyObjectSmall($ossClient,$bucket){
	$fromBucket = $bucket;
	$fromObject = "a.file";
	$toBucket = $bucket;
	$toObject = $fromObject . '.copy';
	$options = array(
		OssClient::OSS_HEADERS => array(
			'x-oss-tagging-directive' => 'Replace',
			'x-oss-tagging'=>'key1=value1&key2=value2&key3=value3',
		));
	
	try {
		$ossClient->copyObject($fromBucket, $fromObject, $toBucket, $toObject, $options);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	
	$config = $ossClient->getObjectTagging($bucket, $toObject);
	Common::println('object tags is:'.$config->serializeToXml());
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Copy a large file
 * @param $ossClient OssClient
 * @param $bucket bucket_name string
 */
function copyObjectLarge($ossClient,$bucket){
	$fromBucket = $bucket;
	$fromObject = "a.file";
	$toBucket = $bucket;
	$toObject = $fromObject . '.copy';
	$options = array(
		OssClient::OSS_HEADERS => array(
			'x-oss-tagging'=>'key1=value1&key2=value2&key3=value3',
		));
	
	$part_size = 256*1024*1024;
	try{
		$objectMeta = $ossClient->getObjectMeta($fromBucket, $fromObject);
		$length = $objectMeta['content-length'];
		$upload_id = $ossClient->initiateMultipartUpload($toBucket, $toObject,$options);
		$pieces = $ossClient->generateMultiuploadParts($length, $part_size);
		$response_upload_part = array();
		$copyId = 1;
		$upload_position = 0;
		foreach ($pieces as $i => $piece) {
			$from_pos = $upload_position + (integer)$piece['seekTo'];
			$to_pos = (integer)$piece['length'] + $from_pos - 1;
			$up_options = array(
				'start' => $from_pos,
				'end' => $to_pos,
			);
			$response_upload_part[] = $ossClient->uploadPartCopy( $fromBucket, $fromObject, $toBucket, $toObject, $copyId, $upload_id, $up_options);
			printf("initiateMultipartUpload, uploadPartCopy - part#{$copyId} OK\n");
			$copyId = $copyId + 1;
		}
		$upload_parts = array();
		foreach ($response_upload_part as $i => $etag) {
			$upload_parts[] = array(
				'PartNumber' => ($i + 1),
				'ETag' => $etag,
			);
		}
		$result = $ossClient->completeMultipartUpload($toBucket, $toObject, $upload_id, $upload_parts);
		printf('copy success'. "\n");
	} catch(OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
		
		
	}
	$config = $ossClient->getObjectTagging($bucket, $toObject);
	Common::println($toObject.' tags is:'.$config->serializeToXml());
	print(__FUNCTION__ . ": OK" . "\n");
}

