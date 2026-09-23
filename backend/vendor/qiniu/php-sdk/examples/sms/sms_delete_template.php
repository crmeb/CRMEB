<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Sms\Sms;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new Sms($auth);

// 删除模板
// 参考文档：https://developer.qiniu.com/sms/api/5896/sms-api-delete-template

// 模板 ID
$template_id = 'xxxxxx';

list($ret, $err) = $client->deleteTemplate($template_id);
if ($err !== null) {
    var_dump($err);
} else {
    echo "\n====> Delete Template $template_id Successfully\n";
}
