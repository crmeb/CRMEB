<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;

// 去我们的portal 后台来获取AK, SK
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');
$key = 'qiniu.png';

$auth = new Auth($accessKey, $secretKey);
// 异步任务的队列， 去后台新建： https://portal.qiniu.com/mps/pipeline
$pipeline = 'sdktest';

$pfop = new PersistentFop($auth, null);

// 进行zip压缩的url
$url1 = 'http://phpsdk.qiniudn.com/php-logo.png';
$url2 = 'http://phpsdk.qiniudn.com/1.png';

//压缩后的key
$zipKey = 'test.zip';

$fops = 'mkzip/2/url/' . \Qiniu\base64_urlSafeEncode($url1);
$fops .= '/url/' . \Qiniu\base64_urlSafeEncode($url2);
$fops .= '|saveas/' . \Qiniu\base64_urlSafeEncode("$bucket:$zipKey");

$notify_url = null;
$force = false;

list($id, $err) = $pfop->execute($bucket, $key, $fops, $pipeline, $notify_url, $force);

echo "\n====> pfop mkzip result: \n";
if ($err != null) {
    var_dump($err);
} else {
    echo "PersistentFop Id: $id\n";

    $res = "http://api.qiniu.com/status/get/prefop?id=$id";
    echo "Processing result: $res";
}
