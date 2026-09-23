<?php

namespace think\tests;

use Closure;
use Exception;
use PHPUnit\Framework\TestCase;
use think\Pipeline;

class PipelineTest extends TestCase
{
    protected $pipeline;

    protected function setUp(): void
    {
        $this->pipeline = new Pipeline();
    }

    public function testSend()
    {
        $data = 'test data';
        $result = $this->pipeline->send($data);
        
        $this->assertSame($this->pipeline, $result);
    }

    public function testThroughWithArray()
    {
        $pipes = [
            function ($passable, $next) {
                return $next($passable . ' pipe1');
            },
            function ($passable, $next) {
                return $next($passable . ' pipe2');
            }
        ];
        
        $result = $this->pipeline->through($pipes);
        
        $this->assertSame($this->pipeline, $result);
    }

    public function testThroughWithArguments()
    {
        $pipe1 = function ($passable, $next) {
            return $next($passable . ' pipe1');
        };
        $pipe2 = function ($passable, $next) {
            return $next($passable . ' pipe2');
        };
        
        $result = $this->pipeline->through($pipe1, $pipe2);
        
        $this->assertSame($this->pipeline, $result);
    }

    public function testThenExecutesPipeline()
    {
        $pipes = [
            function ($passable, $next) {
                return $next($passable . ' pipe1');
            },
            function ($passable, $next) {
                return $next($passable . ' pipe2');
            }
        ];
        
        $destination = function ($passable) {
            return $passable . ' destination';
        };
        
        $result = $this->pipeline
            ->send('start')
            ->through($pipes)
            ->then($destination);
        
        $this->assertEquals('start pipe1 pipe2 destination', $result);
    }

    public function testPipelineExecutesInCorrectOrder()
    {
        $order = [];
        
        $pipes = [
            function ($passable, $next) use (&$order) {
                $order[] = 'pipe1_before';
                $result = $next($passable);
                $order[] = 'pipe1_after';
                return $result;
            },
            function ($passable, $next) use (&$order) {
                $order[] = 'pipe2_before';
                $result = $next($passable);
                $order[] = 'pipe2_after';
                return $result;
            }
        ];
        
        $destination = function ($passable) use (&$order) {
            $order[] = 'destination';
            return $passable;
        };
        
        $this->pipeline
            ->send('test')
            ->through($pipes)
            ->then($destination);
        
        $expected = ['pipe1_before', 'pipe2_before', 'destination', 'pipe2_after', 'pipe1_after'];
        $this->assertEquals($expected, $order);
    }

    public function testEmptyPipelineExecutesDestination()
    {
        $destination = function ($passable) {
            return $passable . ' processed';
        };
        
        $result = $this->pipeline
            ->send('test')
            ->through([])
            ->then($destination);
        
        $this->assertEquals('test processed', $result);
    }

    public function testPipelineCanModifyData()
    {
        $pipes = [
            function ($passable, $next) {
                $passable['pipe1'] = true;
                return $next($passable);
            },
            function ($passable, $next) {
                $passable['pipe2'] = true;
                return $next($passable);
            }
        ];
        
        $destination = function ($passable) {
            $passable['destination'] = true;
            return $passable;
        };
        
        $result = $this->pipeline
            ->send([])
            ->through($pipes)
            ->then($destination);
        
        $expected = [
            'pipe1' => true,
            'pipe2' => true,
            'destination' => true
        ];
        $this->assertEquals($expected, $result);
    }

    public function testPipelineCanShortCircuit()
    {
        $pipes = [
            function ($passable, $next) {
                if ($passable === 'stop') {
                    return 'stopped';
                }
                return $next($passable);
            },
            function ($passable, $next) {
                return $next($passable . ' pipe2');
            }
        ];
        
        $destination = function ($passable) {
            return $passable . ' destination';
        };
        
        $result = $this->pipeline
            ->send('stop')
            ->through($pipes)
            ->then($destination);
        
        $this->assertEquals('stopped', $result);
    }

