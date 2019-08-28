<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$config = new \Qiniu\Config();
$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

$bucket = 'xxxx';

list($Info, $err) = $bucketManager->getBucketLifecycleRules($bucket);
if ($err) {
    print_r($err);
} else {
    print_r($Info);
}
