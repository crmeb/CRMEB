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

// 获取用户 bucket 配额限制
// size 表示空间存储量配额，count 表示空间文件数配额，新创建的空间默认没有限额

$bucket = 'xxxx'; // 存储空间名称

list($ret, $err) = $bucketManager->getBucketQuota($bucket);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
