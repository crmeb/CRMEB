<?php
namespace Qiniu\Tests;

use Qiniu\Processing\Operation;
use Qiniu\Processing\PersistentFop;

class PfopTest extends \PHPUnit_Framework_TestCase
{
    public function testPfop()
    {
        global $testAuth;
        $bucket = 'testres';
        $key = 'sintel_trailer.mp4';
        $pfop = new PersistentFop($testAuth, null);

        $fops = 'avthumb/m3u8/segtime/10/vcodec/libx264/s/320x240';
        list($id, $error) = $pfop->execute($bucket, $key, $fops);
        $this->assertNull($error);
        list($status, $error) = $pfop->status($id);
        $this->assertNotNull($status);
        $this->assertNull($error);
    }


    public function testPfops()
    {
        global $testAuth;
        $bucket = 'testres';
        $key = 'sintel_trailer.mp4';
        $fops = array(
            'avthumb/m3u8/segtime/10/vcodec/libx264/s/320x240',
            'vframe/jpg/offset/7/w/480/h/360',
        );
        $pfop = new PersistentFop($testAuth, null);

        list($id, $error) = $pfop->execute($bucket, $key, $fops);
        $this->assertNull($error);

        list($status, $error) = $pfop->status($id);
        $this->assertNotNull($status);
        $this->assertNull($error);
    }

    public function testMkzip()
    {
        global $testAuth;
        $bucket = 'phpsdk';
        $key = 'php-logo.png';
        $pfop = new PersistentFop($testAuth, null);

        $url1 = 'http://phpsdk.qiniudn.com/php-logo.png';
        $url2 = 'http://phpsdk.qiniudn.com/php-sdk.html';
        $zipKey = 'test.zip';

        $fops = 'mkzip/2/url/' . \Qiniu\base64_urlSafeEncode($url1);
        $fops .= '/url/' . \Qiniu\base64_urlSafeEncode($url2);
        $fops .= '|saveas/' . \Qiniu\base64_urlSafeEncode("$bucket:$zipKey");

        list($id, $error) = $pfop->execute($bucket, $key, $fops);
        $this->assertNull($error);

        list($status, $error) = $pfop->status($id);
        $this->assertNotNull($status);
        $this->assertNull($error);
    }
}
