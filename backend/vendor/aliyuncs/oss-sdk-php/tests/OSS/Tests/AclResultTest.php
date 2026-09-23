<?php

namespace OSS\Tests;

use OSS\Result\AclResult;
use OSS\Core\OssException;
use OSS\Http\ResponseCore;

class AclResultTest extends \PHPUnit\Framework\TestCase
{

    private $validXml = <<<BBBB
<?xml version="1.0" ?>
<AccessControlPolicy>
    <Owner>
        <ID>00220120222</ID>
        <DisplayName>user_example</DisplayName>
    </Owner>
    <AccessControlList>
        <Grant>public-read</Grant>
    </AccessControlList>
</AccessControlPolicy>
BBBB;

    private $invalidXml = <<<BBBB
<?xml version="1.0" ?>
<AccessControlPolicy>
</AccessControlPolicy>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new AclResult($response);
        $this->assertEquals("public-read", $result->getData());
    }

    public function testParseNullXml()
    {
        $response = new ResponseCore(array(), "", 200);
        try {
            new AclResult($response);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('body is null', $e->getMessage());
        }
    }

    public function testParseInvalidXml()
    {
        $response = new ResponseCore(array(), $this->invalidXml, 200);
        try {
            new AclResult($response);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals("xml format exception", $e->getMessage());
        }
    }
}
