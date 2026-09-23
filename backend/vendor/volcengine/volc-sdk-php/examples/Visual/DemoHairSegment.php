<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo HairSegment\n";

$body = [
    'image_url' => '',
//    'image_base64' => '',
//    'mode' => 0,
//    'refine_mask' => 0,
//    'flip_test' => 0
];
$response = $client->HairSegment(['form_params' => $body]);
echo $response;

