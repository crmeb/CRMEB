<?php

namespace OSS\Tests;

use OSS\Core\OssException;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientBucketTransferAccelerationTest extends TestOssClientBase
{
    public function testBucket()
    {
        try {
            Common::waitMetaSync();
            $status = $this->ossClient->getBucketTransferAcceleration($this->bucket);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals("NoSuchTransferAccelerationConfiguration", $e->getErrorCode());
        }

        try {
            $this->ossClient->putBucketTransferAcceleration($this->bucket, true);
            Common::waitMetaSync();
            $status = $this->ossClient->getBucketTransferAcceleration($this->bucket);
            $this->assertEquals(true, $status);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->ossClient->putBucketTransferAcceleration($this->bucket, false);
            Common::waitMetaSync();
            $status = $this->ossClient->getBucketTransferAcceleration($this->bucket);
            $this->assertEquals(false, $status);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }
}
