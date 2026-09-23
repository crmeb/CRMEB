<?php

namespace OSS\Tests;

use OSS\Result\ListObjectVersionsResult;
use OSS\Core\OssException;
use OSS\Http\ResponseCore;

class ListObjectVersionsResultTest extends \PHPUnit\Framework\TestCase
{

    private $validXml = <<<BBBB
<?xml version="1.0" ?>
<ListVersionsResult>
    <Name>oss-example</Name>
    <Prefix></Prefix>
    <KeyMarker>example</KeyMarker>
    <VersionIdMarker>CAEQMxiBgICbof2D0BYiIGRhZjgwMzJiMjA3MjQ0ODE5MWYxZDYwMzJlZjU1****</VersionIdMarker>
    <MaxKeys>100</MaxKeys>
    <Delimiter></Delimiter>
    <IsTruncated>false</IsTruncated>
    <DeleteMarker>
        <Key>example</Key>
        <VersionId>CAEQMxiBgICAof2D0BYiIDJhMGE3N2M1YTI1NDQzOGY5NTkyNTI3MGYyMzJm****</VersionId>
        <IsLatest>false</IsLatest>
        <LastModified>2019-04-09T07:27:28.000Z</LastModified>
        <Owner>
            <ID>1234512528586****</ID>
            <DisplayName>12345125285864390</DisplayName>
        </Owner>
    </DeleteMarker>
    <Version>
        <Key>example</Key>
        <VersionId>CAEQMxiBgMDNoP2D0BYiIDE3MWUxNzgxZDQxNTRiODI5OGYwZGMwNGY3MzZjN****</VersionId>
        <IsLatest>false</IsLatest>
        <LastModified>2019-04-09T07:27:28.000Z</LastModified>
        <ETag>"250F8A0AE989679A22926A875F0A2****"</ETag>
        <Type>Normal</Type>
        <Size>93731</Size>
        <StorageClass>Standard</StorageClass>
        <Owner>
            <ID>1234512528586****</ID>
            <DisplayName>12345125285864390</DisplayName>
        </Owner>
    </Version>
    <Version>
        <Key>pic.jpg</Key>
        <VersionId>CAEQMxiBgMCZov2D0BYiIDY4MDllOTc2YmY5MjQxMzdiOGI3OTlhNTU0ODIx****</VersionId>
        <IsLatest>true</IsLatest>
        <LastModified>2019-04-09T07:27:28.000Z</LastModified>
        <ETag>"3663F7B0B9D3153F884C821E7CF4****"</ETag>
        <Type>Normal</Type>
        <Size>574768</Size>
        <StorageClass>IA</StorageClass>
        <Owner>
            <ID>1234512528586****</ID>
            <DisplayName>12345125285864390</DisplayName>
        </Owner>
    </Version>
</ListVersionsResult>
BBBB;

