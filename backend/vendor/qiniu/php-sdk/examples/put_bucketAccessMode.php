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

$bucket = 'xxxx'; // 存储空间名称
$private = 1; // 公开：0，私有：1

// 设置 Bucket 访问权限
// 参考文档：https://developer.qiniu.com/kodo/api/3946/set-bucket-private

list($ret, $err) = $bucketManager->putBucketAccessMode($bucket, $private);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
