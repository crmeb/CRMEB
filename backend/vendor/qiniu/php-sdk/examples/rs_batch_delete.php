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

// 批量删除文件，每次最多不能超过 1000 个

$keys = array(
    'qiniu.mp4',
    'qiniu.png',
    'qiniu.jpg'
);

$ops = $bucketManager->buildBatchDelete($bucket, $keys);
list($ret, $err) = $bucketManager->batch($ops);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
