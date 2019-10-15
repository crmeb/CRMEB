<?php

namespace think\tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use think\Config;

class ConfigTest extends TestCase
{
    public function testLoad()
    {
        $root = vfsStream::setup();
        $file = vfsStream::newFile('test.php')->setContent("<?php return ['key1'=> 'value1','key2'=>'value2'];");
        $root->addChild($file);

        $config = new Config();

        $config->load($file->url(), 'test');

        $this->assertEquals('value1', $config->get('test.key1'));
        $this->assertEquals('value2', $config->get('test.key2'));

        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $config->get('test'));
    }

    public function testSetAndGet()
    {
        $config = new Config();

        $config->set([
            'key1' => 'value1',
            'key2' => [
                'key3' => 'value3',
            ],
        ], 'test');

        $this->assertTrue($config->has('test.key1'));
        $this->assertEquals('value1', $config->get('test.key1'));
        $this->assertEquals('value3', $config->get('test.key2.key3'));

        $this->assertEquals(['key3' => 'value3'], $config->get('test.key2'));
        $this->assertFalse($config->has('test.key3'));
        $this->assertEquals('none', $config->get('test.key3', 'none'));
    }
}
