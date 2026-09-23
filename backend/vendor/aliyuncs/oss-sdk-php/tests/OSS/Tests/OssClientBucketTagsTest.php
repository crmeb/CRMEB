<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Model\TaggingConfig;
use OSS\Model\Tag;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientBucketTagsTest extends TestOssClientBase
{
    public function testBucket()
    {
        try {
            Common::waitMetaSync();
            $config = $this->ossClient->getBucketTags($this->bucket);
            $this->assertEquals(0, count($config->getTags()));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $config = new TaggingConfig();
            $config->addTag(new Tag("key1", "value1"));
            $config->addTag(new Tag("key2", "value2"));
            $config->addTag(new Tag("key3", "value3"));
            $this->ossClient->putBucketTags($this->bucket, $config);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
        
        try {
            Common::waitMetaSync();
            $config2 = $this->ossClient->getBucketTags($this->bucket);
            $this->assertEquals(3, count($config2->getTags()));
            $this->assertEquals("key1", $config2->getTags()[0]->getKey());
            $this->assertEquals("value1", $config2->getTags()[0]->getValue());
            $this->assertEquals("key2", $config2->getTags()[1]->getKey());
            $this->assertEquals("value2", $config2->getTags()[1]->getValue());
            $this->assertEquals("key3", $config2->getTags()[2]->getKey());
            $this->assertEquals("value3", $config2->getTags()[2]->getValue());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }


        try {
            Common::waitMetaSync();
            //del key1, key3
            $tags = array();
            $tags[] = new Tag("key1", "value1");
            $tags[] = new Tag("key3", "value3");

            $this->ossClient->deleteBucketTags($this->bucket, $tags);
            $config2 = $this->ossClient->getBucketTags($this->bucket);
            $this->assertEquals(1, count($config2->getTags()));
            $this->assertEquals("key2", $config2->getTags()[0]->getKey());
            $this->assertEquals("value2", $config2->getTags()[0]->getValue());
        } catch (OssException $e) {
            $this->assertTrue(false);
        }


        try {
            Common::waitMetaSync();
            //del all
            $this->ossClient->deleteBucketTags($this->bucket);
            $config2 = $this->ossClient->getBucketTags($this->bucket);
            $this->assertEquals(0, count($config2->getTags()));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }
}
