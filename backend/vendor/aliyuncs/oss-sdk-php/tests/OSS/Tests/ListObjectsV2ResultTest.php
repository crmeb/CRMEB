<?php

namespace OSS\Tests;


use OSS\Result\ListObjectsV2Result;
use OSS\Http\ResponseCore;

class ListObjectsV2ResultTest extends \PHPUnit\Framework\TestCase
{

    private $validXml1 = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult>
  <Name>testbucket-hf</Name>
  <Prefix></Prefix>
  <StartAfter></StartAfter>
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
  <StartAfter>xx</StartAfter>
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
  </Contents>
  <KeyCount>1</KeyCount>
</ListBucketResult>
BBBB;

    private $validXmlWithEncodedKey = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult>
  <Name>testbucket-hf</Name>
  <EncodingType>url</EncodingType>
  <Prefix>php%2Fprefix</Prefix>
  <StartAfter>php%2Fmarker</StartAfter>
  <ContinuationToken>1gJiYw--</ContinuationToken>
  <NextContinuationToken>CgJiYw--</NextContinuationToken>
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
  <KeyCount>1</KeyCount>
</ListBucketResult>
BBBB;

    private $validXmlWithRestoreInfo = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult>
  <Name>testbucket-hf</Name>
  <EncodingType>url</EncodingType>
  <Prefix>php%2Fprefix</Prefix>
  <StartAfter>php%2Fmarker</StartAfter>
  <ContinuationToken>1gJiYw--</ContinuationToken>
  <NextContinuationToken>CgJiYw--</NextContinuationToken>
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
    <RestoreInfo>ongoing-request="false", expiry-date="Tue, 25 Apr 2023 07:30:00 GMT"</RestoreInfo>
  </Contents>
  <KeyCount>1</KeyCount>
</ListBucketResult>
BBBB;

    public function testParseValidXml1()
    {
        $response = new ResponseCore(array(), $this->validXml1, 200);
        $result = new ListObjectsV2Result($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $objectListInfo = $result->getData();
        $this->assertEquals(2, count($objectListInfo->getPrefixList()));
        $this->assertEquals(0, count($objectListInfo->getObjectList()));
        $this->assertEquals('testbucket-hf', $objectListInfo->getBucketName());
        $this->assertEquals('', $objectListInfo->getPrefix());
        $this->assertEquals('', $objectListInfo->getStartAfter());
        $this->assertEquals(1000, $objectListInfo->getMaxKeys());
        $this->assertEquals('/', $objectListInfo->getDelimiter());
        $this->assertEquals('false', $objectListInfo->getIsTruncated());
        $this->assertEquals(0, $objectListInfo->getKeyCount());
        $prefixes = $objectListInfo->getPrefixList();
        $this->assertEquals('oss-php-sdk-test/', $prefixes[0]->getPrefix());
        $this->assertEquals('test/', $prefixes[1]->getPrefix());
    }

    public function testParseValidXml2()
    {
        $response = new ResponseCore(array(), $this->validXml2, 200);
        $result = new ListObjectsV2Result($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $objectListInfo = $result->getData();
        $this->assertEquals(0, count($objectListInfo->getPrefixList()));
        $this->assertEquals(1, count($objectListInfo->getObjectList()));
        $this->assertEquals('testbucket-hf', $objectListInfo->getBucketName());
        $this->assertEquals('oss-php-sdk-test/', $objectListInfo->getPrefix());
        $this->assertEquals('xx', $objectListInfo->getStartAfter());
        $this->assertEquals(1000, $objectListInfo->getMaxKeys());
        $this->assertEquals('/', $objectListInfo->getDelimiter());
        $this->assertEquals('false', $objectListInfo->getIsTruncated());
        $this->assertEquals(1, $objectListInfo->getKeyCount());
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
        $result = new ListObjectsV2Result($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $objectListInfo = $result->getData();
        $this->assertEquals(0, count($objectListInfo->getPrefixList()));
        $this->assertEquals(1, count($objectListInfo->getObjectList()));
        $this->assertEquals('testbucket-hf', $objectListInfo->getBucketName());
        $this->assertEquals('php/prefix', $objectListInfo->getPrefix());
        $this->assertEquals('php/marker', $objectListInfo->getStartAfter());
        $this->assertEquals('CgJiYw--', $objectListInfo->getNextContinuationToken());
        $this->assertEquals('1gJiYw--', $objectListInfo->getContinuationToken());
        $this->assertEquals(1000, $objectListInfo->getMaxKeys());
        $this->assertEquals('/', $objectListInfo->getDelimiter());
        $this->assertEquals('true', $objectListInfo->getIsTruncated());
        $this->assertEquals(1, $objectListInfo->getKeyCount());
        $objects = $objectListInfo->getObjectList();
        $this->assertEquals('php/a+b', $objects[0]->getKey());
        $this->assertEquals('2015-11-18T03:36:00.000Z', $objects[0]->getLastModified());
        $this->assertEquals('"89B9E567E7EB8815F2F7D41851F9A2CD"', $objects[0]->getETag());
        $this->assertEquals('Normal', $objects[0]->getType());
        $this->assertEquals(13115, $objects[0]->getSize());
        $this->assertEquals('Standard', $objects[0]->getStorageClass());
    }


    public function testParseValidXmlWithRestoreInfo()
    {
        $response = new ResponseCore(array(), $this->validXmlWithRestoreInfo, 200);
        $result = new ListObjectsV2Result($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $objectListInfo = $result->getData();
        $this->assertEquals(0, count($objectListInfo->getPrefixList()));
        $this->assertEquals(1, count($objectListInfo->getObjectList()));
        $this->assertEquals('testbucket-hf', $objectListInfo->getBucketName());
        $this->assertEquals('php/prefix', $objectListInfo->getPrefix());
        $this->assertEquals('php/marker', $objectListInfo->getStartAfter());
        $this->assertEquals('CgJiYw--', $objectListInfo->getNextContinuationToken());
        $this->assertEquals('1gJiYw--', $objectListInfo->getContinuationToken());
        $this->assertEquals(1000, $objectListInfo->getMaxKeys());
        $this->assertEquals('/', $objectListInfo->getDelimiter());
        $this->assertEquals('true', $objectListInfo->getIsTruncated());
        $this->assertEquals(1, $objectListInfo->getKeyCount());
        $objects = $objectListInfo->getObjectList();
        $this->assertEquals('php/a+b', $objects[0]->getKey());
        $this->assertEquals('2015-11-18T03:36:00.000Z', $objects[0]->getLastModified());
        $this->assertEquals('"89B9E567E7EB8815F2F7D41851F9A2CD"', $objects[0]->getETag());
        $this->assertEquals('Normal', $objects[0]->getType());
        $this->assertEquals(13115, $objects[0]->getSize());
        $this->assertEquals('Standard', $objects[0]->getStorageClass());
        $this->assertEquals('ongoing-request="false", expiry-date="Tue, 25 Apr 2023 07:30:00 GMT"', $objects[0]->getRestoreInfo());
        $this->assertEquals('cname_user', $objects[0]->getOwner()->getId());
        $this->assertEquals('cname_user', $objects[0]->getOwner()->getDisplayName());
    }
}
