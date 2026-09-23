<?php

namespace OSS\Tests;

use OSS\Result\GetBucketEncryptionResult;
use OSS\Core\OssException;
use OSS\Http\ResponseCore;

class GetBucketEncryptionResultTest extends \PHPUnit\Framework\TestCase
{

    private $validXml = <<<BBBB
<?xml version="1.0" ?>
<ServerSideEncryptionRule>
  <ApplyServerSideEncryptionByDefault>
    <SSEAlgorithm>AES256</SSEAlgorithm>
    <KMSMasterKeyID></KMSMasterKeyID>
  </ApplyServerSideEncryptionByDefault>
</ServerSideEncryptionRule>
BBBB;

    private $validXml1 = <<<BBBB
<?xml version="1.0" ?>
<ServerSideEncryptionRule>
  <ApplyServerSideEncryptionByDefault>
    <SSEAlgorithm>KMS</SSEAlgorithm>
    <KMSMasterKeyID>kms-id</KMSMasterKeyID>
  </ApplyServerSideEncryptionByDefault>
</ServerSideEncryptionRule>
BBBB;

    private $validXml2 = <<<BBBB
<?xml version="1.0" ?>
<ServerSideEncryptionRule>
<ApplyServerSideEncryptionByDefault>
<SSEAlgorithm>KMS</SSEAlgorithm>
</ApplyServerSideEncryptionByDefault>
</ServerSideEncryptionRule>
BBBB;

    private $invalidXml = <<<BBBB
<?xml version="1.0" ?>
<ServerSideEncryptionRule>
</ServerSideEncryptionRule>
BBBB;

    public function testParseValidXml()
    {
        $response = new ResponseCore(array(), $this->validXml, 200);
        $result = new GetBucketEncryptionResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $config = $result->getData();
        $this->assertEquals("AES256", $config->getSSEAlgorithm());
        $this->assertEquals("", $config->getKMSMasterKeyID());


        $response = new ResponseCore(array(), $this->validXml1, 200);
        $result = new GetBucketEncryptionResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $config = $result->getData();
        $this->assertEquals("KMS", $config->getSSEAlgorithm());
        $this->assertEquals("kms-id", $config->getKMSMasterKeyID());

        $response = new ResponseCore(array(), $this->validXml2, 200);
        $result = new GetBucketEncryptionResult($response);
        $this->assertTrue($result->isOK());
        $this->assertNotNull($result->getData());
        $this->assertNotNull($result->getRawResponse());
        $config = $result->getData();
        $this->assertEquals("KMS", $config->getSSEAlgorithm());
        $this->assertEquals(null, $config->getKMSMasterKeyID());
    }

    public function testParseNullXml()
    {
        $response = new ResponseCore(array(), "", 200);
        $result = new GetBucketEncryptionResult($response);
        $config = $result->getData();
        $this->assertEquals(null, $config->getSSEAlgorithm());
        $this->assertEquals(null, $config->getKMSMasterKeyID());
    }

    public function testParseInvalidXml()
    {
        $response = new ResponseCore(array(), $this->invalidXml, 200);
        $result = new GetBucketEncryptionResult($response);
        $config = $result->getData();
        $this->assertEquals(null, $config->getSSEAlgorithm());
        $this->assertEquals(null, $config->getKMSMasterKeyID());
    }
}
