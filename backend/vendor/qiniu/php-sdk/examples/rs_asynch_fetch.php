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
$config->useHTTPS = true; // 接口是否使用 HTTPS 协议

$bucketManager = new BucketManager($auth, $config);

// 异步第三方资源抓取
// 参考文档：https://developer.qiniu.com/kodo/api/4097/asynch-fetch

// 需要抓取的文件 URL
$url = 'http://devtools.qiniu.com/qiniu.png';

//回调 URL（需要可以公网访问，并能够相应 200 OK）
$callbackurl = "http://your.domain.com/upload_verify_callback.php";

// 回调Body
$callbackbody = '{"key":"$(key)","hash":"$(etag)","w":"$(imageInfo.width)","h":"$(imageInfo.height)"}';


//---------------------------------------- demo1 ----------------------------------------
// 指定抓取的文件保存到七牛云空间中的名称

$key = time() . '.png';
list($ret, $err) = $bucketManager->asynchFetch($url, $bucket, null, $key, null, null, $callbackurl, $callbackbody);
echo "=====> asynch fetch $url to bucket: $bucket  key: $key\n";
if ($err !== null) {
    var_dump($err);
} else {
    $id = $ret['id'];
    echo "id is: $id\n";
}

//---------------------------------------- demo2 ----------------------------------------
// 不指定 key 时，以文件内容的 hash 作为文件名

$key = null;
list($ret, $err) = $bucketManager->asynchFetch($url, $bucket, null, $key, null, null, $callbackurl, $callbackbody);
echo "=====> asynch fetch $url to bucket: $bucket  key: $(etag)\n";
if ($err !== null) {
    var_dump($err);
} else {
    $id = $ret['id'];
    echo "id is: $id\n";
}

// 查询异步抓取的进度和状态

// 华东：z0，华北：z1，华南：z2，北美：na0，东南亚：as0
$zone = 'z2';

sleep(10); // 由于异步抓取需要耗时，等待 10 秒后再查询状态
list($ret, $err) = $bucketManager->asynchFetchStatus($zone, $id);
echo "\n====> asynch fetch status: \n";
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
