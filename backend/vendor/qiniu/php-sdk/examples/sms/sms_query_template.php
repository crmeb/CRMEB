<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Sms\Sms;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new Sms($auth);

// 列出摸板
// 参考文档：https://developer.qiniu.com/sms/api/5894/sms-api-query-template

// 审核状态, 取值范围为: passed (通过), rejected (未通过), reviewing (审核中)
$audit_status = 'rejected';
$page = 1; // 页码，默认为 1
$page_size = 30; // 分页大小，默认为 20

list($ret, $err) = $client->queryTemplate($audit_status, $page, $page_size);
echo "\n====> query template result： \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
