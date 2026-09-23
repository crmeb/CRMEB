<?php

namespace OSS\Tests;

require_once __DIR__ . '/Common.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';

use OSS\Core\OssException;

class OssClientAsyncProcessObjectTest extends TestOssClientBase
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

        $url = 'https://oss-console-img-demo-cn-hangzhou.oss-cn-hangzhou.aliyuncs.com/video.mp4?spm=a2c4g.64555.0.0.515675979u4B8w&file=video.mp4';
        $file_name = "video.mp4";
        $fp = fopen($file_name, 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        $this->local_file = $file_name;
        $this->object = "oss-example.mp4";

        Common::waitMetaSync();
        $this->client->uploadFile($this->bucketName, $this->object, $this->local_file);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unlink($this->local_file);
    }

    public function testAsyncProcessObject()
    {

        try {
            $object = 'php-async-copy';
            $process = 'video/convert,f_avi,vcodec_h265,s_1920x1080,vb_2000000,fps_30,acodec_aac,ab_100000,sn_1'.
                '|sys/saveas'.
                ',o_'.$this->base64url_encode($object).
                ',b_'.$this->base64url_encode($this->bucketName);
            $result = $this->client->asyncProcessObject($this->bucketName, $this->object, $process);
        }catch (OssException $e){
            $this->assertEquals($e->getErrorCode(),"Imm Client");
            $this->assertTrue(strpos($e->getErrorMessage(), "ResourceNotFound, The specified resource Attachment is not found") !== false);
        }

    }

    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    }
}
