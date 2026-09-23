<?php
require('../../vendor/autoload.php');
use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");


$body = [
    "subAccount"  => "smsAccount",
    "templateId"  => "template_id",
    "name"        => "",
    "area"        => "all",
    "channelType" => "",
    "content"     => "",
    "pageIndex"   => 1,
    "pageSize"    => 10,
];


$response = $client->getSmsTemplateAndOrderList(['json' => $body]);
echo $response;
