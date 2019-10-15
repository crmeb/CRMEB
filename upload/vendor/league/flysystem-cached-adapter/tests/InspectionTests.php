<?php

use League\Flysystem\Cached\CachedAdapter;
use PHPUnit\Framework\TestCase;

class InspectionTests extends TestCase {

    public function testGetAdapter()
    {
        $adapter = Mockery::mock('League\Flysystem\AdapterInterface');
        $cache = Mockery::mock('League\Flysystem\Cached\CacheInterface');
        $cache->shouldReceive('load')->once();
        $cached_adapter = new CachedAdapter($adapter, $cache);
        $this->assertInstanceOf('League\Flysystem\AdapterInterface', $cached_adapter->getAdapter());
    }
}
