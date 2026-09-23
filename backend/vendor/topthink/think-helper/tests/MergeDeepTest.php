<?php

namespace Tests;

use think\helper\Arr;

class MergeDeepTest extends TestCase
{
    public function testMergeDeepWithAssociativeArrays()
    {
        // 测试关联数组的递归合并
        $array1 = ['a' => ['b' => 2], 'c' => 3];
        $array2 = ['a' => ['b' => 4, 'd' => 5], 'e' => 6];
        
        $result = Arr::mergeDeep($array1, $array2);
        
        $expected = [
            'a' => ['b' => 4, 'd' => 5],
            'c' => 3,
            'e' => 6
        ];
        
        $this->assertEquals($expected, $result);
    }
    
    public function testMergeDeepWithIndexedArrays()
    {
        // 测试索引数组的覆盖
        $array1 = ['a' => [1, 2, 3], 'b' => 2];
        $array2 = ['a' => [4, 5], 'c' => 3];
        
        $result = Arr::mergeDeep($array1, $array2);
        
        $expected = [
            'a' => [4, 5],  // 索引数组应该被完全覆盖
            'b' => 2,
            'c' => 3
        ];
        
        $this->assertEquals($expected, $result);
    }
    
    public function testMergeDeepWithMixedArrays()
    {
        // 测试混合数组类型
        $array1 = [
            'a' => ['b' => 2, 'c' => 3],
            'd' => [1, 2, 3],
            'e' => 4
        ];
        
        $array2 = [
            'a' => ['b' => 5, 'f' => 6],
            'd' => [7, 8],
            'g' => 9
        ];
        
        $result = Arr::mergeDeep($array1, $array2);
        
        $expected = [
            'a' => ['b' => 5, 'c' => 3, 'f' => 6],  // 关联数组递归合并
            'd' => [7, 8],  // 索引数组被覆盖
            'e' => 4,
            'g' => 9
        ];
        
        $this->assertEquals($expected, $result);
    }
}
