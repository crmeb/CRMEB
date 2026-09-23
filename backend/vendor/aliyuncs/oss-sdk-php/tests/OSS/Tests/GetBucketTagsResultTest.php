<?php

namespace OSS\Tests;

use OSS\Result\GetBucketTagsResult;
use OSS\Core\OssException;
use OSS\Http\ResponseCore;

class GetBucketTagsResultTest extends \PHPUnit\Framework\TestCase
{
    private $validXml = <<<BBBB
<?xml version="1.0" ?>
<Tagging>
<TagSet>
<Tag>
  <Key>testa</Key>
  <Value>value1-test</Value>
</Tag>
<Tag>
  <Key>testb</Key>
  <Value>value2-test</Value>
</Tag>
</TagSet>
</Tagging>
BBBB;

    private $invalidXml = <<<BBBB
<?xml version="1.0" ?>
<Tagging>
</Tagging>
BBBB;

    private $invalidXml2 = <<<BBBB
<?xml version="1.0" ?>
<Tagging>
  <TagSet>
  </TagSet>
</Tagging>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new GetBucketTagsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $config = $result->getData();
        $this->assertEquals(2, count($config->getTags()));
        $this->assertEquals("testa", $config->getTags()[0]->getKey());
        $this->assertEquals("value1-test", $config->getTags()[0]->getValue());
        $this->assertEquals("testb", $config->getTags()[1]->getKey());
        $this->assertEquals("value2-test", $config->getTags()[1]->getValue());
    }

    public function testParseNullXml()
    {
        $response = new ResponseCore(array(), "", 200);
        $result = new GetBucketTagsResult($response);
        $config = $result->getData();
        $this->assertEquals(0, count($config->getTags()));

    }

    public function testParseInvalidXml()
    {
        $response = new ResponseCore(array(), $this->invalidXml, 200);
        $result = new GetBucketTagsResult($response);
        $config = $result->getData();
        $this->assertEquals(0, count($config->getTags()));

        $response = new ResponseCore(array(), $this->invalidXml2, 200);
        $result = new GetBucketTagsResult($response);
        $config = $result->getData();
        $this->assertEquals(0, count($config->getTags()));
    }
}
