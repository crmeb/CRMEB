<?php

namespace think\tests;

use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\TestCase;
use think\Request;
use think\route\Dispatch;
use think\route\Rule;

class DispatchTest extends TestCase
{
    public function testPsr7Response()
    {
        $request = Mockery::mock(Request::class);
        $rule = Mockery::mock(Rule::class);
        $dispatch = new class($request, $rule, '') extends Dispatch {
            public function exec()
            {
                return new Response(200, ['framework' => ['tp', 'thinkphp'], 'psr' => 'psr-7'], '123');
            }
        };

        $response = $dispatch->run();

        $this->assertInstanceOf(\think\Response::class, $response);
        $this->assertEquals('123', $response->getContent());
        $this->assertEquals('tp, thinkphp', $response->getHeader('framework'));
        $this->assertEquals('psr-7', $response->getHeader('psr'));
    }
}
