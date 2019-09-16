<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$config = new \Qiniu\Config();
$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

$bucket = 'xxxx';
$name = 'testdemo';
$prefix = 'test1';
$suffix = 'mp3';
$event = array("move","copy");
$callbackURL = 'http://www.qiniu.com';

list($Info, $err) = $bucketManager->putBucketEvent($bucket, $name, $prefix, $suffix, $event, $callbackURL);
if ($err) {
    print_r($err);
} else {
    print_r($Info);
}
