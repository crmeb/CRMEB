<?php

require('../../vendor/autoload.php');

use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    "SubAccount" => "subAccount",
    "TemplateId" => "VT_xxxx",
];

$response = $client->getVmsTemplateStatus(['json' => $body]);
echo $response;
