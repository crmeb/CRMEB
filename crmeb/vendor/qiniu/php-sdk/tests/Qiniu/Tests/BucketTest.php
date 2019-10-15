<?php
namespace Qiniu\Tests;

use Qiniu\Storage\BucketManager;

class BucketTest extends \PHPUnit_Framework_TestCase
{
    protected $bucketManager;
    protected $dummyBucketManager;
    protected $bucketName;
    protected $key;
    protected $key2;

    protected function setUp()
    {
        global $bucketName;
        global $key;
        global $key2;
        $this->bucketName = $bucketName;
        $this->key = $key;
        $this->key2 = $key2;

        global $testAuth;
        $this->bucketManager = new BucketManager($testAuth);

        global $dummyAuth;
        $this->dummyBucketManager = new BucketManager($dummyAuth);
    }

    public function testBuckets()
    {

        list($list, $error) = $this->bucketManager->buckets();
        $this->assertTrue(in_array($this->bucketName, $list));
        $this->assertNull($error);

        list($list2, $error) = $this->dummyBucketManager->buckets();
        $this->assertEquals(401, $error->code());
        $this->assertNull($list2);
        $this->assertNotNull($error->message());
    }

    public function testList()
    {
        list($ret, $error) = $this->bucketManager->listFiles($this->bucketName, null, null, 10);
        $this->assertNotNull($ret['items'][0]);
        $this->assertNotNull($ret['marker']);
    }

    public function testStat()
    {
        list($stat, $error) = $this->bucketManager->stat($this->bucketName, $this->key);
        $this->assertNotNull($stat);
        $this->assertNull($error);
        $this->assertNotNull($stat['hash']);

        list($stat, $error) = $this->bucketManager->stat($this->bucketName, 'nofile');
        $this->assertNull($stat);
        $this->assertEquals(612, $error->code());
        $this->assertNotNull($error->message());

        list($stat, $error) = $this->bucketManager->stat('nobucket', 'nofile');
        $this->assertNull($stat);
        $this->assertEquals(631, $error->code());
        $this->assertNotNull($error->message());
    }

    public function testDelete()
    {
        $error = $this->bucketManager->delete($this->bucketName, 'del');
        $this->assertEquals(612, $error->code());
    }


    public function testRename()
    {
        $key = 'renamefrom' . rand();
        $this->bucketManager->copy($this->bucketName, $this->key, $this->bucketName, $key);
        $key2 = 'renameto' . $key;
        $error = $this->bucketManager->rename($this->bucketName, $key, $key2);
        $this->assertNull($error);
        $error = $this->bucketManager->delete($this->bucketName, $key2);
        $this->assertNull($error);
    }


    public function testCopy()
    {
        $key = 'copyto' . rand();
        $this->bucketManager->delete($this->bucketName, $key);

        $error = $this->bucketManager->copy(
            $this->bucketName,
            $this->key,
            $this->bucketName,
            $key
        );
        $this->assertNull($error);

        //test force copy
        $error = $this->bucketManager->copy(
            $this->bucketName,
            $this->key2,
            $this->bucketName,
            $key,
            true
        );
        $this->assertNull($error);

        list($key2Stat,) = $this->bucketManager->stat($this->bucketName, $this->key2);
        list($key2CopiedStat,) = $this->bucketManager->stat($this->bucketName, $key);

        $this->assertEquals($key2Stat['hash'], $key2CopiedStat['hash']);

        $error = $this->bucketManager->delete($this->bucketName, $key);
        $this->assertNull($error);
    }


    public function testChangeMime()
    {
        $error = $this->bucketManager->changeMime(
            $this->bucketName,
            'php-sdk.html',
            'text/html'
        );
        $this->assertNull($error);
    }

    public function testPrefetch()
    {
        $error = $this->bucketManager->prefetch(
            $this->bucketName,
            'php-sdk.html'
        );
        $this->assertNull($error);
    }

    public function testFetch()
    {
        list($ret, $error) = $this->bucketManager->fetch(
            'http://developer.qiniu.com/docs/v6/sdk/php-sdk.html',
            $this->bucketName,
            'fetch.html'
        );
        $this->assertArrayHasKey('hash', $ret);
        $this->assertNull($error);

        list($ret, $error) = $this->bucketManager->fetch(
            'http://developer.qiniu.com/docs/v6/sdk/php-sdk.html',
            $this->bucketName,
            ''
        );
        $this->assertArrayHasKey('key', $ret);
        $this->assertNull($error);

        list($ret, $error) = $this->bucketManager->fetch(
            'http://developer.qiniu.com/docs/v6/sdk/php-sdk.html',
            $this->bucketName
        );
        $this->assertArrayHasKey('key', $ret);
        $this->assertNull($error);
    }

    public function testBatchCopy()
    {
        $key = 'copyto' . rand();
        $ops = BucketManager::buildBatchCopy(
            $this->bucketName,
            array($this->key => $key),
            $this->bucketName,
            true
        );
        list($ret, $error) = $this->bucketManager->batch($ops);
        $this->assertEquals(200, $ret[0]['code']);
        $ops = BucketManager::buildBatchDelete($this->bucketName, array($key));
        list($ret, $error) = $this->bucketManager->batch($ops);
        $this->assertEquals(200, $ret[0]['code']);
    }

    public function testBatchMove()
    {
        $key = 'movefrom' . rand();
        $this->bucketManager->copy($this->bucketName, $this->key, $this->bucketName, $key);
        $key2 = $key . 'to';
        $ops = BucketManager::buildBatchMove(
            $this->bucketName,
            array($key => $key2),
            $this->bucketName,
            true
        );
        list($ret, $error) = $this->bucketManager->batch($ops);
        $this->assertEquals(200, $ret[0]['code']);
        $error = $this->bucketManager->delete($this->bucketName, $key2);
        $this->assertNull($error);
    }

    public function testBatchRename()
    {
        $key = 'rename' . rand();
        $this->bucketManager->copy($this->bucketName, $this->key, $this->bucketName, $key);
        $key2 = $key . 'to';
        $ops = BucketManager::buildBatchRename($this->bucketName, array($key => $key2), true);
        list($ret, $error) = $this->bucketManager->batch($ops);
        $this->assertEquals(200, $ret[0]['code']);
        $error = $this->bucketManager->delete($this->bucketName, $key2);
        $this->assertNull($error);
    }

    public function testBatchStat()
    {
        $ops = BucketManager::buildBatchStat($this->bucketName, array('php-sdk.html'));
        list($ret, $error) = $this->bucketManager->batch($ops);
        $this->assertEquals(200, $ret[0]['code']);
    }

    public function testDeleteAfterDays()
    {
        $key = rand();
        $err = $this->bucketManager->deleteAfterDays($this->bucketName, $key, 1);
        $this->assertEquals(612, $err->code());

        $this->bucketManager->copy($this->bucketName, $this->key, $this->bucketName, $key);
        $err = $this->bucketManager->deleteAfterDays($this->bucketName, $key, 1);
        $this->assertEquals(null, $err);
    }
}
