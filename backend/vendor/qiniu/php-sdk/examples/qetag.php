<?php
require_once __DIR__ . '/../autoload.php';
use Qiniu\Etag;

// 计算文件的 ETag
// 参考文档：https://developer.qiniu.com/kodo/manual/1231/appendix#3

$localFile = "./php-logo.png";
list($ret, $err) = Etag::sum($localFile);
if ($err != null) {
    var_dump($err);
} else {
    echo "Etag: $ret";
}
