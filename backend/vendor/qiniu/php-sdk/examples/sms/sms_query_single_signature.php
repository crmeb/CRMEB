<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Sms\Sms;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new Sms($auth);

// 查询单个签名
// 参考文档：https://developer.qiniu.com/sms/api/5970/query-a-single-signature

// 签名 ID
$signature_id = 'xxxxxx';

list($ret, $err) = $client->checkSingleSignature($signature_id);
echo "\n====> query single signature result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
