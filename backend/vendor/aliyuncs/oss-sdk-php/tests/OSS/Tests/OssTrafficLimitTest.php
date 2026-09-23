<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Http\RequestCore;
use OSS\Http\ResponseCore;
use OSS\OssClient;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssTrafficLimitTest extends TestOssClientBase
{
    function testTrafficLimitInHeader()
    {
        $options = array(
            OssClient::OSS_HEADERS => array(
                OssClient::OSS_TRAFFIC_LIMIT => 819200,
        ));

        try {
            $result = $this->ossClient->putObject($this->bucket, 'default-object', 'content', $options);
            $this->assertTrue(true);
            $this->assertTrue(isset($result["x-oss-qos-delay-time"]));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
    		$result = $this->ossClient->appendObject($this->bucket, 'append-object', 'content', 0, $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
    		$result = $this->ossClient->copyObject($this->bucket, 'default-object', $this->bucket, 'copy-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $result = $this->ossClient->getObject($this->bucket, 'default-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    function testTrafficLimitInQuery()
    {
        $options = array(
            OssClient::OSS_TRAFFIC_LIMIT => 819200,
        );

        $object = "get.file";
        $content = 'hello world';
        $this->ossClient->putObject($this->bucket, $object, $content);
        $timeout = 3600;
        try {
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, "GET", $options);
            $this->assertTrue(stripos($signedUrl, 'x-oss-traffic-limit=819200') > 0);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        $request = new RequestCore($signedUrl);
        $request->set_method('GET');
        $request->add_header('Content-Type', '');
        $request->send_request();
        $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
        $this->assertEquals($content, $res->body);


        $object = "put.file";
        $timeout = 3600;
        try {
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $timeout, "PUT", $options);
            $this->assertTrue(stripos($signedUrl, 'x-oss-traffic-limit=819200') > 0);

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
}
