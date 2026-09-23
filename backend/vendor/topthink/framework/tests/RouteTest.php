<?php

namespace think\tests;

use Closure;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use think\helper\Str;
use think\Request;
use think\response\Redirect;
use think\response\View;
use think\Route;

class RouteTest extends TestCase
{
    use InteractsWithApp;

    /** @var Route|MockInterface */
    protected $route;

    protected function tearDown(): void
    {
        m::close();
    }

    protected function setUp(): void
    {
        $this->prepareApp();

        $this->config->shouldReceive('get')->with('route')->andReturn(['url_route_must' => true]);
        $this->route = new Route($this->app);
    }

    /**
     * @param        $path
     * @param string $method
     * @param string $host
     * @return m\Mock|Request
     */
    protected function makeRequest($path, $method = 'GET', $host = 'localhost')
    {
        $request = m::mock(Request::class)->makePartial();
        $request->shouldReceive('host')->andReturn($host);
        $request->shouldReceive('pathinfo')->andReturn($path);
        $request->shouldReceive('url')->andReturn('/' . $path);
        $request->shouldReceive('method')->andReturn(strtoupper($method));
        return $request;
    }

    public function testSimpleRequest()
    {
        $this->route->get('foo', function () {
            return 'get-foo';
        });

        $this->route->put('foo', function () {
            return 'put-foo';
        });

        $this->route->group(function () {
            $this->route->post('foo', function () {
                return 'post-foo';
            });
        });

        $request  = $this->makeRequest('foo', 'post');
        $response = $this->route->dispatch($request);
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('post-foo', $response->getContent());

        $request  = $this->makeRequest('foo', 'get');
        $response = $this->route->dispatch($request);
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('get-foo', $response->getContent());
    }

    public function testGroup()
    {
        $this->route->group(function () {
            $this->route->group('foo', function () {
                $this->route->post('bar', function () {
                    return 'hello,world!';
                });
            });
        });

        $request  = $this->makeRequest('foo/bar', 'post');
        $response = $this->route->dispatch($request);
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('hello,world!', $response->getContent());
    }

    public function testAllowCrossDomain()
    {
        $this->route->get('foo', function () {
            return 'get-foo';
        })->allowCrossDomain(['some' => 'bar']);

        $request  = $this->makeRequest('foo', 'get');
        $response = $this->route->dispatch($request);

        $this->assertEquals('bar', $response->getHeader('some'));
        $this->assertArrayHasKey('Access-Control-Allow-Credentials', $response->getHeader());

        //$this->expectException(RouteNotFoundException::class);
        $request = $this->makeRequest('foo2', 'options');
        $this->route->dispatch($request);
    }

    public function testControllerDispatch()
    {
        $this->route->get('foo', 'foo/bar');

        $controller = m::Mock(\stdClass::class);

        $this->app->shouldReceive('parseClass')->with('controller', 'Foo')->andReturn($controller->mockery_getName());
        $this->app->shouldReceive('make')->with($controller->mockery_getName(), [], true)->andReturn($controller);

        $controller->shouldReceive('bar')->andReturn('bar');

        $request  = $this->makeRequest('foo');
        $response = $this->route->dispatch($request);
        $this->assertEquals('bar', $response->getContent());
    }

    public function testEmptyControllerDispatch()
    {
        $this->route->get('foo', 'foo/bar');

        $controller = m::Mock(\stdClass::class);

        $this->app->shouldReceive('parseClass')->with('controller', 'Error')->andReturn($controller->mockery_getName());
        $this->app->shouldReceive('make')->with($controller->mockery_getName(), [], true)->andReturn($controller);

        $controller->shouldReceive('bar')->andReturn('bar');

        $request  = $this->makeRequest('foo');
        $response = $this->route->dispatch($request);
        $this->assertEquals('bar', $response->getContent());
    }

    protected function createMiddleware($times = 1)
    {
        $middleware = m::mock(Str::random(5));
        $middleware->shouldReceive('handle')->times($times)->andReturnUsing(function ($request, Closure $next) {
            return $next($request);
        });
        $this->app->shouldReceive('make')->with($middleware->mockery_getName())->andReturn($middleware);

        return $middleware;
    }

    public function testControllerWithMiddleware()
    {
        $this->route->get('foo', 'foo/bar');

        $controller = m::mock(FooClass::class);

        $controller->middleware = [
            $this->createMiddleware()->mockery_getName() . ":params1:params2",
            $this->createMiddleware(0)->mockery_getName() => ['except' => 'bar'],
            $this->createMiddleware()->mockery_getName()  => ['only' => 'bar'],
            [
                'middleware' => [$this->createMiddleware()->mockery_getName(), [new \stdClass()]],
                'options'    => ['only' => 'bar'],
            ],
        ];

        $this->app->shouldReceive('parseClass')->with('controller', 'Foo')->andReturn($controller->mockery_getName());
        $this->app->shouldReceive('make')->with($controller->mockery_getName(), [], true)->andReturn($controller);

        $controller->shouldReceive('bar')->once()->andReturn('bar');

        $request  = $this->makeRequest('foo');
        $response = $this->route->dispatch($request);
        $this->assertEquals('bar', $response->getContent());
    }

    public function testRedirectDispatch()
    {
        $this->route->redirect('foo', 'http://localhost', 302);

        $request = $this->makeRequest('foo');
        $this->app->shouldReceive('make')->with(Request::class)->andReturn($request);
        $response = $this->route->dispatch($request);

        $this->assertInstanceOf(Redirect::class, $response);
        $this->assertEquals(302, $response->getCode());
        $this->assertEquals('http://localhost', $response->getData());
    }

