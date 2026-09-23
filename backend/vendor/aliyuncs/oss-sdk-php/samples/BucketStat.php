<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
$bucket = Common::getBucketName();

//******************************* Simple Usage****************************************************************

// Get Bucket Stat
$stat = $ossClient->getBucketStat($bucket);
Common::println("Bucket ".$bucket." current storage is:".$stat->getStorage().PHP_EOL);
Common::println("Bucket ".$bucket." object count is:".$stat->getObjectCount().PHP_EOL);
Common::println("Bucket ".$bucket." multipart upload count is:".$stat->getMultipartUploadCount().PHP_EOL);
Common::println("Bucket ".$bucket." live channel count is:".$stat->getLiveChannelCount().PHP_EOL);
Common::println("Bucket ".$bucket." last modified time is:".$stat->getLastModifiedTime().PHP_EOL);
Common::println("Bucket ".$bucket." standard storage is:".$stat->getStandardStorage().PHP_EOL);
Common::println("Bucket ".$bucket." standard object count is:".$stat->getStandardObjectCount().PHP_EOL);
Common::println("Bucket ".$bucket." infrequent access storage is:".$stat->getInfrequentAccessStorage().PHP_EOL);
Common::println("Bucket ".$bucket." infrequent access real storage is:".$stat->getInfrequentAccessRealStorage().PHP_EOL);
Common::println("Bucket ".$bucket." infrequent access object count is:".$stat->getInfrequentAccessObjectCount().PHP_EOL);
Common::println("Bucket ".$bucket." archive storage is:".$stat->getArchiveStorage().PHP_EOL);
Common::println("Bucket ".$bucket." archive real storage is:".$stat->getArchiveRealStorage().PHP_EOL);
Common::println("Bucket ".$bucket." archive object count is:".$stat->getArchiveObjectCount().PHP_EOL);
Common::println("Bucket ".$bucket." cold archive storage is:".$stat->getColdArchiveStorage().PHP_EOL);
Common::println("Bucket ".$bucket." cold archive real storage is:".$stat->getColdArchiveRealStorage().PHP_EOL);
Common::println("Bucket ".$bucket." cold archive object count is:".$stat->getColdArchiveObjectCount().PHP_EOL);

//******************************* For complete usage, see the following functions ****************************************************
getBucketStat($ossClient,$bucket);
/**
 * get bucket stat
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket Name of the bucket to create
 * @return null
 */
function getBucketStat($ossClient, $bucket)
{
    try {
        $stat = $ossClient->getBucketStat($bucket);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    printf("Bucket ".$bucket." current storage is:".$stat->getStorage().PHP_EOL);
    printf("Bucket ".$bucket." object count is:".$stat->getObjectCount().PHP_EOL);
    printf("Bucket ".$bucket." multipart upload count is:".$stat->getMultipartUploadCount().PHP_EOL);
    printf("Bucket ".$bucket." live channel count is:".$stat->getLiveChannelCount().PHP_EOL);
    printf("Bucket ".$bucket." last modified time is:".$stat->getLastModifiedTime().PHP_EOL);
    printf("Bucket ".$bucket." standard storage is:".$stat->getStandardStorage().PHP_EOL);
    printf("Bucket ".$bucket." standard object count is:".$stat->getStandardObjectCount().PHP_EOL);
    printf("Bucket ".$bucket." infrequent access storage is:".$stat->getInfrequentAccessStorage().PHP_EOL);
    printf("Bucket ".$bucket." infrequent access real storage is:".$stat->getInfrequentAccessRealStorage().PHP_EOL);
    printf("Bucket ".$bucket." infrequent access object count is:".$stat->getInfrequentAccessObjectCount().PHP_EOL);
    printf("Bucket ".$bucket." archive storage is:".$stat->getArchiveStorage().PHP_EOL);
    printf("Bucket ".$bucket." archive real storage is:".$stat->getArchiveRealStorage().PHP_EOL);
    printf("Bucket ".$bucket." archive object count is:".$stat->getArchiveObjectCount().PHP_EOL);
    printf("Bucket ".$bucket." cold archive storage is:".$stat->getColdArchiveStorage().PHP_EOL);
    printf("Bucket ".$bucket." cold archive real storage is:".$stat->getColdArchiveRealStorage().PHP_EOL);
    printf("Bucket ".$bucket." cold archive object count is:".$stat->getColdArchiveObjectCount().PHP_EOL);
    print(__FUNCTION__ . ": OK" . "\n");
}