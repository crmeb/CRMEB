<?php
namespace OSS\Tests;

use OSS\Core\OssException;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';

class OssClientBucketInfoTest extends TestOssClientBase
{
    public function testBucketInfo()
    {
        try {
            $info = $this->ossClient->getBucketInfo($this->bucket);
            $this->assertEquals($this->bucket, $info->getName());
            $this->assertEquals("Standard", $info->getStorageClass());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }
}
