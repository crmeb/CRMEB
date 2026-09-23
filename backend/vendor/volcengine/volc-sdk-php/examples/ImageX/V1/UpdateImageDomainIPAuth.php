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
    'ip_auth' => [
        'enabled' => true,
        'is_white_mode' => false,
        'values' => ['10.20.0.0/16'],
    ]
];

$response = $client->updateImageDomainIPAuth($query, $body);
print_r($response);
