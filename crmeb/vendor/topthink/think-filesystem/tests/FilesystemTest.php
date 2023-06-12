<?php

namespace think\tests;

use Mockery as m;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use think\App;
use think\Config;
use think\Container;
use think\Filesystem;
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

    protected function setUp(): void
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

}

