<?php

namespace Qiniu\Tests;

/**
 * imageprocess test
 *
 * @package Qiniu
 * @subpackage test
 * @author Sherlock Ren <sherlock_ren@icloud.com>
 */
class ImageUrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * 缩略图测试
     *
     * @test
     * @return void
     * @author Sherlock Ren <sherlock_ren@icloud.com>
     */
    public function testThumbutl()
    {
        $imageUrlBuilder = new \Qiniu\Processing\ImageUrlBuilder();
        $url = 'http://78re52.com1.z0.glb.clouddn.com/resource/gogopher.jpg';
        $url2 = $url . '?watermark/1/gravity/SouthEast/dx/0/dy/0/image/'
            . 'aHR0cDovL2Fkcy1jZG4uY2h1Y2h1amllLmNvbS9Ga1R6bnpIY2RLdmRBUFc5cHZZZ3pTc21UY0tB';
        // 异常测试
        $this->assertEquals($url, $imageUrlBuilder->thumbnail($url, 1, 0, 0));
        $this->assertEquals($url, \Qiniu\thumbnail($url, 1, 0, 0));

        // 简单缩略测试
        $this->assertEquals(
            $url . '?imageView2/1/w/200/h/200/ignore-error/1/',
            $imageUrlBuilder->thumbnail($url, 1, 200, 200)
        );
        $this->assertEquals(
            $url . '?imageView2/1/w/200/h/200/ignore-error/1/',
            \Qiniu\thumbnail($url, 1, 200, 200)
        );

        // 输出格式测试
        $this->assertEquals(
            $url . '?imageView2/1/w/200/h/200/format/png/ignore-error/1/',
            $imageUrlBuilder->thumbnail($url, 1, 200, 200, 'png')
        );
        $this->assertEquals(
            $url . '?imageView2/1/w/200/h/200/format/png/ignore-error/1/',
            \Qiniu\thumbnail($url, 1, 200, 200, 'png')
        );

        // 渐进显示测试
        $this->assertEquals(
            $url . '?imageView2/1/w/200/h/200/format/png/interlace/1/ignore-error/1/',
            $imageUrlBuilder->thumbnail($url, 1, 200, 200, 'png', 1)
        );
        $this->assertEquals(
            $url . '?imageView2/1/w/200/h/200/format/png/ignore-error/1/',
            \Qiniu\thumbnail($url, 1, 200, 200, 'png', 2)
        );

        // 图片质量测试
        $this->assertEquals(
            $url . '?imageView2/1/w/200/h/200/format/png/interlace/1/q/80/ignore-error/1/',
            $imageUrlBuilder->thumbnail($url, 1, 200, 200, 'png', 1, 80)
        );
        $this->assertEquals(
            $url . '?imageView2/1/w/200/h/200/format/png/interlace/1/ignore-error/1/',
            \Qiniu\thumbnail($url, 1, 200, 200, 'png', 1, 101)
        );

        // 多参数测试
        $this->assertEquals(
            $url2 . '|imageView2/1/w/200/h/200/ignore-error/1/',
            $imageUrlBuilder->thumbnail($url2, 1, 200, 200)
        );
        $this->assertEquals(
            $url2 . '|imageView2/1/w/200/h/200/ignore-error/1/',
            \Qiniu\thumbnail($url2, 1, 200, 200)
        );
    }

    /**
     * 图片水印测试
     *
     * @test
     * @param  void
     * @return void
     * @author Sherlock Ren <sherlock_ren@icloud.com>
     */
    public function waterImgTest()
    {
        $imageUrlBuilder = new \Qiniu\Processing\ImageUrlBuilder();
        $url = 'http://78re52.com1.z0.glb.clouddn.com/resource/gogopher.jpg';
        $url2 = $url . '?imageView2/1/w/200/h/200/format/png/ignore-error/1/';
        $image = 'http://developer.qiniu.com/resource/logo-2.jpg';

        // 水印简单测试
        $this->assertEquals(
            $url . '?watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw=='
            . '/dissolve/100/gravity/SouthEast/',
            $imageUrlBuilder->waterImg($url, $image)
        );
        $this->assertEquals(
            $url . '?watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw=='
            . '/gravity/SouthEast/',
            $imageUrlBuilder->waterImg($url, $image, 101)
        );
        $this->assertEquals(
            $url . '?watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw==/',
            $imageUrlBuilder->waterImg($url, $image, 101, 'sdfsd')
        );
        $this->assertEquals(
            $url . '?watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw=='
            . '/dissolve/100/gravity/SouthEast/',
            \Qiniu\waterImg($url, $image)
        );

        // 横轴边距测试
        $this->assertEquals(
            $url . '?watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw=='
            . '/dissolve/100/gravity/SouthEast/dx/10/',
            $imageUrlBuilder->waterImg($url, $image, 100, 'SouthEast', 10)
        );
        $this->assertEquals(
            $url . '?watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw=='
            . '/dissolve/100/gravity/SouthEast/',
            \Qiniu\waterImg($url, $image, 100, 'SouthEast', 'sad')
        );

        // 纵轴边距测试
        $this->assertEquals(
            $url . '?watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw=='
            . '/dissolve/100/gravity/SouthEast/dx/10/dy/10/',
            $imageUrlBuilder->waterImg($url, $image, 100, 'SouthEast', 10, 10)
        );
        $this->assertEquals(
            $url . '?watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw=='
            . '/dissolve/100/gravity/SouthEast/',
            \Qiniu\waterImg($url, $image, 100, 'SouthEast', 'sad', 'asdf')
        );

        // 自适应原图的短边比例测试
        $this->assertEquals(
            $url . '?watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw=='
            . '/dissolve/100/gravity/SouthEast/dx/10/dy/10/ws/0.5/',
            $imageUrlBuilder->waterImg($url, $image, 100, 'SouthEast', 10, 10, 0.5)
        );
        $this->assertEquals(
            $url . '?watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw=='
            . '/dissolve/100/gravity/SouthEast/',
            \Qiniu\waterImg($url, $image, 100, 'SouthEast', 'sad', 'asdf', 2)
        );

        // 多参数测试
        $this->assertEquals(
            $url2 . '|watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw=='
            . '/dissolve/100/gravity/SouthEast/',
            $imageUrlBuilder->waterImg($url2, $image)
        );
        $this->assertEquals(
            $url2 . '|watermark/1/image/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vcmVzb3VyY2UvbG9nby0yLmpwZw=='
            . '/dissolve/100/gravity/SouthEast/',
            \Qiniu\waterImg($url2, $image)
        );
    }

    /**
     * 文字水印测试
     *
     * @test
     * @param  void
     * @return void
     * @author Sherlock Ren <sherlock_ren@icloud.com>
     */
    public function waterTextTest()
    {
        $imageUrlBuilder = new \Qiniu\Processing\ImageUrlBuilder();
        $url = 'http://78re52.com1.z0.glb.clouddn.com/resource/gogopher.jpg';
        $url2 = $url . '?imageView2/1/w/200/h/200/format/png/ignore-error/1/';
        $text = '测试一下';
        $font = '微软雅黑';
        $fontColor = '#FF0000';

        // 水印简单测试
        $this->assertEquals($url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/'
            . 'fontsize/500/dissolve/100/gravity/SouthEast/', $imageUrlBuilder->waterText($url, $text, $font, 500));
        $this->assertEquals(
            $url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/'
            . 'dissolve/100/gravity/SouthEast/',
            \Qiniu\waterText($url, $text, $font, 'sdf')
        );

        // 字体颜色测试
        $this->assertEquals(
            $url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/fontsize/500/fill/'
            . 'I0ZGMDAwMA==/dissolve/100/gravity/SouthEast/',
            $imageUrlBuilder->waterText($url, $text, $font, 500, $fontColor)
        );
        $this->assertEquals(
            $url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/fill/I0ZGMDAwMA=='
            . '/dissolve/100/gravity/SouthEast/',
            \Qiniu\waterText($url, $text, $font, 'sdf', $fontColor)
        );

        // 透明度测试
        $this->assertEquals(
            $url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/fontsize/500/fill/I0ZGMDAwMA=='
            . '/dissolve/80/gravity/SouthEast/',
            $imageUrlBuilder->waterText($url, $text, $font, 500, $fontColor, 80)
        );
        $this->assertEquals(
            $url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/fill/I0ZGMDAwMA=='
            . '/gravity/SouthEast/',
            \Qiniu\waterText($url, $text, $font, 'sdf', $fontColor, 101)
        );

        // 水印位置测试
        $this->assertEquals(
            $url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/fontsize/500/fill/I0ZGMDAwMA=='
            . '/dissolve/80/gravity/East/',
            $imageUrlBuilder->waterText($url, $text, $font, 500, $fontColor, 80, 'East')
        );
        $this->assertEquals(
            $url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/fill/I0ZGMDAwMA==/',
            \Qiniu\waterText($url, $text, $font, 'sdf', $fontColor, 101, 'sdfsdf')
        );

        // 横轴距离测试
        $this->assertEquals(
            $url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/fontsize/500/fill/I0ZGMDAwMA=='
            . '/dissolve/80/gravity/East/dx/10/',
            $imageUrlBuilder->waterText($url, $text, $font, 500, $fontColor, 80, 'East', 10)
        );
        $this->assertEquals(
            $url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/fill/I0ZGMDAwMA==/',
            \Qiniu\waterText($url, $text, $font, 'sdf', $fontColor, 101, 'sdfsdf', 'sdfs')
        );

        // 纵轴距离测试
        $this->assertEquals(
            $url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/fontsize/500/fill/I0ZGMDAwMA=='
            . '/dissolve/80/gravity/East/dx/10/dy/10/',
            $imageUrlBuilder->waterText($url, $text, $font, 500, $fontColor, 80, 'East', 10, 10)
        );
        $this->assertEquals(
            $url . '?watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/fill/I0ZGMDAwMA==/',
            \Qiniu\waterText($url, $text, $font, 'sdf', $fontColor, 101, 'sdfsdf', 'sdfs', 'ssdf')
        );
        // 多参数测试
        $this->assertEquals(
            $url2 . '|watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/'
            . 'fontsize/500/dissolve/100/gravity/SouthEast/',
            $imageUrlBuilder->waterText($url2, $text, $font, 500)
        );
        $this->assertEquals(
            $url2 . '|watermark/2/text/5rWL6K-V5LiA5LiL/font/5b6u6L2v6ZuF6buR/'
            . 'fontsize/500/dissolve/100/gravity/SouthEast/',
            \Qiniu\waterText($url2, $text, $font, 500)
        );
    }
}
