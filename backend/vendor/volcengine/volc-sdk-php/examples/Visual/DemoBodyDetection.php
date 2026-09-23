<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo BodyDetection\n";

$body = [
    'req_key' => 'face_body_detection',
    'binary_data_base64' => [''],
];
$response = $client->BodyDetection(['json' => $body]);
echo $response;

