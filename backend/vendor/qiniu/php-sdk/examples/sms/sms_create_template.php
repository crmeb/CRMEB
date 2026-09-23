<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Sms\Sms;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new Sms($auth);

// 创建模板
// 参考文档：https://developer.qiniu.com/sms/api/5893/sms-api-create-template
// 模板审核标准：https://developer.qiniu.com/sms/manual/5814/template-specification

$name = 'xxxxxx'; // 模板名称
$template = '您的验证码为: ${code}，如非本人操作，请忽略本短信'; // 模板内容 可设置自定义变量，发送短信时候使用

// notification (通知类短信), verification (验证码短信), marketing (营销类短信), voice(语音类短信)
$type = 'verification';
$description = '发送验证码使用'; // 申请理由简述
$signature_id = 'xxxxxx'; // 已经审核通过的签名

list($ret, $err)  = $client->createTemplate($name, $template, $type, $description, $signature_id);

echo "\n====> create signature result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
