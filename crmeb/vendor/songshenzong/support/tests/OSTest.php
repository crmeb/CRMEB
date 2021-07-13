<?php

namespace Songshenzong\Support\Test\Core;

use ReflectionClass;
use ReflectionException;
use Songshenzong\Support\OS;
use PHPUnit\Framework\TestCase;

/**
 * Class OSTest
 *
 * @package Songshenzong\Support\Test\Core
 */
class OSTest extends TestCase
{

    /**
     * @throws ReflectionException
     */
    public function testGetsHomeDirectoryForWindowsUser()
    {
        putenv('HOME=');
        putenv('HOMEDRIVE=C:');
        putenv('HOMEPATH=\\Users\\Support');
        $ref    = new ReflectionClass(OS::class);
        $method = $ref->getMethod('getHomeDirectory');
        $method->setAccessible(true);
        $this->assertEquals('C:\\Users\\Support', $method->invoke(null));
    }

    /**
     * @depends testGetsHomeDirectoryForWindowsUser
     * @throws ReflectionException
     */
    public function testGetsHomeDirectoryForLinuxUser()
    {
        putenv('HOME=/root');
        putenv('HOMEDRIVE=');
        putenv('HOMEPATH=');
        $ref    = new ReflectionClass(OS::class);
        $method = $ref->getMethod('getHomeDirectory');
        $method->setAccessible(true);
        $this->assertEquals('/root', $method->invoke(null));
    }
}
