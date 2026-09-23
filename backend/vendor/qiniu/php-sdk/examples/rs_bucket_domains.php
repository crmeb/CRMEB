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

// 获取指定空间绑定的所有的域名
// 参考文档：https://developer.qiniu.com/kodo/api/3949/get-the-bucket-space-domain

list($ret, $err) = $bucketManager->domains($bucket);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
