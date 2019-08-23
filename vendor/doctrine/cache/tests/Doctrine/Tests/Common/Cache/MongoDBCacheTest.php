<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\MongoDBCache;
use MongoClient;
use MongoCollection;

class MongoDBCacheTest extends CacheTest
{
    /**
     * @var MongoCollection
     */
    private $collection;

    public function setUp()
    {
        if ( ! version_compare(phpversion('mongo'), '1.3.0', '>=')) {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the use of mongo >= 1.3.0');
        }

        $mongo = new MongoClient();
        $this->collection = $mongo->selectCollection('doctrine_common_cache', 'test');
    }

    public function tearDown()
    {
        if ($this->collection instanceof MongoCollection) {
            $this->collection->drop();
        }
    }

    public function testSaveWithNonUtf8String()
    {
        // Invalid 2-octet sequence
        $data = "\xc3\x28";

        $cache = $this->_getCacheDriver();

        $this->assertTrue($cache->save('key', $data));
        $this->assertEquals($data, $cache->fetch('key'));
    }

    public function testGetStats()
    {
        $cache = $this->_getCacheDriver();
        $stats = $cache->getStats();

        $this->assertNull($stats[Cache::STATS_HITS]);
        $this->assertNull($stats[Cache::STATS_MISSES]);
        $this->assertGreaterThan(0, $stats[Cache::STATS_UPTIME]);
        $this->assertEquals(0, $stats[Cache::STATS_MEMORY_USAGE]);
        $this->assertNull($stats[Cache::STATS_MEMORY_AVAILABLE]);
    }

    protected function _getCacheDriver()
    {
        return new MongoDBCache($this->collection);
    }
}
