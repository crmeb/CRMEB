<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\ZendDataCache;

class ZendDataCacheTest extends CacheTest
{
    public function setUp()
    {
        if (!function_exists('zend_shm_cache_fetch') || (php_sapi_name() != 'apache2handler')) {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the use of Zend Data Cache which only works in apache2handler SAPI');
        }
    }

    public function testGetStats()
    {
        $cache = $this->_getCacheDriver();
        $stats = $cache->getStats();

        $this->assertNull($stats);
    }

    protected function _getCacheDriver()
    {
        return new ZendDataCache();
    }
}