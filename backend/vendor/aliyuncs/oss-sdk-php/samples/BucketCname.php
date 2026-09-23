<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;

$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);

//*******************************Simple Usage ***************************************************************

// Add Canme record
$myDomain = '<yourDomainName>';
$ossClient->addBucketCname($bucket, $myDomain);

// View cname records
$cnameConfig = $ossClient->getBucketCname($bucket);
Common::println("bucket $bucket cname:" . $cnameConfig->serializeToXml());

// Delete bucket cname
$myDomain = '<yourDomainName>';
$ossClient->deleteBucketCname($bucket,$myDomain);
Common::println("bucket $bucket cname deleted");

//******************************* For complete usage, see the following functions ****************************************************

addBucketCname($ossClient, $bucket);
getBucketCname($ossClient, $bucket);
deleteBucketCname($ossClient, $bucket);

/**
 * Set bucket cname
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function addBucketCname($ossClient, $bucket)
{
	// Set up a custom domain name.
	$myDomain = '<yourDomainName>';
	try {
		$ossClient->addBucketCname($bucket, $myDomain);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Get bucket cname
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function getBucketCname($ossClient, $bucket)
{
    try {
		$cnameConfig = $ossClient->getBucketCname($bucket);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
    print($cnameConfig->serializeToXml() . "\n");
}

/**
 * Delete bucket cname
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function deleteBucketCname($ossClient, $bucket)
{
	$myDomain = '<yourDomainName>';
    try {
		$ossClient->deleteBucketCname($bucket, $myDomain);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}
