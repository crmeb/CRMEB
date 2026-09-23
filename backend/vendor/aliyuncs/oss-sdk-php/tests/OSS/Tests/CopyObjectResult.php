<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Http\ResponseCore;
use OSS\Result\CopyObjectResult;

class CopyObjectResultTest extends \PHPUnit\Framework\TestCase
{
    private $body = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<CopyObjectResult>
  <LastModified>Fri, 24 Feb 2012 07:18:48 GMT</LastModified>
  <ETag>"5B3C1A2E053D763E1B002CC607C5A0FE"</ETag>
</CopyObjectResult>
BBBB;

    public function testNullResponse()
    {
        $response = null;
        try {
            new CopyObjectResult($response);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('raw response is null', $e->getMessage());
        }
    }

    public function testOkResponse()
    {
        $header= array();
        $response = new ResponseCore($header, $this->body, 200);
        $result = new CopyObjectResult($response);
        $data = $result->getData();
        $this->assertTrue($result->isOK());
        $this->assertEquals("Fri, 24 Feb 2012 07:18:48 GMT", $data[0]);
        $this->assertEquals("\"5B3C1A2E053D763E1B002CC607C5A0FE\"", $data[1]);
    }

    public function testFailResponse()
    {
        $response = new ResponseCore(array(), "", 404);
        try {
            new CopyObjectResult($response);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertFalse(false);
        }
    }

}
