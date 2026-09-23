<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Sms\Sms;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new Sms($auth);

// 发送短信
// 参考文档：https://developer.qiniu.com/sms/api/5897/sms-api-send-message

// 模板 ID
$template_id = 'xxxxxx';

// 接收短信的手机号码
$mobiles = array("15012345678", "15012345679");

// 自定义模板变量
$code = array('code' => 'xxxx');

list($ret, $err) = $client->sendMessage($template_id, $mobiles, $code);
if ($err !== null) {
    var_dump($err);
} else {
    echo "\n====> Send Message Successfully： \n";
    var_dump($ret);
}
