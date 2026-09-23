<?php
namespace Qiniu\Tests;

use PHPUnit\Framework\TestCase;

use Qiniu;

class EntryTest extends TestCase
{
    public function testNormal()
    {
        $bucket = 'qiniuphotos';
        $key = 'gogopher.jpg';
        $encodeEntryURI = Qiniu\entry($bucket, $key);
        $this->assertEquals('cWluaXVwaG90b3M6Z29nb3BoZXIuanBn', $encodeEntryURI);
    }

    public function testKeyEmpty()
    {
        $bucket = 'qiniuphotos';
        $key = '';
        $encodeEntryURI = Qiniu\entry($bucket, $key);
        $this->assertEquals('cWluaXVwaG90b3M6', $encodeEntryURI);
    }

    public function testKeyNull()
    {
        $bucket = 'qiniuphotos';
        $key = null;
        $encodeEntryURI = Qiniu\entry($bucket, $key);
        $this->assertEquals('cWluaXVwaG90b3M=', $encodeEntryURI);
    }

    public function testKeyNeedReplacePlusSymbol()
    {
        $bucket = 'qiniuphotos';
        $key = '012ts>a';
        $encodeEntryURI = Qiniu\entry($bucket, $key);
        $this->assertEquals('cWluaXVwaG90b3M6MDEydHM-YQ==', $encodeEntryURI);
    }

    public function testKeyNeedReplaceSlashSymbol()
    {
        $bucket = 'qiniuphotos';
        $key = '012ts?a';
        $encodeEntryURI = Qiniu\entry($bucket, $key);
        $this->assertEquals('cWluaXVwaG90b3M6MDEydHM_YQ==', $encodeEntryURI);
    }
    public function testDecodeEntry()
    {
        $entry = 'cWluaXVwaG90b3M6Z29nb3BoZXIuanBn';
        list($bucket, $key) = Qiniu\decodeEntry($entry);
        $this->assertEquals('qiniuphotos', $bucket);
        $this->assertEquals('gogopher.jpg', $key);
    }

    public function testDecodeEntryWithEmptyKey()
    {
        $entry = 'cWluaXVwaG90b3M6';
        list($bucket, $key) = Qiniu\decodeEntry($entry);
        $this->assertEquals('qiniuphotos', $bucket);
        $this->assertEquals('', $key);
    }

    public function testDecodeEntryWithNullKey()
    {
        $entry = 'cWluaXVwaG90b3M=';
        list($bucket, $key) = Qiniu\decodeEntry($entry);
        $this->assertEquals('qiniuphotos', $bucket);
        $this->assertNull($key);
    }

    public function testDecodeEntryWithPlusSymbol()
    {
        $entry = 'cWluaXVwaG90b3M6MDEydHM-YQ==';
        list($bucket, $key) = Qiniu\decodeEntry($entry);
        $this->assertEquals('qiniuphotos', $bucket);
        $this->assertEquals('012ts>a', $key);
    }

    public function testDecodeEntryWithSlashSymbol()
    {
        $entry = 'cWluaXVwaG90b3M6MDEydHM_YQ==';
        list($bucket, $key) = Qiniu\decodeEntry($entry);
        $this->assertEquals('qiniuphotos', $bucket);
        $this->assertEquals('012ts?a', $key);
    }
}
