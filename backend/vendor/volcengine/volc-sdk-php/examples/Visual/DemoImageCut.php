<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo ImageCut\n";

$body = [
    'image_url' => '',
//    'image_base64' => ''
    'width' => 100,
    'height' => 120,
    'cut_method' => 'gauss_padding_reserve_score'
];
$response = $client->ImageCut(['form_params' => $body]);
echo $response;

