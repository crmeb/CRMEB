<?php
require('../../vendor/autoload.php');
use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    "subAccount"     => "smsAccount",
    "subAccountName" => "",
    "pageIndex"      => 1,
    "pageSize"       => 20,
];

$response = $client->getSubAccountList(['json' => $body]);
echo $response;