    private $validXml1 = <<<BBBB
<?xml version="1.0" ?>
<ListVersionsResult>
    <Name>oss-example</Name>
    <Prefix></Prefix>
    <KeyMarker>example</KeyMarker>
    <VersionIdMarker>CAEQMxiBgICbof2D0BYiIGRhZjgwMzJiMjA3MjQ0ODE5MWYxZDYwMzJlZjU1****</VersionIdMarker>
    <MaxKeys>100</MaxKeys>
    <Delimiter></Delimiter>
    <IsTruncated>false</IsTruncated>
    <DeleteMarker>
        <Key>example</Key>
        <VersionId>CAEQMxiBgICAof2D0BYiIDJhMGE3N2M1YTI1NDQzOGY5NTkyNTI3MGYyMzJm****</VersionId>
        <IsLatest>true</IsLatest>
        <LastModified>2019-04-09T07:27:28.000Z</LastModified>
        <Owner>
            <ID>1234512528586****</ID>
            <DisplayName>12345125285864390</DisplayName>
        </Owner>
    </DeleteMarker>
    <DeleteMarker>
        <Key>example-1</Key>
        <VersionId>CAEQMxiBgICAof2D0BYiIDJhMGE3N2M1YTI1NDQzOGY5NTkyNTI3MGYyMzJm****</VersionId>
        <LastModified>2019-04-09T07:27:28.000Z</LastModified>
        <Owner>
            <ID>1234512528586****</ID>
            <DisplayName>12345125285864390</DisplayName>
        </Owner>
    </DeleteMarker>
    <Version>
        <Key>example-2</Key>
        <VersionId>CAEQMxiBgMDNoP2D0BYiIDE3MWUxNzgxZDQxNTRiODI5OGYwZGMwNGY3MzZjN****</VersionId>
        <LastModified>2019-04-09T07:27:28.000Z</LastModified>
        <ETag>"250F8A0AE989679A22926A875F0A2****"</ETag>
        <Type>Normal</Type>
        <Size>93731</Size>
        <StorageClass>Standard</StorageClass>
        <Owner>
            <ID>1234512528586****</ID>
            <DisplayName>12345125285864390</DisplayName>
        </Owner>
    </Version>
</ListVersionsResult>
BBBB;

    private $invalidXml = <<<BBBB
<?xml version="1.0" ?>
<ListVersionsResult>
</ListVersionsResult>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new ListObjectVersionsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $list = $result->getData();
        $this->assertEquals(0, count($list->getPrefixList()));
        $this->assertEquals(1, count($list->getDeleteMarkerList()));
        $this->assertEquals(2, count($list->getObjectVersionList()));

        $this->assertEquals('oss-example', $list->getBucketName());
        $this->assertEquals('', $list->getPrefix());
        $this->assertEquals('example', $list->getKeyMarker());
        $this->assertEquals('CAEQMxiBgICbof2D0BYiIGRhZjgwMzJiMjA3MjQ0ODE5MWYxZDYwMzJlZjU1****', $list->getVersionIdMarker());
        $this->assertEquals(100, $list->getMaxKeys());
        $this->assertEquals('', $list->getDelimiter());
        $this->assertEquals('false', $list->getIsTruncated());

        $deleteMarkerList = $list->getDeleteMarkerList();
        $this->assertEquals('example', $deleteMarkerList[0]->getKey());
        $this->assertEquals('CAEQMxiBgICAof2D0BYiIDJhMGE3N2M1YTI1NDQzOGY5NTkyNTI3MGYyMzJm****', $deleteMarkerList[0]->getVersionId());
        $this->assertEquals('false', $deleteMarkerList[0]->getIsLatest());
        $this->assertEquals('2019-04-09T07:27:28.000Z', $deleteMarkerList[0]->getLastModified());

        $objectVersionList = $list->getObjectVersionList();
        $this->assertEquals('example', $objectVersionList[0]->getKey());
        $this->assertEquals('CAEQMxiBgMDNoP2D0BYiIDE3MWUxNzgxZDQxNTRiODI5OGYwZGMwNGY3MzZjN****', $objectVersionList[0]->getVersionId());
        $this->assertEquals('false', $objectVersionList[0]->getIsLatest());
        $this->assertEquals('2019-04-09T07:27:28.000Z', $objectVersionList[0]->getLastModified());
        $this->assertEquals('"250F8A0AE989679A22926A875F0A2****"', $objectVersionList[0]->getETag());
        $this->assertEquals('Normal', $objectVersionList[0]->getType());
        $this->assertEquals(93731, $objectVersionList[0]->getSize());
        $this->assertEquals('Standard', $objectVersionList[0]->getStorageClass());

