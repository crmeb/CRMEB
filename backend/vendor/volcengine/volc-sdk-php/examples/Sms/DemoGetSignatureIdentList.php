<?php

require('../../vendor/autoload.php');

use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    "ids" => [1, 2, 3],
    "pageIndex" => 1,
    "pageSize" => 5,
];

$response = $client->getSignatureIdentList(['json' => $body]);
echo $response;