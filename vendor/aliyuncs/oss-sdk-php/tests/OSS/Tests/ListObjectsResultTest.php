<?php

namespace OSS\Tests;


use OSS\Result\ListObjectsResult;
use OSS\Http\ResponseCore;

class ListObjectsResultTest extends \PHPUnit_Framework_TestCase
{

    private $validXml1 = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult>
  <Name>testbucket-hf</Name>
  <Prefix></Prefix>
  <Marker></Marker>
  <MaxKeys>1000</MaxKeys>
  <Delimiter>/</Delimiter>
  <IsTruncated>false</IsTruncated>
  <CommonPrefixes>
    <Prefix>oss-php-sdk-test/</Prefix>
  </CommonPrefixes>
  <CommonPrefixes>
    <Prefix>test/</Prefix>
  </CommonPrefixes>
</ListBucketResult>
BBBB;

    private $validXml2 = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult>
  <Name>testbucket-hf</Name>
  <Prefix>oss-php-sdk-test/</Prefix>
  <Marker>xx</Marker>
  <MaxKeys>1000</MaxKeys>
  <Delimiter>/</Delimiter>
  <IsTruncated>false</IsTruncated>
  <Contents>
    <Key>oss-php-sdk-test/upload-test-object-name.txt</Key>
    <LastModified>2015-11-18T03:36:00.000Z</LastModified>
    <ETag>"89B9E567E7EB8815F2F7D41851F9A2CD"</ETag>
    <Type>Normal</Type>
    <Size>13115</Size>
    <StorageClass>Standard</StorageClass>
    <Owner>
      <ID>cname_user</ID>
      <DisplayName>cname_user</DisplayName>
    </Owner>
  </Contents>
</ListBucketResult>
BBBB;

    private $validXmlWithEncodedKey = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult>
  <Name>testbucket-hf</Name>
  <EncodingType>url</EncodingType>
  <Prefix>php%2Fprefix</Prefix>
  <Marker>php%2Fmarker</Marker>
  <NextMarker>php%2Fnext-marker</NextMarker>
  <MaxKeys>1000</MaxKeys>
  <Delimiter>%2F</Delimiter>
  <IsTruncated>true</IsTruncated>
  <Contents>
    <Key>php/a%2Bb</Key>
    <LastModified>2015-11-18T03:36:00.000Z</LastModified>
    <ETag>"89B9E567E7EB8815F2F7D41851F9A2CD"</ETag>
    <Type>Normal</Type>
    <Size>13115</Size>
    <StorageClass>Standard</StorageClass>
    <Owner>
      <ID>cname_user</ID>
      <DisplayName>cname_user</DisplayName>
    </Owner>
  </Contents>
</ListBucketResult>
BBBB;

    public function testParseValidXml1()
    {
        $response = new ResponseCore(array(), $this->validXml1, 200);
        $result = new ListObjectsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $objectListInfo = $result->getData();
        $this->assertEquals(2, count($objectListInfo->getPrefixList()));
        $this->assertEquals(0, count($objectListInfo->getObjectList()));
        $this->assertEquals('testbucket-hf', $objectListInfo->getBucketName());
        $this->assertEquals('', $objectListInfo->getPrefix());
        $this->assertEquals('', $objectListInfo->getMarker());
        $this->assertEquals(1000, $objectListInfo->getMaxKeys());
        $this->assertEquals('/', $objectListInfo->getDelimiter());
        $this->assertEquals('false', $objectListInfo->getIsTruncated());
        $prefixes = $objectListInfo->getPrefixList();
        $this->assertEquals('oss-php-sdk-test/', $prefixes[0]->getPrefix());
        $this->assertEquals('test/', $prefixes[1]->getPrefix());
    }

    public function testParseValidXml2()
    {
        $response = new ResponseCore(array(), $this->validXml2, 200);
        $result = new ListObjectsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $objectListInfo = $result->getData();
        $this->assertEquals(0, count($objectListInfo->getPrefixList()));
        $this->assertEquals(1, count($objectListInfo->getObjectList()));
        $this->assertEquals('testbucket-hf', $objectListInfo->getBucketName());
        $this->assertEquals('oss-php-sdk-test/', $objectListInfo->getPrefix());
        $this->assertEquals('xx', $objectListInfo->getMarker());
        $this->assertEquals(1000, $objectListInfo->getMaxKeys());
        $this->assertEquals('/', $objectListInfo->getDelimiter());
        $this->assertEquals('false', $objectListInfo->getIsTruncated());
        $objects = $objectListInfo->getObjectList();
        $this->assertEquals('oss-php-sdk-test/upload-test-object-name.txt', $objects[0]->getKey());
        $this->assertEquals('2015-11-18T03:36:00.000Z', $objects[0]->getLastModified());
        $this->assertEquals('"89B9E567E7EB8815F2F7D41851F9A2CD"', $objects[0]->getETag());
        $this->assertEquals('Normal', $objects[0]->getType());
        $this->assertEquals(13115, $objects[0]->getSize());
        $this->assertEquals('Standard', $objects[0]->getStorageClass());
    }

    public function testParseValidXmlWithEncodedKey()
    {
        $response = new ResponseCore(array(), $this->validXmlWithEncodedKey, 200);
        $result = new ListObjectsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $objectListInfo = $result->getData();
        $this->assertEquals(0, count($objectListInfo->getPrefixList()));
        $this->assertEquals(1, count($objectListInfo->getObjectList()));
        $this->assertEquals('testbucket-hf', $objectListInfo->getBucketName());
        $this->assertEquals('php/prefix', $objectListInfo->getPrefix());
        $this->assertEquals('php/marker', $objectListInfo->getMarker());
        $this->assertEquals('php/next-marker', $objectListInfo->getNextMarker());
        $this->assertEquals(1000, $objectListInfo->getMaxKeys());
        $this->assertEquals('/', $objectListInfo->getDelimiter());
        $this->assertEquals('true', $objectListInfo->getIsTruncated());
        $objects = $objectListInfo->getObjectList();
        $this->assertEquals('php/a+b', $objects[0]->getKey());
        $this->assertEquals('2015-11-18T03:36:00.000Z', $objects[0]->getLastModified());
        $this->assertEquals('"89B9E567E7EB8815F2F7D41851F9A2CD"', $objects[0]->getETag());
        $this->assertEquals('Normal', $objects[0]->getType());
        $this->assertEquals(13115, $objects[0]->getSize());
        $this->assertEquals('Standard', $objects[0]->getStorageClass());
    }
}
