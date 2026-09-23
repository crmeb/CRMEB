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
    "PhoneNumber" =>  "+65xxxxxx",
    "Scene"       => "myscene",
    "Code"        => "123456",
];

$response = $client->checkSmsVerifyCode(['json' => $body]);
echo $response;
