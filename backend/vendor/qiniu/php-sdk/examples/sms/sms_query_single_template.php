<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Sms\Sms;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new Sms($auth);

// 查询单个模板
// 参考文档：https://developer.qiniu.com/sms/api/5969/query-a-single-template

// 模板 ID
$template_id = 'xxxxxx';

list($ret, $err) = $client->querySingleTemplate($template_id);
echo "\n====> query single template result： \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
