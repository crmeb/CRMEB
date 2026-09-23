<?php

namespace think\test\queue;

use InvalidArgumentException;
use Mockery as m;
use think\Config;
use think\Queue;
use think\queue\connector\Sync;

class QueueTest extends TestCase
{
    /** @var Queue */
    protected $queue;

    protected function setUp()
    {
        parent::setUp();
        $this->queue = new Queue($this->app);
    }

    public function testDefaultConnectionCanBeResolved()
    {
        $sync = new Sync();

        $this->app->shouldReceive('invokeClass')->once()->with('\think\queue\connector\Sync', [['driver' => 'sync']])->andReturn($sync);

        $config = m::mock(Config::class);

        $config->shouldReceive('get')->twice()->with('queue.connectors.sync', ['driver' => 'sync'])->andReturn(['driver' => 'sync']);
        $config->shouldReceive('get')->once()->with('queue.default', 'sync')->andReturn('sync');

        $this->app->shouldReceive('get')->times(3)->with('config')->andReturn($config);

        $this->assertSame($sync, $this->queue->driver('sync'));
        $this->assertSame($sync, $this->queue->driver());
    }

    public function testNotSupportDriver()
    {
        $config = m::mock(Config::class);

        $config->shouldReceive('get')->once()->with('queue.connectors.hello', ['driver' => 'sync'])->andReturn(['driver' => 'hello']);
        $this->app->shouldReceive('get')->once()->with('config')->andReturn($config);

        $this->expectException(InvalidArgumentException::class);
        $this->queue->driver('hello');
    }
}
