<?php

namespace think\tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use think\Env;
use think\Exception;

class EnvTest extends TestCase
{
    public function testEnvFile()
    {
        $root    = vfsStream::setup();
        $envFile = vfsStream::newFile('.env')->setContent("key1=value1\nkey2=value2");
        $root->addChild($envFile);

        $env = new Env();

        $env->load($envFile->url());

        $this->assertEquals('value1', $env->get('key1'));
        $this->assertEquals('value2', $env->get('key2'));

        $this->assertSame(['KEY1' => 'value1', 'KEY2' => 'value2'], $env->get());
    }

    public function testServerEnv()
    {
        $env = new Env();

        $this->assertEquals('value2', $env->get('key2', 'value2'));

        putenv('PHP_KEY7=value7');
        putenv('PHP_KEY8=false');
        putenv('PHP_KEY9=true');

        $this->assertEquals('value7', $env->get('key7'));
        $this->assertFalse($env->get('KEY8'));
        $this->assertTrue($env->get('key9'));
    }

    public function testSetEnv()
    {
        $env = new Env();

        $env->set([
            'key1' => 'value1',
            'key2' => [
                'key1' => 'value1-2',
            ],
        ]);

        $env->set('key3', 'value3');

        $env->key4 = 'value4';

        $env['key5'] = 'value5';

        $this->assertEquals('value1', $env->get('key1'));
        $this->assertEquals('value1-2', $env->get('key2.key1'));

        $this->assertEquals('value3', $env->get('key3'));

        $this->assertEquals('value4', $env->key4);

        $this->assertEquals('value5', $env['key5']);

        $this->expectException(Exception::class);

        unset($env['key5']);
    }
}
