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

class InfoTest extends TestCase
{

    public function testOpen()
    {
        $this->setExpectedException("\\think\\image\\Exception");
        Image::open('');
    }

    public function testIllegal()
    {
        $this->setExpectedException("\\think\\image\\Exception", 'Illegal image file');
        Image::open(TEST_PATH . 'images/test.bmp');
    }

    public function testJpeg()
    {
        $image = Image::open($this->getJpeg());
        $this->assertEquals(800, $image->width());
        $this->assertEquals(600, $image->height());
        $this->assertEquals('jpeg', $image->type());
        $this->assertEquals('image/jpeg', $image->mime());
        $this->assertEquals([800, 600], $image->size());
    }


    public function testPng()
    {
        $image = Image::open($this->getPng());
        $this->assertEquals(800, $image->width());
        $this->assertEquals(600, $image->height());
        $this->assertEquals('png', $image->type());
        $this->assertEquals('image/png', $image->mime());
        $this->assertEquals([800, 600], $image->size());
    }

    public function testGif()
    {
        $image = Image::open($this->getGif());
        $this->assertEquals(380, $image->width());
        $this->assertEquals(216, $image->height());
        $this->assertEquals('gif', $image->type());
        $this->assertEquals('image/gif', $image->mime());
        $this->assertEquals([380, 216], $image->size());
    }
}