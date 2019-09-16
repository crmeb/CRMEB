<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$config = new \Qiniu\Config();
$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

$bucket = 'xxxx';
$size = 99999;
$count = 99;

list($Info, $err) = $bucketManager->putBucketQuota($bucket, $size, $count);
if ($err) {
    print_r($err);
} else {
    print_r($Info);
}
