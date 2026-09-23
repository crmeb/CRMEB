<?php

namespace OSS\Tests;

use http\Client;
use OSS\Core\OssException;
use OSS\Http\RequestCore;
use OSS\Http\ResponseCore;
use OSS\OssClient;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientSignatureTest extends TestOssClientBase
{
    public function testGetSignedUrlForGettingObject()
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
    }

    public function testGetSignedUrlForPuttingObject()
    {
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
    }

    public function testGetSignedUrlForPuttingObjectFromFile()
    {
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
            $request->set_read_stream_size(sprintf('%u',filesize($file)));
            $request->send_request();
            $res = new ResponseCore($request->get_response_header(),
                $request->get_response_body(), $request->get_response_code());
            $this->assertTrue($res->isOK());
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

    }

    public function testSignedUrlWithException()
    {
        $file = __FILE__;
        $object = "a.file";
        $timeout = 3600;
        $options = array('Content-Type' => 'txt');
        try {
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, "POST", $options);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
            if (strpos($e, "method is invalid") == false)
            {
                $this->assertTrue(false);
            }
        }

        $object = "?a.file";
        $timeout = 3600;
        $options = array('Content-Type' => 'txt');
        try {
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, "PUT", $options);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
            if (strpos($e, "object name cannot start with `?`") == false)
            {
                $this->assertTrue(false);
            }
        }

        // Set StrictObjectName false
        $object = "?a.file";
        $timeout = 3600;
        $options = array('Content-Type' => 'txt');
        $config = array(
            'strictObjectName' => false
        );
        $ossClient = Common::getOssClient($config);
        try {
            $signedUrl = $ossClient->signUrl($this->bucket, $object, $timeout, "PUT", $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            print_r($e->getMessage());
            $this->assertFalse(true);
        }

        // V4
        $object = "?a.file";
        $timeout = 3600;
        $options = array('Content-Type' => 'txt');
        $config = array(
            'signatureVersion' => OssClient::OSS_SIGNATURE_VERSION_V4
        );
        $ossClient = Common::getOssClient($config);
        try {
            $signedUrl = $ossClient->signUrl($this->bucket, $object, $timeout, "PUT", $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            print_r($e->getMessage());
            $this->assertFalse(true);
        }
    }

    function testGetgenPreSignedUrlForGettingObject()
    {
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
    }

    function testGetgenPreSignedUrlVsSignedUrl()
    {
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
        $this->assertTrue(strpos($signedUrl1, 'Expires='.$expiration) !== false);
    }

    public function testPutObjectWithQueryCallback()
    {
        $object = "a.file";
        $timeout = 3600;
        $url = '{"callbackUrl":"http://aliyun.com", "callbackBody":"bucket=${bucket}&object=${object}"}';
        $var =
            '{
        "x:var1":"value1",
        "x:var2":"value2"
    }';
        try {
            $options[OssClient::OSS_QUERY_STRING] = array(
                'callback'=>base64_encode($url),
                'callback-var'=>base64_encode($var)
            );
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, "PUT", $options);
            $content = file_get_contents(__FILE__);
            $request = new RequestCore($signedUrl);
            $request->set_method('PUT');
            $request->add_header('Content-Type', '');
            $request->add_header('Content-Length', strlen($content));
            $request->set_body($content);
            $request->send_request();
            $res = new ResponseCore($request->get_response_header(),
                $request->get_response_body(), $request->get_response_code());
            $this->assertEquals($res->status, 203);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        try {
            $options = array(OssClient::OSS_CALLBACK => $url,
                OssClient::OSS_CALLBACK_VAR => $var
            );
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, "PUT", $options);
            $content = file_get_contents(__FILE__);
            $request = new RequestCore($signedUrl);
            $request->set_method('PUT');
            $request->add_header('Content-Type', '');
            $request->add_header(OssClient::OSS_CALLBACK, base64_encode($url));
            $request->add_header(OssClient::OSS_CALLBACK_VAR , base64_encode($var));
            $request->add_header('Content-Length', strlen($content));
            $request->set_body($content);
            $request->send_request();
            $res = new ResponseCore($request->get_response_header(),
                $request->get_response_body(), $request->get_response_code());
            $this->assertEquals($res->status, 203);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }


    protected function tearDown(): void
    {
        $this->ossClient->deleteObject($this->bucket, "a.file");
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();
        /**
         *  上传本地变量到bucket
         */
        $object = "a.file";
        $content = file_get_contents(__FILE__);
        $options = array(
            OssClient::OSS_LENGTH => strlen($content),
            OssClient::OSS_HEADERS => array(
                'Expires' => 'Fri, 28 Feb 2020 05:38:42 GMT',
                'Cache-Control' => 'no-cache',
                'Content-Disposition' => 'attachment;filename=oss_download.log',
                'Content-Encoding' => 'utf-8',
                'Content-Language' => 'zh-CN',
                'x-oss-server-side-encryption' => 'AES256',
                'x-oss-meta-self-define-title' => 'user define meta info',
            ),
        );

        try {
            $this->ossClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }
}
