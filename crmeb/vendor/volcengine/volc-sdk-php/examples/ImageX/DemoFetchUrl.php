<?php
require('../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$config = [
    "json" => [
        'Url' => 'image url',
        'ServiceId' => 'imagex service id',
    ],
];

$response = $client->requestImageX("FetchImageUrl", $config);
echo $response;