<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\Cache;

class RedisCacheTest extends CacheTest
{
    private $_redis;

    public function setUp()
    {
        if (extension_loaded('redis')) {
            $this->_redis = new \Redis();
            $ok = @$this->_redis->connect('127.0.0.1');
            if (!$ok) {
                $this->markTestSkipped('The ' . __CLASS__ .' requires the use of redis');
            }
        } else {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the use of redis');
        }
    }

    public function testHitMissesStatsAreProvided()
    {
        $cache = $this->_getCacheDriver();
        $stats = $cache->getStats();

        $this->assertNotNull($stats[Cache::STATS_HITS]);
        $this->assertNotNull($stats[Cache::STATS_MISSES]);
    }

    protected function _getCacheDriver()
    {
        $driver = new RedisCache();
        $driver->setRedis($this->_redis);
        return $driver;
    }
}
