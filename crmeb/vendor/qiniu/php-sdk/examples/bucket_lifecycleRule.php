<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$config = new \Qiniu\Config();
$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

$bucket = 'xxxx';
$name = 'demo';
$prefix = 'test';
$delete_after_days = 80;
$to_line_after_days =70;

list($Info, $err) = $bucketManager->bucketLifecycleRule(
    $bucket,
    $name,
    $prefix,
    $delete_after_days,
    $to_line_after_days
);
if ($err) {
    print_r($err);
} else {
    print_r($Info);
}
