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

// 获取指定账号下所有的空间名
// 参考文档：https://developer.qiniu.com/kodo/api/3926/get-service

list($ret, $err) = $bucketManager->buckets(true);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
