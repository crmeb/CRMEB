<?php
namespace Qiniu\Tests;

use Qiniu;

class Base64Test extends \PHPUnit_Framework_TestCase
{
    public function testUrlSafe()
    {
        $a = '你好';
        $b = \Qiniu\base64_urlSafeEncode($a);
        $this->assertEquals($a, \Qiniu\base64_urlSafeDecode($b));
    }
}
