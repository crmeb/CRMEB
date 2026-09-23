<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Http\RequestCore;
use OSS\OssClient;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientForcePathStyleTest extends TestOssClientBase
{
    public function testForcePathStyle()
    {
        $config = array(
            'signatureVersion' => OssClient::OSS_SIGNATURE_VERSION_V4,
            'forcePathStyle' => true,
        );

        $pathStyleClient = Common::getOssClient($config);

        try {
            $pathStyleClient->getBucketInfo($this->bucket);
            $this->assertTrue(false, "should not here");
        } catch (OssException $e) {
            $this->assertEquals($e->getErrorCode(), "SecondLevelDomainForbidden");
            $this->assertTrue(true);
        }

        try {
            $object = "oss-php-sdk-test/upload-test-object-name.txt";
            $pathStyleClient->putObject($this->bucket, $object, 'hi oss');
            $this->assertTrue(false, "should not here");
        } catch (OssException $e) {
            $this->assertEquals($e->getErrorCode(), "SecondLevelDomainForbidden");
            $this->assertTrue(true);
        }

        try {
            $endpoint = Common::getEndpoint();
            $endpoint = str_replace(array('http://', 'https://'), '', $endpoint);
            $strUrl = $endpoint . "/" . $this->bucket . '/' .  $object;
            $signUrl = $pathStyleClient->signUrl($this->bucket, $object, 3600);
            $this->assertTrue(strpos($signUrl, $strUrl) !== false);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testForcePathStyleOKV1()
    {
        $bucket = Common::getPathStyleBucket();

        $this->assertFalse(empty($bucket), "path style bucket is not set.");

        $config = array(
            'signatureVersion' => OssClient::OSS_SIGNATURE_VERSION_V1,
            'forcePathStyle' => true,
        );

        $pathStyleClient = Common::getOssClient($config);

        // bucket 
        $info = $pathStyleClient->getBucketInfo($bucket);
        $this->assertEquals($bucket, $info->getName());

        // object
        $object = "upload-test-object-name.txt";
        $pathStyleClient->putObject($bucket, $object, 'hi oss');
        $res = $pathStyleClient->getObject($bucket, $object);
        $this->assertEquals($res, 'hi oss');

        //presign
        $signUrl = $pathStyleClient->signUrl($bucket, $object, 3600);

        $httpCore = new RequestCore($signUrl);
        $httpCore->set_body("");
        $httpCore->set_method("GET");
        $httpCore->connect_timeout = 10;
        $httpCore->timeout = 10;
        $httpCore->add_header("Content-Type", "");
        $httpCore->send_request();
        $this->assertEquals(200, $httpCore->response_code);
    }

    public function testForcePathStyleOKV4()
    {
        $bucket = Common::getPathStyleBucket();

        $this->assertFalse(empty($bucket), "path style bucket is not set.");

        $config = array(
            'signatureVersion' => OssClient::OSS_SIGNATURE_VERSION_V4,
            'forcePathStyle' => true,
        );

        $pathStyleClient = Common::getOssClient($config);

        // bucket 
        $info = $pathStyleClient->getBucketInfo($bucket);
        $this->assertEquals($bucket, $info->getName());

        // object
        $object = "upload-test-object-name.txt";
        $pathStyleClient->putObject($bucket, $object, 'hi oss');
        $res = $pathStyleClient->getObject($bucket, $object);
        $this->assertEquals($res, 'hi oss');

        //presign
        $signUrl = $pathStyleClient->signUrl($bucket, $object, 3600);

        #print("signUrl" . $signUrl . "\n");

        $httpCore = new RequestCore($signUrl);
        $httpCore->set_body("");
        $httpCore->set_method("GET");
        $httpCore->connect_timeout = 10;
        $httpCore->timeout = 10;
        $httpCore->add_header("Content-Type", "");
        $httpCore->send_request();
        $this->assertEquals(200, $httpCore->response_code);
    }    
}
