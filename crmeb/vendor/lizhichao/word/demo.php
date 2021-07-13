<?php
/**
 * Created by PhpStorm.
 * User: tanszhe
 * Date: 2017/12/25
 * Time: 下午7:46
 */
//定义词典文件路径

require __DIR__.'/Lib/VicWord.php';

use Lizhichao\Word\VicWord;


//type: 词典格式
$fc = new VicWord();

//长度优先分词
$ar = $fc->getWord('聚知台是一个及时沟通工具');

//细切分
$ar = $fc->getShortWord('聚知台是一个及时沟通工具');

//自动 这种方法最耗时
$ar = $fc->getAutoWord('聚知台是一个及时沟通工具');
print_r($ar);
/*

Array
(
    [0] => Array
        (
            [0] => 聚知台 //词语
            [1] => 8  //词语的位置 utf-8编码
            [2] =>    //词性 tip:词库里面没有词性 欢迎大家添加
            [3] => 1  // 1 词典含有该词语 0没有该词语
        )

    [1] => Array
        (
            [0] => 是
            [1] => 10
            [2] =>
            [3] => 1
        )

    [2] => Array
        (
            [0] => 一个
            [1] => 16
            [2] =>
            [3] => 1
        )

    [3] => Array
        (
            [0] => 及时
            [1] => 23
            [2] =>
            [3] => 1
        )

    [4] => Array
        (
            [0] => 沟通
            [1] => 29
            [2] =>
            [3] => 1
        )

    [5] => Array
        (
            [0] => 工具
            [1] => 36
            [2] =>
            [3] => 1
        )

)

 */
