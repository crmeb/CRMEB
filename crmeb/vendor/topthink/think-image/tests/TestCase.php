<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace tests;

use think\File;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{

    protected function getJpeg()
    {
        return new File(TEST_PATH . 'images/test.jpg');
    }

    protected function getPng()
    {
        return new File(TEST_PATH . 'images/test.png');
    }

    protected function getGif()
    {
        return new File(TEST_PATH . 'images/test.gif');
    }
}