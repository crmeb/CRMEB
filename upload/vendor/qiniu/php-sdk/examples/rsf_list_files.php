<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;

// http://developer.qiniu.com/docs/v6/api/reference/rs/list.html#list-description

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');
$auth = new Auth($accessKey, $secretKey);
$bucketManager = new BucketManager($auth);


// 要列取文件的公共前缀
$prefix = '';

// 上次列举返回的位置标记，作为本次列举的起点信息。
$marker = '';

// 本次列举的条目数
$limit = 100;

$delimiter = '/';

// 列举文件
list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);
if ($err !== null) {
    echo "\n====> list file err: \n";
    var_dump($err);
} else {
    if (array_key_exists('marker', $ret)) {
        echo "Marker:" . $ret["marker"] . "\n";
    }
    echo "\nList Iterms====>\n";
    //var_dump($ret['items']);
}
