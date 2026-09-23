<?php

namespace OSS\Tests;

use OSS\Result\GetBucketStatResult;
use OSS\Core\OssException;
use OSS\Http\ResponseCore;

class GetBucketStatResultTest extends \PHPUnit\Framework\TestCase
{

private $validXml = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<BucketStat>
  <Storage>1600</Storage>
  <ObjectCount>230</ObjectCount>
  <MultipartUploadCount>40</MultipartUploadCount>
  <LiveChannelCount>4</LiveChannelCount>
  <LastModifiedTime>1643341269</LastModifiedTime>
  <StandardStorage>430</StandardStorage>
  <StandardObjectCount>66</StandardObjectCount>
  <InfrequentAccessStorage>2359296</InfrequentAccessStorage>
  <InfrequentAccessRealStorage>360</InfrequentAccessRealStorage>
  <InfrequentAccessObjectCount>54</InfrequentAccessObjectCount>
  <ArchiveStorage>2949120</ArchiveStorage>
  <ArchiveRealStorage>450</ArchiveRealStorage>
  <ArchiveObjectCount>74</ArchiveObjectCount>
  <ColdArchiveStorage>2359296</ColdArchiveStorage>
  <ColdArchiveRealStorage>360</ColdArchiveRealStorage>
  <ColdArchiveObjectCount>36</ColdArchiveObjectCount>
</BucketStat>
BBBB;

private $invalidXml = <<<BBBB
<?xml version="1.0" ?>
<BucketStat>
</BucketStat>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new GetBucketStatResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $stat = $result->getData();
        $this->assertEquals(1600, $stat->getStorage());
        $this->assertEquals(230, $stat->getObjectCount());
        $this->assertEquals(40, $stat->getMultipartUploadCount());
        $this->assertEquals(4, $stat->getLiveChannelCount());
        $this->assertEquals(1643341269, $stat->getLastModifiedTime());
        $this->assertEquals(430, $stat->getStandardStorage());
        $this->assertEquals(66, $stat->getStandardObjectCount());
        $this->assertEquals(2359296, $stat->getInfrequentAccessStorage());
        $this->assertEquals(360, $stat->getInfrequentAccessRealStorage());
        $this->assertEquals(54, $stat->getInfrequentAccessObjectCount());
        $this->assertEquals(2949120, $stat->getArchiveStorage());
        $this->assertEquals(450, $stat->getArchiveRealStorage());
        $this->assertEquals(74, $stat->getArchiveObjectCount());
        $this->assertEquals(2359296, $stat->getColdArchiveStorage());
        $this->assertEquals(360, $stat->getColdArchiveRealStorage());
        $this->assertEquals(36, $stat->getColdArchiveObjectCount());
    }

    public function testParseNullXml()
    {
        $response = new ResponseCore(array(), "", 200);
        $result = new GetBucketStatResult($response);
        $stat = $result->getData();
        $this->assertEquals(0, $stat->getStorage());
        $this->assertEquals(0, $stat->getObjectCount());
        $this->assertEquals(0, $stat->getMultipartUploadCount());
    }

    public function testParseInvalidXml()
    {
        $response = new ResponseCore(array(), $this->invalidXml, 200);
        $result = new GetBucketStatResult($response);
        $stat = $result->getData();
        $this->assertEquals(0, $stat->getStorage());
        $this->assertEquals(0, $stat->getObjectCount());
        $this->assertEquals(0, $stat->getMultipartUploadCount());
    }
}
