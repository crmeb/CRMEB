<?php

require('../../vendor/autoload.php');

use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    "subAccount" => "your subAccountName",
    "signatures" => ["sig1, sig2"],
    "id" => 1,  // 资质id
];

$response = $client->batchBindSignatureIdent(['json' => $body]);
echo $response;