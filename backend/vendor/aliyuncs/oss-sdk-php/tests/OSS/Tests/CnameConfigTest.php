<?php

namespace OSS\Tests;


use OSS\Model\CnameConfig;
use OSS\Core\OssException;

class CnameConfigTest extends \PHPUnit\Framework\TestCase
{
    private $xml1 = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<BucketCnameConfiguration>
  <Cname>
    <Domain>www.foo.com</Domain>
    <Status>enabled</Status>
    <LastModified>20150101</LastModified>
  </Cname>
  <Cname>
    <Domain>bar.com</Domain>
    <Status>disabled</Status>
    <LastModified>20160101</LastModified>
  </Cname>
</BucketCnameConfiguration>
BBBB;

    public function testFromXml()
    {
        $cnameConfig = new CnameConfig();
        $cnameConfig->parseFromXml($this->xml1);

        $cnames = $cnameConfig->getCnames();
        $this->assertEquals(2, count($cnames));
        $this->assertEquals('www.foo.com', $cnames[0]['Domain']);
        $this->assertEquals('enabled', $cnames[0]['Status']);
        $this->assertEquals('20150101', $cnames[0]['LastModified']);

        $this->assertEquals('bar.com', $cnames[1]['Domain']);
        $this->assertEquals('disabled', $cnames[1]['Status']);
        $this->assertEquals('20160101', $cnames[1]['LastModified']);
    }

    public function testToXml()
    {
        $cnameConfig = new CnameConfig();
        $cnameConfig->addCname('www.foo.com');
        $cnameConfig->addCname('bar.com');

        $xml = $cnameConfig->serializeToXml();
        $comp = new CnameConfig();
        $comp->parseFromXml($xml);

        $cnames1 = $cnameConfig->getCnames();
        $cnames2 = $comp->getCnames();

        $this->assertEquals(count($cnames1), count($cnames2));
        $this->assertEquals(count($cnames1[0]), count($cnames2[0]));
        $this->assertEquals(1, count($cnames1[0]));
        $this->assertEquals($cnames1[0]['Domain'], $cnames2[0]['Domain']);
    }

    public function testCnameNumberLimit()
    {
        $cnameConfig = new CnameConfig();
        for ($i = 0; $i < CnameConfig::OSS_MAX_RULES; $i += 1) {
            $cnameConfig->addCname(strval($i) . '.foo.com');
        }
        try {
            $cnameConfig->addCname('www.foo.com');
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals(
                $e->getMessage(),
                "num of cname in the config exceeds self::OSS_MAX_RULES: " . strval(CnameConfig::OSS_MAX_RULES));
        }
    }
}
