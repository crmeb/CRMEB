<?php

namespace think\tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use stdClass;
use think\Container;
use think\exception\ClassNotFoundException;
use think\exception\FuncNotFoundException;

class Taylor
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function some(Container $container)
    {
    }

    protected function protectionFun()
    {
        return true;
    }

    public static function test(Container $container)
    {
        return $container;
    }

    public static function __make()
    {
        return new self('Taylor');
    }
}

class SomeClass
{
    public $container;

    public $count = 0;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}

class ContainerTest extends TestCase
{
    protected function tearDown(): void
    {
        Container::setInstance(null);
    }

    public function testClosureResolution()
    {
        $container = new Container;

        Container::setInstance($container);

        $container->bind('name', function () {
            return 'Taylor';
        });

        $this->assertEquals('Taylor', $container->make('name'));

        $this->assertEquals('Taylor', Container::pull('name'));
    }

    public function testGet()
    {
        $container = new Container;

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage('class not exists: name');
        $container->get('name');

        $container->bind('name', function () {
            return 'Taylor';
        });

        $this->assertSame('Taylor', $container->get('name'));
    }

    public function testExist()
    {
        $container = new Container;

        $container->bind('name', function () {
            return 'Taylor';
        });

        $this->assertFalse($container->exists("name"));

        $container->make('name');

        $this->assertTrue($container->exists('name'));
    }

    public function testInstance()
    {
        $container = new Container;

        $container->bind('name', function () {
            return 'Taylor';
        });

        $this->assertEquals('Taylor', $container->get('name'));

        $container->bind('name2', Taylor::class);

        $object = new stdClass();

        $this->assertFalse($container->exists('name2'));

        $container->instance('name2', $object);

        $this->assertTrue($container->exists('name2'));

        $this->assertTrue($container->exists(Taylor::class));

        $this->assertEquals($object, $container->make(Taylor::class));

        unset($container->name1);

        $this->assertFalse($container->exists('name1'));

        $container->delete('name2');

        $this->assertFalse($container->exists('name2'));

        foreach ($container as $class => $instance) {

        }
    }

    public function testBind()
    {
        $container = new Container;

        $object = new stdClass();

        $container->bind(['name' => Taylor::class]);

        $container->bind('name2', $object);

        $container->bind('name3', Taylor::class);

        $container->name4 = $object;

        $container['name5'] = $object;

        $this->assertTrue(isset($container->name4));

        $this->assertTrue(isset($container['name5']));

        $this->assertInstanceOf(Taylor::class, $container->get('name'));

        $this->assertSame($object, $container->get('name2'));

        $this->assertSame($object, $container->name4);

        $this->assertSame($object, $container['name5']);

        $this->assertInstanceOf(Taylor::class, $container->get('name3'));

        unset($container['name']);

        $this->assertFalse(isset($container['name']));

        unset($container->name3);

        $this->assertFalse(isset($container->name3));
    }

    public function testAutoConcreteResolution()
    {
        $container = new Container;

        $taylor = $container->make(Taylor::class);

        $this->assertInstanceOf(Taylor::class, $taylor);
        $this->assertAttributeSame('Taylor', 'name', $taylor);
    }

    public function testGetAndSetInstance()
    {
        $this->assertInstanceOf(Container::class, Container::getInstance());

        $object = new stdClass();

        Container::setInstance($object);

        $this->assertSame($object, Container::getInstance());

        Container::setInstance(function () {
            return $this;
        });

        $this->assertSame($this, Container::getInstance());
    }

    public function testResolving()
    {
        $container = new Container();
        $container->bind(Container::class, $container);

        $container->resolving(function (SomeClass $taylor, Container $container) {
            $taylor->count++;
        });
        $container->resolving(SomeClass::class, function (SomeClass $taylor, Container $container) {
            $taylor->count++;
        });

        /** @var SomeClass $someClass */
        $someClass = $container->invokeClass(SomeClass::class);
        $this->assertEquals(2, $someClass->count);
    }

    public function testInvokeFunctionWithoutMethodThrowsException()
    {
        $this->expectException(FuncNotFoundException::class);
        $this->expectExceptionMessage('function not exists: ContainerTestCallStub()');
        $container = new Container();
        $container->invokeFunction('ContainerTestCallStub', []);
    }

    public function testInvokeProtectionMethod()
    {
        $container = new Container();
        $this->assertTrue($container->invokeMethod([Taylor::class, 'protectionFun'], [], true));
    }

    public function testInvoke()
    {
        $container = new Container();

        Container::setInstance($container);

        $container->bind(Container::class, $container);

        $stub = $this->createMock(Taylor::class);

        $stub->expects($this->once())->method('some')->with($container)->will($this->returnSelf());

        $container->invokeMethod([$stub, 'some']);

        $this->assertEquals('48', $container->invoke('ord', ['0']));

        $this->assertSame($container, $container->invoke(Taylor::class . '::test', []));

        $this->assertSame($container, $container->invokeMethod(Taylor::class . '::test'));

        $reflect = new ReflectionMethod($container, 'exists');

        $this->assertTrue($container->invokeReflectMethod($container, $reflect, [Container::class]));

        $this->assertSame($container, $container->invoke(function (Container $container) {
            return $container;
        }));

        $this->assertSame($container, $container->invoke(Taylor::class . '::test'));

        $object = $container->invokeClass(SomeClass::class);
        $this->assertInstanceOf(SomeClass::class, $object);
        $this->assertSame($container, $object->container);

        $stdClass = new stdClass();

        $container->invoke(function (Container $container, stdClass $stdObject, $key1, $lowKey, $key2 = 'default') use ($stdClass) {
            $this->assertEquals('value1', $key1);
            $this->assertEquals('default', $key2);
            $this->assertEquals('value2', $lowKey);
            $this->assertSame($stdClass, $stdObject);
            return $container;
        }, ['some' => $stdClass, 'key1' => 'value1', 'low_key' => 'value2']);
    }

    public function testInvokeMethodNotExists()
    {
        $container = $this->resolveContainer();
        $this->expectException(FuncNotFoundException::class);

        $container->invokeMethod([SomeClass::class, 'any']);
    }

    public function testInvokeClassNotExists()
    {
        $container = new Container();

        Container::setInstance($container);

        $container->bind(Container::class, $container);

        $this->expectExceptionObject(new ClassNotFoundException('class not exists: SomeClass'));

        $container->invokeClass('SomeClass');
    }

    protected function resolveContainer()
    {
        $container = new Container();

        Container::setInstance($container);
        return $container;
    }

}
