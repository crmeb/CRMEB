<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Model\RefererConfig;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientBucketRefererTest extends TestOssClientBase
{

    public function testBucket()
    {
        $refererConfig = new RefererConfig();
        $refererConfig->addReferer('http://www.aliyun.com');

        try {
            $this->ossClient->putBucketReferer($this->bucket, $refererConfig);
        } catch (OssException $e) {
            var_dump($e->getMessage());
            $this->assertTrue(false);
        }
        try {
            Common::waitMetaSync();
            $refererConfig2 = $this->ossClient->getBucketReferer($this->bucket);
            $this->assertEquals($refererConfig->serializeToXml(), $refererConfig2->serializeToXml());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
        try {
            Common::waitMetaSync();
            $nullRefererConfig = new RefererConfig();
            $nullRefererConfig->setAllowEmptyReferer(false);
            $this->ossClient->putBucketReferer($this->bucket, $nullRefererConfig);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
        try {
            Common::waitMetaSync();
            $refererConfig3 = $this->ossClient->getBucketLogging($this->bucket);
            $this->assertNotEquals($refererConfig->serializeToXml(), $refererConfig3->serializeToXml());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }
}
