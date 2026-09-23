<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\OssClient;
use OSS\Model\RestoreConfig;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientRestoreObjectTest extends TestOssClientBase
{
    private $iaBucket;
    private $archiveBucket;

    public function testIARestoreObject()
    {
        $object = 'storage-object';

        $this->ossClient->putObject($this->iaBucket, $object,'testcontent');
        try{
            $this->ossClient->restoreObject($this->iaBucket, $object);
            $this->assertTrue(false);
        }catch (OssException $e){
            $this->assertEquals('400', $e->getHTTPStatus());
            $this->assertEquals('OperationNotSupported', $e->getErrorCode());
        }
    }

    public function testNullObjectRestoreObject()
    {
        $object = 'null-object';

        try{
            $this->ossClient->restoreObject($this->bucket, $object);
            $this->assertTrue(false);
        }catch (OssException $e){
            $this->assertEquals('404', $e->getHTTPStatus());
        }
    }

    public function testArchiveRestoreObject()
    {
        $object = 'storage-object';

        $this->ossClient->putObject($this->archiveBucket, $object,'testcontent');
        try{
            $this->ossClient->getObject($this->archiveBucket, $object);
            $this->assertTrue(false);
        }catch (OssException $e){
            $this->assertEquals('403', $e->getHTTPStatus());
            $this->assertEquals('InvalidObjectState', $e->getErrorCode());
        }
        $result = $this->ossClient->restoreObject($this->archiveBucket, $object);
        common::waitMetaSync();
        $this->assertEquals('202', $result['info']['http_code']);

        try{
            $this->ossClient->restoreObject($this->archiveBucket, $object);
        }catch(OssException $e){
            $this->assertEquals('409', $e->getHTTPStatus());
            $this->assertEquals('RestoreAlreadyInProgress', $e->getErrorCode());
        }
    }

    public function testColdArchiveRestoreObject()
    {
        $client = new OssClient(
            getenv('OSS_ACCESS_KEY_ID'),
            getenv('OSS_ACCESS_KEY_SECRET'),
            'oss-ap-southeast-1.aliyuncs.com', false);

        $bucket = $this->bucket . 'cold-archive';
        $object = 'storage-object';

        //create bucket
        $options = array(
            OssClient::OSS_STORAGE => OssClient::OSS_STORAGE_COLDARCHIVE
        );
        $client->createBucket($bucket, OssClient::OSS_ACL_TYPE_PRIVATE, $options);

        //test with days
        $client->putObject($bucket, $object,'testcontent');

        try{
            $client->getObject($bucket, $object);
            $this->assertTrue(false);
        }catch (OssException $e){
            $this->assertEquals('403', $e->getHTTPStatus());
            $this->assertEquals('InvalidObjectState', $e->getErrorCode());
        }

        $config = new RestoreConfig(5);
        $resoptions = array(
            OssClient::OSS_RESTORE_CONFIG => $config
        );
        try{
            $client->restoreObject($bucket, $object, $resoptions);
        }catch(OssException $e){
            $this->assertTrue(false);
        }

        try{
            $client->restoreObject($bucket, $object, $resoptions);
        }catch(OssException $e){
            $this->assertEquals('409', $e->getHTTPStatus());
            $this->assertEquals('RestoreAlreadyInProgress', $e->getErrorCode());
        }

        //test with days & tier
        $client->putObject($bucket, $object,'testcontent');

        try{
            $client->getObject($bucket, $object);
            $this->assertTrue(false);
        }catch (OssException $e){
            $this->assertEquals('403', $e->getHTTPStatus());
            $this->assertEquals('InvalidObjectState', $e->getErrorCode());
        }

        $config = new RestoreConfig(5, "Expedited");
        $resoptions = array(
            OssClient::OSS_RESTORE_CONFIG => $config
        );
        try{
            $client->restoreObject($bucket, $object, $resoptions);
        }catch(OssException $e){
            $this->assertTrue(false);
        }

        try{
            $client->restoreObject($bucket, $object, $resoptions);
        }catch(OssException $e){
            $this->assertEquals('409', $e->getHTTPStatus());
            $this->assertEquals('RestoreAlreadyInProgress', $e->getErrorCode());
        }

        $client->deleteObject($bucket, $object);
        $client->deleteBucket($bucket);
    }


    protected function setUp(): void
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

    protected function tearDown(): void
    {
        parent::tearDown();

        $object = 'storage-object';

        $this->ossClient->deleteObject($this->iaBucket, $object);
        $this->ossClient->deleteObject($this->archiveBucket, $object);
        $this->ossClient->deleteBucket($this->iaBucket);
        $this->ossClient->deleteBucket($this->archiveBucket);
    }
}
