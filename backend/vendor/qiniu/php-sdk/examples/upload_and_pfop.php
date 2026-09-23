<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');
$auth = new Auth($accessKey, $secretKey);

// 使用上传策略实现上传文件后，自动对该文件进行转码处理，并持久化生成一个新的文件存储到空间中

// 上传到七牛后保存的文件名
$key = 'php-logo.png';

// 要上传文件的本地路径
$filePath = './php-logo.png';

$uploadMgr = new UploadManager();

// 视频处理完毕后保存到空间中的名称
$saveasKey = 'php-logo-rotate.png';

// 处理参数
$pfop = "imageMogr2/rotate/90|saveas/" . \Qiniu\base64_urlSafeEncode("$bucket:$saveasKey");

// 转码完成后通知到你的业务服务器（需要可以公网访问，并能够相应 200 OK）
$notifyUrl = 'http://notify.fake.com';

// 用户默认没有私有队列，需要在这里创建然后填写 https://portal.qiniu.com/dora/media-gate/pipeline
$pipeline = 'sdktest';

// 上传策略参数：https://developer.qiniu.com/kodo/manual/1206/put-policy
$policy = array(
    'persistentOps' => $pfop,
    'persistentNotifyUrl' => $notifyUrl,
    'persistentPipeline' => $pipeline
);
$token = $auth->uploadToken($bucket, null, 3600, $policy);

list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
echo "\n====> putFile result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
