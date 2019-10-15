<?php

require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;

$accessKey = 'Access_Key';
$secretKey = 'Secret_Key';
$auth = new Auth($accessKey, $secretKey);

// 要转码的文件所在的空间。
$bucket = 'Bucket_Name';

// 转码是使用的队列名称。 https://portal.qiniu.com/mps/pipeline
$pipeline = 'pipeline_name';

// 初始化
$pfop = new PersistentFop($auth, $bucket, $pipeline);
