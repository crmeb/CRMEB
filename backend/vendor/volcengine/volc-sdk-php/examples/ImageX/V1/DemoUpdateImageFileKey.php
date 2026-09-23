<?php
include_once(__DIR__ . '/../../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$serviceId = '';

$req = [
    'OriKey' => '',
    'DstKey' => '',
];

$response = $client->updateImageFileKey($serviceId, $req);
print_r($response);
