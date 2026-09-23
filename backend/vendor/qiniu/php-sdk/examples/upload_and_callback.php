<?php
require_once __DIR__ . '/../autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');
$auth = new Auth($accessKey, $secretKey);

// 上传完成后通知到你的业务服务器（需要可以公网访问，并能够相应 200 OK）
// 上传策略参数：https://developer.qiniu.com/kodo/manual/1206/put-policy

$policy = array(
    'callbackUrl' => 'http://your.domain.com/upload_verify_callback.php',
    'callbackBody' => 'filename=$(fname)&filesize=$(fsize)'
);
$uptoken = $auth->uploadToken($bucket, null, 3600, $policy);

// 上传文件的本地路径
$filePath = './php-logo.png';

$uploadMgr = new UploadManager();
list($ret, $err) = $uploadMgr->putFile($uptoken, null, $filePath);
echo "\n====> putFile result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
