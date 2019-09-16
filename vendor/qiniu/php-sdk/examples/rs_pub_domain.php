<?php
require_once __DIR__ . '/../autoload.php';

use \Qiniu\Auth;

$accessKey = 'gwd_gV4gPKZZsmEOvAuNU1AcumicmuHooTfu64q5';
$secretKey = '9G4isTkVuj5ITPqH1ajhljJMTc2k4m-hZh5r5ZsK';
$bucket = 'file';

$domain = 'lfxfile.qiniuts.com';

$auth = new Auth($accessKey, $secretKey);
$config = new \Qiniu\Config();
$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

$err = $bucketManager->publishDomain($bucket, $domain);
if ($err) {
    print_r($err);
}
