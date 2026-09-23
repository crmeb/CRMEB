<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Sms\Sms;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new Sms($auth);

// 编辑签名
// 参考文档：https://developer.qiniu.com/sms/api/5890/sms-api-edit-signature
// 签名审核标准：https://developer.qiniu.com/sms/manual/5816/signature-specification

$id = 'xxxxxx'; // 签名 ID
$signature = 'xxxx'; // 签名
$source = 'website'; // 签名来源，申请签名时必须指定签名来源
$pics = '../php-logo.png'; // 签名对应的资质证明图片

list($ret, $err)  = $client->updateSignature($id, $signature, $source, $pics);

echo "\n====> edit signature result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    echo "\n====> Update Signature Successfully\n";
}
