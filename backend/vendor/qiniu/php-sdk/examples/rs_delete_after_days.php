<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');

$auth = new Auth($accessKey, $secretKey);
$config = new \Qiniu\Config();
$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

// 设置文件的生命周期
// 参考文档：https://developer.qiniu.com/kodo/api/1732/update-file-lifecycle

$key = 'qiniu.mp4';
$days = 10; // 设置为 0 表示取消生命周期

list($ret, $err) = $bucketManager->deleteAfterDays($bucket, $key, $days);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
