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

// 删除存储空间 - 生命周期规则
// 参考文档：https://developer.qiniu.com/kodo/manual/3699/life-cycle-management

$bucket = 'xxxx'; // 存储空间名称
$name = 'demo'; // 生命周期规则名称

list($ret, $err) = $bucketManager->deleteBucketLifecycleRule($bucket, $name);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
