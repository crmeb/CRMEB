<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Model\ServerSideEncryptionConfig;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientBucketEncryptionTest extends TestOssClientBase
{
    public function testBucket()
    {
        $config = new ServerSideEncryptionConfig("AES256");
        try {
            $this->ossClient->putBucketEncryption($this->bucket, $config);
        } catch (OssException $e) {
            var_dump($e->getMessage());
            $this->assertTrue(false);
        }
        try {
            Common::waitMetaSync();
            $config2 = $this->ossClient->getBucketEncryption($this->bucket);
            $this->assertEquals($config->serializeToXml(), $config2->serializeToXml());
            $this->assertEquals("AES256", $config2->getSSEAlgorithm());
            $this->assertEquals(null, $config2->getKMSMasterKeyID());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        $config = new ServerSideEncryptionConfig("KMS", "kms-id");
        try {
            $this->ossClient->putBucketEncryption($this->bucket, $config);
        } catch (OssException $e) {
            var_dump($e->getMessage());
            $this->assertTrue(false);
        }
        try {
            Common::waitMetaSync();
            $config2 = $this->ossClient->getBucketEncryption($this->bucket);
            $this->assertEquals($config->serializeToXml(), $config2->serializeToXml());
            $this->assertEquals("KMS", $config2->getSSEAlgorithm());
            $this->assertEquals("kms-id", $config2->getKMSMasterKeyID());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            Common::waitMetaSync();
            $this->ossClient->deleteBucketEncryption($this->bucket);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
        try {
            Common::waitMetaSync();
            $config2 = $this->ossClient->getBucketEncryption($this->bucket);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals("NoSuchServerSideEncryptionRule", $e->getErrorCode());
        }
    }
}
