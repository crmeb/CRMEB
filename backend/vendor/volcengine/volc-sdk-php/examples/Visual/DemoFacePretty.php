<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo FacePretty\n";

$body = [
    'image_base64' => '/9xxx',
    'do_risk' => TRUE,
    'multi_face' => '1',
    'beauty_level' => 1.0
];
$response = $client->FacePretty(['form_params' => $body]);
echo $response;