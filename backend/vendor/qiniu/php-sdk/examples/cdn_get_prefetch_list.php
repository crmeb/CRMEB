<?php

require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Cdn\CdnManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$cdnManager = new CdnManager($auth);

// 查询 CDN 预取记录
// 参考文档：https://developer.qiniu.com/fusion/api/1227/file-prefetching#4

$requestId = null;     // 指定要查询记录所在的刷新请求id

// 要查询的url列表
$urls = array(
    "http://sdk.peterpy.cn/sdktest.png",
    "http://sdk.peterpy.cn/qiniulogo.png"
);

$state = 'success';        // 指定要查询记录的状态，取值 processing／success／failure
$pageNo = 0;               // 要求返回的页号，默认为0
$pageSize = 100;           // 要求返回的页长度，默认为100
$startTime = '2020-09-11 12:00:00';     // 指定查询的开始日期，格式2006-01-01
$endTime = '2020-09-20 21:00:00';       // 指定查询的结束日期，格式2006-01-01

list($ret, $err) = $cdnManager->getCdnPrefetchList(
    $requestId,
    $urls,
    $state,
    $pageNo,
    $pageSize,
    $startTime,
    $endTime
);
echo "\n====> query prefetch list: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
