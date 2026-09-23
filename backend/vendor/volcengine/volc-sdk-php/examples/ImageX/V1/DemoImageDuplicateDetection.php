<?php
include_once(__DIR__ . '/../../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$args = [
    'ServiceId' => "",
    'Async' => true,
    'Urls' => ["url 1", "url 2"]
];

$response = $client->getImageDuplicateDetection($args);
print_r($response);

if (!is_array($response) || isset($response['TeskId'])) {
    exit();
}

sleep(60);

$response2 = $client->getImageDuplicateDetectionTaskStatus($response);
print_r($response2);
