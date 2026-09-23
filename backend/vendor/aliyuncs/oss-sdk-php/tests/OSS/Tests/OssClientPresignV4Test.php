<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Credentials\StaticCredentialsProvider;
use OSS\Http\RequestCore;
use OSS\Http\ResponseCore;
use OSS\OssClient;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientPresignV4Test extends TestOssClientBase
{
    protected $stsOssClient;

    public function testObjectWithSignV4()
    {
        $object = "a.file";
        $this->ossClient->putObject($this->bucket, $object, file_get_contents(__FILE__));
        $timeout = 3600;
        try {
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        $request = new RequestCore($signedUrl);
        $request->set_method('GET');
        $request->add_header('Content-Type', '');
        $request->send_request();
        $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
        $this->assertEquals(file_get_contents(__FILE__), $res->body);
        sleep(1);

        //testGetSignedUrlForPuttingObject
        $object = "a.file";
        $timeout = 3600;
        try {
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, "PUT");
            $content = file_get_contents(__FILE__);
            $request = new RequestCore($signedUrl);
            $request->set_method('PUT');
            $request->add_header('Content-Type', '');
            $request->add_header('Content-Length', strlen($content));
            $request->set_body($content);
            $request->send_request();
            $res = new ResponseCore($request->get_response_header(),
                $request->get_response_body(), $request->get_response_code());
            $this->assertTrue($res->isOK());
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        sleep(1);

        // test Get SignedUrl For Putting Object From File
        $file = __FILE__;
        $object = "a.file";
        $timeout = 3600;
        $options = array('Content-Type' => 'txt');
        try {
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, "PUT", $options);
            $request = new RequestCore($signedUrl);
            $request->set_method('PUT');
            $request->add_header('Content-Type', 'txt');
            $request->set_read_file($file);
            $request->set_read_stream_size(sprintf('%u', filesize($file)));
            $request->send_request();
            $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
            $this->assertTrue($res->isOK());
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        sleep(1);
        // test SignedUrl With Exception
        $object = "a.file";
        $timeout = 3600;
        $options = array('Content-Type' => 'txt');
        try {
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, "POST", $options);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
            if (strpos($e, "method is invalid") == false) {
                $this->assertTrue(false);
            }
        }

        // test GetgenPreSignedUrl For GettingObject
        $object = "a.file";
        $this->ossClient->putObject($this->bucket, $object, file_get_contents(__FILE__));
        $expires = time() + 3600;
        try {
            $signedUrl = $this->ossClient->generatePresignedUrl($this->bucket, $object, $expires);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        $request = new RequestCore($signedUrl);
        $request->set_method('GET');
        $request->add_header('Content-Type', '');
        $request->send_request();
        $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
        $this->assertEquals(file_get_contents(__FILE__), $res->body);
        sleep(1);
        // test Get genPreSignedUrl Vs SignedUrl

        $object = "object-vs.file";
        $signedUrl1 = '245';
        $signedUrl2 = '123';
        $expiration = 0;

        do {
            usleep(500000);
            $begin = time();
            $expiration = time() + 3600;
            $signedUrl1 = $this->ossClient->generatePresignedUrl($this->bucket, $object, $expiration);
            $signedUrl2 = $this->ossClient->signUrl($this->bucket, $object, 3600);
            $end = time();
        } while ($begin != $end);
        $this->assertEquals($signedUrl1, $signedUrl2);
        $this->assertTrue(strpos($signedUrl1, 'x-oss-expires=') !== false);

        $object = "a.file";
        $options = array(
            OssClient::OSS_HEADERS => array(
                'name' => 'aliyun',
                'email' => 'aliyun@aliyun.com',
                'book' => 'english',
            ),
            OssClient::OSS_ADDITIONAL_HEADERS => array("name", "email")
        );
        $this->ossClient->putObject($this->bucket, $object, file_get_contents(__FILE__), $options);
        $expires = time() + 3600;
        try {
            $signedUrl = $this->ossClient->generatePresignedUrl($this->bucket, $object, $expires, "GET", $options);
            $request = new RequestCore($signedUrl);
            $request->set_method('GET');
            $request->add_header('Content-Type', '');
            $request->add_header('name', 'aliyun');
            $request->add_header('email', 'aliyun@aliyun.com');
            $request->send_request();
            $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
            $this->assertEquals(file_get_contents(__FILE__), $res->body);
            sleep(1);
        } catch (OssException $e) {
            print_r($e->getMessage());
            $this->assertFalse(true);
        }


        try {
            $signedUrl = $this->ossClient->generatePresignedUrl($this->bucket, $object, $expires);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        $request = new RequestCore($signedUrl);
        $request->set_method('GET');
        $request->add_header('Content-Type', '');
        $request->send_request();
        $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
        $this->assertEquals(file_get_contents(__FILE__), $res->body);
        sleep(1);


    }

    public function testObjectWithStsClientSignV4()
    {
        $object = "a.file";
        $this->stsOssClient->putObject($this->bucket, $object, file_get_contents(__FILE__));
        $timeout = 3600;
        try {
            $signedUrl = $this->stsOssClient->signUrl($this->bucket, $object, $timeout);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        $request = new RequestCore($signedUrl);
        $request->set_method('GET');
        $request->add_header('Content-Type', '');
        $request->send_request();
        $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
        $this->assertEquals(file_get_contents(__FILE__), $res->body);
        sleep(1);

        //testGetSignedUrlForPuttingObject
        $object = "a.file";
        $timeout = 3600;
        try {
            $signedUrl = $this->stsOssClient->signUrl($this->bucket, $object, $timeout, "PUT");
            $content = file_get_contents(__FILE__);
            $request = new RequestCore($signedUrl);
            $request->set_method('PUT');
            $request->add_header('Content-Type', '');
            $request->add_header('Content-Length', strlen($content));
            $request->set_body($content);
            $request->send_request();
            $res = new ResponseCore($request->get_response_header(),
                $request->get_response_body(), $request->get_response_code());
            $this->assertTrue($res->isOK());
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        sleep(1);

        // test Get SignedUrl For Putting Object From File
        $file = __FILE__;
        $object = "a.file";
        $timeout = 3600;
        $options = array('Content-Type' => 'txt');
        try {
            $signedUrl = $this->stsOssClient->signUrl($this->bucket, $object, $timeout, "PUT", $options);
            $request = new RequestCore($signedUrl);
            $request->set_method('PUT');
            $request->add_header('Content-Type', 'txt');
            $request->set_read_file($file);
            $request->set_read_stream_size(sprintf('%u', filesize($file)));
            $request->send_request();
            $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
            $this->assertTrue($res->isOK());
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        sleep(1);
        // test SignedUrl With Exception
        $file = __FILE__;
        $object = "a.file";
        $timeout = 3600;
        $options = array('Content-Type' => 'txt');
        try {
            $signedUrl = $this->stsOssClient->signUrl($this->bucket, $object, $timeout, "POST", $options);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
            if (strpos($e, "method is invalid") == false) {
                $this->assertTrue(false);
            }
        }

        // test GetgenPreSignedUrl For GettingObject
        $object = "a.file";
        $this->stsOssClient->putObject($this->bucket, $object, file_get_contents(__FILE__));
        $expires = time() + 3600;
        try {
            $signedUrl = $this->stsOssClient->generatePresignedUrl($this->bucket, $object, $expires);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        try {
            $request = new RequestCore($signedUrl);
            $request->set_method('GET');
            $request->add_header('Content-Type', '');
            $request->send_request();
            $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
            $this->assertEquals(file_get_contents(__FILE__), $res->body);
            sleep(1);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        // test Get genPreSignedUrl Vs SignedUrl

        $object = "object-vs.file";

        do {
            usleep(500000);
            $begin = time();
            $expiration = time() + 3600;
            $signedUrl1 = $this->stsOssClient->generatePresignedUrl($this->bucket, $object, $expiration);
            $signedUrl2 = $this->stsOssClient->signUrl($this->bucket, $object, 3600);
            $end = time();
        } while ($begin != $end);
        $this->assertEquals($signedUrl1, $signedUrl2);
        $this->assertTrue(strpos($signedUrl1, 'x-oss-expires=') !== false);

        $object = "a.file";
        $options = array(
            OssClient::OSS_HEADERS => array(
                'name' => 'aliyun',
                'email' => 'aliyun@aliyun.com',
                'book' => 'english',
            ),
            OssClient::OSS_ADDITIONAL_HEADERS => array("name", "email")
        );
        $this->stsOssClient->putObject($this->bucket, $object, file_get_contents(__FILE__), $options);
        $expires = time() + 3600;
        try {
            $signedUrl = $this->stsOssClient->generatePresignedUrl($this->bucket, $object, $expires, "GET", $options);
            $request = new RequestCore($signedUrl);
            $request->set_method('GET');
            $request->add_header('Content-Type', '');
            $request->add_header('name', 'aliyun');
            $request->add_header('email', 'aliyun@aliyun.com');
            $request->send_request();
            $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
            $this->assertEquals(file_get_contents(__FILE__), $res->body);
            sleep(1);
        } catch (OssException $e) {
            print_r($e->getMessage());
            $this->assertFalse(true);
        }
    }

    public function testObjectWithSignV4AndResponseQuery()
    {
        $config = array(
            'signatureVersion' => OssClient::OSS_SIGNATURE_VERSION_V4
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
            "response-content-disposition" => "attachment"
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
        $config = array(
            'signatureVersion' => OssClient::OSS_SIGNATURE_VERSION_V4
        );
        $this->bucket = Common::getBucketName() . '-' . time();
        $this->ossClient = Common::getOssClient($config);
        $this->ossClient->createBucket($this->bucket);
        Common::waitMetaSync();
        $this->stsOssClient = Common::getStsOssClient($config);
        Common::waitMetaSync();
    }
}
