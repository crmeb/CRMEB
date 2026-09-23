<?php
namespace Qiniu\Tests;

use PHPUnit\Framework\TestCase;

use Qiniu\Http\RequestOptions;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Qiniu\Http\Client;
use Qiniu\Config;

class ResumeUpTest extends TestCase
{
    private static $bucketName;

    private static $auth;

    private static $keysToCleanup = array();

    /**
     * @beforeClass
     */
    public static function setUpAuthAndBucket()
    {
        global $bucketName;
        self::$bucketName = $bucketName;

        global $testAuth;
        self::$auth = $testAuth;
    }


    /**
     * @afterClass
     */
    public static function cleanupTestData()
    {
        $ops = BucketManager::buildBatchDelete(self::$bucketName, self::$keysToCleanup);

        $bucketManager = new BucketManager(self::$auth);
        $bucketManager->batch($ops);
    }

    private static function getObjectKey($key)
    {
        $result = $key . rand();
        self::$keysToCleanup[] = $result;
        return $result;
    }

    public function test4ML()
    {
        $key = self::getObjectKey('resumePutFile4ML_');
        $upManager = new UploadManager();
        $token = self::$auth->uploadToken(self::$bucketName, $key);
        $tempFile = qiniuTempFile(4 * 1024 * 1024 + 10);
        $resumeFile = tempnam(sys_get_temp_dir(), 'resume_file');
        $this->assertNotFalse($resumeFile);
        list($ret, $error) = $upManager->putFile(
            $token,
            $key,
            $tempFile,
            null,
            'application/octet-stream',
            false,
            $resumeFile
        );
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);

