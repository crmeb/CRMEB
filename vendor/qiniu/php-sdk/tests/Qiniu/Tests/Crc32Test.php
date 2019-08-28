<?php
namespace Qiniu\Tests;

use Qiniu;

class Crc32Test extends \PHPUnit_Framework_TestCase
{
    public function testData()
    {
        $a = '你好';
        $b = \Qiniu\crc32_data($a);
        $this->assertEquals('1352841281', $b);
    }

    public function testFile()
    {
        $b = \Qiniu\crc32_file(__file__);
        $c = \Qiniu\crc32_file(__file__);
        $this->assertEquals($c, $b);
    }
}
