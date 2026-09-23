<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Sms\Sms;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new Sms($auth);

// 查询发送记录
// 参考文档：https://developer.qiniu.com/sms/api/5852/query-send-sms

$job_id = 'xxxx'; // 发送任务返回的 id
$message_id = 'xxxx'; // 单条短信发送接口返回的 id
$mobile = 'xxxx'; // 接收短信的手机号码

// 短信的状态，sending: 发送中，success: 发送成功，failed: 发送失败，waiting: 等待发送
$status = 'success';
$template_id = 'xxxx'; // 模版 id

// marketing: 营销短信，notification: 通知短信，verification: 验证码类短信，voice: 语音短信
$type = 'notification';

$start = 1599976580; // 开始时间
$end = 1599977229; // 结束时间
$page = 1; // 页码，默认为 1
$page_size = 20; // 每页返回的数据条数，默认20，最大200

list($ret, $err) = $client->querySendSms(
    $job_id,
    $message_id,
    $mobile,
    $status,
    $template_id,
    $type,
    $start,
    $end,
    $page,
    $page_size
);
echo "\n====> query send sms result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
