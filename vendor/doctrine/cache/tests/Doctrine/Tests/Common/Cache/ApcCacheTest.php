<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\ApcCache;

class ApcCacheTest extends CacheTest
{
    public function setUp()
    {
        if ( ! extension_loaded('apc') || false === @apc_cache_info()) {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the use of APC');
        }
    }

    protected function _getCacheDriver()
    {
        return new ApcCache();
    }
}