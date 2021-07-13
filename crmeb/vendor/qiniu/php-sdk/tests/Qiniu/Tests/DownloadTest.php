<?php
namespace Qiniu\Tests;

use Qiniu\Http\Client;

class DownloadTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        global $testAuth;
        $base_url = 'http://private-res.qiniudn.com/gogopher.jpg';
        $private_url = $testAuth->privateDownloadUrl($base_url);
        $response = Client::get($private_url);
        $this->assertEquals(200, $response->statusCode);
    }

    public function testFop()
    {
        global $testAuth;
        $base_url = 'http://private-res.qiniudn.com/gogopher.jpg?exif';
        $private_url = $testAuth->privateDownloadUrl($base_url);
        $response = Client::get($private_url);
        $this->assertEquals(200, $response->statusCode);
    }
}
