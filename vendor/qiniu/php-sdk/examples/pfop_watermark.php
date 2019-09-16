<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;

//对已经上传到七牛的视频发起异步转码操作 

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');

$auth = new Auth($accessKey, $secretKey);

//要转码的文件所在的空间和文件名。
$key = 'qiniu.mp4';

//转码是使用的队列名称。 https://portal.qiniu.com/mps/pipeline
$pipeline = 'sdktest';

//转码完成后通知到你的业务服务器。
$notifyUrl = 'http://375dec79.ngrok.com/notify.php';
$force = false;

$config = new \Qiniu\Config();
//$config->useHTTPS=true;
$pfop = new PersistentFop($auth, $config);

//需要添加水印的图片UrlSafeBase64
//可以参考http://developer.qiniu.com/code/v6/api/dora-api/av/video-watermark.html
$base64URL = Qiniu\base64_urlSafeEncode('http://devtools.qiniu.com/qiniu.png');

//水印参数
$fops = "avthumb/mp4/s/640x360/vb/1.4m/image/" . $base64URL . "|saveas/"
    . \Qiniu\base64_urlSafeEncode($bucket . ":qiniu_wm.mp4");

list($id, $err) = $pfop->execute($bucket, $key, $fops, $pipeline, $notifyUrl, $force);
echo "\n====> pfop avthumb result: \n";
if ($err != null) {
    var_dump($err);
} else {
    echo "PersistentFop Id: $id\n";
}

//查询转码的进度和状态
list($ret, $err) = $pfop->status($id);
echo "\n====> pfop avthumb status: \n";
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
