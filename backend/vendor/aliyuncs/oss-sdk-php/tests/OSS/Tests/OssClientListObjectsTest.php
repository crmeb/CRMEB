<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\OssClient;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientListObjectsTest extends TestOssClientBase
{
    public function testListObjectsDefault()
    {
        try {
            $listObjectInfo = $this->ossClient->listObjects($this->bucket);
            $objectList = $listObjectInfo->getObjectList();
            $prefixList = $listObjectInfo->getPrefixList();
            $this->assertNotNull($objectList);
            $this->assertNotNull($prefixList);
            $this->assertTrue(is_array($objectList));
            $this->assertTrue(is_array($prefixList));
            $this->assertEquals((2), count($objectList));
            $this->assertEquals(4, count($prefixList));

            $this->assertEquals('file++00', $objectList[0]->getKey());
            $this->assertEquals('file++01', $objectList[1]->getKey());

            $this->assertEquals('folder/', $prefixList[0]->getPrefix());
            $this->assertEquals('sub++/', $prefixList[1]->getPrefix());
            $this->assertEquals('test/', $prefixList[2]->getPrefix());
            $this->assertEquals('work/', $prefixList[3]->getPrefix());

        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testListObjectsWithPrefix()
    {
        /**
         * List the files in your bucket.
         */
        $prefix = 'folder/';
        $delimiter = '';
        $next_marker = '';
        $maxkeys = 1000;
        $options = array(
            'delimiter' => $delimiter,
            'prefix' => $prefix,
            'max-keys' => $maxkeys,
            'marker' => $next_marker,
        );

        try {
            $listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
            $objectList = $listObjectInfo->getObjectList();
            $prefixList = $listObjectInfo->getPrefixList();
            $this->assertNotNull($objectList);
            $this->assertNotNull($prefixList);
            $this->assertTrue(is_array($objectList));
            $this->assertTrue(is_array($prefixList));
            $this->assertEquals(12, count($objectList));
            $this->assertEquals(0, count($prefixList));

            $this->assertEquals('folder/00', $objectList[0]->getKey());
            $this->assertEquals('folder/01', $objectList[1]->getKey());
            $this->assertEquals('folder/11', $objectList[11]->getKey());

        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testListObjectsWithMaxKeysAndMarker()
    {
        $count = 0;
        $nextMarker = '';

        while (true) {
            try {
                $options = array(
                    'delimiter' => '',
                    'marker' => $nextMarker,
                    'max-keys' => 2,
                );
                $listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
            } catch (OssException $e) {
                $this->assertTrue(false);
            }
            $nextMarker = $listObjectInfo->getNextMarker();
            $listObject = $listObjectInfo->getObjectList();
            $count += count($listObject);
            $this->assertEquals(2, count($listObject));
            if ($listObjectInfo->getIsTruncated() !== "true") {
                break;
            }
        }
        $this->assertEquals(12 + 8 + 5 + 3 + 2, $count);
    }

    public function testListObjectsWithMarker()
    {
        $count = 0;
        $nextMarker = 'h';

        while (true) {
            try {
                $options = array(
                    'delimiter' => '',
                    'marker' => $nextMarker,
                    'max-keys' => 1,
                );
                $listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
            } catch (OssException $e) {
                $this->assertTrue(false);
            }
            $nextMarker = $listObjectInfo->getNextMarker();
            $listObject = $listObjectInfo->getObjectList();
            $count += count($listObject);
            $this->assertEquals(1, count($listObject));
            if ($listObjectInfo->getIsTruncated() !== "true") {
                break;
            }
        }
        $this->assertEquals(8 + 5 + 3, $count);


        $nextMarker = 'h';

        try {
            $options = array(
                'delimiter' => '',
                'marker' => $nextMarker,
                'max-keys' => 5,
            );
            $listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
        $nextMarker = $listObjectInfo->getNextMarker();
        $listObject = $listObjectInfo->getObjectList();
        $this->assertEquals('test/01', $nextMarker);
        $this->assertEquals(5, count($listObject));
        $this->assertEquals("true", $listObjectInfo->getIsTruncated());
    }

    protected function setUp(): void
    {
        parent::setUp();
        //folder 
        for ($i = 0; $i < 12; $i++) {
            $key = 'folder/'. sprintf("%02d",$i);
            $this->ossClient->putObject($this->bucket, $key, "content");
        }
        //test
        for ($i = 0; $i < 8; $i++) {
            $key = 'test/'. sprintf("%02d",$i);
            $this->ossClient->putObject($this->bucket, $key, "content");
        }
        //work
        for ($i = 0; $i < 5; $i++) {
            $key = 'work/'. sprintf("%02d",$i);
            $this->ossClient->putObject($this->bucket, $key, "content");
        }
        //sub++
        for ($i = 0; $i < 3; $i++) {
            $key = 'sub++/'. sprintf("%02d",$i);
            $this->ossClient->putObject($this->bucket, $key, "content");
        }

        //file++
        for ($i = 0; $i < 2; $i++) {
            $key = 'file++'. sprintf("%02d",$i);
            $this->ossClient->putObject($this->bucket, $key, "content");
        }

    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
