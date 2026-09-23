<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');

$auth = new Auth($accessKey, $secretKey);
$bucketManager = new BucketManager($auth);

// 第三方资源抓取
// 参考文档：https://developer.qiniu.com/kodo/api/1263/fetch

// 需要抓取的文件 URL
$url = 'http://devtools.qiniu.com/qiniu.png';

//---------------------------------------- demo1 ----------------------------------------
// 指定抓取的文件保存到七牛云空间中的名称

$key = time() . '.png';
list($ret, $err) = $bucketManager->fetch($url, $bucket, $key);
echo "=====> fetch $url to bucket: $bucket  key: $key\n";
if ($err !== null) {
    var_dump($err);
} else {
    print_r($ret);
}

//---------------------------------------- demo2 ----------------------------------------
// 不指定 key 时，以文件内容的 hash 作为文件名

$key = null;
list($ret, $err) = $bucketManager->fetch($url, $bucket, $key);
echo "=====> fetch $url to bucket: $bucket  key: $(etag)\n";
if ($err !== null) {
    var_dump($err);
} else {
    print_r($ret);
}
