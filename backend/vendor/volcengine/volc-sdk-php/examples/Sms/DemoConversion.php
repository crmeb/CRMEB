<?php
require('../../vendor/autoload.php');
use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    "MessageIDs" => [
        "test_msg_id1",
        "test+msg_id2",
    ],
];

$response = $client->conversion(["json" => $body]);
echo $response;
