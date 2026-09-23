<?php

namespace OSS\Tests;

require_once __DIR__ . '/Common.php';

use OSS\Core\OssException;
use OSS\Model\CnameConfig;

class BucketCnameTest extends \PHPUnit\Framework\TestCase
{
    private $bucketName;
    private $client;

    protected function setUp(): void
    {
        $this->client = Common::getOssClient();
        $this->bucketName = 'php-sdk-test-bucket-' . strval(rand(0, 10000));
        $this->client->createBucket($this->bucketName);
    }

    protected function tearDown(): void
    {
        $this->client->deleteBucket($this->bucketName);
    }

    public function testBucketWithoutCname()
    {
        $cnameConfig = $this->client->getBucketCname($this->bucketName);
        $this->assertEquals(0, count($cnameConfig->getCnames()));
    }

    public function testAddCname()
    {
        try {
            $this->client->addBucketCname($this->bucketName, 'www.baidu.com');
        } catch (OssException $e) {
            print_r($e->getMessage());
            $this->assertTrue(true);
        }

        try {
            $ret = $this->client->getBucketCname($this->bucketName);
            $this->assertEquals(0, count($ret->getCnames()));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testDeleteCname()
    {
        try {
            $this->client->deleteBucketCname($this->bucketName, 'www.not-exist.com');
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $ret = $this->client->getBucketCname($this->bucketName);
            $this->assertEquals(0, count($ret->getCnames()));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }
}
