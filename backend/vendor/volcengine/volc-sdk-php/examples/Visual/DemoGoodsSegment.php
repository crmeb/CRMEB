<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo GoodsSegment\n";

$body = [
    'image_url' => '',
    'method' => 'human'
//    'image_base64' => ''
];
$response = $client->GoodsSegment(['form_params' => $body]);
echo $response;

