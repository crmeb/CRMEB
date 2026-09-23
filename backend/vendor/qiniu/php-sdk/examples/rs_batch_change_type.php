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

// 批量修改文件存储类型，每次最多不能超过 1000 个

$keys = array(
    'qiniu.mp4',
    'qiniu.png',
    'qiniu.jpg'
);

$keyTypePairs = array();

// key 是文件
// value 是存储类型（fileType）
// 0 表示普通存储
// 1 表示低频存储
// 2 表示归档存储
// 3 表示深度归档存储
// 4 表示归档直读存储
foreach ($keys as $key) {
    $keyTypePairs[$key] = 1;
}

$ops = BucketManager::buildBatchChangeType($bucket, $keyTypePairs);
list($ret, $err) = $bucketManager->batch($ops);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
