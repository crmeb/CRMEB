<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');

// 用户默认没有私有队列，需要在这里创建然后填写 https://portal.qiniu.com/dora/media-gate/pipeline
$pipeline = 'sdktest';

$auth = new Auth($accessKey, $secretKey);
$token = $auth->uploadToken($bucket);
$uploadMgr = new UploadManager();

//---------------------------------------- upload demo1 ----------------------------------------
// 上传字符串到七牛

list($ret, $err) = $uploadMgr->put($token, null, 'content string');
echo "\n====> put result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}


//---------------------------------------- upload demo2 ----------------------------------------
// 上传文件到七牛

$filePath = './php-logo.png';
$key = 'php-logo.png';
list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
echo "\n====> putFile result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}


//---------------------------------------- upload demo3 ----------------------------------------
// 上传文件到七牛后， 七牛将文件名和文件大小回调给业务服务器.
// 可参考文档: https://developer.qiniu.com/kodo/manual/1206/put-policy

$policy = array(
    'callbackUrl' => 'http://172.30.251.210/upload_verify_callback.php',
    'callbackBody' => 'filename=$(fname)&filesize=$(fsize)'
//  'callbackBodyType' => 'application/json',
//  'callbackBody' => '{"filename":$(fname), "filesize": $(fsize)}'  //设置application/json格式回调
);
$token = $auth->uploadToken($bucket, null, 3600, $policy);


list($ret, $err) = $uploadMgr->putFile($token, null, $key);
echo "\n====> putFile result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}


//---------------------------------------- upload demo4 ----------------------------------------
// 上传视频，上传完成后进行 m3u8 的转码， 并给视频打水印

$wmImg = Qiniu\base64_urlSafeEncode('http://devtools.qiniudn.com/qiniu.png');
$pfop = "avthumb/m3u8/wmImage/$wmImg";

// 转码完成后回调到业务服务器。（公网可以访问，并相应 200 OK）
$notifyUrl = 'http://notify.fake.com';

$policy = array(
    'persistentOps' => $pfop,
    'persistentNotifyUrl' => $notifyUrl,
    'persistentPipeline' => $pipeline
);
$token = $auth->uploadToken($bucket, null, 3600, $policy);
print($token);
list($ret, $err) = $uploadMgr->putFile($token, null, $key);
echo "\n====> putFile result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
