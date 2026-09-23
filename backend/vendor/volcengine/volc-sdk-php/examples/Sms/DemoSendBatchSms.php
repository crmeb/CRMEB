<?php
require('../../vendor/autoload.php');
use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$template = [
    "code" => "123456",
];

$messages = [
    [
        "TemplateParam" => json_encode($template),
        "PhoneNumber"   => "188xxxxxxxx",
    ],
];

$body = [
    "SmsAccount" => "account",
    "Sign"       => "your sign",
    "TemplateID" => "ST_710f2b42",
    "Tag"        => "xixi",
    "Messages"   => $messages,
];

$response = $client->sendBatchSms(['json' => $body]);
echo $response;
