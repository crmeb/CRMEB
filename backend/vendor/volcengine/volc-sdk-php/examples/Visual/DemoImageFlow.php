<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo ImageFlow\n";

$body = [
    'image_url' => '',
//    'image_base64' => '',
//    'motion_ratio' => 2
];
$response = $client->ImageFlow(['form_params' => $body]);
echo $response;

