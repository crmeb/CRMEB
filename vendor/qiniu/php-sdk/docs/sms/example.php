<?php
require_once("../../autoload.php");

use \Qiniu\Auth;

$ak="xxxx";
$sk="xxxx";

$auth = new Auth($ak, $sk);
$client = new Qiniu\Sms\Sms($auth);

//发送信息模块
$template_id="1131792074274775040";
$mobiles=array("18011111111","18011111111");
$code = array('code' => 'code' );
try {
    //发送短信
    $resp = $client->sendMessage($template_id, $mobiles, $code);
    print_r($resp);
} catch (\Exception $e) {
    echo "Error:", $e, "\n";
}exit;
//模板模块
$name="tstest001";
$template="tesy001 ${code}";
$type="notification";
$description="tstest001";
$signature_id="1131464448834277376";
$id="1131810682442883072";

try {
    //创建模板
    $resp = $client->createTemplate($name, $template, $type, $description, $signature_id);
    print_r($resp);
    //查询模板
    $resp = $client->queryTemplate();
    print_r($resp);
    //修改模板
    $resp = $client->updateTemplate($id, $name, $template, $description, $signature_id);
    print_r($resp);
    //删除模板
    $resp = $client->deleteTemplate($id);
    print_r($resp);
} catch (\Exception $e) {
    echo "Error:", $e, "\n";
}
//签名模块
$signature = 'lfxlive2';
$source = 'enterprises_and_institutions';
$pic="/Users/Desktop/sss.jpg";
$audit_status="passed";
$page=1;
$page_size=1;
$id="1131464448834277376";

try {
    //创建签名
    $resp = $client->createSignature($signature, $source, $pic);
    print_r($resp);
     //查询签名
    $resp = $client->checkSignature($audit_status);
    //修改签名
    $resp = $client->updateSignature($id, $signature, $source, $pic);
    print_r($resp);
    //删除ID
    $resp = $client->deleteSignature($id);
    print_r($resp);
} catch (\Exception $e) {
    echo "Error:", $e, "\n";
}
