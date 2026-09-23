<?php

namespace OSS\Tests;

use OSS\Result\GetBucketWormResult;
use OSS\Core\OssException;
use OSS\Http\ResponseCore;

class GetBucketWormResultTest extends \PHPUnit\Framework\TestCase
{

    private $validXml = <<<BBBB
<?xml version="1.0" ?>
<WormConfiguration>
<WormId>ID1</WormId>	
<State>Locked</State>
<RetentionPeriodInDays>1</RetentionPeriodInDays>
<CreationDate>2018-08-14T15:50:32</CreationDate>
</WormConfiguration>
BBBB;

    private $validXml2 = <<<BBBB
<?xml version="1.0" ?>
<WormConfiguration>
    <WormId>ID2</WormId>
    <State>InProgress</State>
    <RetentionPeriodInDays>10</RetentionPeriodInDays>
    <CreationDate>2018-09-14T15:50:32</CreationDate>
</WormConfiguration>
BBBB;

    private $invalidXml = <<<BBBB
<?xml version="1.0" ?>
<WormConfiguration>
</WormConfiguration>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new GetBucketWormResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $config = $result->getData();
        $this->assertEquals("ID1", $config->getWormId());
        $this->assertEquals("Locked", $config->getState());
        $this->assertEquals(1, $config->getDay());
        $this->assertEquals("2018-08-14T15:50:32", $config->getCreationDate());

        $response = new ResponseCore(array(), $this->validXml2, 200);
        $result = new GetBucketWormResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $config = $result->getData();
        $this->assertEquals("ID2", $config->getWormId());
        $this->assertEquals("InProgress", $config->getState());
        $this->assertEquals(10, $config->getDay());
        $this->assertEquals("2018-09-14T15:50:32", $config->getCreationDate());
    }

    public function testParseNullXml()
    {
        $response = new ResponseCore(array(), "", 200);
        $result = new GetBucketWormResult($response);
        $config = $result->getData();
        $this->assertEquals("", $config->getWormId());
        $this->assertEquals("", $config->getState());
        $this->assertEquals(0, $config->getDay());
        $this->assertEquals("", $config->getCreationDate());
    }

    public function testParseInvalidXml()
    {
        $response = new ResponseCore(array(), $this->invalidXml, 200);
        $result = new GetBucketWormResult($response);
        $config = $result->getData();
        $this->assertEquals("", $config->getWormId());
        $this->assertEquals("", $config->getState());
        $this->assertEquals(0, $config->getDay());
        $this->assertEquals("", $config->getCreationDate());
    }
}
