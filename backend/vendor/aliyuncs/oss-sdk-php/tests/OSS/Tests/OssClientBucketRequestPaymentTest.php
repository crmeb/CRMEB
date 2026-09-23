<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Model\RequestPaymentConfig;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientBucketRequestPaymentTest extends TestOssClientBase
{
    public function testBucket()
    {
        try {
            Common::waitMetaSync();
            $payer = $this->ossClient->getBucketRequestPayment($this->bucket);
            $this->assertEquals("BucketOwner", $payer);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->ossClient->putBucketRequestPayment($this->bucket, "Requester");
        } catch (OssException $e) {
            var_dump($e->getMessage());
            $this->assertTrue(false);
        }
        try {
            Common::waitMetaSync();
            $payer = $this->ossClient->getBucketRequestPayment($this->bucket);
            $this->assertEquals("Requester", $payer);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->ossClient->putBucketRequestPayment($this->bucket, "BucketOwner");
        } catch (OssException $e) {
            var_dump($e->getMessage());
            $this->assertTrue(false);
        }
        try {
            Common::waitMetaSync();
            $payer = $this->ossClient->getBucketRequestPayment($this->bucket);
            $this->assertEquals("BucketOwner", $payer);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }
}
