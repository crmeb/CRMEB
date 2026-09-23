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
    'domain' => 'domain',
    'refer_link' => [
        'enabled' => true,
        'is_white_mode' => false,
        'values' => ['example.com'],
        'allow_empty_refer' => true,
    ],
];

$response = $client->updateRefer($query, $body);
print_r($response);
