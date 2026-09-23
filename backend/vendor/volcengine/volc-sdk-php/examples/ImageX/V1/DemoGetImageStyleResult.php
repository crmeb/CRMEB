<?php
include_once(__DIR__ . '/../../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$query = [
    'ServiceId' => 'service id',
];

$body = [
    'StyleId' => 'style id',
    'Params' => [
        'key' => 'value',
    ],
    'OutputFormat' => 'jpeg',
    'OutputQuality' => 90,
];

$response = $client->getImageStyleResult($query, $body);
print_r($response);
