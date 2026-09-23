<?php

namespace think\tests;

use Mockery as m;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use think\App;
use think\cache\Driver;
use think\Config;
use think\Container;
use think\contract\SessionHandlerInterface;
use think\helper\Str;
use think\Session;
use think\session\driver\Cache;
use think\session\driver\File;

class SessionTest extends TestCase
{
    /** @var App|MockInterface */
    protected $app;

    /** @var Session|MockInterface */
    protected $session;

    /** @var Config|MockInterface */
    protected $config;

    protected $handler;

    protected function tearDown(): void
    {
        m::close();
    }

    protected function setUp(): void
    {
        $this->app = m::mock(App::class)->makePartial();
        Container::setInstance($this->app);

        $this->app->shouldReceive('make')->with(App::class)->andReturn($this->app);
        $this->config = m::mock(Config::class)->makePartial();

        $this->app->shouldReceive('get')->with('config')->andReturn($this->config);
        $handlerClass = "\\think\\session\\driver\\Test" . Str::random(10);
        $this->config->shouldReceive("get")->with("session.type", "file")->andReturn($handlerClass);
        $this->session = new Session($this->app);

        $this->handler = m::mock('overload:' . $handlerClass, SessionHandlerInterface::class);
    }

    public function testLoadData()
    {
        $data = [
            "bar" => 'foo',
        ];

        $id = md5(uniqid());

        $this->handler->shouldReceive("read")->once()->with($id)->andReturn(serialize($data));

        $this->session->setId($id);
        $this->session->init();

        $this->assertEquals('foo', $this->session->get('bar'));
        $this->assertTrue($this->session->has('bar'));
        $this->assertFalse($this->session->has('foo'));

        $this->session->set('foo', 'bar');
        $this->assertTrue($this->session->has('foo'));

        $this->assertEquals('bar', $this->session->pull('foo'));
        $this->assertFalse($this->session->has('foo'));
    }

    public function testSave()
    {

        $id = md5(uniqid());

        $this->handler->shouldReceive('read')->once()->with($id)->andReturn("");

        $this->handler->shouldReceive('write')->once()->with($id, serialize([
            "bar" => 'foo',
        ]))->andReturnTrue();

        $this->session->setId($id);
        $this->session->init();

        $this->session->set('bar', 'foo');

        $this->session->save();
    }

    public function testFlash()
    {
        $this->session->flash('foo', 'bar');
        $this->session->flash('bar', 0);
        $this->session->flash('baz', true);

        $this->assertTrue($this->session->has('foo'));
        $this->assertEquals('bar', $this->session->get('foo'));
        $this->assertEquals(0, $this->session->get('bar'));
        $this->assertTrue($this->session->get('baz'));

        $this->session->clearFlashData();

        $this->assertTrue($this->session->has('foo'));
        $this->assertEquals('bar', $this->session->get('foo'));
        $this->assertEquals(0, $this->session->get('bar'));

        $this->session->clearFlashData();

        $this->assertFalse($this->session->has('foo'));
        $this->assertNull($this->session->get('foo'));

        $this->session->flash('foo', 'bar');
        $this->assertTrue($this->session->has('foo'));
        $this->session->clearFlashData();
        $this->session->reflash();
        $this->session->clearFlashData();

        $this->assertTrue($this->session->has('foo'));
    }

    public function testClear()
    {
        $this->session->set('bar', 'foo');
        $this->assertEquals('foo', $this->session->get('bar'));
        $this->session->clear();
        $this->assertFalse($this->session->has('foo'));
    }

    public function testSetName()
    {
        $this->session->setName('foo');
        $this->assertEquals('foo', $this->session->getName());
    }

    public function testDestroy()
    {
        $id = md5(uniqid());

        $this->handler->shouldReceive('read')->once()->with($id)->andReturn("");
        $this->handler->shouldReceive('delete')->once()->with($id)->andReturnTrue();

        $this->session->setId($id);
        $this->session->init();

        $this->session->set('bar', 'foo');

        $this->session->destroy();

        $this->assertFalse($this->session->has('bar'));

        $this->assertNotEquals($id, $this->session->getId());
    }

    public function testFileHandler()
    {
        $root = vfsStream::setup();

        vfsStream::newFile('bar')
            ->at($root)
            ->lastModified(time());

        vfsStream::newFile('bar')
            ->at(vfsStream::newDirectory("foo")->at($root))
            ->lastModified(100);

        $this->assertTrue($root->hasChild("bar"));
        $this->assertTrue($root->hasChild("foo/bar"));

        $handler = new TestFileHandle($this->app, [
            'path'           => $root->url(),
            'gc_probability' => 1,
            'gc_divisor'     => 1,
        ]);

        $this->assertTrue($root->hasChild("bar"));
        $this->assertFalse($root->hasChild("foo/bar"));

        $id = md5(uniqid());
        $handler->write($id, "bar");

        $this->assertTrue($root->hasChild("sess_{$id}"));

        $this->assertEquals("bar", $handler->read($id));

        $handler->delete($id);

        $this->assertFalse($root->hasChild("sess_{$id}"));
    }

    public function testCacheHandler()
    {
        $id = md5(uniqid());

        $cache = m::mock(\think\Cache::class);

        $store = m::mock(Driver::class);

        $cache->shouldReceive('store')->once()->with('redis')->andReturn($store);

        $handler = new Cache($cache, ['store' => 'redis']);

        $store->shouldReceive("set")->with($id, "bar", 1440)->once()->andReturnTrue();
        $handler->write($id, "bar");

        $store->shouldReceive("get")->with($id)->once()->andReturn("bar");
        $this->assertEquals("bar", $handler->read($id));

        $store->shouldReceive("delete")->with($id)->once()->andReturnTrue();
        $handler->delete($id);
    }
}

class TestFileHandle extends File
{
    protected function writeFile($path, $content): bool
    {
        return (bool) file_put_contents($path, $content);
    }
}
