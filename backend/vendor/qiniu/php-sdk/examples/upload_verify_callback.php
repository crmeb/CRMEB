<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');

$auth = new Auth($accessKey, $secretKey);

// 获取回调的 body 信息
$callbackBody = file_get_contents('php://input');

// 回调的 contentType
$contentType = 'application/x-www-form-urlencoded';

// 回调的签名信息，可以验证该回调是否来自七牛
$authorization = $_SERVER['HTTP_AUTHORIZATION'];

// 七牛回调的 url，具体可以参考：https://developer.qiniu.com/kodo/manual/1206/put-policy
$url = 'http://172.30.251.210/upload_verify_callback.php';

//参考文档：https://developer.qiniu.com/kodo/manual/1653/callback
$isQiniuCallback = $auth->verifyCallback($contentType, $authorization, $url, $callbackBody);

if ($isQiniuCallback) {
    $resp = array('ret' => 'success');
} else {
    $resp = array('ret' => 'failed');
}

echo json_encode($resp);
