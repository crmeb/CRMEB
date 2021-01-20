<?php

namespace think\tests;

use Mockery as m;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use stdClass;
use think\App;
use think\Env;
use think\Event;
use think\event\AppInit;
use think\exception\ClassNotFoundException;
use think\Service;

class SomeService extends Service
{
    public $bind = [
        'some' => 'class',
    ];

    public function register()
    {

    }

    public function boot()
    {

    }
}

/**
 * @property array initializers
 */
class AppTest extends TestCase
{
    /** @var App */
    protected $app;

    protected function setUp()
    {
        $this->app = new App();
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testService()
    {
        $this->app->register(stdClass::class);

        $this->assertInstanceOf(stdClass::class, $this->app->getService(stdClass::class));

        $service = m::mock(SomeService::class);

        $service->shouldReceive('register')->once();

        $this->app->register($service);

        $this->assertEquals($service, $this->app->getService(SomeService::class));

        $service2 = m::mock(SomeService::class);

        $service2->shouldReceive('register')->once();

        $this->app->register($service2);

        $this->assertEquals($service, $this->app->getService(SomeService::class));

        $this->app->register($service2, true);

        $this->assertEquals($service2, $this->app->getService(SomeService::class));

        $service->shouldReceive('boot')->once();
        $service2->shouldReceive('boot')->once();

        $this->app->boot();
    }

    public function testDebug()
    {
        $this->app->debug(false);

        $this->assertFalse($this->app->isDebug());

        $this->app->debug(true);

        $this->assertTrue($this->app->isDebug());
    }

    public function testNamespace()
    {
        $namespace = 'test';

        $this->app->setNamespace($namespace);

        $this->assertEquals($namespace, $this->app->getNamespace());
    }

    public function testVersion()
    {
        $this->assertEquals(App::VERSION, $this->app->version());
    }

    public function testPath()
    {
        $rootPath = __DIR__ . DIRECTORY_SEPARATOR;

        $app = new App($rootPath);

        $this->assertEquals($rootPath, $app->getRootPath());

        $this->assertEquals(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $app->getThinkPath());

        $this->assertEquals($rootPath . 'app' . DIRECTORY_SEPARATOR, $app->getAppPath());

        $appPath = $rootPath . 'app' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $app->setAppPath($appPath);
        $this->assertEquals($appPath, $app->getAppPath());

        $this->assertEquals($rootPath . 'app' . DIRECTORY_SEPARATOR, $app->getBasePath());

        $this->assertEquals($rootPath . 'config' . DIRECTORY_SEPARATOR, $app->getConfigPath());

        $this->assertEquals($rootPath . 'runtime' . DIRECTORY_SEPARATOR, $app->getRuntimePath());

        $runtimePath = $rootPath . 'runtime' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $app->setRuntimePath($runtimePath);
        $this->assertEquals($runtimePath, $app->getRuntimePath());
    }

    /**
     * @param vfsStreamDirectory $root
     * @param bool               $debug
     * @return App
     */
    protected function prepareAppForInitialize(vfsStreamDirectory $root, $debug = true)
    {
        $rootPath = $root->url() . DIRECTORY_SEPARATOR;

        $app = new App($rootPath);

        $initializer = m::mock();
        $initializer->shouldReceive('init')->once()->with($app);

        $app->instance($initializer->mockery_getName(), $initializer);

        (function () use ($initializer) {
            $this->initializers = [$initializer->mockery_getName()];
        })->call($app);

        $env = m::mock(Env::class);
        $env->shouldReceive('load')->once()->with($rootPath . '.env');
        $env->shouldReceive('get')->once()->with('config_ext', '.php')->andReturn('.php');
        $env->shouldReceive('get')->once()->with('app_debug')->andReturn($debug);

        $event = m::mock(Event::class);
        $event->shouldReceive('trigger')->once()->with(AppInit::class);
        $event->shouldReceive('bind')->once()->with([]);
        $event->shouldReceive('listenEvents')->once()->with([]);
        $event->shouldReceive('subscribe')->once()->with([]);

        $app->instance('env', $env);
        $app->instance('event', $event);

        return $app;
    }

    public function testInitialize()
    {
        $root = vfsStream::setup('rootDir', null, [
            '.env'   => '',
            'app'    => [
                'common.php'   => '',
                'event.php'    => '<?php return ["bind"=>[],"listen"=>[],"subscribe"=>[]];',
                'provider.php' => '<?php return [];',
            ],
            'config' => [
                'app.php' => '<?php return [];',
            ],
        ]);

        $app = $this->prepareAppForInitialize($root, true);

        $app->debug(false);

        $app->initialize();

        $this->assertIsInt($app->getBeginMem());
        $this->assertIsFloat($app->getBeginTime());

        $this->assertTrue($app->initialized());
    }

    public function testFactory()
    {
        $this->assertInstanceOf(stdClass::class, App::factory(stdClass::class));

        $this->expectException(ClassNotFoundException::class);

        App::factory('SomeClass');
    }

    public function testParseClass()
    {
        $this->assertEquals('app\\controller\\SomeClass', $this->app->parseClass('controller', 'some_class'));
        $this->app->setNamespace('app2');
        $this->assertEquals('app2\\controller\\SomeClass', $this->app->parseClass('controller', 'some_class'));
    }

}
