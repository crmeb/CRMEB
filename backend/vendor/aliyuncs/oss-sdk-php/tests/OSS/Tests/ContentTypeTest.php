<?php

namespace OSS\Tests;

use OSS\Core\OssUtil;
use OSS\OssClient;

require_once __DIR__ . '/Common.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';

class ContentTypeTest extends TestOssClientBase
{
    private function getContentType($bucket, $object)
    {
        $client = $this->ossClient;
        $headers = $client->getObjectMeta($bucket, $object);
        return $headers['content-type'];
    }

    public function testByFileName()
    {
        $client = $this->ossClient;
        $bucket = $this->bucket;

        $file = __DIR__ . DIRECTORY_SEPARATOR . 'x.html';
        $object = 'test/x';
        OssUtil::generateFile($file, 5);

        $client->uploadFile($bucket, $object, $file);
        $type = $this->getContentType($bucket, $object);
        $this->assertEquals('text/html', $type);
        unlink($file);

        $file = __DIR__ . DIRECTORY_SEPARATOR . 'x.json';
        $object = 'test/y';
        OssUtil::generateFile($file, 100 * 1024);

        $client->multiuploadFile($bucket, $object, $file, array('partSize' => 100));
        $type = $this->getContentType($bucket, $object);
        unlink($file);
        $this->assertEquals('application/json', $type);
    }

    public function testByObjectKey()
    {
        $client = $this->ossClient;
        $bucket = $this->bucket;

        $object = "test/x.txt";
        $client->putObject($bucket, $object, "hello world");
        $type = $this->getContentType($bucket, $object);
        $this->assertEquals('text/plain', $type);

        $file = __DIR__ . DIRECTORY_SEPARATOR . 'x.html';
        $object = 'test/x.txt';
        OssUtil::generateFile($file, 5);
        $client->uploadFile($bucket, $object, $file);
        unlink($file);
        $type = $this->getContentType($bucket, $object);
        $this->assertEquals('text/html', $type);

        $file = __DIR__ . DIRECTORY_SEPARATOR . 'x.none';
        $object = 'test/x.txt';
        OssUtil::generateFile($file, 5);
        $client->uploadFile($bucket, $object, $file);
        unlink($file);
        $type = $this->getContentType($bucket, $object);
        $this->assertEquals('text/plain', $type);

        $file = __DIR__ . DIRECTORY_SEPARATOR . 'x.mp3';
        OssUtil::generateFile($file, 1024 * 100);
        $object = 'test/y.json';
        $client->multiuploadFile($bucket, $object, $file, array('partSize' => 100));
        unlink($file);
        $type = $this->getContentType($bucket, $object);
        $this->assertEquals('audio/mpeg', $type);
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'x.none';
        OssUtil::generateFile($file, 1024 * 100);
        $object = 'test/y.json';
        $client->multiuploadFile($bucket, $object, $file, array('partSize' => 100));
        unlink($file);
        $type = $this->getContentType($bucket, $object);
        $this->assertEquals('application/json', $type);
    }

    public function testByUser()
    {
        $client = $this->ossClient;
        $bucket = $this->bucket;

        $object = "test/x.txt";
        $client->putObject($bucket, $object, "hello world", array(
            'Content-Type' => 'text/html'
        ));
        $type = $this->getContentType($bucket, $object);

        $this->assertEquals('text/html', $type);

        $file = __DIR__ . DIRECTORY_SEPARATOR . 'x.html';
        $object = 'test/x';
        OssUtil::generateFile($file, 100);

        $client->uploadFile($bucket, $object, $file, array(OssClient::OSS_HEADERS => array(
            'Content-Type' => 'application/json'
        )));
        unlink($file);
        $type = $this->getContentType($bucket, $object);

        $this->assertEquals('application/json', $type);

        $file = __DIR__ . DIRECTORY_SEPARATOR . 'x.json';
        $object = 'test/y';
        OssUtil::generateFile($file, 100 * 1024);

        $client->multiuploadFile($bucket, $object, $file, array(
            'partSize' => 100,
            'Content-Type' => 'audio/mpeg'
        ));
        unlink($file);
        $type = $this->getContentType($bucket, $object);
        $this->assertEquals('audio/mpeg', $type);
    }
}
