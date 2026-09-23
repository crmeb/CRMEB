<?php

namespace OSS\Tests;

use OSS\Result\ListMultipartUploadResult;
use OSS\Http\ResponseCore;

/**
 * Class ListMultipartUploadResultTest
 * @package OSS\Tests
 */
class ListMultipartUploadResultTest extends \PHPUnit\Framework\TestCase
{
    private $validXml = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<ListMultipartUploadsResult xmlns="http://doc.oss-cn-hangzhou.aliyuncs.com">
    <Bucket>oss-example</Bucket>
    <KeyMarker>xx</KeyMarker>
    <UploadIdMarker>3</UploadIdMarker>
    <NextKeyMarker>oss.avi</NextKeyMarker>
    <NextUploadIdMarker>0004B99B8E707874FC2D692FA5D77D3F</NextUploadIdMarker>
    <Delimiter>x</Delimiter>
    <Prefix>xx</Prefix>
    <MaxUploads>1000</MaxUploads>
    <IsTruncated>false</IsTruncated>
    <Upload>
        <Key>multipart.data</Key>
        <UploadId>0004B999EF518A1FE585B0C9360DC4C8</UploadId>
        <Initiated>2012-02-23T04:18:23.000Z</Initiated>
    </Upload>
    <Upload>
        <Key>multipart.data</Key>
        <UploadId>0004B999EF5A239BB9138C6227D69F95</UploadId>
        <Initiated>2012-02-23T04:18:23.000Z</Initiated>
    </Upload>
    <Upload>
        <Key>oss.avi</Key>
        <UploadId>0004B99B8E707874FC2D692FA5D77D3F</UploadId>
        <Initiated>2012-02-23T06:14:27.000Z</Initiated>
    </Upload>
</ListMultipartUploadsResult>
BBBB;

    private $validXmlWithEncodedKey = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<ListMultipartUploadsResult xmlns="http://doc.oss-cn-hangzhou.aliyuncs.com">
    <Bucket>oss-example</Bucket>
    <EncodingType>url</EncodingType>
    <KeyMarker>php%2Bkey-marker</KeyMarker>
    <UploadIdMarker>3</UploadIdMarker>
    <NextKeyMarker>php%2Bnext-key-marker</NextKeyMarker>
    <NextUploadIdMarker>0004B99B8E707874FC2D692FA5D77D3F</NextUploadIdMarker>
    <Delimiter>%2F</Delimiter>
    <Prefix>php%2Bprefix</Prefix>
    <MaxUploads>1000</MaxUploads>
    <IsTruncated>true</IsTruncated>
    <Upload>
        <Key>php%2Bkey-1</Key>
        <UploadId>0004B999EF518A1FE585B0C9360DC4C8</UploadId>
        <Initiated>2012-02-23T04:18:23.000Z</Initiated>
    </Upload>
    <Upload>
        <Key>php%2Bkey-2</Key>
        <UploadId>0004B999EF5A239BB9138C6227D69F95</UploadId>
        <Initiated>2012-02-23T04:18:23.000Z</Initiated>
    </Upload>
    <Upload>
        <Key>php%2Bkey-3</Key>
        <UploadId>0004B99B8E707874FC2D692FA5D77D3F</UploadId>
        <Initiated>2012-02-23T06:14:27.000Z</Initiated>
    </Upload>
</ListMultipartUploadsResult>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new ListMultipartUploadResult($response);
        $listMultipartUploadInfo = $result->getData();
        $this->assertEquals("oss-example", $listMultipartUploadInfo->getBucket());
        $this->assertEquals("xx", $listMultipartUploadInfo->getKeyMarker());
        $this->assertEquals(3, $listMultipartUploadInfo->getUploadIdMarker());
        $this->assertEquals("oss.avi", $listMultipartUploadInfo->getNextKeyMarker());
        $this->assertEquals("0004B99B8E707874FC2D692FA5D77D3F", $listMultipartUploadInfo->getNextUploadIdMarker());
        $this->assertEquals("x", $listMultipartUploadInfo->getDelimiter());
        $this->assertEquals("xx", $listMultipartUploadInfo->getPrefix());
        $this->assertEquals(1000, $listMultipartUploadInfo->getMaxUploads());
        $this->assertEquals("false", $listMultipartUploadInfo->getIsTruncated());
        $uploads = $listMultipartUploadInfo->getUploads();
        $this->assertEquals("multipart.data", $uploads[0]->getKey());
        $this->assertEquals("0004B999EF518A1FE585B0C9360DC4C8", $uploads[0]->getUploadId());
        $this->assertEquals("2012-02-23T04:18:23.000Z", $uploads[0]->getInitiated());
    }

    public function testParseValidXmlWithEncodedKey()
    {
        $response = new ResponseCore(array(), $this->validXmlWithEncodedKey, 200);
        $result = new ListMultipartUploadResult($response);
        $listMultipartUploadInfo = $result->getData();
        $this->assertEquals("oss-example", $listMultipartUploadInfo->getBucket());
        $this->assertEquals("php+key-marker", $listMultipartUploadInfo->getKeyMarker());
        $this->assertEquals("php+next-key-marker", $listMultipartUploadInfo->getNextKeyMarker());
        $this->assertEquals(3, $listMultipartUploadInfo->getUploadIdMarker());
        $this->assertEquals("0004B99B8E707874FC2D692FA5D77D3F", $listMultipartUploadInfo->getNextUploadIdMarker());
        $this->assertEquals("/", $listMultipartUploadInfo->getDelimiter());
        $this->assertEquals("php+prefix", $listMultipartUploadInfo->getPrefix());
        $this->assertEquals(1000, $listMultipartUploadInfo->getMaxUploads());
        $this->assertEquals("true", $listMultipartUploadInfo->getIsTruncated());
        $uploads = $listMultipartUploadInfo->getUploads();
        $this->assertEquals("php+key-1", $uploads[0]->getKey());
        $this->assertEquals("0004B999EF518A1FE585B0C9360DC4C8", $uploads[0]->getUploadId());
        $this->assertEquals("2012-02-23T04:18:23.000Z", $uploads[0]->getInitiated());
    }
}
