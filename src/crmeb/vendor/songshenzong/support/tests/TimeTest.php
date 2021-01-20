<?php

namespace Songshenzong\Support\Test\Core;

use Songshenzong\Support\Time;
use PHPUnit\Framework\TestCase;

class TimeTest extends TestCase
{
    public function testDates()
    {
        $expect = [
            '2019-09-10',
            '2019-09-11',
            '2019-09-12',
        ];

        self::assertEquals($expect, Time::dates('2019-09-10', '2019-09-12'));

        self::assertEquals($expect, Time::dates(strtotime('2019-09-10'), strtotime('2019-09-12')));
    }
}