        $domain = getenv('QINIU_TEST_DOMAIN');
        $response = Client::get("http://$domain/$key");
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals(md5_file($tempFile, true), md5($response->body(), true));
        unlink($tempFile);
    }

    public function test4ML2()
    {
        $key = self::getObjectKey('resumePutFile4ML_');
        $cfg = new Config();
        $upManager = new UploadManager($cfg);
        $token = self::$auth->uploadToken(self::$bucketName, $key);
        $tempFile = qiniuTempFile(4 * 1024 * 1024 + 10);
        $resumeFile = tempnam(sys_get_temp_dir(), 'resume_file');
        $this->assertNotFalse($resumeFile);
        list($ret, $error) = $upManager->putFile(
            $token,
            $key,
            $tempFile,
            null,
            'application/octet-stream',
            false,
            $resumeFile
        );
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);

        $domain = getenv('QINIU_TEST_DOMAIN');
        $response = Client::get("http://$domain/$key");
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals(md5_file($tempFile, true), md5($response->body(), true));
        unlink($tempFile);
    }

    public function test4ML2WithProxy()
    {
        $key = self::getObjectKey('resumePutFile4ML_');
        $cfg = new Config();
        $upManager = new UploadManager($cfg);
        $token = self::$auth->uploadToken(self::$bucketName, $key);
        $tempFile = qiniuTempFile(4 * 1024 * 1024 + 10);
        $resumeFile = tempnam(sys_get_temp_dir(), 'resume_file');
        $this->assertNotFalse($resumeFile);
        list($ret, $error) = $upManager->putFile(
            $token,
            $key,
            $tempFile,
            null,
            'application/octet-stream',
            false,
            $resumeFile,
            'v2',
            Config::BLOCK_SIZE,
            $this->makeReqOpt()
        );
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);

        $domain = getenv('QINIU_TEST_DOMAIN');
        $response = Client::get("http://$domain/$key");
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals(md5_file($tempFile, true), md5($response->body(), true));
        unlink($tempFile);
    }

    // public function test8M()
    // {
    //     $key = 'resumePutFile8M';
    //     $upManager = new UploadManager();
    //     $token = self::$auth->uploadToken(self::$bucketName, $key);
    //     $tempFile = qiniuTempFile(8*1024*1024+10);
    //     list($ret, $error) = $upManager->putFile($token, $key, $tempFile);
    //     $this->assertNull($error);
    //     $this->assertNotNull($ret['hash']);
    //     unlink($tempFile);
    // }

    public function testFileWithFileType()
    {
        $config = new Config();
        $bucketManager = new BucketManager(self::$auth, $config);

        $testCases = array(
            array(
                "fileType" => 1,
                "name" => "IA"
            ),
            array(
                "fileType" => 2,
                "name" => "Archive"
            ),
            array(
                "fileType" => 3,
                "name" => "DeepArchive"
            )
        );

        foreach ($testCases as $testCase) {
            $key = self::getObjectKey('FileType' . $testCase["name"]);
            $police = array(
                "fileType" => $testCase["fileType"],
            );
            $token = self::$auth->uploadToken(self::$bucketName, $key, 3600, $police);
            $upManager = new UploadManager();
            list($ret, $error) = $upManager->putFile($token, $key, __file__, null, 'text/plain');
            $this->assertNull($error);
            $this->assertNotNull($ret);
            list($ret, $err) = $bucketManager->stat(self::$bucketName, $key);
            $this->assertNull($err);
            $this->assertEquals($testCase["fileType"], $ret["type"]);
        }
    }

    public function testResumeUploadWithParams()
    {
        $key = self::getObjectKey('resumePutFile4ML_');
        $upManager = new UploadManager();
        $policy = array('returnBody' => '{"hash":$(etag),"fname":$(fname),"var_1":$(x:var_1),"var_2":$(x:var_2)}');
        $token = self::$auth->uploadToken(self::$bucketName, $key, 3600, $policy);
        $tempFile = qiniuTempFile(4 * 1024 * 1024 + 10);
        $resumeFile = tempnam(sys_get_temp_dir(), 'resume_file');
        $this->assertNotFalse($resumeFile);
        list($ret, $error) = $upManager->putFile(
            $token,
            $key,
            $tempFile,
            array("x:var_1" => "val_1", "x:var_2" => "val_2", "x-qn-meta-m1" => "val_1", "x-qn-meta-m2" => "val_2"),
            'application/octet-stream',
            false,
            $resumeFile
        );
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
        $this->assertEquals("val_1", $ret['var_1']);
        $this->assertEquals("val_2", $ret['var_2']);
        $this->assertEquals(basename($tempFile), $ret['fname']);

        $domain = getenv('QINIU_TEST_DOMAIN');
        $response = Client::get("http://$domain/$key");
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals(md5_file($tempFile, true), md5($response->body(), true));
        $headers = $response->headers();
        $this->assertEquals("val_1", $headers["X-Qn-Meta-M1"]);
        $this->assertEquals("val_2", $headers["X-Qn-Meta-M2"]);
        unlink($tempFile);
    }

    public function testResumeUploadV2()
    {
        $cfg = new Config();
        $upManager = new UploadManager($cfg);
        $testFileSize = array(
            config::BLOCK_SIZE / 2,
            config::BLOCK_SIZE,
            config::BLOCK_SIZE + 10,
            config::BLOCK_SIZE * 2,
            config::BLOCK_SIZE * 2.5
        );
        $partSize = 5 * 1024 * 1024;
        foreach ($testFileSize as $item) {
            $key = self::getObjectKey('resumePutFile4ML_');
            $token = self::$auth->uploadToken(self::$bucketName, $key);
            $tempFile = qiniuTempFile($item);
            $resumeFile = tempnam(sys_get_temp_dir(), 'resume_file');
            $this->assertNotFalse($resumeFile);
            list($ret, $error) = $upManager->putFile(
                $token,
                $key,
                $tempFile,
                null,
                'application/octet-stream',
                false,
                $resumeFile,
                'v2',
                $partSize
            );
            $this->assertNull($error);
            $this->assertNotNull($ret['hash']);

            $domain = getenv('QINIU_TEST_DOMAIN');
            $response = Client::get("http://$domain/$key");
            $this->assertEquals(200, $response->statusCode);
            $this->assertEquals(md5_file($tempFile, true), md5($response->body(), true));
            unlink($tempFile);
        }
    }

    public function testResumeUploadV2WithParams()
    {
        $key = self::getObjectKey('resumePutFile4ML_');
        $upManager = new UploadManager();
        $policy = array('returnBody' => '{"hash":$(etag),"fname":$(fname),"var_1":$(x:var_1),"var_2":$(x:var_2)}');
        $token = self::$auth->uploadToken(self::$bucketName, $key, 3600, $policy);
        $tempFile = qiniuTempFile(4 * 1024 * 1024 + 10);
        $resumeFile = tempnam(sys_get_temp_dir(), 'resume_file');
        $this->assertNotFalse($resumeFile);
        list($ret, $error) = $upManager->putFile(
            $token,
            $key,
            $tempFile,
            array("x:var_1" => "val_1", "x:var_2" => "val_2", "x-qn-meta-m1" => "val_1", "x-qn-meta-m2" => "val_2"),
            'application/octet-stream',
            false,
            $resumeFile,
            'v2'
        );
        $this->assertNull($error);
        $this->assertNotNull($ret['hash']);
        $this->assertEquals("val_1", $ret['var_1']);
        $this->assertEquals("val_2", $ret['var_2']);
        $this->assertEquals(basename($tempFile), $ret['fname']);

        $domain = getenv('QINIU_TEST_DOMAIN');
        $response = Client::get("http://$domain/$key");
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals(md5_file($tempFile, true), md5($response->body(), true));
        $headers = $response->headers();
        $this->assertEquals("val_1", $headers["X-Qn-Meta-M1"]);
        $this->assertEquals("val_2", $headers["X-Qn-Meta-M2"]);
        unlink($tempFile);
    }

    // valid versions are tested above
    // Use PHPUnit's Data Provider to test multiple Exception is better,
    // but not match the test style of this project
    public function testResumeUploadWithInvalidVersion()
    {
        $cfg = new Config();
        $upManager = new UploadManager($cfg);
        $testFileSize = config::BLOCK_SIZE * 2;
        $partSize = 5 * 1024 * 1024;
        $testInvalidVersions = array(
            // High probability invalid versions
            'v',
            '1',
            '2'
        );

        $expectExceptionCount = 0;
        foreach ($testInvalidVersions as $invalidVersion) {
            $key = self::getObjectKey('resumePutFile4ML_');
            $token = self::$auth->uploadToken(self::$bucketName, $key);
            $tempFile = qiniuTempFile($testFileSize);
            $resumeFile = tempnam(sys_get_temp_dir(), 'resume_file');
            $this->assertNotFalse($resumeFile);
            try {
                $upManager->putFile(
                    $token,
                    $key,
                    $tempFile,
                    null,
                    'application/octet-stream',
                    false,
                    $resumeFile,
                    $invalidVersion,
                    $partSize
                );
            } catch (\Exception $e) {
                $isRightException = false;
                $expectExceptionCount++;
                while ($e) {
                    $isRightException = $e instanceof \UnexpectedValueException;
                    if ($isRightException) {
                        break;
                    }
                    $e = $e->getPrevious();
                }
                $this->assertTrue($isRightException);
            }

            unlink($tempFile);
        }
        $this->assertEquals(count($testInvalidVersions), $expectExceptionCount);
    }

    private function makeReqOpt()
    {
        $reqOpt = new RequestOptions();
        $reqOpt->proxy = 'socks5://127.0.0.1:8080';
        $reqOpt->proxy_user_password = 'user:pass';
        return $reqOpt;
    }
}