    public function testViewDispatch()
    {
        $this->route->view('foo', 'index/hello', ['city' => 'shanghai']);

        $request  = $this->makeRequest('foo');
        $response = $this->route->dispatch($request);

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals(['city' => 'shanghai'], $response->getVars());
        $this->assertEquals('index/hello', $response->getData());
    }

    public function testDomainBindResponse()
    {
        $this->route->domain('test', function () {
            $this->route->get('/', function () {
                return 'Hello,ThinkPHP';
            });
        });

        $request  = $this->makeRequest('', 'get', 'test.domain.com');
        $response = $this->route->dispatch($request);

        $this->assertEquals('Hello,ThinkPHP', $response->getContent());
        $this->assertEquals(200, $response->getCode());
    }

    public function testResourceRouting()
    {
        // Test basic resource registration (returns ResourceRegister when not lazy)
        $resource = $this->route->resource('users', 'Users');
        $this->assertTrue($resource instanceof \think\route\Resource || $resource instanceof \think\route\ResourceRegister);
        
        // Test REST methods configuration
        $restMethods = $this->route->getRest();
        $this->assertIsArray($restMethods);
        $this->assertArrayHasKey('index', $restMethods);
        $this->assertArrayHasKey('create', $restMethods);
        $this->assertArrayHasKey('save', $restMethods);
        $this->assertArrayHasKey('read', $restMethods);
        $this->assertArrayHasKey('edit', $restMethods);
        $this->assertArrayHasKey('update', $restMethods);
        $this->assertArrayHasKey('delete', $restMethods);
        
        // Test custom REST method modification
        $this->route->rest('custom', ['get', '/custom', 'customAction']);
        $customMethod = $this->route->getRest('custom');
        $this->assertEquals(['get', '/custom', 'customAction'], $customMethod);
    }

    public function testUrlGeneration()
    {
        $this->route->get('user/<id>', 'User/detail')->name('user.detail');
        $this->route->post('user', 'User/save')->name('user.save');

        $urlBuild = $this->route->buildUrl('user.detail', ['id' => 123]);
        $this->assertInstanceOf(\think\route\Url::class, $urlBuild);

        $urlBuild = $this->route->buildUrl('user.save');
        $this->assertInstanceOf(\think\route\Url::class, $urlBuild);
    }

    public function testRouteParameterBinding()
    {
        $this->route->get('user/<id>', function ($id) {
            return "User ID: $id";
        });

        $request = $this->makeRequest('user/123', 'get');
        $response = $this->route->dispatch($request);
        $this->assertEquals('User ID: 123', $response->getContent());

        // Test multiple parameters
        $this->route->get('post/<year>/<month>', function ($year, $month) {
            return "Year: $year, Month: $month";
        });

        $request = $this->makeRequest('post/2024/12', 'get');
        $response = $this->route->dispatch($request);
        $this->assertEquals('Year: 2024, Month: 12', $response->getContent());
    }

    public function testRoutePatternValidation()
    {
        $this->route->get('user/<id>', function ($id) {
            return "User ID: $id";
        })->pattern(['id' => '\d+']);

        // Valid numeric ID
        $request = $this->makeRequest('user/123', 'get');
        $response = $this->route->dispatch($request);
        $this->assertEquals('User ID: 123', $response->getContent());

        // Test pattern with name validation
        $this->route->get('profile/<name>', function ($name) {
            return "Profile: $name";
        })->pattern(['name' => '[a-zA-Z]+']);

        $request = $this->makeRequest('profile/john', 'get');
        $response = $this->route->dispatch($request);
        $this->assertEquals('Profile: john', $response->getContent());
    }

    public function testMissRoute()
    {
        $this->route->get('home', function () {
            return 'home page';
        });

        $this->route->miss(function () {
            return 'Page not found';
        });

        // Test existing route
        $request = $this->makeRequest('home', 'get');
        $response = $this->route->dispatch($request);
        $this->assertEquals('home page', $response->getContent());

        // Test miss route
        $request = $this->makeRequest('nonexistent', 'get');
        $response = $this->route->dispatch($request);
        $this->assertEquals('Page not found', $response->getContent());
    }

    public function testRouteMiddleware()
    {
        $middleware = $this->createMiddleware();
        
        $this->route->get('protected', function () {
            return 'protected content';
        })->middleware($middleware->mockery_getName());

        $request = $this->makeRequest('protected', 'get');
        $response = $this->route->dispatch($request);
        $this->assertEquals('protected content', $response->getContent());
    }

    public function testRouteOptions()
    {
        $this->route->get('api/<version>/users', function ($version) {
            return "API Version: $version";
        })->option(['version' => '1.0']);

        $request = $this->makeRequest('api/v2/users', 'get');
        $response = $this->route->dispatch($request);
        $this->assertEquals('API Version: v2', $response->getContent());
    }

    public function testRouteCache()
    {
        // Test route configuration
        $config = $this->route->config();
        $this->assertIsArray($config);
        
        $caseConfig = $this->route->config('url_case_sensitive');
        $this->assertIsBool($caseConfig);
        
        // Test route name management
        $this->route->get('test', function () {
            return 'test';
        })->name('test.route');
        
        $names = $this->route->getName('test.route');
        $this->assertIsArray($names);
    }

}

class FooClass
{
    public $middleware = [];

    public function bar()
    {

    }
}
