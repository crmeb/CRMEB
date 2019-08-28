<?php

namespace think\tests;

use Mockery as m;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use think\App;
use think\Config;
use think\Console;
use think\Event;
use think\Exception;
use think\exception\Handle;
use think\Http;
use think\Log;
use think\Request;
use think\Response;
use think\Route;
use think\Session;

class HttpTest extends TestCase
{
    /** @var App|MockInterface */
    protected $app;

    /** @var Http|MockInterface */
    protected $http;

    protected function tearDown(): void
    {
        m::close();
    }

    protected function setUp()
    {
        $this->app = m::mock(App::class)->makePartial();

        $this->http = m::mock(Http::class, [$this->app])->shouldAllowMockingProtectedMethods()->makePartial();
    }

    protected function prepareApp($request, $response)
    {
        $this->app->shouldReceive('instance')->once()->with('request', $request);
        $this->app->shouldReceive('initialized')->once()->andReturnFalse();
        $this->app->shouldReceive('initialize')->once();
        $this->app->shouldReceive('get')->with('request')->andReturn($request);

        $route = m::mock(Route::class);

        $route->shouldReceive('dispatch')->withArgs(function ($req, $withRoute) use ($request) {
            if ($withRoute) {
                $withRoute();
            }
            return $req === $request;
        })->andReturn($response);

        $route->shouldReceive('config')->with('route_annotation')->andReturn(true);

        $this->app->shouldReceive('get')->with('route')->andReturn($route);

        $console = m::mock(Console::class);

        $console->shouldReceive('call');

        $this->app->shouldReceive('get')->with('console')->andReturn($console);
    }

    public function testRun()
    {
        $root = vfsStream::setup('rootDir', null, [
            'app'   => [
                'controller'     => [],
                'middleware.php' => '<?php return [];',
            ],
            'route' => [
                'route.php' => '<?php return [];',
            ],
        ]);

        $this->http->multi(false);

        $this->app->shouldReceive('getBasePath')->andReturn($root->getChild('app')->url() . DIRECTORY_SEPARATOR);
        $this->app->shouldReceive('getRootPath')->andReturn($root->url() . DIRECTORY_SEPARATOR);

        $request  = m::mock(Request::class)->makePartial();
        $response = m::mock(Response::class)->makePartial();

        $this->prepareApp($request, $response);

        $this->assertEquals($response, $this->http->run($request));

        $this->assertFalse($this->http->isMulti());
    }

    /**
     * @param $request
     * @param $auto
     * @param $name
     * @dataProvider multiAppRunProvider
     */
    public function testMultiAppRun($request, $auto, $name, $path = null)
    {
        $root = vfsStream::setup('rootDir', null, [
            'app'    => [
                'middleware.php' => '<?php return [];',
                'app1'           => [
                    'common.php'     => '',
                    'event.php'      => '<?php return ["bind"=>[],"listen"=>[],"subscribe"=>[]];',
                    'provider.php'   => '<?php return [];',
                    'middleware.php' => '<?php return [];',
                    'config'         => [
                        'app.php' => '<?php return [];',
                    ],
                ],
            ],
            'config' => [
                'app.php' => '<?php return [];',
                'app1'    => [
                    'app.php' => '<?php return [];',
                ],
            ],
            'route'  => [
                'route.php' => '<?php return [];',
            ],
        ]);

        $config = m::mock(Config::class)->makePartial();

        $config->shouldReceive('get')->with('app.auto_multi_app', false)->andReturn($auto);

        $config->shouldReceive('get')->with('app.domain_bind', [])->andReturn([
            'www.domain.com' => 'app1',
            'app2'           => 'app2',
        ]);

        $config->shouldReceive('get')->with('app.app_map', [])->andReturn([
            'some1' => 'app3',
        ]);

        $this->app->shouldReceive('get')->with('config')->andReturn($config);

        $this->app->shouldReceive('getBasePath')->andReturn($root->getChild('app')->url() . DIRECTORY_SEPARATOR);
        $this->app->shouldReceive('getRootPath')->andReturn($root->url() . DIRECTORY_SEPARATOR);
        $this->app->shouldReceive('getConfigPath')->andReturn($root->getChild('config')->url() . DIRECTORY_SEPARATOR);

        $response = m::mock(Response::class)->makePartial();

        $this->prepareApp($request, $response);

        $this->assertTrue($this->http->isMulti());

        if (null === $name) {
            $this->http->shouldReceive('reportException')->once();

            $this->http->shouldReceive('renderException')->once()->andReturn($response);
        }

        if (!$auto) {
            $this->http->name($name);
        }

        if ($path) {
            $this->http->path($path);
        }

        $this->assertEquals($response, $this->http->run($request));

        if (null !== $name) {
            $this->assertEquals($name, $this->http->getName());
        }

        if ('app1' === $name || 'app2' === $name) {
            $this->assertTrue($this->http->isBindDomain());
        }
        if ($path) {
            $this->assertEquals(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $this->app->getAppPath());
        }
    }

    public function multiAppRunProvider()
    {
        $request1 = m::mock(Request::class)->makePartial();
        $request1->shouldReceive('subDomain')->andReturn('www');
        $request1->shouldReceive('host')->andReturn('www.domain.com');

        $request2 = m::mock(Request::class)->makePartial();
        $request2->shouldReceive('subDomain')->andReturn('app2');
        $request2->shouldReceive('host')->andReturn('app2.domain.com');

        $request3 = m::mock(Request::class)->makePartial();
        $request3->shouldReceive('pathinfo')->andReturn('some1/a/b/c');

        $request4 = m::mock(Request::class)->makePartial();
        $request4->shouldReceive('pathinfo')->andReturn('app3/a/b/c');

        $request5 = m::mock(Request::class)->makePartial();
        $request5->shouldReceive('pathinfo')->andReturn('some2/a/b/c');

        return [
            [$request1, true, 'app1'],
            [$request2, true, 'app2'],
            [$request3, true, 'app3'],
            [$request4, true, null],
            [$request5, true, 'some2', 'path'],
            [$request1, false, 'some3'],
        ];
    }

    public function testRunWithException()
    {
        $request  = m::mock(Request::class);
        $response = m::mock(Response::class);

        $this->app->shouldReceive('instance')->once()->with('request', $request);

        $exception = new Exception();

        $this->http->shouldReceive('runWithRequest')->once()->with($request)->andThrow($exception);

        $handle = m::mock(Handle::class);

        $handle->shouldReceive('report')->once()->with($exception);
        $handle->shouldReceive('render')->once()->with($request, $exception)->andReturn($response);

        $this->app->shouldReceive('make')->with(Handle::class)->andReturn($handle);

        $response->shouldReceive('setCookie')->andReturn($response);

        $this->assertEquals($response, $this->http->run($request));
    }

    public function testEnd()
    {
        $response = m::mock(Response::class);
        $event    = m::mock(Event::class);
        $event->shouldReceive('trigger')->once()->with('HttpEnd', $response);
        $this->app->shouldReceive('get')->once()->with('event')->andReturn($event);
        $log = m::mock(Log::class);
        $log->shouldReceive('save')->once();
        $this->app->shouldReceive('get')->once()->with('log')->andReturn($log);
        $session = m::mock(Session::class);
        $session->shouldReceive('save')->once();
        $this->app->shouldReceive('get')->once()->with('session')->andReturn($session);

        $this->http->end($response);
    }

}
