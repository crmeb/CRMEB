<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Model\StorageCapacityConfig;

class StorageCapacityConfigTest extends \PHPUnit\Framework\TestCase
{
    private $validXml_10 = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<BucketUserQos>
<StorageCapacity>10</StorageCapacity>
</BucketUserQos>
BBBB;

private $validXml_20 = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<BucketUserQos>
<StorageCapacity>20</StorageCapacity>
</BucketUserQos>
BBBB;

    public function testConstruct()
    {
        $config = new StorageCapacityConfig(10);
        $this->assertEquals($config->getStorageCapacity(), 10);
        $this->assertEquals($this->cleanXml($this->validXml_10), $this->cleanXml($config->serializeToXml()));
    }

    public function testSetStorageCapacity()
    {
        $config = new StorageCapacityConfig(2);
        $config->setStorageCapacity(20);
        $this->assertEquals($this->cleanXml($this->validXml_20), $this->cleanXml($config->serializeToXml()));
        $this->assertEquals($this->cleanXml($this->validXml_20), $this->cleanXml($config->__toString()));
    }

    public function testParseFromXml()
    {
        try {
            $config = new StorageCapacityConfig(10);
            $config->parseFromXml('invaide xml');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
            if (strpos($e, "Not implemented.") == false)
            {
                $this->assertTrue(false);
            }
        }
    }

    private function cleanXml($xml)
    {
        return str_replace("\n", "", str_replace("\r", "", $xml));
    }
}
