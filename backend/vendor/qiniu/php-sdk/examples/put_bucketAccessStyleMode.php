<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$config = new Config();
$bucketManager = new BucketManager($auth, $config);

$bucket = 'xxxx'; // 存储空间名称
$mode = 1; // 关闭：0 ，开启：1

// 存储空间 - 原图保护开关
// 原图保护：https://developer.qiniu.com/kodo/kb/1359/what-is-the-original-protection

list($ret, $err) = $bucketManager->putBucketAccessStyleMode($bucket, $mode);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
