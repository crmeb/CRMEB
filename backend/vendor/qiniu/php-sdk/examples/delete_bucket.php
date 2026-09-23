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

// 删除指定的 Bucket
// 1、空间绑定了自定义域名，禁止删除，需要先解绑域名
// 2、空间不为空，禁止删除，需要先把空间内的文件删除完毕

$bucket = 'xxxx'; // 存储空间名称

list($ret, $err) = $bucketManager->deleteBucket($bucket);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
