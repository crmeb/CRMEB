<?php

namespace think\tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use think\Db;
use think\db\connector\Mysql;

class DbTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @dataProvider        connectProvider
     * @param $config
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testConnect($config)
    {
        $mysql = m::mock('overload:' . Mysql::class);

        $db = new Db($config);
    }

    public function connectProvider()
    {
        return [
            [['type' => 'mysql']],
        ];
    }

}
