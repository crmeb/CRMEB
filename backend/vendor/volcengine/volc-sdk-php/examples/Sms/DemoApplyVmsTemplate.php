<?php
require('../../vendor/autoload.php');
use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$contents = [
    [
        "sourceType" => "text/string",
        "content" => "",
    ],
];

$body = [
    "subAccount"  => "subAccount",
    "channelType" => "",
    "name"        => "",
    "theme"       => "",
    "signature"   => "your signature",
    "contents"    => "",
    "caller"      => "",
];

$response = $client->applyVmsTemplate(['json' => $body]);
echo $response;
