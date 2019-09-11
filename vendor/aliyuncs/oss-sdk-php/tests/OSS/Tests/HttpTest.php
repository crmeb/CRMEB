<?php

namespace OSS\Tests;

use OSS\Http\RequestCore;
use OSS\Http\ResponseCore;
use OSS\Http\RequestCore_Exception;
use Symfony\Component\Config\Definition\Exception\Exception;

class HttpTest extends \PHPUnit_Framework_TestCase
{

    public function testResponseCore()
    {
        $res = new ResponseCore(null, "", 500);
        $this->assertFalse($res->isOK());
        $this->assertTrue($res->isOK(500));
    }

    public function testGet()
    {
        $httpCore = new RequestCore("http://www.baidu.com");
        $httpResponse = $httpCore->send_request();
        $this->assertNotNull($httpResponse);
    }

    public function testSetProxyAndTimeout()
    {
        $httpCore = new RequestCore("http://www.baidu.com");
        $httpCore->set_proxy("1.0.2.1:8888");
        $httpCore->connect_timeout = 1;
        try {
            $httpResponse = $httpCore->send_request();
            $this->assertTrue(false);
        } catch (RequestCore_Exception $e) {

        }
    }

    public function testGetParseTrue()
    {
        $httpCore = new RequestCore("http://www.baidu.com");
        $httpCore->curlopts = array(CURLOPT_HEADER => true);
        $url = $httpCore->send_request(true);
        foreach ($httpCore->get_response_header() as $key => $value) {
            $this->assertEquals($httpCore->get_response_header($key), $value);
        }
        $this->assertNotNull($url);
    }

    public function testParseResponse()
    {
        $httpCore = new RequestCore("http://www.baidu.com");
        $response = $httpCore->send_request();
        $parsed = $httpCore->process_response(null, $response);
        $this->assertNotNull($parsed);
    }

    public function testExceptionGet()
    {
        $httpCore = null;
        $exception = false;
        try {
            $httpCore = new RequestCore("http://www.notexistsitexx.com");
            $httpCore->set_body("");
            $httpCore->set_method("GET");
            $httpCore->connect_timeout = 10;
            $httpCore->timeout = 10;
            $res = $httpCore->send_request();
        } catch (RequestCore_Exception $e) {
            $exception = true;
        }
        $this->assertTrue($exception);
    }
}


