<?php

use League\Flysystem\Cached\Storage\PhpRedis;
use PHPUnit\Framework\TestCase;

class PhpRedisTests extends TestCase
{
    public function testLoadFail()
    {
        $client = Mockery::mock('Redis');
        $client->shouldReceive('get')->with('flysystem')->once()->andReturn(false);
        $cache = new PhpRedis($client);
        $cache->load();
        $this->assertFalse($cache->isComplete('', false));
    }

    public function testLoadSuccess()
    {
        $response = json_encode([[], ['' => true]]);
        $client = Mockery::mock('Redis');
        $client->shouldReceive('get')->with('flysystem')->once()->andReturn($response);
        $cache = new PhpRedis($client);
        $cache->load();
        $this->assertTrue($cache->isComplete('', false));
    }

    public function testSave()
    {
        $data = json_encode([[], []]);
        $client = Mockery::mock('Redis');
        $client->shouldReceive('set')->with('flysystem', $data)->once();
        $cache = new PhpRedis($client);
        $cache->save();
    }

    public function testSaveWithExpire()
    {
        $data = json_encode([[], []]);
        $client = Mockery::mock('Redis');
        $client->shouldReceive('set')->with('flysystem', $data)->once();
        $client->shouldReceive('expire')->with('flysystem', 20)->once();
        $cache = new PhpRedis($client, 'flysystem', 20);
        $cache->save();
    }
}
