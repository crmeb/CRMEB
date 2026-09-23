<?php

namespace OSS\Tests;

use OSS\Core\OssException;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientBucketWormTest extends TestOssClientBase
{
    public function testBucket()
    {
        try {
            $wormId = $this->ossClient->initiateBucketWorm($this->bucket, 30);
            $config = $this->ossClient->getBucketWorm($this->bucket);
            $this->assertEquals($wormId, $config->getWormId());
            $this->assertEquals("InProgress", $config->getState());
            $this->assertEquals(30, $config->getDay());
            $this->ossClient->abortBucketWorm($this->bucket);

            $wormId = $this->ossClient->initiateBucketWorm($this->bucket, 60);
            $this->ossClient->completeBucketWorm($this->bucket, $wormId);
            $config = $this->ossClient->getBucketWorm($this->bucket);

            $this->ossClient->ExtendBucketWorm($this->bucket, $wormId, 120);
            $config = $this->ossClient->getBucketWorm($this->bucket);
            $this->assertEquals($wormId, $config->getWormId());
            $this->assertEquals("Locked", $config->getState());
            $this->assertEquals(120, $config->getDay());

        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }
}
