<?php

namespace OSS\Tests;

use OSS\Core\OssException;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientBucketVersioningTest extends TestOssClientBase
{
    public function testBucket()
    {
        try {
            Common::waitMetaSync();
            $status = $this->ossClient->getBucketVersioning($this->bucket);
            $this->assertEquals(null, $status);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->ossClient->putBucketVersioning($this->bucket, "Enabled");
            Common::waitMetaSync();
            $status = $this->ossClient->getBucketVersioning($this->bucket);
            $this->assertEquals("Enabled", $status);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->ossClient->putBucketVersioning($this->bucket, "Suspended");
            Common::waitMetaSync();
            $status = $this->ossClient->getBucketVersioning($this->bucket);
            $this->assertEquals("Suspended", $status);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }
}
