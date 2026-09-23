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

// 资源元信息查询
// 参考文档：https://developer.qiniu.com/kodo/api/1308/stat

$key = "qiniu.mp4";

list($ret, $err) = $bucketManager->stat($bucket, $key);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
