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

// 列举空间，返回 bucket 列表
// 存储区域参考文档：https://developer.qiniu.com/kodo/manual/1671/region-endpoint

$region = 'z1';  // 华东：z0，华北：z1，华南：z2，北美：na0，东南亚：as0

list($ret, $err) = $bucketManager->listbuckets($region);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
