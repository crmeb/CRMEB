<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');

$auth = new Auth($accessKey, $secretKey);
$bucketManager = new BucketManager($auth);

$url = 'http://devtools.qiniu.com/qiniu.png';
$key = time() . '.png';

// 指定抓取的文件保存名称
list($ret, $err) = $bucketManager->fetch($url, $bucket, $key);
echo "=====> fetch $url to bucket: $bucket  key: $key\n";
if ($err !== null) {
    var_dump($err);
} else {
    print_r($ret);
}

// 不指定key时，以文件内容的hash作为文件名
$key = null;
list($ret, $err) = $bucketManager->fetch($url, $bucket, $key);
echo "=====> fetch $url to bucket: $bucket  key: $(etag)\n";
if ($err !== null) {
    var_dump($err);
} else {
    print_r($ret);
}
