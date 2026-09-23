<?php
require('../../vendor/autoload.php');
use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    "SmsAccount"  => "smsAccount",
    "Sign"        => "your sign",
    "TemplateID"  => "ST_xxx",
    "PhoneNumber" => "188xxxxxxx",
    "Scene"       => "myscene",
    "ExpireTime"  => 1800,
    "TryCount"    => 3,
    "CodeType"    => 6,
    "Tag"         => "tag",
];

$response = $client->sendSmsVerifyCode(['json' => $body]);
echo $response;
