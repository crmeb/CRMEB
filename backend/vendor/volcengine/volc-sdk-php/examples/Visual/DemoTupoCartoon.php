<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo TupoCartoon\n";

$body = [
    'req_key' => 'tupo_cartoon',
    'image_urls' => [''],
//    'binary_data_base64' => [''],
];
$response = $client->TupoCartoon(['json' => $body]);
echo $response;

