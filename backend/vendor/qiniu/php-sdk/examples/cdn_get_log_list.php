<?php

require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Cdn\CdnManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$cdnManager = new CdnManager($auth);

// 获取日志下载链接
// 参考文档：https://developer.qiniu.com/fusion/api/1226/download-the-log

$domains = array(
    "javasdk.qiniudn.com",
    "phpsdk.qiniudn.com"
);

$logDate = '2020-08-05';

list($logListData, $getLogErr) = $cdnManager->getCdnLogList($domains, $logDate);
if ($getLogErr != null) {
    var_dump($getLogErr);
} else {
    echo "get cdn log list success\n";
    print_r($logListData);
}
