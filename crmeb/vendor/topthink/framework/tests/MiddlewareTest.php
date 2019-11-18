<?php

namespace think\tests;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use think\App;
use think\Config;
use think\Container;
use think\Exception;
use think\exception\Handle;
use think\Middleware;
use think\Pipeline;
use think\Request;
use think\Response;

class MiddlewareTest extends TestCase
{
    /** @var App|MockInterface */
    protected $app;

    /** @var Middleware|MockInterface */
    protected $middleware;

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
        $this->app->shouldReceive('runningInConsole')->andReturn(false);

        $this->middleware = new Middleware($this->app);
    }

    public function testSetMiddleware()
    {
        $this->middleware->add('BarMiddleware', 'bar');

        $this->assertEquals(1, count($this->middleware->all('bar')));

        $this->middleware->controller('BarMiddleware');
        $this->assertEquals(1, count($this->middleware->all('controller')));

        $this->middleware->import(['FooMiddleware']);
        $this->assertEquals(1, count($this->middleware->all()));

        $this->middleware->unshift(['BazMiddleware', 'baz']);
        $this->assertEquals(2, count($this->middleware->all()));
        $this->assertEquals([['BazMiddleware', 'handle'], 'baz'], $this->middleware->all()[0]);

        $this->config->shouldReceive('get')->with('middleware.alias', [])->andReturn(['foo' => ['FooMiddleware', 'FarMiddleware']]);

        $this->middleware->add('foo');
        $this->assertEquals(3, count($this->middleware->all()));
        $this->middleware->add(function () {
        });
        $this->middleware->add(function () {
        });
        $this->assertEquals(5, count($this->middleware->all()));
    }

    public function testPipelineAndEnd()
    {
        $bar = m::mock("overload:BarMiddleware");
        $foo = m::mock("overload:FooMiddleware", Foo::class);

        $request  = m::mock(Request::class);
        $response = m::mock(Response::class);

        $e = new Exception();

        $handle = m::mock(Handle::class);
        $handle->shouldReceive('report')->with($e)->andReturnNull();
        $handle->shouldReceive('render')->with($request, $e)->andReturn($response);

        $foo->shouldReceive('handle')->once()->andReturnUsing(function ($request, $next) {
            return $next($request);
        });
        $bar->shouldReceive('handle')->once()->andReturnUsing(function ($request, $next) use ($e) {
            $next($request);
            throw  $e;
        });

        $foo->shouldReceive('end')->once()->with($response)->andReturnNull();

        $this->app->shouldReceive('make')->with(Handle::class)->andReturn($handle);

        $this->config->shouldReceive('get')->once()->with('middleware.priority', [])->andReturn(['FooMiddleware', 'BarMiddleware']);

        $this->middleware->import([function ($request, $next) {
            return $next($request);
        }, 'BarMiddleware', 'FooMiddleware']);

        $this->assertInstanceOf(Pipeline::class, $pipeline = $this->middleware->pipeline());

        $pipeline->send($request)->then(function ($request) use ($e, $response) {
            throw $e;
        });

        $this->middleware->end($response);
    }
}

class Foo
{
    public function end(Response $response)
    {
    }
}
