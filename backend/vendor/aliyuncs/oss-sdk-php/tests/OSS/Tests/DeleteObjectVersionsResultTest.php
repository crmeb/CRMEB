<?php

namespace OSS\Tests;

use OSS\Result\DeleteObjectVersionsResult;
use OSS\Core\OssException;
use OSS\Http\ResponseCore;

class DeleteObjectVersionsResultTest extends \PHPUnit\Framework\TestCase
{

    private $validXml = <<<BBBB
<?xml version="1.0" ?>
<DeleteResult>
    <Deleted>
        <Key>demo.jpg</Key>
        <VersionId>CAEQNRiBgICEoPiC0BYiIGMxZWJmYmMzYjE0OTQ0ZmZhYjgzNzkzYjc2NjZk****</VersionId>
        <DeleteMarker>true</DeleteMarker>
        <DeleteMarkerVersionId>111111</DeleteMarkerVersionId>
    </Deleted>
</DeleteResult>
BBBB;

    private $validXml1 = <<<BBBB
<?xml version="1.0" ?>
<DeleteResult>
<Deleted>
    <Key>multipart.data</Key>
    <VersionId>CAEQNRiBgIDyz.6C0BYiIGQ2NWEwNmVhNTA3ZTQ3MzM5ODliYjM1ZTdjYjA4****</VersionId>
</Deleted>
</DeleteResult>
BBBB;

    private $validXml2 = <<<BBBB
<?xml version="1.0" ?>
<DeleteResult>
    <Deleted>
        <Key>multipart.data</Key>
        <DeleteMarker>true</DeleteMarker>
        <DeleteMarkerVersionId>CAEQMhiBgIDXiaaB0BYiIGQzYmRkZGUxMTM1ZDRjOTZhNjk4YjRjMTAyZjhl****</DeleteMarkerVersionId>
    </Deleted>
    <Deleted>
        <Key>test.jpg</Key>
        <DeleteMarker>true</DeleteMarker>
        <DeleteMarkerVersionId>CAEQMhiBgIDB3aWB0BYiIGUzYTA3YzliMzVmNzRkZGM5NjllYTVlMjYyYWEy****</DeleteMarkerVersionId>
    </Deleted>
</DeleteResult>
BBBB;

    private $validXml3 = <<<BBBB
<?xml version="1.0" ?>
<DeleteResult>
    <Deleted>
        <Key>multipart.data</Key>
    </Deleted>
    <Deleted>
        <Key>test.jpg</Key>
    </Deleted>
    <Deleted>
        <Key>demo.jpg</Key>
    </Deleted>
</DeleteResult>
BBBB;

    private $validXml4 = <<<BBBB
<?xml version="1.0" ?>
<DeleteResult>
    <EncodingType>url</EncodingType>
    <Deleted>
        <Key>multipart%2F.data</Key>
    </Deleted>
    <Deleted>
        <Key>test%2F.jpg</Key>
    </Deleted>
    <Deleted>
        <Key>demo%2F.jpg</Key>
    </Deleted>
</DeleteResult>
BBBB;

    private $invalidXml = <<<BBBB
<?xml version="1.0" ?>
<DeleteResult>
</DeleteResult>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new DeleteObjectVersionsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $list = $result->getData();
        $this->assertEquals(1, count($list));
        $this->assertEquals('demo.jpg', $list[0]->getKey());
        $this->assertEquals('CAEQNRiBgICEoPiC0BYiIGMxZWJmYmMzYjE0OTQ0ZmZhYjgzNzkzYjc2NjZk****', $list[0]->getVersionId());
        $this->assertEquals('true', $list[0]->getDeleteMarker());
        $this->assertEquals('111111', $list[0]->getDeleteMarkerVersionId());


