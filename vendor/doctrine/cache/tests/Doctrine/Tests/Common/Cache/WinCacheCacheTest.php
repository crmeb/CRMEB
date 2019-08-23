<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\WincacheCache;

class WincacheCacheTest extends CacheTest
{
    public function setUp()
    {
        if ( ! extension_loaded('wincache') || ! function_exists('wincache_ucache_info')) {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the use of Wincache');
        }
    }

    protected function _getCacheDriver()
    {
        return new WincacheCache();
    }
}