<?php
namespace Qiniu\Tests;

use Qiniu\Storage\ResumeUploader;
use Qiniu\Storage\UploadManager;
use Qiniu\Config;
use Qiniu\Zone;

class ResumeUpTest extends \PHPUnit_Framework_TestCase
{
    protected $bucketName;
    protected $auth;

    protected function setUp()
    {
        global $bucketName;
        $this->bucketName = $bucketName;

        global $testAuth;
        $this->auth = $testAuth;
    }

    public function test4ML()
    {
        $key = 'resumePutFile4ML';
        $upManager = new UploadManager();
        $token = $this->auth->uploadToken($this->bucketName, $key);
        $tempFile = qiniuTempFile(4 * 1024 * 1024 + 10);
        list($ret, $error) = $upManager->putFile($token, $key, $tempFile);
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
        unlink($tempFile);
    }

    public function test4ML2()
    {
        $key = 'resumePutFile4ML';
        $zone = new Zone(array('up.fake.qiniu.com'), array('up.qiniup.com'));
        $cfg = new Config($zone);
        $upManager = new UploadManager($cfg);
        $token = $this->auth->uploadToken($this->bucketName, $key);
        $tempFile = qiniuTempFile(4 * 1024 * 1024 + 10);
        list($ret, $error) = $upManager->putFile($token, $key, $tempFile);
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
        unlink($tempFile);
    }

    // public function test8M()
    // {
    //     $key = 'resumePutFile8M';
    //     $upManager = new UploadManager();
    //     $token = $this->auth->uploadToken($this->bucketName, $key);
    //     $tempFile = qiniuTempFile(8*1024*1024+10);
    //     list($ret, $error) = $upManager->putFile($token, $key, $tempFile);
    //     $this->assertNull($error);
    //     $this->assertNotNull($ret['hash']);
    //     unlink($tempFile);
    // }
}
