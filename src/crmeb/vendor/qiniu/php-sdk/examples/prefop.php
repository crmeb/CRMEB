<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;

$accessKey = 'Access_Key';
$secretKey = 'Secret_Key';
$auth = new Auth($accessKey, $secretKey);

//要持久化处理的文件所在的空间和文件名。
$bucket = 'Bucket_Name';

//持久化处理使用的队列名称。 https://portal.qiniu.com/mps/pipeline
$pipeline = 'pipeline_name';

//持久化处理完成后通知到你的业务服务器。
$notifyUrl = 'http://375dec79.ngrok.com/notify.php';
$pfop = new PersistentFop($auth, $bucket, $pipeline, $notifyUrl);

$id = "z2.5955c739e3d0041bf80c9baa";
//查询持久化处理的进度和状态
list($ret, $err) = $pfop->status($id);
echo "\n====> pfop avthumb status: \n";
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
