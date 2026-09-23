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

// 增加 bucket 事件通知规则
// 参考文档：https://developer.qiniu.com/kodo/manual/6095/event-notification

$bucket = getenv('QINIU_TEST_BUCKET');
$name = 'testnotification';
$prefix = 'test1';
$suffix = 'mp3';
$event = array("move","copy");
$callbackURL = 'http://www.qiniu.com'; // 回调服务器地址，需要可以公网访问，并能够相应 200 OK

list($ret, $err) = $bucketManager->putBucketEvent($bucket, $name, $prefix, $suffix, $event, $callbackURL);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
