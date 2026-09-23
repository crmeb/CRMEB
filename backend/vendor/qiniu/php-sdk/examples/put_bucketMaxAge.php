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

// 设置 Bucket 的 maxAge
// 参考文档：https://developer.qiniu.com/kodo/kb/1475/space-maxage-attribute-to-the-cache-control

$bucket = getenv('QINIU_TEST_BUCKET');
$maxAge = 31536000;

list($ret, $err) = $bucketManager->putBucketMaxAge($bucket, $maxAge);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
