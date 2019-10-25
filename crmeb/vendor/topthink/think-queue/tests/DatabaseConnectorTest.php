<?php

namespace think\test\queue;

use Carbon\Carbon;
use Mockery as m;
use Mockery\MockInterface;
use ReflectionClass;
use stdClass;
use think\Db;
use think\queue\Connector;
use think\queue\connector\Database;

class DatabaseConnectorTest extends TestCase
{
    /** @var Database|MockInterface */
    protected $connector;

    /** @var Db|MockInterface */
    protected $db;

    protected function setUp()
    {
        parent::setUp();
        $this->db        = m::mock(Db::class);
        $this->connector = new Database($this->db, 'table', 'default');
    }

    public function testPushProperlyPushesJobOntoDatabase()
    {
        $this->db->shouldReceive('name')->with('table')->andReturn($query = m::mock(stdClass::class));

        $query->shouldReceive('insertGetId')->once()->andReturnUsing(function ($array) {
            $this->assertEquals('default', $array['queue']);
            $this->assertEquals(json_encode(['job' => 'foo', 'maxTries' => null, 'timeout' => null, 'data' => ['data']]), $array['payload']);
            $this->assertEquals(0, $array['attempts']);
            $this->assertNull($array['reserved_at']);
            $this->assertInternalType('int', $array['available_at']);
        });
        $this->connector->push('foo', ['data']);
    }

    public function testDelayedPushProperlyPushesJobOntoDatabase()
    {
        $this->db->shouldReceive('name')->with('table')->andReturn($query = m::mock(stdClass::class));

        $query->shouldReceive('insertGetId')->once()->andReturnUsing(function ($array) {
            $this->assertEquals('default', $array['queue']);
            $this->assertEquals(json_encode(['job' => 'foo', 'maxTries' => null, 'timeout' => null, 'data' => ['data']]), $array['payload']);
            $this->assertEquals(0, $array['attempts']);
            $this->assertNull($array['reserved_at']);
            $this->assertInternalType('int', $array['available_at']);
        });

        $this->connector->later(10, 'foo', ['data']);
    }

    public function testFailureToCreatePayloadFromObject()
    {
        $this->expectException('InvalidArgumentException');

        $job          = new stdClass;
        $job->invalid = "\xc3\x28";

        $queue = $this->getMockForAbstractClass(Connector::class);
        $class = new ReflectionClass(Connector::class);

        $createPayload = $class->getMethod('createPayload');
        $createPayload->setAccessible(true);
        $createPayload->invokeArgs($queue, [
            $job,
            'queue-name',
        ]);
    }

    public function testFailureToCreatePayloadFromArray()
    {
        $this->expectException('InvalidArgumentException');

        $queue = $this->getMockForAbstractClass(Connector::class);
        $class = new ReflectionClass(Connector::class);

        $createPayload = $class->getMethod('createPayload');
        $createPayload->setAccessible(true);
        $createPayload->invokeArgs($queue, [
            ["\xc3\x28"],
            'queue-name',
        ]);
    }

    public function testBulkBatchPushesOntoDatabase()
    {

        $this->db->shouldReceive('name')->with('table')->andReturn($query = m::mock(stdClass::class));

        Carbon::setTestNow(
            $now = Carbon::now()->addSeconds()
        );

        $query->shouldReceive('insertAll')->once()->andReturnUsing(function ($records) use ($now) {
            $this->assertEquals([
                [
                    'queue'        => 'queue',
                    'payload'      => json_encode(['job' => 'foo', 'maxTries' => null, 'timeout' => null, 'data' => ['data']]),
                    'attempts'     => 0,
                    'reserved_at'  => null,
                    'available_at' => $now->getTimestamp(),
                    'created_at'   => $now->getTimestamp(),
                ], [
                    'queue'        => 'queue',
                    'payload'      => json_encode(['job' => 'bar', 'maxTries' => null, 'timeout' => null, 'data' => ['data']]),
                    'attempts'     => 0,
                    'reserved_at'  => null,
                    'available_at' => $now->getTimestamp(),
                    'created_at'   => $now->getTimestamp(),
                ],
            ], $records);
        });

        $this->connector->bulk(['foo', 'bar'], ['data'], 'queue');
    }

}
