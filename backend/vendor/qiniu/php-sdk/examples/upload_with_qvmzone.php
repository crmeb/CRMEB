<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Zone;
use Qiniu\Config;
use Qiniu\Storage\UploadManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);

// 上传到七牛存储后文件所在的空间和文件名
$bucket = getenv('QINIU_TEST_BUCKET');
$key = 'php-logo.png';

$uptoken = $auth->uploadToken($bucket);

// 上传文件的本地路径
$filePath = './php-logo.png';

// 七牛云主机QVM和七牛对象存储KODO内网上传，目前支持华东1区域(杭州)和华北2区域(北京)的云主机可以访问同区域的对象存储服务
// 参考文档：https://developer.qiniu.com/qvm/manual/4269/qvm-kodo

$zone = Zone::qvmZonez0(); // 华东：z0，华北：z1
$config = new Config($zone);
$config->useHTTPS = true;

// 指定 config
$uploadMgr = new UploadManager($config);

list($ret, $err) = $uploadMgr->putFile($uptoken, $key, $filePath);
echo "\n====> putFile result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
