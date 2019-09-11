<?php

namespace OSS\Tests;

use OSS\Result\GetRefererResult;
use OSS\Http\ResponseCore;
use OSS\Core\OssException;


class GetRefererResultTest extends \PHPUnit_Framework_TestCase
{
    private $validXml = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<RefererConfiguration>
<AllowEmptyReferer>true</AllowEmptyReferer>
<RefererList>
<Referer>http://www.aliyun.com</Referer>
<Referer>https://www.aliyun.com</Referer>
<Referer>http://www.*.com</Referer>
<Referer>https://www.?.aliyuncs.com</Referer>
</RefererList>
</RefererConfiguration>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new GetRefererResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $refererConfig = $result->getData();
        $this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($refererConfig->serializeToXml()));
    }

    private function cleanXml($xml)
    {
        return str_replace("\n", "", str_replace("\r", "", $xml));
    }

    public function testInvalidResponse()
    {
        $response = new ResponseCore(array(), $this->validXml, 300);
        try {
            new GetRefererResult($response);
            $this->assertTrue(false);
        } catch (OssException $e) {

        }
    }
}
