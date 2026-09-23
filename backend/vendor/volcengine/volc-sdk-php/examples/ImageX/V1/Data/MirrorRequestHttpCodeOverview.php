<?php
include_once(__DIR__ . '/../../../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$body = [
    'StartTime' => "2023-01-21T00:00:00+08:00",
    'EndTime' => "2023-01-28T00:00:00+08:00",
    'AggregateCode' => "false",
];

$response = $client->describeImageXMirrorRequestHttpCodeOverview($body);
print_r($response);
