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

// 批量修改文件类型，每次最多不能超过 1000 个

$keyMimePairs = array(
    'qiniu.mp4' => 'video/x-mp4',
    'qiniu.png' => 'image/x-png',
    'qiniu.jpg' => 'image/x-jpg'
);

$ops = $bucketManager->buildBatchChangeMime($bucket, $keyMimePairs);
list($ret, $err) = $bucketManager->batch($ops);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
