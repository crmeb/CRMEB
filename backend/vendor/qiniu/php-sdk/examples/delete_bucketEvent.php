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

// 删除 bucket 事件通知规则
// 参考文档：https://developer.qiniu.com/kodo/manual/6095/event-notification

$bucket = 'xxxx';  // 存储空间名称

$name = 'demo'; // 规则名称 bucket 内唯一，由 1 ~ 50 个字符组成，可包含：字母、数字和下划线

list($ret, $err) = $bucketManager->deleteBucketEvent($bucket, $name);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
