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

$status = 1;// 启用：0，禁用：1

// 修改文件的存储状态。参考文档：https://developer.qiniu.com/kodo/api/4173/modify-the-file-status

$key = "qiniu.jpg";

list($ret, $err) = $bucketManager->changeStatus($bucket, $key, $status);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
