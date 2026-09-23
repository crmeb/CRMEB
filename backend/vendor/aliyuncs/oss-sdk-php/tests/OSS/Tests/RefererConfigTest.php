<?php

namespace OSS\Tests;


use OSS\Model\RefererConfig;

class RefererConfigTest extends \PHPUnit\Framework\TestCase
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

    private $validXml2 = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<RefererConfiguration>
<AllowEmptyReferer>true</AllowEmptyReferer>
<RefererList>
<Referer>http://www.aliyun.com</Referer>
</RefererList>
</RefererConfiguration>
BBBB;

    public function testParseValidXml()
    {
        $refererConfig = new RefererConfig();
        $refererConfig->parseFromXml($this->validXml);
        $this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($refererConfig->serializeToXml()));
    }

    public function testParseValidXml2()
    {
        $refererConfig = new RefererConfig();
        $refererConfig->parseFromXml($this->validXml2);
        $this->assertEquals(true, $refererConfig->isAllowEmptyReferer());
        $this->assertEquals(1, count($refererConfig->getRefererList()));
        $this->assertEquals($this->cleanXml($this->validXml2), $this->cleanXml(strval($refererConfig)));
    }

    private function cleanXml($xml)
    {
        return str_replace("\n", "", str_replace("\r", "", $xml));
    }
}
