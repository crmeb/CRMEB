<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\MemcacheCache;
use Memcache;

class MemcacheCacheTest extends CacheTest
{
    private $memcache;

    public function setUp()
    {
        if ( ! extension_loaded('memcache')) {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the use of memcache');
        }

        $this->memcache = new Memcache();

        if (@$this->memcache->connect('localhost', 11211) === false) {
            unset($this->memcache);
            $this->markTestSkipped('The ' . __CLASS__ .' cannot connect to memcache');
        }
    }

    public function tearDown()
    {
        if ($this->memcache instanceof Memcache) {
            $this->memcache->flush();
        }
    }

    public function testNoExpire()
    {
        $cache = $this->_getCacheDriver();
        $cache->save('noexpire', 'value', 0);
        sleep(1);
        $this->assertTrue($cache->contains('noexpire'), 'Memcache provider should support no-expire');
    }

    public function testLongLifetime()
    {
        $cache = $this->_getCacheDriver();
        $cache->save('key', 'value', 30 * 24 * 3600 + 1);
        $this->assertTrue($cache->contains('key'), 'Memcache provider should support TTL > 30 days');
    }

    protected function _getCacheDriver()
    {
        $driver = new MemcacheCache();
        $driver->setMemcache($this->memcache);
        return $driver;
    }
}
