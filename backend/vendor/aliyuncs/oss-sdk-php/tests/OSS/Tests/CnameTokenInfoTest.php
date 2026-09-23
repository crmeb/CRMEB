<?php

namespace OSS\Tests;


use OSS\Model\CnameTokenInfo;
use OSS\Core\OssException;

class CnameTokenInfoTest extends \PHPUnit\Framework\TestCase
{
    private $xml1 = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<CnameToken>
  <Bucket>bucket</Bucket>
  <Cname>www.foo.com</Cname>
  <Token>1234</Token>
  <ExpireTime>20150101</ExpireTime>
</CnameToken>
BBBB;

    public function testFromXml()
    {
        $info = new CnameTokenInfo();
        $info->parseFromXml($this->xml1);

        $this->assertEquals('bucket', $info->getBucket());
        $this->assertEquals('www.foo.com', $info->getCname());
        $this->assertEquals('1234', $info->getToken());
        $this->assertEquals('20150101', $info->getExpireTime());
    }
}
