<?php
require_once __DIR__ . '/../autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

// use Qiniu\Config;
// use Qiniu\Zone;

// 指定zone上传
// $zone = Zone::qvmZonez0(); //华东QVM内网上传指定host
// $config = new Config($zone);
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');
$auth = new Auth($accessKey, $secretKey);
// 上传文件到七牛后， 七牛将文件名和文件大小回调给业务服务器.
// 可参考文档: http://developer.qiniu.com/docs/v6/api/reference/security/put-policy.html
$policy = array(
    'callbackUrl' => 'http://your.domain.com/upload_verify_callback.php',
    'callbackBody' => 'filename=$(fname)&filesize=$(fsize)'
);
$uptoken = $auth->uploadToken($bucket, null, 3600, $policy);
//上传文件的本地路径
$filePath = './php-logo.png';
//指定 config
// $uploadMgr = new UploadManager($config);
$uploadMgr = new UploadManager();
list($ret, $err) = $uploadMgr->putFile($uptoken, null, $filePath);
echo "\n====> putFile result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
