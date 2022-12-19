<?php
require('../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$config = [
    "query" => [],  // api query param
    "json" => [], // api json param
];

// ImageX api doc: https://www.volcengine.cn/docs/508/14106
$response = $client->requestImageX("Action", $config);
echo $response;
