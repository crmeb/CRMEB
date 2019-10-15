<?php

namespace OSS\Tests;


use OSS\Core\OssException;
use OSS\Result\InitiateMultipartUploadResult;
use OSS\Http\ResponseCore;

class InitiateMultipartUploadResultTest extends \PHPUnit_Framework_TestCase
{
    private $validXml = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<InitiateMultipartUploadResult xmlns="http://doc.oss-cn-hangzhou.aliyuncs.com">
    <Bucket> multipart_upload</Bucket>
    <Key>multipart.data</Key>
    <UploadId>0004B9894A22E5B1888A1E29F8236E2D</UploadId>
</InitiateMultipartUploadResult>
BBBB;

    private $invalidXml = <<<BBBB
<?xml version="1.0" encoding="UTF-8"?>
<InitiateMultipartUploadResult xmlns="http://doc.oss-cn-hangzhou.aliyuncs.com">
    <Bucket> multipart_upload</Bucket>
    <Key>multipart.data</Key>
</InitiateMultipartUploadResult>
BBBB;


    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new InitiateMultipartUploadResult($response);
        $this->assertEquals("0004B9894A22E5B1888A1E29F8236E2D", $result->getData());
    }

    public function testParseInvalidXml()
    {
        $response = new ResponseCore(array(), $this->invalidXml, 200);
        try {
            $result = new InitiateMultipartUploadResult($response);
            $this->assertTrue(false);
        } catch (OssException $e) {

        }
    }
}
