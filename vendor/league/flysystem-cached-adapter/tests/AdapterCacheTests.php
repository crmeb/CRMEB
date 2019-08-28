<?php

use League\Flysystem\Cached\Storage\Adapter;
use PHPUnit\Framework\TestCase;

class AdapterCacheTests extends TestCase
{
    public function testLoadFail()
    {
        $adapter = Mockery::mock('League\Flysystem\AdapterInterface');
        $adapter->shouldReceive('has')->once()->with('file.json')->andReturn(false);
        $cache = new Adapter($adapter, 'file.json', 10);
        $cache->load();
        $this->assertFalse($cache->isComplete('', false));
    }

    public function testLoadExpired()
    {
        $response = ['contents' => json_encode([[], ['' => true], 1234567890]), 'path' => 'file.json'];
        $adapter = Mockery::mock('League\Flysystem\AdapterInterface');
        $adapter->shouldReceive('has')->once()->with('file.json')->andReturn(true);
        $adapter->shouldReceive('read')->once()->with('file.json')->andReturn($response);
        $adapter->shouldReceive('delete')->once()->with('file.json');
        $cache = new Adapter($adapter, 'file.json', 10);
        $cache->load();
        $this->assertFalse($cache->isComplete('', false));
    }

    public function testLoadSuccess()
    {
        $response = ['contents' => json_encode([[], ['' => true], 9876543210]), 'path' => 'file.json'];
        $adapter = Mockery::mock('League\Flysystem\AdapterInterface');
        $adapter->shouldReceive('has')->once()->with('file.json')->andReturn(true);
        $adapter->shouldReceive('read')->once()->with('file.json')->andReturn($response);
        $cache = new Adapter($adapter, 'file.json', 10);
        $cache->load();
        $this->assertTrue($cache->isComplete('', false));
    }

    public function testSaveExists()
    {
        $response = json_encode([[], [], null]);
        $adapter = Mockery::mock('League\Flysystem\AdapterInterface');
        $adapter->shouldReceive('has')->once()->with('file.json')->andReturn(true);
        $adapter->shouldReceive('update')->once()->with('file.json', $response, Mockery::any());
        $cache = new Adapter($adapter, 'file.json', null);
        $cache->save();
    }

    public function testSaveNew()
    {
        $response = json_encode([[], [], null]);
        $adapter = Mockery::mock('League\Flysystem\AdapterInterface');
        $adapter->shouldReceive('has')->once()->with('file.json')->andReturn(false);
        $adapter->shouldReceive('write')->once()->with('file.json', $response, Mockery::any());
        $cache = new Adapter($adapter, 'file.json', null);
        $cache->save();
    }

    public function testStoreContentsRecursive()
    {
        $adapter = Mockery::mock('League\Flysystem\AdapterInterface');
        $adapter->shouldReceive('has')->once()->with('file.json')->andReturn(false);
        $adapter->shouldReceive('write')->once()->with('file.json', Mockery::any(), Mockery::any());

        $cache = new Adapter($adapter, 'file.json', null);

        $contents = [
            ['path' => 'foo/bar', 'dirname' => 'foo'],
            ['path' => 'afoo/bang', 'dirname' => 'afoo'],
        ];

        $cache->storeContents('foo', $contents, true);

        $this->assertTrue($cache->isComplete('foo', true));
        $this->assertFalse($cache->isComplete('afoo', true));
    }

    public function testDeleteDir()
    {
        $cache_data = [
            'foo' => ['path' => 'foo', 'type' => 'dir', 'dirname' => ''],
            'foo/bar' => ['path' => 'foo/bar', 'type' => 'file', 'dirname' => 'foo'],
            'foobaz' => ['path' => 'foobaz', 'type' => 'file', 'dirname' => ''],
        ];

        $response = [
            'contents' => json_encode([$cache_data, [], null]),
            'path' => 'file.json',
        ];

        $adapter = Mockery::mock('League\Flysystem\AdapterInterface');
        $adapter->shouldReceive('has')->zeroOrMoreTimes()->with('file.json')->andReturn(true);
        $adapter->shouldReceive('read')->once()->with('file.json')->andReturn($response);
        $adapter->shouldReceive('update')->once()->with('file.json', Mockery::any(), Mockery::any())->andReturn(true);

        $cache = new Adapter($adapter, 'file.json', null);
        $cache->load();

        $cache->deleteDir('foo', true);

        $this->assertSame(1, count($cache->listContents('', true)));
    }
}
