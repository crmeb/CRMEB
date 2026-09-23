<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Credentials\StaticCredentialsProvider;
use OSS\Http\RequestCore;
use OSS\Http\ResponseCore;
use OSS\OssClient;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientPresignTest extends TestOssClientBase
{
    protected $stsOssClient;

    public function testObjectWithSignV1()
    {
        $config = array(
            'signatureVersion' => OssClient::OSS_SIGNATURE_VERSION_V1
        );
        $this->bucket = Common::getBucketName() . '-' . time();
        $this->ossClient = Common::getOssClient($config);
        $this->ossClient->createBucket($this->bucket);
        Common::waitMetaSync();

        $object = "a.file";
        $this->ossClient->putObject($this->bucket, $object, "hi oss");
        $timeout = 3600;
        $options = array(
            "response-content-disposition" => "inline"
        );
        try {
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, OssClient::OSS_HTTP_GET, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        $this->assertStringContainsString("response-content-disposition=inline", $signedUrl);
        $options = array(
            "response-content-disposition" => "attachment",
        );

        try {
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, OssClient::OSS_HTTP_GET, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        $this->assertStringContainsString("response-content-disposition=attachment", $signedUrl);

        $httpCore = new RequestCore($signedUrl);
        $httpCore->set_body("");
        $httpCore->set_method("GET");
        $httpCore->connect_timeout = 10;
        $httpCore->timeout = 10;
        $httpCore->add_header("Content-Type", "");
        $httpCore->send_request();
        $this->assertEquals(200, $httpCore->response_code);        
    }

    protected function tearDown(): void
    {
        $this->ossClient->deleteObject($this->bucket, "a.file");
        parent::tearDown();
    }

    protected function setUp(): void
    {
    }
}
