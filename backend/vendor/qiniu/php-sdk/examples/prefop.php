<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Processing\PersistentFop;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$auth = new Auth($accessKey, $secretKey);

$config = new Config();
$config->useHTTPS=true;

$pfop = new PersistentFop($auth, $config);

$id = "z2.01z201c4oyre6q1hgy00murnel0002nh";

// 查询持久化处理的进度和状态
list($ret, $err) = $pfop->status($id);
echo "\n====> pfop avthumb status: \n";
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
