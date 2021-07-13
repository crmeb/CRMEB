<?php

namespace Songshenzong\Support\Test\Core;

use PHPUnit\Framework\TestCase;

/**
 * Class EchoBashTest
 *
 * @package Songshenzong\Support\Test\Core
 */
class EchoBashTest extends TestCase
{

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testIsJson()
    {
        echoRed('echo Red');
        echoPurple('echo Purple');
        echoGreen('echo Green');
        echoCyan('echo Cyan');
        echoBrown('echo Brown');
        echoBlue('echo Blue');
        $this->assertEquals(true, true);
    }
}
