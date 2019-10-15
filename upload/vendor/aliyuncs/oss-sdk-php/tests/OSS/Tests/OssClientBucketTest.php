<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\OssClient;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientBucketTest extends TestOssClientBase
{
    private $iaBucket;
    private $archiveBucket;

    public function testBucketWithInvalidName()
    {
        try {
            $this->ossClient->createBucket("s");
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('"s"bucket name is invalid', $e->getMessage());
        }
    }

    public function testBucketWithInvalidACL()
    {
        try {
            $this->ossClient->createBucket($this->bucket, "invalid");
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('invalid:acl is invalid(private,public-read,public-read-write)', $e->getMessage());
        }
    }

    public function testBucket()
    {
        $this->ossClient->createBucket($this->bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);

        $bucketListInfo = $this->ossClient->listBuckets();
        $this->assertNotNull($bucketListInfo);
        
        $bucketList = $bucketListInfo->getBucketList();
        $this->assertTrue(is_array($bucketList));
        $this->assertGreaterThan(0, count($bucketList));
        
        $this->ossClient->putBucketAcl($this->bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
        Common::waitMetaSync();
        $this->assertEquals($this->ossClient->getBucketAcl($this->bucket), OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);

        $this->assertTrue($this->ossClient->doesBucketExist($this->bucket));
        $this->assertFalse($this->ossClient->doesBucketExist($this->bucket . '-notexist'));
        
        $this->assertEquals($this->ossClient->getBucketLocation($this->bucket), 'oss-us-west-1');
        
        $res = $this->ossClient->getBucketMeta($this->bucket);
        $this->assertEquals('200', $res['info']['http_code']);
        $this->assertEquals('oss-us-west-1', $res['x-oss-bucket-region']);
    }

    public function  testCreateBucketWithStorageType()
    {
        $object = 'storage-object';

        $this->ossClient->putObject($this->archiveBucket, $object,'testcontent');
        try {
            $this->ossClient->getObject($this->archiveBucket, $object);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('403', $e->getHTTPStatus());
            $this->assertEquals('InvalidObjectState', $e->getErrorCode());
        }

        $this->ossClient->putObject($this->iaBucket, $object,'testcontent');
        $result = $this->ossClient->getObject($this->iaBucket, $object);
        $this->assertEquals($result, 'testcontent');

        $this->ossClient->putObject($this->bucket, $object,'testcontent');
        $result = $this->ossClient->getObject($this->bucket, $object);
        $this->assertEquals($result, 'testcontent');
    }

    public function setUp()
    {
        parent::setUp();

        $this->iaBucket = 'ia-' . $this->bucket;
        $this->archiveBucket = 'archive-' . $this->bucket;
        $options = array(
            OssClient::OSS_STORAGE => OssClient::OSS_STORAGE_IA
        );

        $this->ossClient->createBucket($this->iaBucket, OssClient::OSS_ACL_TYPE_PRIVATE, $options);

        $options = array(
            OssClient::OSS_STORAGE => OssClient::OSS_STORAGE_ARCHIVE
        );

        $this->ossClient->createBucket($this->archiveBucket, OssClient::OSS_ACL_TYPE_PRIVATE, $options);
    }

    public function tearDown()
    {
        parent::tearDown();

        $object = 'storage-object';

        $this->ossClient->deleteObject($this->iaBucket, $object);
        $this->ossClient->deleteObject($this->archiveBucket, $object);
        $this->ossClient->deleteBucket($this->iaBucket);
        $this->ossClient->deleteBucket($this->archiveBucket);
    }
}
