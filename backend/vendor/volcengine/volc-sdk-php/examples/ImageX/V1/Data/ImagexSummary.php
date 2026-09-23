<?php
include_once(__DIR__ . '/../../../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$query = [
    'Timestamp' => "2023-02-08T00:00:00+08:00",
    'ServiceIds' => 'service1,service2',
];

$response = $client->describeImageXSummary($query);
print_r($response);

