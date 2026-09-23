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

use think\Image;

class RotateTest extends TestCase
{
    public function testJpeg()
    {
        $pathname = TEST_PATH . 'tmp/rotate.jpg';
        $image    = Image::open($this->getJpeg());
        $image->rotate(90)->save($pathname);

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);
    }

    public function testGif()
    {
        $pathname = TEST_PATH . 'tmp/rotate.gif';
        $image    = Image::open($this->getGif());
        $image->rotate(90)->save($pathname);

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);
    }
}