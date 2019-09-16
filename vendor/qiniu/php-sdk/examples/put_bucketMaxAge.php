<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$config = new \Qiniu\Config();
$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

$bucket = 'xxxx';
$maxAge = 15768000;

list($Info, $err) = $bucketManager->putBucketMaxAge($bucket, $maxAge);
if ($err) {
    print_r($err);
} else {
    print_r($Info);
}
