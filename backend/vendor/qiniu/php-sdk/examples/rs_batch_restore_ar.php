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

// 批量解冻归档/深度归档文件，每次最多不能超过 1000 个

$keys = array(
    'archived_file.mp4',
    'deep_archived_file.png',
    'others.jpg'
);

$keyTypePairs = array();


// key 是文件
// value 是解冻时间（单位：天，范围 1~7）
foreach ($keys as $key) {
    $keyTypePairs[$key] = 1;
}

$ops = BucketManager::buildBatchRestoreAr($bucket, $keyTypePairs);
list($ret, $err) = $bucketManager->batch($ops);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
