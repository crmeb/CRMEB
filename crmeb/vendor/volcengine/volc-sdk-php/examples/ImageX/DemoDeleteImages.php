<?php
require('../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$serviceID = "imagex service id";
$uris = ["image uri 1", "image uri 2"];

$response = $client->deleteImages($serviceID, $uris);
echo $response;