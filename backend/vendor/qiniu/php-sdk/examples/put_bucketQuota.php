<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');

$auth = new Auth($accessKey, $secretKey);

$config = new Config();
$bucketManager = new BucketManager($auth, $config);

// 设置 bucket 配额
// size 表示空间存储量配额，count 表示空间文件数配额，新创建的空间默认没有限额

$size = 99999;
$count = 99;

list($ret, $err) = $bucketManager->putBucketQuota($bucket, $size, $count);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
