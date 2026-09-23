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

// 存储空间 - 设置 referer 防盗链
// 参考文档：https://developer.qiniu.com/kodo/manual/6093/set-the-hotlinking-prevention

$bucket = getenv('QINIU_TEST_BUCKET');
$mode = 1; // 关闭Referer(使用此选项将会忽略以下参数并将恢复默认值):0，设置Referer白名单：1，表示设置Referer黑名单：2
$norefer = "1"; // 表示不允许空 Refer 访问：0，表示允许空 Refer 访问：1
$pattern = "*.qiniu.com"; // 当前仅支持这三种格式：a.b.com，*.b.com，*

list($ret, $err) = $bucketManager->putReferAntiLeech($bucket, $mode, $norefer, $pattern);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
