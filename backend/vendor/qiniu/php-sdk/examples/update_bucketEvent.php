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

// 更新 bucket 事件通知规则
// 参考文档：https://developer.qiniu.com/kodo/manual/6095/event-notification

$bucket = 'xxxx';
$name = 'demo';
$prefix = 'test';
$suffix = 'mp4';
$event = array("move","copy");
$callbackURL = 'https://www.qiniu.com';

list($ret, $err) = $bucketManager->updateBucketEvent($bucket, $name, $prefix, $suffix, $event, $callbackURL);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
