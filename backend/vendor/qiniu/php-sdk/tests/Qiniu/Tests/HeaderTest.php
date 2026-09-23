<?php
namespace Qiniu\Tests;

use PHPUnit\Framework\TestCase;

use Qiniu\Http\Header;

class HeaderTest extends TestCase
{
    protected $heads = array(
        ':status' => array('200'),
        ':x-test-1' => array('hello1'),
        ':x-Test-2' => array('hello2'),
        'content-type' => array('application/json'),
        'CONTENT-LENGTH' => array(1234),
        'oRiGin' => array('https://www.qiniu.com'),
        'ReFer' => array('www.qiniu.com'),
        'Last-Modified' => array('Mon, 06 Sep 2021 06:44:52 GMT'),
        'acCePt-ChArsEt' => array('utf-8'),
        'x-test-3' => array('hello3'),
        'cache-control' => array('no-cache', 'no-store'),
    );

    public function testNormalizeKey()
    {
        $except = array(
            ':status',
            ':x-test-1',
            ':x-Test-2',
            'Content-Type',
            'Content-Length',
            'Origin',
            'Refer',
            'Last-Modified',
            'Accept-Charset',
            'X-Test-3',
            'Cache-Control'
        );
        $actual = array_map(function ($str) {
            return Header::normalizeKey($str);
        }, array_keys($this->heads));
        $this->assertEquals($actual, $except);
    }


    public function testInvalidKeyName()
    {
        $except = array(
            'a:x-test-1',
        );

        $actual = array_map(function ($str) {
            return Header::normalizeKey($str);
        }, $except);

        $this->assertEquals($except, $actual);
    }

    public function testGetRawData()
    {
        $header = new Header($this->heads);
        foreach ($this->heads as $k => $v) {
            $rawHeader = $header->getRawData();
            $this->assertEquals($v, $rawHeader[Header::normalizeKey($k)]);
        }
    }

    public function testOffsetExists()
    {
        $header = new Header($this->heads);
        foreach (array_keys($this->heads) as $k) {
            $this->assertNotNull($header[$k]);
        }

        $except = array(
            ':status',
            ':x-test-1',
            ':x-Test-2',
            'Content-Type',
            'Content-Length',
            'Origin',
            'Refer',
            'Last-Modified',
            'Accept-Charset',
            'X-Test-3',
            'Cache-Control'
        );
        foreach ($except as $k) {
            $this->assertNotNull($header[$k], $k." is null");
        }
    }

    public function testOffsetGet()
    {
        $header = new Header($this->heads);
        foreach ($this->heads as $k => $v) {
            $this->assertEquals($v[0], $header[$k]);
        }

        $this->assertNull($header['no-exist']);
    }

    public function testOffsetSet()
    {
        $header = new Header($this->heads);
        $header["X-Test-3"] = "hello";
        $this->assertEquals("hello", $header["X-Test-3"]);
        $header["x-test-3"] = "hello test3";
        $this->assertEquals("hello test3", $header["x-test-3"]);
        $header[":x-Test-2"] = "hello";
        $this->assertEquals("hello", $header[":x-Test-2"]);
        $header[":x-test-2"] = "hello test2";
        $this->assertEquals("hello", $header[":x-Test-2"]);
    }

    public function testOffsetUnset()
    {
        $header = new Header($this->heads);
        unset($header["X-Test-3"]);
        $this->assertFalse(isset($header["X-Test-3"]));

        $header = new Header($this->heads);
        unset($header["x-test-3"]);
        $this->assertFalse(isset($header["x-test-3"]));

        $header = new Header($this->heads);
        unset($header[":x-test-2"]);
        $this->assertTrue(isset($header[":x-Test-2"]));

        $header = new Header($this->heads);
        unset($header[":x-Test-2"]);
        $this->assertFalse(isset($header[":x-Test-2"]));
    }

    public function testGetIterator()
    {
        $header = new Header($this->heads);

        $hasException = false;
        try {
            foreach ($header as $k => $v) {
                $hasException = !isset($header[$k]);
            }
        } catch (\Exception $e) {
            $hasException = true;
        }
        $this->assertFalse($hasException);
    }

    public function testEmptyHeaderIterator()
    {
        $emptyHeader = new Header();

        $hasException = false;
        try {
            foreach ($emptyHeader as $k => $v) {
                $hasException = !isset($header[$k]);
            }
        } catch (\Exception $e) {
            $hasException = true;
        }
        $this->assertFalse($hasException);
    }

    public function testCount()
    {
        $header = new Header($this->heads);

        $this->assertEquals(count($this->heads), count($header));
    }

    public function testFromRaw()
    {
        $lines = array();
        foreach ($this->heads as $k => $vs) {
            foreach ($vs as $v) {
                array_push($lines, $k . ": " . $v);
            }
        }
        $raw = implode("\r\n", $lines);
        $headerFromRaw = Header::fromRawText($raw);
        $this->assertEquals(new Header($this->heads), $headerFromRaw);
    }
}
