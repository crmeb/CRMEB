<?php

require_once __DIR__ . '/../autoload.php';

use \Qiniu\Cdn\CdnManager;

//创建时间戳防盗链
//时间戳防盗链密钥，后台获取
$encryptKey = 'your_domain_timestamp_antileech_encryptkey';

//带访问协议的域名
$url1 = 'http://phpsdk.qiniuts.com/24.jpg?avinfo';
$url2 = 'http://phpsdk.qiniuts.com/24.jpg';

//有效期时间（单位秒）
$durationInSeconds = 3600;

$signedUrl = CdnManager::createTimestampAntiLeechUrl($url1, $encryptKey, $durationInSeconds);
print($signedUrl);
