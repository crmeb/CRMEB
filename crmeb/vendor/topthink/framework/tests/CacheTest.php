<?php

namespace think\tests;

use InvalidArgumentException;
use Mockery as m;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use think\App;
use think\Cache;
use think\Config;
use think\Container;

class CacheTest extends TestCase
{
    /** @var App|MockInterface */
    protected $app;

    /** @var Cache|MockInterface */
    protected $cache;

    /** @var Config|MockInterface */
    protected $config;

    protected function tearDown(): void
    {
        m::close();
    }

    protected function setUp()
    {
        $this->app = m::mock(App::class)->makePartial();

        Container::setInstance($this->app);
        $this->app->shouldReceive('make')->with(App::class)->andReturn($this->app);
        $this->config = m::mock(Config::class)->makePartial();
        $this->app->shouldReceive('get')->with('config')->andReturn($this->config);

        $this->cache = new Cache($this->app);
    }

    public function testGetConfig()
    {
        $config = [
            'default' => 'file',
        ];

        $this->config->shouldReceive('get')->with('cache')->andReturn($config);

        $this->assertEquals($config, $this->cache->getConfig());

        $this->expectException(InvalidArgumentException::class);
        $this->cache->getStoreConfig('foo');
    }

    public function testCacheManagerInstances()
    {
        $this->config->shouldReceive('get')->with("cache.stores.single", null)->andReturn(['type' => 'file']);

        $channel1 = $this->cache->store('single');
        $channel2 = $this->cache->store('single');

        $this->assertSame($channel1, $channel2);
    }

    public function testFileCache()
    {
        $root = vfsStream::setup();

        $this->config->shouldReceive('get')->with("cache.default", null)->andReturn('file');

        $this->config->shouldReceive('get')->with("cache.stores.file", null)->andReturn(['type' => 'file', 'path' => $root->url()]);

        $this->cache->set('foo', 5);
        $this->cache->inc('foo');
        $this->assertEquals(6, $this->cache->get('foo'));
        $this->cache->dec('foo', 2);
        $this->assertEquals(4, $this->cache->get('foo'));

        $this->cache->set('bar', true);
        $this->assertTrue($this->cache->get('bar'));

        $this->cache->set('baz', null);
        $this->assertNull($this->cache->get('baz'));

        $this->assertTrue($this->cache->has('baz'));
        $this->cache->delete('baz');
        $this->assertFalse($this->cache->has('baz'));
        $this->assertNull($this->cache->get('baz'));
        $this->assertFalse($this->cache->get('baz', false));

        $this->assertTrue($root->hasChildren());
        $this->cache->clear();
        $this->assertFalse($root->hasChildren());

        //tags
        $this->cache->tag('foo')->set('bar', 'foobar');
        $this->assertEquals('foobar', $this->cache->get('bar'));
        $this->cache->tag('foo')->clear();
        $this->assertFalse($this->cache->has('bar'));

        //multiple
        $this->cache->setMultiple(['foo' => ['foobar', 'bar'], 'foobar' => ['foo', 'bar']]);
        $this->assertEquals(['foo' => ['foobar', 'bar'], 'foobar' => ['foo', 'bar']], $this->cache->getMultiple(['foo', 'foobar']));
        $this->assertTrue($this->cache->deleteMultiple(['foo', 'foobar']));
    }

    public function testRedisCache()
    {
        if (extension_loaded('redis')) {
            return;
        }
        $this->config->shouldReceive('get')->with("cache.default", null)->andReturn('redis');
        $this->config->shouldReceive('get')->with("cache.stores.redis", null)->andReturn(['type' => 'redis']);

        $redis = m::mock('overload:\Predis\Client');

        $redis->shouldReceive("set")->once()->with('foo', 5)->andReturnTrue();
        $redis->shouldReceive("incrby")->once()->with('foo', 1)->andReturnTrue();
        $redis->shouldReceive("decrby")->once()->with('foo', 2)->andReturnTrue();
        $redis->shouldReceive("get")->once()->with('foo')->andReturn(6);
        $redis->shouldReceive("get")->once()->with('foo')->andReturn(4);
        $redis->shouldReceive("set")->once()->with('bar', \Opis\Closure\serialize(true))->andReturnTrue();
        $redis->shouldReceive("set")->once()->with('baz', \Opis\Closure\serialize(null))->andReturnTrue();
        $redis->shouldReceive("del")->once()->with('baz')->andReturnTrue();
        $redis->shouldReceive("flushDB")->once()->andReturnTrue();
        $redis->shouldReceive("set")->once()->with('bar', \Opis\Closure\serialize('foobar'))->andReturnTrue();
        $redis->shouldReceive("sAdd")->once()->with('tag:' . md5('foo'), 'bar')->andReturnTrue();
        $redis->shouldReceive("sMembers")->once()->with('tag:' . md5('foo'))->andReturn(['bar']);
        $redis->shouldReceive("del")->once()->with(['bar'])->andReturnTrue();
        $redis->shouldReceive("del")->once()->with('tag:' . md5('foo'))->andReturnTrue();

        $this->cache->set('foo', 5);
        $this->cache->inc('foo');
        $this->assertEquals(6, $this->cache->get('foo'));
        $this->cache->dec('foo', 2);
        $this->assertEquals(4, $this->cache->get('foo'));

        $this->cache->set('bar', true);
        $this->cache->set('baz', null);
        $this->cache->delete('baz');
        $this->cache->clear();

        //tags
        $this->cache->tag('foo')->set('bar', 'foobar');
        $this->cache->tag('foo')->clear();
    }
}
