<?php

namespace OSS\Tests;

use OSS\Result\GetBucketRequestPaymentResult;
use OSS\Core\OssException;
use OSS\Http\ResponseCore;

class GetBucketRequestPaymentResultTest extends \PHPUnit\Framework\TestCase
{

    private $validXml = <<<BBBB
<?xml version="1.0" ?>
<RequestPaymentConfiguration>
  <Payer>Requester</Payer>
</RequestPaymentConfiguration>
BBBB;

    private $validXml2 = <<<BBBB
<?xml version="1.0" ?>
<RequestPaymentConfiguration>
  <Payer>BucketOwner</Payer>
</RequestPaymentConfiguration>
BBBB;

    private $invalidXml = <<<BBBB
<?xml version="1.0" ?>
<RequestPaymentConfiguration>
</RequestPaymentConfiguration>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new GetBucketRequestPaymentResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $payer = $result->getData();
        $this->assertEquals("Requester", $payer);

        $response = new ResponseCore(array(), $this->validXml2, 200);
        $result = new GetBucketRequestPaymentResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $payer = $result->getData();
        $this->assertEquals("BucketOwner", $payer);
    }

    public function testParseNullXml()
    {
        $response = new ResponseCore(array(), "", 200);
        $result = new GetBucketRequestPaymentResult($response);
        $payer = $result->getData();
        $this->assertEquals(null, $payer);
    }

    public function testParseInvalidXml()
    {
        $response = new ResponseCore(array(), $this->invalidXml, 200);
        $result = new GetBucketRequestPaymentResult($response);
        $payer = $result->getData();
        $this->assertEquals(null, $payer);
    }
}
