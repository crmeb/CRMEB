<?php
include_once(__DIR__ . '/../../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$req = [
    'Url' => 'image url',
    'ServiceId' => 'imagex service id',
//    'Async' => true,
];

$response = $client->fetchImageUrl($req);
print_r($response);

if (is_string($response) || !isset($response['TaskId'])) {
    exit();
}

$req2 = [
    'ServiceId' => $req['ServiceId'],
    'Id' => $response['TaskId'],
];

$response2 = $client->getUrlFetchTask($req2);
print_r($response2);
