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

class ThumbTest extends TestCase
{
    public function testJpeg()
    {
        $pathname = TEST_PATH . 'tmp/thumb.jpg';
        
        //1
        $image    = Image::open($this->getJpeg());

        $image->thumb(200, 200, Image::THUMB_CENTER)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //2
        $image    = Image::open($this->getJpeg());

        $image->thumb(200, 200, Image::THUMB_SCALING)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(150, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //3
        $image    = Image::open($this->getJpeg());

        $image->thumb(200, 200, Image::THUMB_FILLED)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //4
        $image    = Image::open($this->getJpeg());

        $image->thumb(200, 200, Image::THUMB_NORTHWEST)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //5
        $image    = Image::open($this->getJpeg());

        $image->thumb(200, 200, Image::THUMB_SOUTHEAST)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //6
        $image    = Image::open($this->getJpeg());

        $image->thumb(200, 200, Image::THUMB_FIXED)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);
    }


    public function testPng()
    {
        $pathname = TEST_PATH . 'tmp/thumb.png';

        //1
        $image    = Image::open($this->getPng());

        $image->thumb(200, 200, Image::THUMB_CENTER)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //2
        $image    = Image::open($this->getPng());

        $image->thumb(200, 200, Image::THUMB_SCALING)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(150, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //3
        $image    = Image::open($this->getPng());

        $image->thumb(200, 200, Image::THUMB_FILLED)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //4
        $image    = Image::open($this->getPng());

        $image->thumb(200, 200, Image::THUMB_NORTHWEST)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //5
        $image    = Image::open($this->getPng());

        $image->thumb(200, 200, Image::THUMB_SOUTHEAST)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //6
        $image    = Image::open($this->getPng());

        $image->thumb(200, 200, Image::THUMB_FIXED)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);
    }

    public function testGif()
    {
        $pathname = TEST_PATH . 'tmp/thumb.gif';

        //1
        $image    = Image::open($this->getGif());

        $image->thumb(200, 200, Image::THUMB_CENTER)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //2
        $image    = Image::open($this->getGif());

        $image->thumb(200, 200, Image::THUMB_SCALING)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(113, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //3
        $image    = Image::open($this->getGif());

        $image->thumb(200, 200, Image::THUMB_FILLED)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //4
        $image    = Image::open($this->getGif());

        $image->thumb(200, 200, Image::THUMB_NORTHWEST)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //5
        $image    = Image::open($this->getGif());

        $image->thumb(200, 200, Image::THUMB_SOUTHEAST)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);

        //6
        $image    = Image::open($this->getGif());

        $image->thumb(200, 200, Image::THUMB_FIXED)->save($pathname);

        $this->assertEquals(200, $image->width());
        $this->assertEquals(200, $image->height());

        $file = new \SplFileInfo($pathname);

        $this->assertTrue($file->isFile());

        @unlink($pathname);
    }
}