<?php

require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use \Qiniu\Cdn\CdnManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$cdnManager = new CdnManager($auth);

// 获取流量和带宽数据
// 参考文档：https://developer.qiniu.com/fusion/api/1230/traffic-bandwidth

$domains = array(
    "javasdk.qiniudn.com",
    "phpsdk.qiniudn.com"
);

$startDate = "2020-08-03";
$endDate = "2020-08-05";

//5min or hour or day
$granularity = "day";

list($fluxData, $getFluxErr) = $cdnManager->getFluxData($domains, $startDate, $endDate, $granularity);
if ($getFluxErr != null) {
    var_dump($getFluxErr);
} else {
    echo "get flux data success\n";
    print_r($fluxData);
}
