<?php

namespace OSS\Tests;


use OSS\Result\GetWebsiteResult;
use OSS\Http\ResponseCore;
use OSS\Core\OssException;

class GetWebsiteResultTest extends \PHPUnit\Framework\TestCase
{
    private $validXml = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<WebsiteConfiguration>
<IndexDocument>
<Suffix>index.html</Suffix>
</IndexDocument>
<ErrorDocument>
<Key>errorDocument.html</Key>
</ErrorDocument>
</WebsiteConfiguration>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new GetWebsiteResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $websiteConfig = $result->getData();
        $this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($websiteConfig->serializeToXml()));
    }

    private function cleanXml($xml)
    {
        return str_replace("\n", "", str_replace("\r", "", $xml));
    }

    public function testInvalidResponse()
    {
        $response = new ResponseCore(array(), $this->validXml, 300);
        try {
            $result = new GetWebsiteResult($response);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
        }
    }
}
