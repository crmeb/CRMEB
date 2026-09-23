<?php

namespace Qiniu\Tests;

use PHPUnit\Framework\TestCase;

use Qiniu\Config;
use Qiniu\Storage\BucketManager;

class BucketTest extends TestCase
{
    /**
     * @var BucketManager
     */
    private static $bucketManager;
    private static $dummyBucketManager;
    private static $bucketName;
    private static $key;
    private static $key2;
    private static $customCallbackURL;

    private static $bucketToCreate;

    private static $bucketLifeRuleName;
    private static $bucketLifeRulePrefix;

    private static $bucketEventName;
    private static $bucketEventPrefix;

    private static $keysToCleanup;

    /**
     * @beforeClass
     */
    public static function prepareEnvironment()
    {
        global $bucketName;
        global $key;
        global $key2;
        self::$bucketName = $bucketName;
        self::$key = $key;
        self::$key2 = $key2;

        global $customCallbackURL;
        self::$customCallbackURL = $customCallbackURL;

        global $testAuth;
        $config = new Config();
        self::$bucketManager = new BucketManager($testAuth, $config);

        global $dummyAuth;
        self::$dummyBucketManager = new BucketManager($dummyAuth);

        self::$bucketToCreate = 'phpsdk-ci-test' . rand(1, 1000);

        self::$bucketLifeRuleName = 'bucket_life_rule' . rand(1, 1000);
        self::$bucketLifeRulePrefix = 'prefix-test' . rand(1, 1000);

        self::$bucketEventName = 'bucketevent' . rand(1, 1000);
        self::$bucketEventPrefix = 'event-test' . rand(1, 1000);

        self::$keysToCleanup = array();
    }

    /**
     * @afterClass
     */
    public static function cleanupTestData()
    {
        $ops = BucketManager::buildBatchDelete(self::$bucketName, self::$keysToCleanup);
        // ignore result for cleanup
        self::$bucketManager->batch($ops);
    }

    private static function getObjectKey($key)
    {
        $result = $key . rand();

        self::$bucketManager->copy(
            self::$bucketName,
            $key,
            self::$bucketName,
            $result
        );

        self::$keysToCleanup[] = $result;

        return $result;
    }

    public function testBuckets()
    {

        list($list, $error) = self::$bucketManager->buckets();
        $this->assertNull($error);
        $this->assertTrue(in_array(self::$bucketName, $list));

        list($list2, $error) = self::$dummyBucketManager->buckets();
        $this->assertEquals(401, $error->code());
        $this->assertNotNull($error->message());
        $this->assertNotNull($error->getResponse());
        $this->assertNull($list2);
    }

