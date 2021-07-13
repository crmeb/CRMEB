<?php

namespace think\tests;

use Mockery as m;
use Mockery\MockInterface;
use think\App;
use think\Config;
use think\Container;

trait InteractsWithApp
{
    /** @var App|MockInterface */
    protected $app;

    /** @var Config|MockInterface */
    protected $config;

    protected function prepareApp()
    {
        $this->app = m::mock(App::class)->makePartial();
        Container::setInstance($this->app);
        $this->app->shouldReceive('make')->with(App::class)->andReturn($this->app);
        $this->app->shouldReceive('isDebug')->andReturnTrue();
        $this->config = m::mock(Config::class)->makePartial();
        $this->config->shouldReceive('get')->with('app.show_error_msg')->andReturnTrue();
        $this->app->shouldReceive('get')->with('config')->andReturn($this->config);
        $this->app->shouldReceive('runningInConsole')->andReturn(false);
    }
}
