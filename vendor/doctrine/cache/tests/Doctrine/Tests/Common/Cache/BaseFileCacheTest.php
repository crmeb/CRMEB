<?php

namespace Doctrine\Tests\Common\Cache;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

abstract class BaseFileCacheTest extends CacheTest
{
    protected $directory;

    public function testFlushAllRemovesBalancingDirectories()
    {
        $cache = $this->_getCacheDriver();

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->save('key2', 2));
        $this->assertTrue($cache->flushAll());

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

        $this->assertCount(0, $iterator);
    }

    protected function setUp()
    {
        do {
            $this->directory = sys_get_temp_dir() . '/doctrine_cache_'. uniqid();
        } while (file_exists($this->directory));
    }

    protected function tearDown()
    {
        if ( ! is_dir($this->directory)) {
            return;
        }

        $iterator = new RecursiveDirectoryIterator($this->directory);

        foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if ($file->isFile()) {
                @unlink($file->getRealPath());
            } elseif ($file->isDir()) {
                @rmdir($file->getRealPath());
            }
        }

        @rmdir($this->directory);
    }

    protected function isSharedStorage()
    {
        return false;
    }
}
