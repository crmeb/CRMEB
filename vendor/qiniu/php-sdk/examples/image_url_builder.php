<?php
require_once __DIR__ . '/../autoload.php';

// 引入图片处理类
use Qiniu\Processing\ImageUrlBuilder;

$imageUrlBuilder = new ImageUrlBuilder();

// 要处理图片
$url = 'http://78re52.com1.z0.glb.clouddn.com/resource/gogopher.jpg';
$url2 = 'http://78re52.com1.z0.glb.clouddn.com/resource/gogopher.jpg?watermark/1/gravity/SouthEast/dx/0/dy/0/image/'
    . 'aHR0cDovL2Fkcy1jZG4uY2h1Y2h1amllLmNvbS9Ga1R6bnpIY2RLdmRBUFc5cHZZZ3pTc21UY0tB';
$waterImage = 'http://developer.qiniu.com/resource/logo-2.jpg';

/**
 * 缩略图链接拼接
 *
 * @param  string $url 图片链接
 * @param  int $mode 缩略模式
 * @param  int $width 宽度
 * @param  int $height 长度
 * @param  string $format 输出类型 [可选]
 * @param  int $quality 图片质量 [可选]
 * @param  int $interlace 是否支持渐进显示 [可选]
 * @param  int $ignoreError 忽略结果 [可选]
 * @return string
 * @link http://developer.qiniu.com/code/v6/api/kodo-api/image/imageview2.html
 * @author Sherlock Ren <sherlock_ren@icloud.com>
 */
$thumbLink = $imageUrlBuilder->thumbnail($url, 1, 100, 100);

// 函数方式调用 也可拼接多个操作参数 图片+水印
$thumbLink2 = \Qiniu\thumbnail($url2, 1, 100, 100);
var_dump($thumbLink, $thumbLink2);

/**
 * 图片水印
 *
 * @param  string $url 图片链接
 * @param  string $image 水印图片链接
 * @param  numeric $dissolve 透明度 [可选]
 * @param  string $gravity 水印位置 [可选]
 * @param  numeric $dx 横轴边距 [可选]
 * @param  numeric $dy 纵轴边距 [可选]
 * @param  numeric $watermarkScale 自适应原图的短边比例 [可选]
 * @link   http://developer.qiniu.com/code/v6/api/kodo-api/image/watermark.html
 * @return string
 * @author Sherlock Ren <sherlock_ren@icloud.com>
 */
$waterLink = $imageUrlBuilder->waterImg($url, $waterImage);
// 函数调用方法
//$waterLink = \Qiniu\waterImg($url, $waterImage);
var_dump($waterLink);

/**
 * 文字水印
 *
 * @param  string $url 图片链接
 * @param  string $text 文字
 * @param  string $font 文字字体
 * @param  string $fontSize 文字字号
 * @param  string $fontColor 文字颜色 [可选]
 * @param  numeric $dissolve 透明度 [可选]
 * @param  string $gravity 水印位置 [可选]
 * @param  numeric $dx 横轴边距 [可选]
 * @param  numeric $dy 纵轴边距 [可选]
 * @link   http://developer.qiniu.com/code/v6/api/kodo-api/image/watermark.html#text-watermark
 * @return string
 * @author Sherlock Ren <sherlock_ren@icloud.com>
 */
$textLink = $imageUrlBuilder->waterText($url, '你瞅啥', '微软雅黑', 300);
// 函数调用方法
// $textLink = \Qiniu\waterText($url, '你瞅啥', '微软雅黑', 300);
var_dump($textLink);