    public function testExceptionInDestinationIsHandled()
    {
        $pipes = [
            function ($passable, $next) {
                return $next($passable);
            }
        ];
        
        $destination = function ($passable) {
            throw new Exception('Destination error');
        };
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Destination error');
        
        $this->pipeline
            ->send('test')
            ->through($pipes)
            ->then($destination);
    }

    public function testExceptionInPipeIsHandled()
    {
        $pipes = [
            function ($passable, $next) {
                throw new Exception('Pipe error');
            }
        ];
        
        $destination = function ($passable) {
            return $passable;
        };
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Pipe error');
        
        $this->pipeline
            ->send('test')
            ->through($pipes)
            ->then($destination);
    }

    public function testWhenExceptionSetsHandler()
    {
        $result = $this->pipeline->whenException(function ($passable, $e) {
            return 'handled';
        });
        
        $this->assertSame($this->pipeline, $result);
    }

    public function testExceptionHandlerIsCalledOnException()
    {
        $pipes = [
            function ($passable, $next) {
                throw new Exception('Test exception');
            }
        ];
        
        $destination = function ($passable) {
            return $passable;
        };
        
        $result = $this->pipeline
            ->send('test')
            ->through($pipes)
            ->whenException(function ($passable, $e) {
                return 'handled: ' . $e->getMessage();
            })
            ->then($destination);
        
        $this->assertEquals('handled: Test exception', $result);
    }

    public function testExceptionHandlerReceivesCorrectParameters()
    {
        $pipes = [
            function ($passable, $next) {
                throw new Exception('Test exception');
            }
        ];
        
        $destination = function ($passable) {
            return $passable;
        };
        
        $handlerCalled = false;
        $receivedPassable = null;
        $receivedException = null;
        
        $this->pipeline
            ->send('original data')
            ->through($pipes)
            ->whenException(function ($passable, $e) use (&$handlerCalled, &$receivedPassable, &$receivedException) {
                $handlerCalled = true;
                $receivedPassable = $passable;
                $receivedException = $e;
                return 'handled';
            })
            ->then($destination);
        
        $this->assertTrue($handlerCalled);
        $this->assertEquals('original data', $receivedPassable);
        $this->assertInstanceOf(Exception::class, $receivedException);
        $this->assertEquals('Test exception', $receivedException->getMessage());
    }

    public function testExceptionInDestinationWithHandler()
    {
        $destination = function ($passable) {
            throw new Exception('Destination exception');
        };
        
        $result = $this->pipeline
            ->send('test')
            ->through([])
            ->whenException(function ($passable, $e) {
                return 'destination handled: ' . $e->getMessage();
            })
            ->then($destination);
        
        $this->assertEquals('destination handled: Destination exception', $result);
    }

    public function testComplexPipelineWithExceptions()
    {
        $pipes = [
            function ($passable, $next) {
                $passable[] = 'pipe1';
                return $next($passable);
            },
            function ($passable, $next) {
                if (in_array('error', $passable)) {
                    throw new Exception('Pipe2 error');
                }
                $passable[] = 'pipe2';
                return $next($passable);
            },
            function ($passable, $next) {
                $passable[] = 'pipe3';
                return $next($passable);
            }
        ];
        
        $destination = function ($passable) {
            $passable[] = 'destination';
            return $passable;
        };
        
        // Normal execution
        $result1 = $this->pipeline
            ->send(['start'])
            ->through($pipes)
            ->then($destination);
        
        $this->assertEquals(['start', 'pipe1', 'pipe2', 'pipe3', 'destination'], $result1);
        
        // With exception handling
        $result2 = $this->pipeline
            ->send(['start', 'error'])
            ->through($pipes)
            ->whenException(function ($passable, $e) {
                return ['error_handled', $e->getMessage()];
            })
            ->then($destination);
        
        $this->assertEquals(['error_handled', 'Pipe2 error'], $result2);
    }
}