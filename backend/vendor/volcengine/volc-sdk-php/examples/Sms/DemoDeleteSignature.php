<?php
require('../../vendor/autoload.php');
use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    "subAccount" => "subAccount",
    "id"  => "123456",
    "isOrder"  => true,
];

$response = $client->deleteSignature(['json' => $body]);
echo $response;
