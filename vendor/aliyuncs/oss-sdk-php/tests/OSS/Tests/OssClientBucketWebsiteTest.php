<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Model\WebsiteConfig;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientBucketWebsiteTest extends TestOssClientBase
{
    public function testBucket()
    {

        $websiteConfig = new WebsiteConfig("index.html", "error.html");

        try {
            $this->ossClient->putBucketWebsite($this->bucket, $websiteConfig);
        } catch (OssException $e) {
            var_dump($e->getMessage());
            $this->assertTrue(false);
        }

        try {
            Common::waitMetaSync();
            $websiteConfig2 = $this->ossClient->getBucketWebsite($this->bucket);
            $this->assertEquals($websiteConfig->serializeToXml(), $websiteConfig2->serializeToXml());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
        try {
            Common::waitMetaSync();
            $this->ossClient->deleteBucketWebsite($this->bucket);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
        try {
            Common::waitMetaSync();
            $websiteConfig3 = $this->ossClient->getBucketLogging($this->bucket);
            $this->assertNotEquals($websiteConfig->serializeToXml(), $websiteConfig3->serializeToXml());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }
}
