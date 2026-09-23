<?php
require('../../vendor/autoload.php');
use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('your region');// 不传默认cn-north-1，可选ap-singapore-1 新加坡
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

// template
$template = [
    'code' => 1111,
];

$body = [
    'SmsAccount' => "your sms account",
    'Sign' => "sign",
    "TemplateID"=>    "ST_xxx",
    "TemplateParam"=> json_encode($template),
    "PhoneNumbers"=>  "phone numbers",
    "Tag"=>           "tag",
];

$response = $client->sendSms(['json' => $body]);
echo $response;
