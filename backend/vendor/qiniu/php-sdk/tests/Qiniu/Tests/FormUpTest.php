<?php
namespace Qiniu\Tests;

use PHPUnit\Framework\TestCase;

use Qiniu\Http\RequestOptions;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\FormUploader;
use Qiniu\Storage\UploadManager;
use Qiniu\Config;

class FormUpTest extends TestCase
{
    private static $bucketName;
    private static $auth;
    private static $cfg;
    private static $keysToCleanup;

    /**
     * @beforeClass
     */
    public static function setUpConfigAndBucket()
    {
        global $bucketName;
        self::$bucketName = $bucketName;

        global $testAuth;
        self::$auth = $testAuth;
        self::$cfg = new Config();

        self::$keysToCleanup = array();
    }

    /**
     * @afterClass
     */
    public static function cleanupTestData()
    {
        $bucketManager = new BucketManager(self::$auth);
        $ops = BucketManager::buildBatchDelete(self::$bucketName, self::$keysToCleanup);
        // ignore result for cleanup
        $bucketManager->batch($ops);
    }

    private static function getObjectKey($key)
    {
        $result = $key . rand();
        self::$keysToCleanup[] = $result;
        return $result;
    }

    public function testData()
    {
        $key = self::getObjectKey('formput');
        $token = self::$auth->uploadToken(self::$bucketName);
        list($ret, $error) = FormUploader::put($token, $key, 'hello world', self::$cfg, null, 'text/plain', null);
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
    }

    public function testDataWithProxy()
    {
        $key = self::getObjectKey('formput');
        $token = self::$auth->uploadToken(self::$bucketName);
        list($ret, $error) = FormUploader::put(
            $token,
            $key,
            'hello world',
            self::$cfg,
            null,
            'text/plain',
            null,
            $this->makeReqOpt()
        );
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
    }

    public function testData2()
    {
        $key = self::getObjectKey('formput');
        $upManager = new UploadManager();
        $token = self::$auth->uploadToken(self::$bucketName);
        list($ret, $error) = $upManager->put($token, $key, 'hello world', null, 'text/plain', null);
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
    }

    public function testData2WithProxy()
    {
        $key = self::getObjectKey('formput');
        $upManager = new UploadManager();
        $token = self::$auth->uploadToken(self::$bucketName);
        list($ret, $error) = $upManager->put(
            $token,
            $key,
            'hello world',
            null,
            'text/plain',
            null,
            $this->makeReqOpt()
        );
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
    }

    public function testDataFailed()
    {
        $key = self::getObjectKey('formput');
        $token = self::$auth->uploadToken('fakebucket');
        list($ret, $error) = FormUploader::put(
            $token,
            $key,
            'hello world',
            self::$cfg,
            null,
            'text/plain',
            null
        );
        $this->assertNull($ret);
        $this->assertNotNull($error);
    }

    public function testFile()
    {
        $key = self::getObjectKey('formPutFile');
        $token = self::$auth->uploadToken(self::$bucketName, $key);
        list($ret, $error) = FormUploader::putFile(
            $token,
            $key,
            __file__,
            self::$cfg,
            null,
            'text/plain',
            null
        );
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
    }

    public function testFileWithProxy()
    {
        $key = self::getObjectKey('formPutFile');
        $token = self::$auth->uploadToken(self::$bucketName, $key);
        list($ret, $error) = FormUploader::putFile(
            $token,
            $key,
            __file__,
            self::$cfg,
            null,
            'text/plain',
            $this->makeReqOpt()
        );
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
    }

    public function testFile2()
    {
        $key = self::getObjectKey('formPutFile');
        $token = self::$auth->uploadToken(self::$bucketName, $key);
        $upManager = new UploadManager();
        list($ret, $error) = $upManager->putFile($token, $key, __file__, null, 'text/plain', null);
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
    }

    public function testFile2WithProxy()
    {
        $key = self::getObjectKey('formPutFile');
        $token = self::$auth->uploadToken(self::$bucketName, $key);
        $upManager = new UploadManager();
        list($ret, $error) = $upManager->putFile(
            $token,
            $key,
            __file__,
            null,
            'text/plain',
            false,
            null,
            'v1',
            Config::BLOCK_SIZE,
            $this->makeReqOpt()
        );
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
    }

    public function testFileFailed()
    {
        $key = self::getObjectKey('fakekey');
        $token = self::$auth->uploadToken('fakebucket', $key);
        list($ret, $error) = FormUploader::putFile($token, $key, __file__, self::$cfg, null, 'text/plain', null);
        $this->assertNull($ret);
        $this->assertNotNull($error);
    }

    private function makeReqOpt()
    {
        $reqOpt = new RequestOptions();
        $reqOpt->proxy = 'socks5://127.0.0.1:8080';
        $reqOpt->proxy_user_password = 'user:pass';
        return $reqOpt;
    }
}
