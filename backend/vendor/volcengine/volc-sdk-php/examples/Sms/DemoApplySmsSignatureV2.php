<?php

require('../../vendor/autoload.php');

use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$uploadFileSignAuthFileList = [[
    "fileType" => 1,
    "fileContent" => "fileContent",
    "fileUrl" => "fileUrl",
    "fileSuffix" => "fileSuffix",
]];

$appIcpFileList = [[
    "fileType" => 1,
    "fileContent" => "fileContent",
    "fileUrl" => "fileUrl",
    "fileSuffix" => "fileSuffix",
]];

$trademarkFileList = [[
    "fileType" => 1,
    "fileContent" => "fileContent",
    "fileUrl" => "fileUrl",
    "fileSuffix" => "fileSuffix",
]];

$body = [
    "subAccount" => "your subAccountName",
    "source" => 1,
    "domain" => "your domain",
    "content" => "your content",
    "desc" => "your desc",
    "purpose" => 1,
    "signatureIdentificationID" => 123,
    "uploadFileList" => $uploadFileSignAuthFileList,
    "appIcp" => [
        "appIcpFilling" => "appIcpFilling",
        "appIcpFileList" => $appIcpFileList,
    ],
    "trademark" => [
        "trademarkCn" => "trademarkCn",
        "trademarkEn" => "trademarkEn",
        "trademarkNumber" => "trademarkNumber",
        "trademarkFileList" => $trademarkFileList,
    ],
];

$response = $client->applySmsSignatureV2(['json' => $body]);
echo $response;