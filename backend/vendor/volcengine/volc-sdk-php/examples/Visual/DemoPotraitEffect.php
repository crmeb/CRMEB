<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo PotraitEffect\n";
$body = [
    'image_url' => 'http://xxxx',
    // 'image_base64' => '/9xx',
    'type' => 'pixar',
    'return_type' => 0,
];
$response = $client->PotraitEffect(['form_params' => $body]);
echo $response;