<?php
include_once(__DIR__ . '/../../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$params = array();
$params["ServiceId"] = "service id";
$params["TTL"] = 0;

$response = $client->updateImageStorageTTL($params);
print_r($response);
