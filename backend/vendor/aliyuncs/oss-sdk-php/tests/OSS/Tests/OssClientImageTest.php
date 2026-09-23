<?php

namespace OSS\Tests;

require_once __DIR__ . '/Common.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';

use OSS\OssClient;

class OssClientImageTest extends TestOssClientBase
{
    private $bucketName;
    private $client;
    private $local_file;
    private $object;
    private $download_file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->ossClient;
        $this->bucketName = $this->bucket;
        $this->local_file = "example.jpg";
        $this->object = "oss-example.jpg";
        $this->download_file = "image.jpg";

        Common::waitMetaSync();
        $this->client->uploadFile($this->bucketName, $this->object, $this->local_file);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unlink($this->download_file);
    }

    public function testImageResize()
    {
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $this->download_file,
            OssClient::OSS_PROCESS => "image/resize,m_fixed,h_100,w_100",);
        $this->check($options, 100, 100, 3267, 'jpg');
    }

    public function testImageCrop()
    {
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $this->download_file,
            OssClient::OSS_PROCESS => "image/crop,w_100,h_100,x_100,y_100,r_1",);
        $this->check($options, 100, 100, 1969, 'jpg');
    }

    public function testImageRotate()
    {
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $this->download_file,
            OssClient::OSS_PROCESS => "image/rotate,90",);
        $this->check($options, 267, 400, 20998, 'jpg');
    }

    public function testImageSharpen()
    {
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $this->download_file,
            OssClient::OSS_PROCESS => "image/sharpen,100",);
        $this->check($options, 400, 267, 23015, 'jpg');
    }

    public function testImageWatermark()
    {
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $this->download_file,
            OssClient::OSS_PROCESS => "image/watermark,text_SGVsbG8g5Zu-54mH5pyN5YqhIQ",);
        $this->check($options, 400, 267, 26369, 'jpg');
    }

    public function testImageFormat()
    {
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $this->download_file,
            OssClient::OSS_PROCESS => "image/format,png",);
        $this->check($options, 400, 267, 160733, 'png');
    }

    public function testImageTofile()
    {
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $this->download_file,
            OssClient::OSS_PROCESS => "image/resize,m_fixed,w_100,h_100",);
        $this->check($options, 100, 100, 3267, 'jpg');
    }

    public function testProcesObject()
    {
        $object = 'process-object.jpg';
        $process = 'image/resize,m_fixed,w_100,h_100' .
            '|sys/saveas' .
            ',o_' . $this->base64url_encode($object) .
            ',b_' . $this->base64url_encode($this->bucketName);
        $result = $this->client->processObject($this->bucketName, $this->object, $process);
        $this->assertTrue(stripos($result, '"object": "process-object.jpg",') > 0);
        $this->assertTrue(stripos($result, '"status": "OK"') > 0);


        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $this->download_file,
        );
        $this->client->getObject($this->bucketName, $object, $options);
        $array = getimagesize($this->download_file);
        $this->assertEquals(100, $array[0]);
        $this->assertEquals(100, $array[1]);
        $this->assertEquals(2, $array[2]);

        //without bucket
        $object = 'process-object-1.jpg';
        $process = 'image/watermark,text_SGVsbG8g5Zu-54mH5pyN5YqhIQ' .
            '|sys/saveas' .
            ',o_' . $this->base64url_encode($object);
        $result = $this->client->processObject($this->bucketName, $this->object, $process);
        $this->assertTrue(stripos($result, '"object": "process-object-1.jpg",') > 0);
        $this->assertTrue(stripos($result, '"status": "OK"') > 0);


        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $this->download_file,
        );
        $this->client->getObject($this->bucketName, $object, $options);
        $array = getimagesize($this->download_file);
        $this->assertEquals(400, $array[0]);
        $this->assertEquals(267, $array[1]);
        $this->assertEquals(2, $array[2]);
    }

    private function check($options, $width, $height, $size, $type)
    {
        $this->client->getObject($this->bucketName, $this->object, $options);
        $array = getimagesize($this->download_file);
        $this->assertEquals($width, $array[0]);
        $this->assertEquals($height, $array[1]);
        $this->assertEquals($type === 'jpg' ? 2 : 3, $array[2]);//2 <=> jpg
    }

    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
