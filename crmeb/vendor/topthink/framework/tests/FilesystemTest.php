<?php

namespace think\tests;

use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\AdapterInterface;
use Mockery as m;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use think\App;
use think\Cache;
use think\cache\driver\File;
use think\Config;
use think\Container;
use think\Filesystem;
use think\filesystem\Driver;
use think\filesystem\driver\Local;

class FilesystemTest extends TestCase
{
    /** @var App|MockInterface */
    protected $app;

    /** @var Filesystem */
    protected $filesystem;

    /** @var Config|MockInterface */
    protected $config;

    /** @var vfsStreamDirectory */
    protected $root;

    protected function setUp()
    {
        $this->app = m::mock(App::class)->makePartial();
        Container::setInstance($this->app);
        $this->app->shouldReceive('make')->with(App::class)->andReturn($this->app);
        $this->config = m::mock(Config::class);
        $this->config->shouldReceive('get')->with('filesystem.default', null)->andReturn('local');
        $this->app->shouldReceive('get')->with('config')->andReturn($this->config);
        $this->filesystem = new Filesystem($this->app);

        $this->root = vfsStream::setup('rootDir');
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testDisk()
    {
        $this->config->shouldReceive('get')->with('filesystem.disks.local', null)->andReturn([
            'type' => 'local',
            'root' => $this->root->url(),
        ]);

        $this->config->shouldReceive('get')->with('filesystem.disks.foo', null)->andReturn([
            'type' => 'local',
            'root' => $this->root->url(),
        ]);

        $this->assertInstanceOf(Local::class, $this->filesystem->disk());

        $this->assertInstanceOf(Local::class, $this->filesystem->disk('foo'));
    }

    public function testCache()
    {
        $this->config->shouldReceive('get')->with('filesystem.disks.local', null)->andReturn([
            'type'  => 'local',
            'root'  => $this->root->url(),
            'cache' => true,
        ]);

        $this->assertInstanceOf(Local::class, $this->filesystem->disk());

        $this->config->shouldReceive('get')->with('filesystem.disks.cache', null)->andReturn([
            'type'  => NullDriver::class,
            'root'  => $this->root->url(),
            'cache' => [
                'store' => 'flysystem',
            ],
        ]);

        $cache = m::mock(Cache::class);

        $cacheDriver = m::mock(File::class);

        $cache->shouldReceive('store')->once()->with('flysystem')->andReturn($cacheDriver);

        $this->app->shouldReceive('make')->with(Cache::class)->andReturn($cache);

        $cacheDriver->shouldReceive('get')->with('flysystem')->once()->andReturn(null);

        $cacheDriver->shouldReceive('set')->withAnyArgs();

        $this->filesystem->disk('cache')->put('test.txt', 'aa');
    }

    public function testPutFile()
    {
        $root = vfsStream::setup('rootDir', null, [
            'foo.jpg' => 'hello',
        ]);

        $this->config->shouldReceive('get')->with('filesystem.disks.local', null)->andReturn([
            'type'  => NullDriver::class,
            'root'  => $root->url(),
            'cache' => true,
        ]);

        $file = m::mock(\think\File::class);

        $file->shouldReceive('hashName')->with(null)->once()->andReturn('foo.jpg');

        $file->shouldReceive('getRealPath')->once()->andReturn($root->getChild('foo.jpg')->url());

        $this->filesystem->putFile('test', $file);
    }
}

class NullDriver extends Driver
{
    protected function createAdapter(): AdapterInterface
    {
        return new NullAdapter();
    }
}
