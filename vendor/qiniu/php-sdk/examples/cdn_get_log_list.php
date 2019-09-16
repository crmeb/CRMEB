<?php

require_once __DIR__ . '/../autoload.php';

use \Qiniu\Cdn\CdnManager;

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Qiniu\Auth($accessKey, $secretKey);
$cdnManager = new CdnManager($auth);

$domains = array(
    "javasdk.qiniudn.com",
    "phpsdk.qiniudn.com"
);

$logDate = '2017-08-20';

//获取日志下载链接
//参考文档：http://developer.qiniu.com/article/fusion/api/log.html

list($logListData, $getLogErr) = $cdnManager->getCdnLogList($domains, $logDate);
if ($getLogErr != null) {
    var_dump($getLogErr);
} else {
    echo "get cdn log list success\n";
    print_r($logListData);
}
