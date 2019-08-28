<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\OssClient;


class OssClientTest extends \PHPUnit_Framework_TestCase
{
    public function testConstrunct()
    {
        try {
            $ossClient = new OssClient('id', 'key', 'http://oss-cn-hangzhou.aliyuncs.com');
            $this->assertFalse($ossClient->isUseSSL());
            $ossClient->setUseSSL(true);
            $this->assertTrue($ossClient->isUseSSL());
            $this->assertTrue(true);
            $this->assertEquals(3, $ossClient->getMaxRetries());
            $ossClient->setMaxTries(4);
            $this->assertEquals(4, $ossClient->getMaxRetries());
            $ossClient->setTimeout(10);
            $ossClient->setConnectTimeout(20);
        } catch (OssException $e) {
            assertFalse(true);
        }
    }

    public function testConstrunct2()
    {
        try {
            $ossClient = new OssClient('id', "", 'http://oss-cn-hangzhou.aliyuncs.com');
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals("access key secret is empty", $e->getMessage());
        }
    }

    public function testConstrunct3()
    {
        try {
            $ossClient = new OssClient("", 'key', 'http://oss-cn-hangzhou.aliyuncs.com');
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals("access key id is empty", $e->getMessage());
        }
    }

    public function testConstrunct4()
    {
        try {
            $ossClient = new OssClient('id', 'key', "");
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('endpoint is empty', $e->getMessage());
        }
    }

    public function testConstrunct5()
    {
        try {
            $ossClient = new OssClient('id', 'key', "123.123.123.1");
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testConstrunct6()
    {
        try {
            $ossClient = new OssClient('id', 'key', "https://123.123.123.1");
            $this->assertTrue($ossClient->isUseSSL());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testConstrunct7()
    {
        try {
            $ossClient = new OssClient('id', 'key', "http://123.123.123.1");
            $this->assertFalse($ossClient->isUseSSL());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testConstrunct8()
    {
        try {
            $ossClient = new OssClient('id', 'key', "http://123.123.123.1", true);
            $ossClient->listBuckets();
            $this->assertFalse(true);
        } catch (OssException $e) {

        }
    }

    public function testConstrunct9()
    {
        try {
            $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = ' ' . getenv('OSS_ENDPOINT') . '/ ';
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
            $ossClient->listBuckets();
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testSupportPutEmptyObject()
    {
        try {
            $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = ' ' . getenv('OSS_ENDPOINT') . '/ ';
            $bucket = getenv('OSS_BUCKET');
            $ossClient = new OssClient($accessKeyId, $accessKeySecret , $endpoint, false);
            $ossClient->putObject($bucket,'test_emptybody','');
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testCreateObjectDir()
    {
        try {
            $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = ' ' . getenv('OSS_ENDPOINT') . '/ ';
            $bucket = getenv('OSS_BUCKET');
            $object='test-dir';
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
            $ossClient->createObjectDir($bucket,$object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testGetBucketCors()
    {
        try {
            $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = ' ' . getenv('OSS_ENDPOINT') . '/ ';
            $bucket = getenv('OSS_BUCKET');
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
            $ossClient->getBucketCors($bucket);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testGetBucketCname()
    {
        try {
            $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = ' ' . getenv('OSS_ENDPOINT') . '/ ';
            $bucket = getenv('OSS_BUCKET');
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
            $ossClient->getBucketCname($bucket);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testProxySupport()
    {
        $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
        $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
        $endpoint = ' ' . getenv('OSS_ENDPOINT') . '/ ';
        $bucket = getenv('OSS_BUCKET') . '-proxy';
        $requestProxy  = getenv('OSS_PROXY');
        $key = 'test-proxy-srv-object';
        $content = 'test-content';
        $proxys = parse_url($requestProxy);

        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, null, $requestProxy);

        $result = $ossClient->createBucket($bucket);
        $this->checkProxy($result, $proxys);

        $result = $ossClient->putObject($bucket, $key, $content);
        $this->checkProxy($result, $proxys);
        $result = $ossClient->getObject($bucket, $key);
        $this->assertEquals($content, $result);

        // list object
        $objectListInfo = $ossClient->listObjects($bucket);
        $objectList = $objectListInfo->getObjectList();
        $this->assertNotNull($objectList);
        $this->assertTrue(is_array($objectList));
        $objects = array();
        foreach ($objectList as $value) {
            $objects[] = $value->getKey();
        }
        $this->assertEquals(1, count($objects));
        $this->assertTrue(in_array($key, $objects));

        $result = $ossClient->deleteObject($bucket, $key);
        $this->checkProxy($result,$proxys);

        $result = $ossClient->deleteBucket($bucket);
        $this->checkProxy($result, $proxys);
    }

    private function checkProxy($result, $proxys)
    {
        $this->assertEquals($result['info']['primary_ip'], $proxys['host']);
        $this->assertEquals($result['info']['primary_port'], $proxys['port']);
        $this->assertTrue(array_key_exists('via', $result));
    }

}
