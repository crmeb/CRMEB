<?php
require('../../vendor/autoload.php');
use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    "subAccount" => "sunAccount",
    "content"    => "your content",
    "source"     => "",
    "domain"     => "",
    "desc"       => "",
    "uploadFileKey" => "",
];

$response = $client->applySmsSignature(['json' => $body]);
echo $response;