    public function testListBuckets()
    {
        list($ret, $error) = self::$bucketManager->listbuckets('z0');
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testCreateBucket()
    {
        list($ret, $error) = self::$bucketManager->createBucket(self::$bucketToCreate);
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testDeleteBucket()
    {
        list($ret, $error) = self::$bucketManager->deleteBucket(self::$bucketToCreate);
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testDomains()
    {
        list($ret, $error) = self::$bucketManager->domains(self::$bucketName);
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testBucketInfo()
    {
        list($ret, $error) = self::$bucketManager->bucketInfo(self::$bucketName);
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testBucketInfos()
    {
        list($ret, $error) = self::$bucketManager->bucketInfos('z0');
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testList()
    {
        list($ret, $error) = self::$bucketManager->listFiles(self::$bucketName, null, null, 10);
        $this->assertNull($error);
        $this->assertNotNull($ret['items'][0]);
        $this->assertNotNull($ret['marker']);
    }

    public function testListFilesv2()
    {
        list($ret, $error) = self::$bucketManager->listFilesv2(self::$bucketName, null, null, 10);
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testBucketLifecycleRule()
    {
        // delete
        self::$bucketManager->deleteBucketLifecycleRule(self::$bucketName, self::$bucketLifeRuleName);

        // add
        list($ret, $error) = self::$bucketManager->bucketLifecycleRule(
            self::$bucketName,
            self::$bucketLifeRuleName,
            self::$bucketLifeRulePrefix,
            80,
            70,
            72,
            74,
            71
        );
        $this->assertNull($error);
        $this->assertNotNull($ret);

        // get
        list($ret, $error) = self::$bucketManager->getBucketLifecycleRules(self::$bucketName);
        $this->assertNull($error);
        $this->assertNotNull($ret);
        $rule = null;
        foreach ($ret as $r) {
            if ($r["name"] === self::$bucketLifeRuleName) {
                $rule = $r;
                break;
            }
        }
        $this->assertNotNull($rule);
        $this->assertEquals(self::$bucketLifeRulePrefix, $rule["prefix"]);
        $this->assertEquals(80, $rule["delete_after_days"]);
        $this->assertEquals(70, $rule["to_line_after_days"]);
        $this->assertEquals(71, $rule["to_archive_ir_after_days"]);
        $this->assertEquals(72, $rule["to_archive_after_days"]);
        $this->assertEquals(74, $rule["to_deep_archive_after_days"]);

        // update
        list($ret, $error) = self::$bucketManager->updateBucketLifecycleRule(
            self::$bucketName,
            self::$bucketLifeRuleName,
            'update-' . self::$bucketLifeRulePrefix,
            90,
            75,
            80,
            85,
            78
        );
        $this->assertNull($error);
        $this->assertNotNull($ret);

        // get
        list($ret, $error) = self::$bucketManager->getBucketLifecycleRules(self::$bucketName);
        $this->assertNull($error);
        $this->assertNotNull($ret);
        $rule = null;
        foreach ($ret as $r) {
            if ($r["name"] === self::$bucketLifeRuleName) {
                $rule = $r;
                break;
            }
        }
        $this->assertNotNull($rule);
        $this->assertEquals('update-' . self::$bucketLifeRulePrefix, $rule["prefix"]);
        $this->assertEquals(90, $rule["delete_after_days"]);
        $this->assertEquals(75, $rule["to_line_after_days"]);
        $this->assertEquals(78, $rule["to_archive_ir_after_days"]);
        $this->assertEquals(80, $rule["to_archive_after_days"]);
        $this->assertEquals(85, $rule["to_deep_archive_after_days"]);

        // delete
        list($ret, $error) = self::$bucketManager->deleteBucketLifecycleRule(
            self::$bucketName,
            self::$bucketLifeRuleName
        );
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testPutBucketEvent()
    {
        list($ret, $error) = self::$bucketManager->putBucketEvent(
            self::$bucketName,
            self::$bucketEventName,
            self::$bucketEventPrefix,
            'img',
            array('copy'),
            self::$customCallbackURL
        );
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testUpdateBucketEvent()
    {
        list($ret, $error) = self::$bucketManager->updateBucketEvent(
            self::$bucketName,
            self::$bucketEventName,
            self::$bucketEventPrefix,
            'video',
            array('copy'),
            self::$customCallbackURL
        );
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testGetBucketEvents()
    {
        list($ret, $error) = self::$bucketManager->getBucketEvents(self::$bucketName);
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testDeleteBucketEvent()
    {
        list($ret, $error) = self::$bucketManager->deleteBucketEvent(self::$bucketName, self::$bucketEventName);
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testStat()
    {
        list($stat, $error) = self::$bucketManager->stat(self::$bucketName, self::$key);
        $this->assertNull($error);
        $this->assertNotNull($stat);
        $this->assertNotNull($stat['hash']);

        list($stat, $error) = self::$bucketManager->stat(self::$bucketName, 'nofile');
        $this->assertEquals(612, $error->code());
        $this->assertNotNull($error->message());
        $this->assertNull($stat);

        list($stat, $error) = self::$bucketManager->stat('nobucket', 'nofile');
        $this->assertEquals(631, $error->code());
        $this->assertNotNull($error->message());
        $this->assertNull($stat);
    }

    public function testDelete()
    {
        $fileToDel = self::getObjectKey(self::$key);
        list(, $error) = self::$bucketManager->delete(self::$bucketName, $fileToDel);
        $this->assertNull($error);
    }


    public function testRename()
    {
        $fileToRename = self::getObjectKey(self::$key);
        $fileRenamed = $fileToRename . 'new';
        list(, $error) = self::$bucketManager->rename(self::$bucketName, $fileToRename, $fileRenamed);
        $this->assertNull($error);
        self::$keysToCleanup[] = $fileRenamed;
    }


    public function testCopy()
    {
        $fileToCopy = self::getObjectKey(self::$key2);
        $fileCopied = $fileToCopy . 'copied';

        //test force copy
        list(, $error) = self::$bucketManager->copy(
            self::$bucketName,
            $fileToCopy,
            self::$bucketName,
            $fileCopied,
            true
        );
        $this->assertNull($error);

        list($fileToCopyStat,) = self::$bucketManager->stat(self::$bucketName, $fileToCopy);
        list($fileCopiedStat,) = self::$bucketManager->stat(self::$bucketName, $fileCopied);

        $this->assertEquals($fileToCopyStat['hash'], $fileCopiedStat['hash']);

        self::$keysToCleanup[] = $fileCopied;
    }


    public function testChangeMime()
    {
        $fileToChange = self::getObjectKey('php-sdk.html');
        list(, $error) = self::$bucketManager->changeMime(
            self::$bucketName,
            $fileToChange,
            'text/plain'
        );
        $this->assertNull($error);

        list($ret, $error) = self::$bucketManager->stat(
            self::$bucketName,
            $fileToChange
        );
        $this->assertNull($error);
        $this->assertEquals('text/plain', $ret['mimeType']);
    }

    public function testPrefetch()
    {
        list($ret, $error) = self::$bucketManager->prefetch(
            self::$bucketName,
            'php-sdk.html'
        );
        $this->assertNull($error);
        $this->assertNotNull($ret);
    }

    public function testPrefetchFailed()
    {
        list($ret, $error) = self::$bucketManager->prefetch(
            'fakebucket',
            'php-sdk.html'
        );
        $this->assertNotNull($error);
        $this->assertNull($ret);
    }

    public function testFetch()
    {
        list($ret, $error) = self::$bucketManager->fetch(
            'http://developer.qiniu.com/docs/v6/sdk/php-sdk.html',
            self::$bucketName,
            'fetch.html'
        );
        $this->assertNull($error);
        $this->assertArrayHasKey('hash', $ret);

        list($ret, $error) = self::$bucketManager->fetch(
            'http://developer.qiniu.com/docs/v6/sdk/php-sdk.html',
            self::$bucketName,
            ''
        );
        $this->assertNull($error);
        $this->assertArrayHasKey('key', $ret);

        list($ret, $error) = self::$bucketManager->fetch(
            'http://developer.qiniu.com/docs/v6/sdk/php-sdk.html',
            self::$bucketName
        );
        $this->assertNull($error);
        $this->assertArrayHasKey('key', $ret);
    }

    public function testFetchFailed()
    {
        list($ret, $error) = self::$bucketManager->fetch(
            'http://developer.qiniu.com/docs/v6/sdk/php-sdk.html',
            'fakebucket'
        );
        $this->assertNotNull($error);
        $this->assertNull($ret);
    }

    public function testAsynchFetch()
    {
        list($ret, $error) = self::$bucketManager->asynchFetch(
            'http://devtools.qiniu.com/qiniu.png',
            self::$bucketName,
            null,
            'qiniu.png'
        );
        $this->assertNull($error);
        $this->assertArrayHasKey('id', $ret);

        list($ret, $error) = self::$bucketManager->asynchFetch(
            'http://devtools.qiniu.com/qiniu.png',
            self::$bucketName,
            null,
            ''
        );
        $this->assertNull($error);
        $this->assertArrayHasKey('id', $ret);

        list($ret, $error) = self::$bucketManager->asynchFetch(
            'http://devtools.qiniu.com/qiniu.png',
            self::$bucketName
        );
        $this->assertNull($error);
        $this->assertArrayHasKey('id', $ret);
    }

    public function testAsynchFetchFailed()
    {
        list($ret, $error) = self::$bucketManager->asynchFetch(
            'http://devtools.qiniu.com/qiniu.png',
            'fakebucket'
        );
        $this->assertNotNull($error);
        $this->assertNull($ret);
    }


    public function testBatchCopy()
    {
        $key = 'copyto' . rand();
        $ops = BucketManager::buildBatchCopy(
            self::$bucketName,
            array(self::$key => $key),
            self::$bucketName,
            true
        );
        list($ret, $error) = self::$bucketManager->batch($ops);
        $this->assertNull($error);
        $this->assertEquals(200, $ret[0]['code']);

        self::$keysToCleanup[] = $key;
    }

    public function testBatchMove()
    {
        $fileToMove = self::getObjectKey(self::$key);
        $fileMoved = $fileToMove . 'to';
        $ops = BucketManager::buildBatchMove(
            self::$bucketName,
            array($fileToMove => $fileMoved),
            self::$bucketName,
            true
        );
        list($ret, $error) = self::$bucketManager->batch($ops);
        $this->assertNull($error);
        $this->assertEquals(200, $ret[0]['code']);
        self::$keysToCleanup[] = $fileMoved;
    }

    public function testBatchRename()
    {
        $fileToRename = self::getObjectKey(self::$key);
        $fileRenamed = $fileToRename . 'to';

        $ops = BucketManager::buildBatchRename(
            self::$bucketName,
            array($fileToRename => $fileRenamed),
            true
        );
        list($ret, $error) = self::$bucketManager->batch($ops);
        $this->assertNull($error);
        $this->assertEquals(200, $ret[0]['code']);

        self::$keysToCleanup[] = $fileRenamed;
    }

    public function testBatchStat()
    {
        $ops = BucketManager::buildBatchStat(self::$bucketName, array('php-sdk.html'));
        list($ret, $error) = self::$bucketManager->batch($ops);
        $this->assertNull($error);
        $this->assertEquals(200, $ret[0]['code']);
    }

    public function testBatchChangeTypeAndBatchRestoreAr()
    {
        $key = self::getObjectKey(self::$key);

        $ops = BucketManager::buildBatchChangeType(self::$bucketName, array($key => 2)); // 2 Archive
        list($ret, $error) = self::$bucketManager->batch($ops);
        $this->assertNull($error);
        $this->assertEquals(200, $ret[0]['code']);

        $ops = BucketManager::buildBatchRestoreAr(self::$bucketName, array($key => 1)); // 1 day
        list($ret, $error) = self::$bucketManager->batch($ops);
        $this->assertNull($error);
        $this->assertEquals(200, $ret[0]['code']);
    }

    public function testDeleteAfterDays()
    {
        $key = "noexist" . rand();
        list($ret, $error) = self::$bucketManager->deleteAfterDays(self::$bucketName, $key, 1);
        $this->assertNotNull($error);
        $this->assertNull($ret);

        $key = self::getObjectKey(self::$key);
        list(, $error) = self::$bucketManager->deleteAfterDays(self::$bucketName, $key, 1);
        $this->assertNull($error);

        list($ret, $error) = self::$bucketManager->stat(self::$bucketName, $key);
        $this->assertNull($error);
        $this->assertGreaterThan(23 * 3600, $ret['expiration'] - time());
        $this->assertLessThan(48 * 3600, $ret['expiration'] - time());
    }

    public function testSetObjectLifecycle()
    {
        $key = self::getObjectKey(self::$key);

        list(, $err) = self::$bucketManager->setObjectLifecycle(
            self::$bucketName,
            $key,
            10,
            20,
            30,
            40,
            15
        );
        $this->assertNull($err);

        list($ret, $error) = self::$bucketManager->stat(self::$bucketName, $key);
        $this->assertNull($error);
        $this->assertNotNull($ret['transitionToIA']);
        $this->assertNotNull($ret['transitionToArchiveIR']);
        $this->assertNotNull($ret['transitionToARCHIVE']);
        $this->assertNotNull($ret['transitionToDeepArchive']);
        $this->assertNotNull($ret['expiration']);
    }

    public function testSetObjectLifecycleWithCond()
    {
        $key = self::getObjectKey(self::$key);

        list($ret, $err) = self::$bucketManager->stat(self::$bucketName, $key);
        $this->assertNull($err);
        $key_hash = $ret['hash'];
        $key_fsize = $ret['fsize'];

        list(, $err) = self::$bucketManager->setObjectLifecycleWithCond(
            self::$bucketName,
            $key,
            array(
                'hash' => $key_hash,
                'fsize' => $key_fsize
            ),
            10,
            20,
            30,
            40,
            15
        );
        $this->assertNull($err);

        list($ret, $error) = self::$bucketManager->stat(self::$bucketName, $key);
        $this->assertNull($error);
        $this->assertNotNull($ret['transitionToIA']);
        $this->assertNotNull($ret['transitionToArchiveIR']);
        $this->assertNotNull($ret['transitionToARCHIVE']);
        $this->assertNotNull($ret['transitionToDeepArchive']);
        $this->assertNotNull($ret['expiration']);
    }

    public function testBatchSetObjectLifecycle()
    {
        $key = self::getObjectKey(self::$key);

        $ops = BucketManager::buildBatchSetObjectLifecycle(
            self::$bucketName,
            array($key),
            10,
            20,
            30,
            40,
            15
        );
        list($ret, $err) = self::$bucketManager->batch($ops);
        $this->assertNull($err);
        $this->assertEquals(200, $ret[0]['code']);
    }

    public function testGetCorsRules()
    {
        list(, $err) = self::$bucketManager->getCorsRules(self::$bucketName);
        $this->assertNull($err);
    }

    public function testPutBucketAccessStyleMode()
    {
        list(, $err) = self::$bucketManager->putBucketAccessStyleMode(self::$bucketName, 0);
        $this->assertNull($err);
    }

    public function testPutBucketAccessMode()
    {
        list(, $err) = self::$bucketManager->putBucketAccessMode(self::$bucketName, 0);
        $this->assertNull($err);
    }

    public function testPutReferAntiLeech()
    {
        list(, $err) = self::$bucketManager->putReferAntiLeech(self::$bucketName, 0, "1", "*");
        $this->assertNull($err);
    }

    public function testPutBucketMaxAge()
    {
        list(, $err) = self::$bucketManager->putBucketMaxAge(self::$bucketName, 31536000);
        $this->assertNull($err);
    }

    public function testPutBucketQuota()
    {
        list(, $err) = self::$bucketManager->putBucketQuota(self::$bucketName, -1, -1);
        $this->assertNull($err);
    }

    public function testGetBucketQuota()
    {
        list(, $err) = self::$bucketManager->getBucketQuota(self::$bucketName);
        $this->assertNull($err);
    }

    public function testChangeType()
    {
        $fileToChange = self::getObjectKey(self::$key);

        list(, $err) = self::$bucketManager->changeType(self::$bucketName, $fileToChange, 0);
        $this->assertNull($err);

        list(, $err) = self::$bucketManager->changeType(self::$bucketName, $fileToChange, 1);
        $this->assertNull($err);
    }

    public function testArchiveRestoreAr()
    {
        $key =  self::getObjectKey(self::$key);

        self::$bucketManager->changeType(self::$bucketName, $key, 2);

        list(, $err) = self::$bucketManager->restoreAr(self::$bucketName, $key, 2);
        $this->assertNull($err);

        list($ret, $err) = self::$bucketManager->stat(self::$bucketName, $key);
        $this->assertNull($err);

        $this->assertEquals(2, $ret["type"]);

        // restoreStatus
        // null means frozen;
        // 1 means be unfreezing;
        // 2 means be unfrozen;
        $this->assertNotNull($ret["restoreStatus"]);
        $this->assertContains($ret["restoreStatus"], array(1, 2));
    }

    public function testDeepArchiveRestoreAr()
    {
        $key =  self::getObjectKey(self::$key);

        self::$bucketManager->changeType(self::$bucketName, $key, 3);

        list(, $err) = self::$bucketManager->restoreAr(self::$bucketName, $key, 1);
        $this->assertNull($err);
        list($ret, $err) = self::$bucketManager->stat(self::$bucketName, $key);
        $this->assertNull($err);

        $this->assertEquals(3, $ret["type"]);

        // restoreStatus
        // null means frozen;
        // 1 means be unfreezing;
        // 2 means be unfrozen;
        $this->assertNotNull($ret["restoreStatus"]);
        $this->assertContains($ret["restoreStatus"], array(1, 2));
    }

    public function testChangeStatus()
    {
        $key = self::getObjectKey(self::$key);

        list(, $err) = self::$bucketManager->changeStatus(self::$bucketName, $key, 1);
        $this->assertNull($err);
        list($ret, $err) = self::$bucketManager->stat(self::$bucketName, $key);
        $this->assertNull($err);
        $this->assertEquals(1, $ret['status']);

        list(, $err) = self::$bucketManager->changeStatus(self::$bucketName, $key, 0);
        $this->assertNull($err);
        list($ret, $err) = self::$bucketManager->stat(self::$bucketName, $key);
        $this->assertNull($err);
        $this->assertArrayNotHasKey('status', $ret);
    }
}
