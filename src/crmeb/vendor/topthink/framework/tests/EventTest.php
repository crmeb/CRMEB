<?php

namespace think\tests;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use think\App;
use think\Config;
use think\Container;
use think\Event;

class EventTest extends TestCase
{
    /** @var App|MockInterface */
    protected $app;

    /** @var Event|MockInterface */
    protected $event;

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

        $this->event = new Event($this->app);
    }

    public function testBasic()
    {
        $this->event->bind(['foo' => 'baz']);

        $this->event->listen('foo', function ($bar) {
            $this->assertEquals('bar', $bar);
        });

        $this->assertTrue($this->event->hasListener('foo'));

        $this->event->trigger('baz', 'bar');

        $this->event->remove('foo');

        $this->assertFalse($this->event->hasListener('foo'));
    }

    public function testOnceEvent()
    {
        $this->event->listen('AppInit', function ($bar) {
            $this->assertEquals('bar', $bar);
            return 'foo';
        });

        $this->assertEquals('foo', $this->event->trigger('AppInit', 'bar', true));
        $this->assertEquals(['foo'], $this->event->trigger('AppInit', 'bar'));
    }

    public function testClassListener()
    {
        $listener = m::mock("overload:SomeListener", TestListener::class);

        $listener->shouldReceive('handle')->andReturnTrue();

        $this->event->listen('some', "SomeListener");

        $this->assertTrue($this->event->until('some'));
    }

    public function testSubscribe()
    {
        $listener = m::mock("overload:SomeListener", TestListener::class);

        $listener->shouldReceive('subscribe')->andReturnUsing(function (Event $event) use ($listener) {

            $listener->shouldReceive('onBar')->once()->andReturnFalse();

            $event->listenEvents(['SomeListener::onBar' => [[$listener, 'onBar']]]);
        });

        $this->event->subscribe('SomeListener');

        $this->assertTrue($this->event->hasListener('SomeListener::onBar'));

        $this->event->trigger('SomeListener::onBar');
    }

    public function testAutoObserve()
    {
        $listener = m::mock("overload:SomeListener", TestListener::class);

        $listener->shouldReceive('onBar')->once();

        $this->app->shouldReceive('make')->with('SomeListener')->andReturn($listener);

        $this->event->observe('SomeListener');

        $this->event->trigger('bar');
    }

}

class TestListener
{
    public function handle()
    {

    }

    public function onBar()
    {

    }

    public function onFoo()
    {

    }

    public function subscribe()
    {

    }
}
