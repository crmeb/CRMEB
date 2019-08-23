<?php

require_once __DIR__ . '/../autoload.php';

use \Qiniu\Cdn\CdnManager;

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Qiniu\Auth($accessKey, $secretKey);
$cdnManager = new CdnManager($auth);

//获取流量和带宽数据
//参考文档：http://developer.qiniu.com/article/fusion/api/traffic-bandwidth.html

$domains = array(
    "javasdk.qiniudn.com",
    "phpsdk.qiniudn.com"
);

$startDate = "2017-08-20";
$endDate = "2017-08-21";

//5min or hour or day
$granularity = "day";

//获取带宽数据
list($bandwidthData, $getBandwidthErr) = $cdnManager->getBandwidthData(
    $domains,
    $startDate,
    $endDate,
    $granularity
);

if ($getBandwidthErr != null) {
    var_dump($getBandwidthErr);
} else {
    echo "get bandwidth data success\n";
    print_r($bandwidthData);
}
