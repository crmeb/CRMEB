<?php

namespace OSS\Tests;

require_once __DIR__ . '/../../../autoload.php';

use OSS\OssClient;
use OSS\Core\OssException;

/**
 * Class Common
 *
 * Sample program [Samples / *. Php] Common class, used to obtain OssClient instance and other public methods
 */
class Common
{
    /**
     * According to the Config configuration, get an OssClient instance
     *
     * @return OssClient  An OssClient instance
     */
    public static function getOssClient()
    {
        try {
            $ossClient = new OssClient(
                getenv('OSS_ACCESS_KEY_ID'),
                getenv('OSS_ACCESS_KEY_SECRET'),
                getenv('OSS_ENDPOINT'), false);
        } catch (OssException $e) {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");
            return null;
        }
        return $ossClient;
    }

    public static function getBucketName()
    {
        return getenv('OSS_BUCKET');
    }

    /**
     * Tool method, create a bucket
     */
    public static function createBucket()
    {
        $ossClient = self::getOssClient();
        if (is_null($ossClient)) exit(1);
        $bucket = self::getBucketName();
        $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ;
        try {
            $ossClient->createBucket($bucket, $acl);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": OK" . "\n");
    }

    /**
     * Wait for bucket meta sync
     */
    public static function waitMetaSync()
    {
        if (getenv('TRAVIS')) {
            sleep(10);
        }
    }
}
