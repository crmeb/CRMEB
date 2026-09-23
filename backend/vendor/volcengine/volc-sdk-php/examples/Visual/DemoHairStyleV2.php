<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo HairStyleV2\n";

$body = [
    'req_key' => 'hair_style',
    'image_urls' => [''],
//    'image_base64' => [''],
    'hair_type' => 801
];
$response = $client->HairStyleV2(['json' => $body]);
echo $response;

