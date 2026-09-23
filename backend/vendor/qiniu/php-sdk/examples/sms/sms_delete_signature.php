<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Sms\Sms;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new Sms($auth);

// 删除签名
// 参考文档：https://developer.qiniu.com/sms/api/5891/sms-api-delete-signature

// 签名 ID
$signature_id = 'xxxxxx';

list($ret, $err) = $client->deleteSignature($signature_id);
if ($err !== null) {
    var_dump($err);
} else {
    echo "\n====> Delete Signature $signature_id Successfully\n";
}
