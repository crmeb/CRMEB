<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');

$key = "qiniu.mp4";

$auth = new Auth($accessKey, $secretKey);
$config = new \Qiniu\Config();
$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

// 镜像资源更新
// 参考文档：https://developer.qiniu.com/kodo/api/1293/prefetch

list($ret, $err) = $bucketManager->prefetch($bucket, $key);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