        $response = new ResponseCore(array(), $this->validXml1, 200);
        $result = new DeleteObjectVersionsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $list = $result->getData();
        $this->assertEquals(1, count($list));
        $this->assertEquals('multipart.data', $list[0]->getKey());
        $this->assertEquals('CAEQNRiBgIDyz.6C0BYiIGQ2NWEwNmVhNTA3ZTQ3MzM5ODliYjM1ZTdjYjA4****', $list[0]->getVersionId());
        $this->assertEquals('', $list[0]->getDeleteMarker());
        $this->assertEquals('', $list[0]->getDeleteMarkerVersionId());

        $response = new ResponseCore(array(), $this->validXml2, 200);
        $result = new DeleteObjectVersionsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $list = $result->getData();
        $this->assertEquals(2, count($list));
        $this->assertEquals('multipart.data', $list[0]->getKey());
        $this->assertEquals('', $list[0]->getVersionId());
        $this->assertEquals('true', $list[0]->getDeleteMarker());
        $this->assertEquals('CAEQMhiBgIDXiaaB0BYiIGQzYmRkZGUxMTM1ZDRjOTZhNjk4YjRjMTAyZjhl****', $list[0]->getDeleteMarkerVersionId());
        $this->assertEquals('test.jpg', $list[1]->getKey());
        $this->assertEquals('', $list[1]->getVersionId());
        $this->assertEquals('true', $list[1]->getDeleteMarker());
        $this->assertEquals('CAEQMhiBgIDB3aWB0BYiIGUzYTA3YzliMzVmNzRkZGM5NjllYTVlMjYyYWEy****', $list[1]->getDeleteMarkerVersionId());


        $response = new ResponseCore(array(), $this->validXml3, 200);
        $result = new DeleteObjectVersionsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $list = $result->getData();
        $this->assertEquals(3, count($list));
        $this->assertEquals('multipart.data', $list[0]->getKey());
        $this->assertEquals('', $list[0]->getVersionId());
        $this->assertEquals('', $list[0]->getDeleteMarker());
        $this->assertEquals('', $list[0]->getDeleteMarkerVersionId());
        $this->assertEquals('test.jpg', $list[1]->getKey());
        $this->assertEquals('', $list[1]->getVersionId());
        $this->assertEquals('', $list[1]->getDeleteMarker());
        $this->assertEquals('', $list[1]->getDeleteMarkerVersionId());
        $this->assertEquals('demo.jpg', $list[2]->getKey());
        $this->assertEquals('', $list[2]->getVersionId());
        $this->assertEquals('', $list[2]->getDeleteMarker());
        $this->assertEquals('', $list[2]->getDeleteMarkerVersionId());

        $response = new ResponseCore(array(), $this->validXml4, 200);
        $result = new DeleteObjectVersionsResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $list = $result->getData();
        $this->assertEquals(3, count($list));
        $this->assertEquals('multipart/.data', $list[0]->getKey());
        $this->assertEquals('', $list[0]->getVersionId());
        $this->assertEquals('', $list[0]->getDeleteMarker());
        $this->assertEquals('', $list[0]->getDeleteMarkerVersionId());
        $this->assertEquals('test/.jpg', $list[1]->getKey());
        $this->assertEquals('', $list[1]->getVersionId());
        $this->assertEquals('', $list[1]->getDeleteMarker());
        $this->assertEquals('', $list[1]->getDeleteMarkerVersionId());
        $this->assertEquals('demo/.jpg', $list[2]->getKey());
        $this->assertEquals('', $list[2]->getVersionId());
        $this->assertEquals('', $list[2]->getDeleteMarker());
        $this->assertEquals('', $list[2]->getDeleteMarkerVersionId());
    }

    public function testParseNullXml()
    {
        $response = new ResponseCore(array(), "", 200);
        $result = new DeleteObjectVersionsResult($response);
        $list = $result->getData();
        $this->assertEquals(0, count($list));
    }

    public function testParseInvalidXml()
    {
        $response = new ResponseCore(array(), $this->invalidXml, 200);
        $result = new DeleteObjectVersionsResult($response);
        $list = $result->getData();
        $this->assertEquals(0, count($list));
    }
}
