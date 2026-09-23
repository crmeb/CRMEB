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



// 修改指定资源的存储类型
// 参考文档：https://developer.qiniu.com/kodo/api/3710/chtype

$key = "qiniu.mp4";
// 0 表示标准存储；
// 1 表示低频存储；
// 2 表示归档存储；
// 3 表示深度归档存储；
// 4 表示归档直读存储；
$fileType = 1;

list($ret, $err) = $bucketManager->changeType($bucket, $key, $fileType);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
