<?php
require('../../vendor/autoload.php');
use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    "subAccount" => "smsAccount",
    "id"         => "idOfTemplateToDelete",
    "isOrder"    => true,
];

$response = $client->deleteSmsTemplate(['json' => $body]);
echo $response;
