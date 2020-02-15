<?php

namespace think\tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use think\Cache;
use think\Config;
use think\Db;
use think\Event;
use think\Log;

class DbTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testMake()
    {
        $event  = m::mock(Event::class);
        $config = m::mock(Config::class);
        $log    = m::mock(Log::class);
        $cache  = m::mock(Cache::class);

        $db = Db::__make($event, $config, $log, $cache);

        $config->shouldReceive('get')->with('database.foo', null)->andReturn('foo');
        $this->assertEquals('foo', $db->getConfig('foo'));

        $config->shouldReceive('get')->with('database', [])->andReturn([]);
        $this->assertEquals([], $db->getConfig());

        $callback = function () {
        };
        $event->shouldReceive('listen')->with('db.some', $callback);
        $db->event('some', $callback);

        $event->shouldReceive('trigger')->with('db.some', null, false);
        $db->trigger('some');
    }

}
