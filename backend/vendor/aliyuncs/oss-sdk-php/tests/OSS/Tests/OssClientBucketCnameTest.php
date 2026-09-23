<?php

namespace OSS\Tests;

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\CnameConfig;
use OSS\Model\CnameTokenInfo;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientBucketCnameTest extends TestOssClientBase
{
    public function testBucketCname()
    {
        $bucketName = $this->bucket . '-cname';
        $client = new OssClient(
            getenv('OSS_ACCESS_KEY_ID'),
            getenv('OSS_ACCESS_KEY_SECRET'),
            "oss-ap-southeast-2.aliyuncs.com", false);

        $client->createBucket($bucketName);

        try {
            $info1 = $client->createBucketCnameToken($bucketName, "www.example.com");
            $this->assertEquals("www.example.com", $info1->getCname());
            $this->assertEquals($bucketName, $info1->getBucket());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $info2 = $client->getBucketCnameToken($bucketName, "www.example.com");
            $this->assertEquals("www.example.com", $info2->getCname());
            $this->assertEquals($bucketName, $info2->getBucket());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $result = $client->addBucketCname($bucketName, "www.example.com");
        } catch (OssException $e) {
            $this->assertEquals('NeedVerifyDomainOwnership', $e->getErrorCode());
        }

        try {
            $config = $client->getBucketCname($bucketName);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $result = $client->deleteBucketCname($bucketName, "www.example.com");
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        $client->deleteBucket($bucketName);
    }
}
