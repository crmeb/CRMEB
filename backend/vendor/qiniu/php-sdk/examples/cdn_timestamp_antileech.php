<?php

require_once __DIR__ . '/../autoload.php';

use Qiniu\Cdn\CdnManager;

// 创建带时间戳防盗链的 URL
// 参考文档：https://developer.qiniu.com/fusion/manual/3841/timestamp-hotlinking-prevention-fusion

$encryptKey = 'your_domain_timestamp_antileech_encryptkey';

// 一定要带访问协议，也就是 http:// 或者 https://
$url1 = 'http://phpsdk.qiniuts.com/24.jpg?imageInfo';
$url2 = 'http://phpsdk.qiniuts.com/24.jpg';

// 有效期时间（单位秒）
$durationInSeconds = 3600;

$signedUrl = CdnManager::createTimestampAntiLeechUrl($url1, $encryptKey, $durationInSeconds);
print($signedUrl);
