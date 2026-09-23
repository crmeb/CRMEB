<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Sms\Sms;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new Sms($auth);

// 编辑模板
// 参考文档：https://developer.qiniu.com/sms/api/5895/sms-api-edit-template
// 模板审核标准：https://developer.qiniu.com/sms/manual/5814/template-specification

$template_id = 'xxxxxx'; // 模板 ID
$name = '验证码'; // 模板名称
$template = '验证码为: ${code}，如非本人操作，请忽略本短信'; // 模板内容 可设置自定义变量，发送短信时候使用
$description = '发送验证码'; // 申请理由简述
$signature_id = 'xxxxxx'; // 已经审核通过的签名

list($ret, $err)  = $client->updateTemplate($template_id, $name, $template, $description, $signature_id);

echo "\n====> edit template result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    echo "\n====> Update Template Successfully\n";
}
