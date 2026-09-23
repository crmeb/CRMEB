<?php

namespace OSS\Tests;

use OSS\Model\TransferAccelerationConfig;

class TransferAccelerationConfigTest extends \PHPUnit\Framework\TestCase
{

    private $validXml = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<TransferAccelerationConfiguration>
<Enabled>true</Enabled>
</TransferAccelerationConfiguration>
BBBB;
    private $validXml1 = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<TransferAccelerationConfiguration>
<Enabled>false</Enabled>
</TransferAccelerationConfiguration>
BBBB;

    private $invalidXml1 = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<TransferAccelerationConfiguration>
</TransferAccelerationConfiguration>
BBBB;

    public function testParseValidXml()
    {
        $transferConfig = new TransferAccelerationConfig();
        $transferConfig->parseFromXml($this->validXml);
        $this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml(strval($transferConfig)));
        $this->assertEquals(true,$transferConfig->getEnabled());
    }

    public function testValidXml1()
    {
        $transferConfig = new TransferAccelerationConfig();
        $transferConfig->parseFromXml($this->validXml1);
        $this->assertEquals($this->cleanXml($this->validXml1), $this->cleanXml(strval($transferConfig)));
        $this->assertEquals(false,$transferConfig->getEnabled());
    }

    public function testInvalidXml1()
    {
        $transferConfig = new TransferAccelerationConfig();
        $transferConfig->parseFromXml($this->invalidXml1);
        $this->assertEquals(false,$transferConfig->getEnabled());
    }

    private function cleanXml($xml)
    {
        return str_replace("\n", "", str_replace("\r", "", $xml));
    }
}
