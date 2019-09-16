<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Http\ResponseCore;
use OSS\Result\PutSetDeleteResult;

class ResultTest extends \PHPUnit_Framework_TestCase
{

    public function testNullResponse()
    {
        $response = null;
        try {
            new PutSetDeleteResult($response);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('raw response is null', $e->getMessage());
        }
    }

    public function testOkResponse()
    {
        $header= array(
            'x-oss-request-id' => '582AA51E004C4550BD27E0E4',
            'etag' => '595FA1EA77945233921DF12427F9C7CE',
            'content-md5' => 'WV+h6neUUjOSHfEkJ/nHzg==',
            'info' => array(
                'http_code' => '200',
                'method' => 'PUT'
            ),
        );
        $response = new ResponseCore($header, "this is a mock body, just for test", 200);
        $result = new PutSetDeleteResult($response);
        $data = $result->getData();
        $this->assertTrue($result->isOK());
        $this->assertEquals("this is a mock body, just for test", $data['body']);
        $this->assertEquals('582AA51E004C4550BD27E0E4', $data['x-oss-request-id']);
        $this->assertEquals('595FA1EA77945233921DF12427F9C7CE', $data['etag']);
        $this->assertEquals('WV+h6neUUjOSHfEkJ/nHzg==', $data['content-md5']);
        $this->assertEquals('200', $data['info']['http_code']);
        $this->assertEquals('PUT', $data['info']['method']);
    }

    public function testFailResponse()
    {
        $response = new ResponseCore(array(), "", 301);
        try {
            new PutSetDeleteResult($response);
            $this->assertFalse(true);
        } catch (OssException $e) {

        }
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }
}
