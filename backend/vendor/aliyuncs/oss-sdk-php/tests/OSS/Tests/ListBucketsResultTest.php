<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Http\ResponseCore;
use OSS\Result\ListBucketsResult;

class ListBucketsResultTest extends \PHPUnit\Framework\TestCase
{
    private $validXml = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<ListAllMyBucketsResult>
  <Owner>
    <ID>ut_test_put_bucket</ID>
    <DisplayName>ut_test_put_bucket</DisplayName>
  </Owner>
  <Buckets>
    <Bucket>
      <Location>oss-cn-hangzhou-a</Location>
      <Name>xz02tphky6fjfiuc0</Name>
      <CreationDate>2014-05-15T11:18:32.000Z</CreationDate>
    </Bucket>
    <Bucket>
      <Location>oss-cn-hangzhou-a</Location>
      <Name>xz02tphky6fjfiuc1</Name>
      <CreationDate>2014-05-15T11:18:32.000Z</CreationDate>
    </Bucket>
  </Buckets>
</ListAllMyBucketsResult>
BBBB;

    private $nullXml = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<ListAllMyBucketsResult>
  <Owner>
    <ID>ut_test_put_bucket</ID>
    <DisplayName>ut_test_put_bucket</DisplayName>
  </Owner>
  <Buckets>
  </Buckets>
</ListAllMyBucketsResult>
BBBB;


    private $errorBody = <<< BBBB
<?xml version="1.0" encoding="UTF-8"?>
<Error>
  <Code>NoSuchBucket</Code>
  <Message>The specified bucket does not exist.</Message>
  <RequestId>566B870D207FB3044302EB0A</RequestId>
  <HostId>hello.oss-test.aliyun-inc.com</HostId>
  <BucketName>hello</BucketName>
</Error>
BBBB;

    private $xml = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<ListAllMyBucketsResult>
  <Owner>
    <ID>ut_test_put_bucket</ID>
    <DisplayName>ut_test_put_bucket</DisplayName>
  </Owner>
  <Buckets>
    <Bucket>
      <CreationDate>2015-12-17T18:12:43.000Z</CreationDate>
      <ExtranetEndpoint>oss-cn-shanghai.aliyuncs.com</ExtranetEndpoint>
      <IntranetEndpoint>oss-cn-shanghai-internal.aliyuncs.com</IntranetEndpoint>
      <Location>oss-cn-shanghai</Location>
      <Name>app-base-oss</Name>
      <Region>cn-shanghai</Region>
      <StorageClass>Standard</StorageClass>
    </Bucket>
    <Bucket>
      <CreationDate>2014-12-25T11:21:04.000Z</CreationDate>
      <ExtranetEndpoint>oss-cn-hangzhou.aliyuncs.com</ExtranetEndpoint>
      <IntranetEndpoint>oss-cn-hangzhou-internal.aliyuncs.com</IntranetEndpoint>
      <Location>oss-cn-hangzhou</Location>
      <Name>atestleo23</Name>
      <Region>cn-hangzhou</Region>
      <StorageClass>IA</StorageClass>
    </Bucket>
    <Bucket>
      <CreationDate>2014-12-25T11:21:04.000Z</CreationDate>
      <Location>oss-cn-hangzhou</Location>
      <Name>atestleo23</Name>
    </Bucket>
  </Buckets>
</ListAllMyBucketsResult>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new ListBucketsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $bucketListInfo = $result->getData();
        $this->assertEquals(2, count($bucketListInfo->getBucketList()));
    }

    public function testParseNullXml()
    {
        $response = new ResponseCore(array(), $this->nullXml, 200);
        $result = new ListBucketsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $bucketListInfo = $result->getData();
        $this->assertEquals(0, count($bucketListInfo->getBucketList()));
    }

    public function test403()
    {
        $errorHeader = array(
            'x-oss-request-id' => '1a2b-3c4d'
        );
        $response = new ResponseCore($errorHeader, $this->errorBody, 403);
        try {
            new ListBucketsResult($response);
        } catch (OssException $e) {
            $this->assertEquals(
                $e->getMessage(),
                'NoSuchBucket: The specified bucket does not exist. RequestId: 1a2b-3c4d');
            $this->assertEquals($e->getHTTPStatus(), '403');
            $this->assertEquals($e->getRequestId(), '1a2b-3c4d');
            $this->assertEquals($e->getErrorCode(), 'NoSuchBucket');
            $this->assertEquals($e->getErrorMessage(), 'The specified bucket does not exist.');
            $this->assertEquals($e->getDetails(), $this->errorBody);
        }
    }

    public function testParseXml2()
    {
        $response = new ResponseCore(array(), $this->xml, 200);
        $result = new ListBucketsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $bucketListInfo = $result->getData();
        $this->assertEquals(3, count($bucketListInfo->getBucketList()));
        $this->assertEquals("2015-12-17T18:12:43.000Z", $bucketListInfo->getBucketList()[0]->getCreateDate());
        $this->assertEquals("oss-cn-shanghai", $bucketListInfo->getBucketList()[0]->getLocation());
        $this->assertEquals("app-base-oss", $bucketListInfo->getBucketList()[0]->getName());
        $this->assertEquals("oss-cn-shanghai.aliyuncs.com", $bucketListInfo->getBucketList()[0]->getExtranetEndpoint());
        $this->assertEquals("oss-cn-shanghai-internal.aliyuncs.com", $bucketListInfo->getBucketList()[0]->getIntranetEndpoint());
        $this->assertEquals("cn-shanghai", $bucketListInfo->getBucketList()[0]->getRegion());
        $this->assertEquals("Standard", $bucketListInfo->getBucketList()[0]->getStorageClass());
    
        $this->assertEquals("2014-12-25T11:21:04.000Z", $bucketListInfo->getBucketList()[1]->getCreateDate());
        $this->assertEquals("oss-cn-hangzhou", $bucketListInfo->getBucketList()[1]->getLocation());
        $this->assertEquals("atestleo23", $bucketListInfo->getBucketList()[1]->getName());
        $this->assertEquals("oss-cn-hangzhou.aliyuncs.com", $bucketListInfo->getBucketList()[1]->getExtranetEndpoint());
        $this->assertEquals("oss-cn-hangzhou-internal.aliyuncs.com", $bucketListInfo->getBucketList()[1]->getIntranetEndpoint());
        $this->assertEquals("cn-hangzhou", $bucketListInfo->getBucketList()[1]->getRegion());
        $this->assertEquals("IA", $bucketListInfo->getBucketList()[1]->getStorageClass());

        $this->assertEquals("2014-12-25T11:21:04.000Z", $bucketListInfo->getBucketList()[2]->getCreateDate());
        $this->assertEquals("oss-cn-hangzhou", $bucketListInfo->getBucketList()[2]->getLocation());
        $this->assertEquals("atestleo23", $bucketListInfo->getBucketList()[2]->getName());
        $this->assertEquals(null, $bucketListInfo->getBucketList()[2]->getExtranetEndpoint());
        $this->assertEquals(null, $bucketListInfo->getBucketList()[2]->getIntranetEndpoint());
        $this->assertEquals(null, $bucketListInfo->getBucketList()[2]->getRegion());
        $this->assertEquals(null, $bucketListInfo->getBucketList()[2]->getStorageClass());

      }
}
