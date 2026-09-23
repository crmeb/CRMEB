<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo EmoticonEdit\n";

$body = [
    'image_url' => '',
//    'image_base64' => '',
//    'service_choice' => 0,
    'do_risk'=> false
];
$response = $client->EmoticonEdit(['form_params' => $body]);
echo $response;

