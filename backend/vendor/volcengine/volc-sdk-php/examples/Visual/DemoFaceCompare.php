<?php
require('../../vendor/autoload.php');

use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');
// $client->setSessionToken('sts-token');


echo "\nDemo FaceCompare\n";

$body = [
    "req_key" => "face_compare",
    "image_urls" => [
        "http://xxx",
        "http://xxx"
    ]
];
$response = $client->FaceCompare(['json' => $body]);
echo $response;