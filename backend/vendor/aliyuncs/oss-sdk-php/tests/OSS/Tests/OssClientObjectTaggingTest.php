<?php

namespace OSS\Tests;

use OSS\OssClient;
use OSS\Core\OssUtil;
use OSS\Core\OssException;
use OSS\Model\TaggingConfig;
use OSS\Model\Tag;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientObjectTaggingTest extends TestOssClientBase
{
    public function testObjectTagging()
    {
        $object = "object-tagging.txt";
        $content = "hello world";

        try {
            $this->ossClient->putObject($this->bucket, $object, $content);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $config = $this->ossClient->getObjectTagging($this->bucket, $object);
            $this->assertEquals(0, count($config->getTags()));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $config = new TaggingConfig();
            $config->addTag(new Tag("key1", "value1"));
            $config->addTag(new Tag("key2", "value2"));
            $config->addTag(new Tag("key3", "value3"));
            $this->ossClient->putObjectTagging($this->bucket, $object, $config);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
        
        try {
            $config2 = $this->ossClient->getObjectTagging($this->bucket, $object);
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
            $this->ossClient->deleteObjectTagging($this->bucket, $object);
            $config2 = $this->ossClient->getObjectTagging($this->bucket, $object);
            $this->assertEquals(0, count($config2->getTags()));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testPutObjectTaggingFromHeader()
    {
        $object = "object-tagging-header.txt";
        $content = "hello world";

        try {
            $options = array(
                OssClient::OSS_HEADERS => array(
                    'x-oss-tagging' => 'key1=value1&key2=value2&key3=value3',
            ));

            $this->ossClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
           
            $config2 = $this->ossClient->getObjectTagging($this->bucket, $object);
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
    }

    public function testAppendObjectTaggingFromHeader()
    {
        $object = "append-object-tagging-header.txt";
        $content_array = array('Hello OSS', 'Hi OSS', 'OSS OK');
        
        try {
            $options = array(
                OssClient::OSS_HEADERS => array(
                    'x-oss-tagging' => 'key1=value1&key2=value2&key3=value3',
            ));

            $position = $this->ossClient->appendObject($this->bucket, $object, $content_array[0], 0, $options);
            $this->assertEquals($position, strlen($content_array[0]));
            $position = $this->ossClient->appendObject($this->bucket, $object, $content_array[1], $position);
            $this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]));
            $position = $this->ossClient->appendObject($this->bucket, $object, $content_array[2], $position, array(OssClient::OSS_LENGTH => strlen($content_array[2])));
            $this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]) + strlen($content_array[2]));

            $config2 = $this->ossClient->getObjectTagging($this->bucket, $object);
            $this->assertEquals(3, count($config2->getTags()));
            $this->assertEquals("key1", $config2->getTags()[0]->getKey());
            $this->assertEquals("value1", $config2->getTags()[0]->getValue());
            $this->assertEquals("key2", $config2->getTags()[1]->getKey());
            $this->assertEquals("value2", $config2->getTags()[1]->getValue());
            $this->assertEquals("key3", $config2->getTags()[2]->getKey());
            $this->assertEquals("value3", $config2->getTags()[2]->getValue());

        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testMultipartUploadTaggingFromHeader()
    {
    	$file = __DIR__ . DIRECTORY_SEPARATOR . "/bigfile.tmp";
    	OssUtil::generateFile($file, 110 * 1024);

        $object = "mpu-object-tagging-header.txt";
    	$options = array(
    			OssClient::OSS_CHECK_MD5 => true,
                OssClient::OSS_PART_SIZE => 1,
                OssClient::OSS_HEADERS => array(
                    'x-oss-tagging' => 'key1=value1&key2=value2&key3=value3',
                ),
    	);
    	try {
    		$this->ossClient->multiuploadFile($this->bucket, $object, $file, $options);

            $config2 = $this->ossClient->getObjectTagging($this->bucket, $object);
            $this->assertEquals(3, count($config2->getTags()));
            $this->assertEquals("key1", $config2->getTags()[0]->getKey());
            $this->assertEquals("value1", $config2->getTags()[0]->getValue());
            $this->assertEquals("key2", $config2->getTags()[1]->getKey());
            $this->assertEquals("value2", $config2->getTags()[1]->getValue());
            $this->assertEquals("key3", $config2->getTags()[2]->getKey());
            $this->assertEquals("value3", $config2->getTags()[2]->getValue());
        } catch (OssException $e) {
    		$this->assertFalse(true);
        }
        
        unlink($file);
    }

}
