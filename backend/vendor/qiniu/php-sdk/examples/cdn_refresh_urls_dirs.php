<?php

require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Cdn\CdnManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);

//---------------------------------------- demo1 ----------------------------------------
// 刷新文件和目录
// 文件列表一次最多提交 60 个，目录一次最多提交 10 个
// 参考文档：https://developer.qiniu.com/fusion/api/1229/cache-refresh

$urls = array(
    "http://phpsdk.qiniudn.com/qiniu.jpg",
    "http://phpsdk.qiniudn.com/qiniu2.jpg",
);

$dirs = array(
    "http://phpsdk.qiniudn.com/test/"
);

$cdnManager = new CdnManager($auth);

// 如果刷新返回 400038 报错，则需要联系七牛技术支持开通刷新目录权限，参考：https://developer.qiniu.com/fusion/api/1229/cache-refresh，
list($refreshResult, $refreshErr) = $cdnManager->refreshUrlsAndDirs($urls, $dirs);
if ($refreshErr != null) {
    var_dump($refreshErr);
} else {
    echo "refresh request sent\n";
    print_r($refreshResult);
}

//---------------------------------------- demo2 ----------------------------------------
// 刷新文件

list($refreshResult, $refreshErr) = $cdnManager->refreshUrls($urls);
if ($refreshErr != null) {
    var_dump($refreshErr);
} else {
    echo "refresh urls request sent\n";
    print_r($refreshResult);
}

//---------------------------------------- demo3 ----------------------------------------
// 刷新目录

list($refreshResult, $refreshErr) = $cdnManager->refreshDirs($dirs);
if ($refreshErr != null) {
    var_dump($refreshErr);
} else {
    echo "refresh dirs request sent\n";
    print_r($refreshResult);
}
