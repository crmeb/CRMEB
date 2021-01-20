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

    protected function setUp()
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

        $this->config->shouldReceive('get')->with("log.channels.file", null)->andReturn(['type' => 'file', 'path' => $root->url()]);

        $this->log->info('foo');

        $this->assertEquals($this->log->getLog(), ['info' => ['foo']]);

        $this->log->clear();

        $this->assertEmpty($this->log->getLog());

        $this->log->error('foo');
        $this->assertArrayHasKey('error', $this->log->getLog());

        $this->log->emergency('foo');
        $this->assertArrayHasKey('emergency', $this->log->getLog());

        $this->log->alert('foo');
        $this->assertArrayHasKey('alert', $this->log->getLog());

        $this->log->critical('foo');
        $this->assertArrayHasKey('critical', $this->log->getLog());

        $this->log->warning('foo');
        $this->assertArrayHasKey('warning', $this->log->getLog());

        $this->log->notice('foo');
        $this->assertArrayHasKey('notice', $this->log->getLog());

        $this->log->debug('foo');
        $this->assertArrayHasKey('debug', $this->log->getLog());

        $this->log->sql('foo');
        $this->assertArrayHasKey('sql', $this->log->getLog());

        $this->log->custom('foo');
        $this->assertArrayHasKey('custom', $this->log->getLog());

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

        $this->config->shouldReceive('get')->with("log.channels.file", null)->andReturn(['type' => 'file', 'path' => $root->url()]);

        $this->log->info('foo');

        $this->log->save();

        $this->assertTrue($root->hasChildren());
    }

}
