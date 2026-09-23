<?php

namespace Tests;

use think\helper\Arr;

class IsAssocTest extends TestCase
{
    public function testEmptyArray()
    {
        // 空数组不是关联数组
        $this->assertFalse(Arr::isAssoc([]));
    }

    public function testSequentialArray()
    {
        // 顺序索引数组不是关联数组
        $this->assertFalse(Arr::isAssoc([1, 2, 3]));
        $this->assertFalse(Arr::isAssoc(['a', 'b', 'c']));
        $this->assertFalse(Arr::isAssoc([null, false, true]));
    }

    public function testNonSequentialArray()
    {
        // 非顺序索引数组是关联数组
        $this->assertTrue(Arr::isAssoc([1 => 'a', 0 => 'b'])); // 键顺序不是0,1
        $this->assertTrue(Arr::isAssoc([1 => 'a', 2 => 'b'])); // 不是从0开始
        $this->assertTrue(Arr::isAssoc([0 => 'a', 2 => 'b'])); // 不连续
    }

    public function testStringKeys()
    {
        // 字符串键的数组是关联数组
        $this->assertTrue(Arr::isAssoc(['a' => 1, 'b' => 2]));
        // 注意：PHP会将字符串数字键'0'、'1'自动转换为整数键0、1
        // 所以这个实际上是顺序索引数组，不是关联数组
        $this->assertFalse(Arr::isAssoc(['0' => 'a', '1' => 'b']));
        $this->assertTrue(Arr::isAssoc(['a' => 'a', 0 => 'b'])); // 混合键
    }

    public function testMixedKeys()
    {
        // 混合键类型的数组是关联数组
        $this->assertTrue(Arr::isAssoc([0 => 'a', 'b' => 'b']));
        $this->assertTrue(Arr::isAssoc(['a' => 1, 2 => 'b']));
    }

}