        $this->assertEquals('pic.jpg', $objectVersionList[1]->getKey());
        $this->assertEquals('CAEQMxiBgMCZov2D0BYiIDY4MDllOTc2YmY5MjQxMzdiOGI3OTlhNTU0ODIx****', $objectVersionList[1]->getVersionId());
        $this->assertEquals('true', $objectVersionList[1]->getIsLatest());
        $this->assertEquals('2019-04-09T07:27:28.000Z', $objectVersionList[1]->getLastModified());
        $this->assertEquals('"3663F7B0B9D3153F884C821E7CF4****"', $objectVersionList[1]->getETag());
        $this->assertEquals('Normal', $objectVersionList[1]->getType());
        $this->assertEquals(574768, $objectVersionList[1]->getSize());
        $this->assertEquals('IA', $objectVersionList[1]->getStorageClass());


        $response = new ResponseCore(array(), $this->validXml1, 200);
        $result = new ListObjectVersionsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $list = $result->getData();
        $this->assertEquals(0, count($list->getPrefixList()));
        $this->assertEquals(2, count($list->getDeleteMarkerList()));
        $this->assertEquals(1, count($list->getObjectVersionList()));

        $this->assertEquals('oss-example', $list->getBucketName());
        $this->assertEquals('', $list->getPrefix());
        $this->assertEquals('example', $list->getKeyMarker());
        $this->assertEquals('CAEQMxiBgICbof2D0BYiIGRhZjgwMzJiMjA3MjQ0ODE5MWYxZDYwMzJlZjU1****', $list->getVersionIdMarker());
        $this->assertEquals(100, $list->getMaxKeys());
        $this->assertEquals('', $list->getDelimiter());
        $this->assertEquals('false', $list->getIsTruncated());

        $deleteMarkerList = $list->getDeleteMarkerList();
        $this->assertEquals('example', $deleteMarkerList[0]->getKey());
        $this->assertEquals('CAEQMxiBgICAof2D0BYiIDJhMGE3N2M1YTI1NDQzOGY5NTkyNTI3MGYyMzJm****', $deleteMarkerList[0]->getVersionId());
        $this->assertEquals('true', $deleteMarkerList[0]->getIsLatest());
        $this->assertEquals('2019-04-09T07:27:28.000Z', $deleteMarkerList[0]->getLastModified());

        $this->assertEquals('example-1', $deleteMarkerList[1]->getKey());
        $this->assertEquals('CAEQMxiBgICAof2D0BYiIDJhMGE3N2M1YTI1NDQzOGY5NTkyNTI3MGYyMzJm****', $deleteMarkerList[1]->getVersionId());
        $this->assertEquals('', $deleteMarkerList[1]->getIsLatest());
        $this->assertEquals('2019-04-09T07:27:28.000Z', $deleteMarkerList[1]->getLastModified());

        $objectVersionList = $list->getObjectVersionList();
        $this->assertEquals('example-2', $objectVersionList[0]->getKey());
        $this->assertEquals('CAEQMxiBgMDNoP2D0BYiIDE3MWUxNzgxZDQxNTRiODI5OGYwZGMwNGY3MzZjN****', $objectVersionList[0]->getVersionId());
        $this->assertEquals('', $objectVersionList[0]->getIsLatest());
        $this->assertEquals('2019-04-09T07:27:28.000Z', $objectVersionList[0]->getLastModified());
        $this->assertEquals('"250F8A0AE989679A22926A875F0A2****"', $objectVersionList[0]->getETag());
        $this->assertEquals('Normal', $objectVersionList[0]->getType());
        $this->assertEquals(93731, $objectVersionList[0]->getSize());
        $this->assertEquals('Standard', $objectVersionList[0]->getStorageClass());
    }

    public function testParseNullXml()
    {
        $response = new ResponseCore(array(), "", 200);
        $result = new ListObjectVersionsResult($response);
        $list = $result->getData();
        $this->assertTrue(true);
    }

    public function testParseInvalidXml()
    {
        $response = new ResponseCore(array(), $this->invalidXml, 200);
        $result = new ListObjectVersionsResult($response);
        $stat = $result->getData();
        $this->assertTrue(true);
    }
}
