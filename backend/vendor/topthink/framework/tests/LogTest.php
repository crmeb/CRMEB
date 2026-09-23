<?php

namespace think\tests;

use InvalidArgumentException;
use Mockery as m;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use think\Log;
use think\log\ChannelSet;

class LogTest extends TestCase
{
    use InteractsWithApp;

    /** @var Log|MockInterface */
    protected $log;

    protected function tearDown(): void
    {
        m::close();
    }

    protected function setUp(): void
    {
        $this->prepareApp();

        $this->log = new Log($this->app);
    }

    public function testGetConfig()
    {
        $config = [
            'default' => 'file',
        ];

        $this->config->shouldReceive('get')->with('log')->andReturn($config);

        $this->assertEquals($config, $this->log->getConfig());

        $this->expectException(InvalidArgumentException::class);
        $this->log->getChannelConfig('foo');
    }

    public function testChannel()
    {
        $this->assertInstanceOf(ChannelSet::class, $this->log->channel(['file', 'mail']));
    }

    public function testLogManagerInstances()
    {
        $this->config->shouldReceive('get')->with("log.channels.single", null)->andReturn(['type' => 'file']);

        $channel1 = $this->log->channel('single');
        $channel2 = $this->log->channel('single');

        $this->assertSame($channel1, $channel2);
    }

    public function testFileLog()
    {
        $root = vfsStream::setup();

        $this->config->shouldReceive('get')->with("log.default", null)->andReturn('file');

        $this->config->shouldReceive('get')->with("log.channels.file", null)
            ->andReturn(['type' => 'file', 'path' => $root->url()]);

        $this->log->info('foo');

        $this->assertEquals([['info', 'foo']], array_map(fn($log) => [$log->type, $log->message], $this->log->getLog()));

        $this->log->clear();

        $this->assertEmpty($this->log->getLog());

        $this->log->error('foo');
        $this->log->emergency('foo');
        $this->log->alert('foo');
        $this->log->critical('foo');
        $this->log->warning('foo');
        $this->log->notice('foo');
        $this->log->debug('foo');
        $this->log->sql('foo');
        $this->log->custom('foo');

        $this->assertEquals([
            ['error', 'foo'],
            ['emergency', 'foo'],
            ['alert', 'foo'],
            ['critical', 'foo'],
            ['warning', 'foo'],
            ['notice', 'foo'],
            ['debug', 'foo'],
            ['sql', 'foo'],
            ['custom', 'foo'],
        ], array_map(fn($log) => [$log->type, $log->message], $this->log->getLog()));

        $this->log->write('foo');
        $this->assertTrue($root->hasChildren());
        $this->assertEmpty($this->log->getLog());

        $this->log->close();

        $this->log->info('foo');

        $this->assertEmpty($this->log->getLog());
    }

    public function testSave()
    {
        $root = vfsStream::setup();

        $this->config->shouldReceive('get')->with("log.default", null)->andReturn('file');

        $this->config->shouldReceive('get')->with("log.channels.file", null)
            ->andReturn(['type' => 'file', 'path' => $root->url()]);

        $this->log->info('foo');

        $this->log->save();

        $this->assertTrue($root->hasChildren());
    }

}
