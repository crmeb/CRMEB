<?php

namespace OSS\Tests;

use OSS\Model\LoggingConfig;

class LoggingConfigTest extends \PHPUnit_Framework_TestCase
{
    private $validXml = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<BucketLoggingStatus>
<LoggingEnabled>
<TargetBucket>TargetBucket</TargetBucket>
<TargetPrefix>TargetPrefix</TargetPrefix>
</LoggingEnabled>
</BucketLoggingStatus>
BBBB;

    private $nullXml = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<BucketLoggingStatus/>
BBBB;

    public function testParseValidXml()
    {
        $loggingConfig = new LoggingConfig();
        $loggingConfig->parseFromXml($this->validXml);
        $this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml(strval($loggingConfig)));
    }

    public function testConstruct()
    {
        $loggingConfig = new LoggingConfig('TargetBucket', 'TargetPrefix');
        $this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($loggingConfig->serializeToXml()));
    }

    public function testFailedConstruct()
    {
        $loggingConfig = new LoggingConfig('TargetBucket', null);
        $this->assertEquals($this->cleanXml($this->nullXml), $this->cleanXml($loggingConfig->serializeToXml()));
    }

    private function cleanXml($xml)
    {
        return str_replace("\n", "", str_replace("\r", "", $xml));
    }
}
