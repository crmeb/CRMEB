<?php
require_once __DIR__ . '/../autoload.php';

use \Qiniu\Auth;

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');

$key = "qiniu.mp4";
$auth = new Auth($accessKey, $secretKey);
$config = new \Qiniu\Config();
$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

$srcBucket = $bucket;
$destBucket = $bucket;
$srcKey = $key . "_copy";
$destKey = $key . "_move";
$err = $bucketManager->move($srcBucket, $srcKey, $destBucket, $destKey, true);
if ($err) {
    print_r($err);
}
