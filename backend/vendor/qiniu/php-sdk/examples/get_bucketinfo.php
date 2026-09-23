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

// 获取指定空间的相关信息

$bucket = 'xxxx'; // 存储空间名称

list($ret, $err) = $bucketManager->bucketInfo($bucket);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
