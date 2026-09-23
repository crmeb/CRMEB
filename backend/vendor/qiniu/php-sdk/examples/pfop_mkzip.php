<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);

// 将七牛存储空间中的资源进行批量压缩
// 参考文档：https://developer.qiniu.com/dora/api/1667/mkzip

// 要压缩的文件所在的空间和文件名
$bucket = getenv('QINIU_TEST_BUCKET');
$key = 'qiniu.png';

// 用户默认没有私有队列，需要在这里创建然后填写 https://portal.qiniu.com/dora/media-gate/pipeline
$pipeline = 'sdktest';

$pfop = new PersistentFop($auth, null);

// 进行 zip 压缩的 url
$url1 = 'http://phpsdk.qiniudn.com/php-logo.png';
$url2 = 'http://phpsdk.qiniudn.com/1.png';

// 压缩后的 key
$zipKey = 'test.zip';

$fops = 'mkzip/2/url/' . \Qiniu\base64_urlSafeEncode($url1);
$fops .= '/url/' . \Qiniu\base64_urlSafeEncode($url2);
$fops .= '|saveas/' . \Qiniu\base64_urlSafeEncode("$bucket:$zipKey");

// 处理完成后通知到你的业务服务器（需要可以公网访问，并能够相应 200 OK）
$notify_url = null;

// 当转码后的文件名与源文件名相同时，是否覆盖源文件
$force = false;

list($id, $err) = $pfop->execute($bucket, $key, $fops, $pipeline, $notify_url, $force);

echo "\n====> pfop mkzip result: \n";
if ($err != null) {
    var_dump($err);
} else {
    echo "PersistentFop Id: $id\n";
}

// 查询转码的进度和状态
list($ret, $err) = $pfop->status($id);
echo "\n====> pfop mkzip status: \n";
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
