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

// 资源列举 V2
// https://developer.qiniu.com/kodo/api/4539/v2-list

// 要列取文件的公共前缀
$prefix = '';

// 上次列举返回的位置标记，作为本次列举的起点信息。
$marker = '';

// 本次列举的条目数，范围为 1-1000
$limit = 1000;

$delimiter = '';

list($ret, $err) = $bucketManager->listFilesv2($bucket, $prefix, $marker, $limit, $delimiter, true);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
