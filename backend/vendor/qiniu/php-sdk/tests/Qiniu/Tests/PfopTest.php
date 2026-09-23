<?php
namespace Qiniu\Tests;

use PHPUnit\Framework\TestCase;

use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;
use Qiniu\Storage\UploadManager;

//use Qiniu\Region;
//use Qiniu\Config;

class PfopTest extends TestCase
{
    /**
     * @var Auth
     */
    private static $testAuth;

    private static $bucketName;

    /**
     * @beforeClass
     */
    public static function prepareEnvironment()
    {
        global $bucketName;
        global $testAuth;

        self::$bucketName = $bucketName;
        self::$testAuth = $testAuth;
    }

    private static function getConfig()
    {
        // use this func to test in test env
        // `null` means to use production env
        return null;
    }

    public function testPfopExecuteAndStatusWithSingleFop()
    {
        global $testAuth;
        $bucket = 'testres';
        $key = 'sintel_trailer.mp4';
        $pfop = new PersistentFop($testAuth, self::getConfig());

        $fops = 'avthumb/m3u8/segtime/10/vcodec/libx264/s/320x240';
        list($id, $error) = $pfop->execute($bucket, $key, $fops);
        $this->assertNull($error);
        list($status, $error) = $pfop->status($id);
        $this->assertNotNull($status);
        $this->assertNull($error);
    }


    public function testPfopExecuteAndStatusWithMultipleFops()
    {
        global $testAuth;
        $bucket = 'testres';
        $key = 'sintel_trailer.mp4';
        $fops = array(
            'avthumb/m3u8/segtime/10/vcodec/libx264/s/320x240',
            'vframe/jpg/offset/7/w/480/h/360',
        );
        $pfop = new PersistentFop($testAuth, self::getConfig());

        list($id, $error) = $pfop->execute($bucket, $key, $fops);
        $this->assertNull($error);

        list($status, $error) = $pfop->status($id);
        $this->assertNotNull($status);
        $this->assertNull($error);
    }

    private function pfopOptionsTestData()
    {
        return array(
            array(
                'type' => null
            ),
            array(
                'type' => -1
            ),
            array(
                'type' => 0
            ),
            array(
                'type' => 1
            ),
            array(
                'type' => 2
            ),
            array(
                'workflowTemplateID' => 'test-workflow'
            )
        );
    }

    public function testPfopExecuteWithOptions()
    {
        $bucket = self::$bucketName;
        $key = 'qiniu.png';
        $pfop = new PersistentFop(self::$testAuth, self::getConfig());

        $testCases = $this->pfopOptionsTestData();

        foreach ($testCases as $testCase) {
            $workflowTemplateID = null;
            $type = null;

            if (array_key_exists('workflowTemplateID', $testCase)) {
                $workflowTemplateID = $testCase['workflowTemplateID'];
            }
            if (array_key_exists('type', $testCase)) {
                $type = $testCase['type'];
            }

            if ($workflowTemplateID) {
                $fops = null;
            } else {
                $persistentEntry =  \Qiniu\entry(
                    $bucket,
                    implode(
                        '_',
                        array(
                            'test-pfop/test-pfop-by-api',
                            'type',
                            $type
                        )
                    )
                );
                $fops = 'avinfo|saveas/' . $persistentEntry;
            }
            list($id, $error) = $pfop->execute(
                $bucket,
                $key,
                $fops,
                null,
                null,
                false,
                $type,
                $workflowTemplateID
            );

            if (in_array($type, array(null, 0, 1))) {
                $this->assertNull($error);
                list($status, $error) = $pfop->status($id);
                $this->assertNotNull($status);
                $this->assertNull($error);
                if ($type == 1) {
                    $this->assertEquals(1, $status['type']);
                }
                if ($workflowTemplateID) {
                    // assertStringContainsString when PHPUnit >= 8.0
                    $this->assertTrue(
                        strpos(
                            $status['taskFrom'],
                            $workflowTemplateID
                        ) !== false
                    );
                }
                $this->assertNotEmpty($status['creationDate']);
            } else {
                $this->assertNotNull($error);
            }
        }
    }

    public function testPfopWithInvalidArgument()
    {
        $bucket = self::$bucketName;
        $key = 'qiniu.png';
        $pfop = new PersistentFop(self::$testAuth, self::getConfig());
        $err = null;
        try {
            $pfop->execute(
                $bucket,
                $key
            );
        } catch (\Exception $e) {
            $err = $e;
        }

        $this->assertNotEmpty($err);
        $this->assertTrue(
            strpos(
                $err->getMessage(),
                'Must provide one of fops or template_id'
            ) !== false
        );
    }

    public function testPfopWithUploadPolicy()
    {
        $bucket = self::$bucketName;
        $testAuth = self::$testAuth;
        $key = 'test-pfop/upload-file';

        $testCases = $this->pfopOptionsTestData();

        foreach ($testCases as $testCase) {
            $workflowTemplateID = null;
            $type = null;

            if (array_key_exists('workflowTemplateID', $testCase)) {
                $workflowTemplateID = $testCase['workflowTemplateID'];
            }
            if (array_key_exists('type', $testCase)) {
                $type = $testCase['type'];
            }

            $putPolicy = array(
                'persistentType' => $type
            );
            if ($workflowTemplateID) {
                $putPolicy['persistentWorkflowTemplateID'] = $workflowTemplateID;
            } else {
                $persistentEntry =  \Qiniu\entry(
                    $bucket,
                    implode(
                        '_',
                        array(
                            'test-pfop/test-pfop-by-upload',
                            'type',
                            $type
                        )
                    )
                );
                $putPolicy['persistentOps'] = 'avinfo|saveas/' . $persistentEntry;
            }

            if ($type == null) {
                unset($putPolicy['persistentType']);
            }

            $token = $testAuth->uploadToken(
                $bucket,
                $key,
                3600,
                $putPolicy
            );
            $upManager = new UploadManager(self::getConfig());
            list($ret, $error) = $upManager->putFile(
                $token,
                $key,
                __file__,
                null,
                'text/plain',
                true
            );

            if (in_array($type, array(null, 0, 1))) {
                $this->assertNull($error);
                $this->assertNotEmpty($ret['persistentId']);
                $id = $ret['persistentId'];
            } else {
                $this->assertNotNull($error);
                return;
            }

            $pfop = new PersistentFop($testAuth, self::getConfig());
            list($status, $error) = $pfop->status($id);

            $this->assertNotNull($status);
            $this->assertNull($error);
            if ($type == 1) {
                $this->assertEquals(1, $status['type']);
            }
            if ($workflowTemplateID) {
                // assertStringContainsString when PHPUnit >= 8.0
                $this->assertTrue(
                    strpos(
                        $status['taskFrom'],
                        $workflowTemplateID
                    ) !== false
                );
            }
            $this->assertNotEmpty($status['creationDate']);
        }
    }

    public function testMkzip()
    {
        $bucket = self::$bucketName;
        $key = 'php-logo.png';
        $pfop = new PersistentFop(self::$testAuth, null);

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
