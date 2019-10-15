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

class TextTest extends TestCase
{
    public function testJpeg()
    {
        $pathname = TEST_PATH . 'tmp/text.jpg';
        $image    = Image::open($this->getJpeg());

        $image->text('test', TEST_PATH . 'images/test.ttf', 12)->save($pathname);

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);
    }

    public function testPng()
    {
        $pathname = TEST_PATH . 'tmp/text.png';
        $image    = Image::open($this->getPng());

        $image->text('test', TEST_PATH . 'images/test.ttf', 12)->save($pathname);

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);
    }

    public function testGif()
    {
        $pathname = TEST_PATH . 'tmp/text.gif';
        $image    = Image::open($this->getGif());

        $image->text('test', TEST_PATH . 'images/test.ttf', 12)->save($pathname);

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);
    }
}