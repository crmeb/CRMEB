<?php
require('../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$serviceID = "imagex service id";
$urls = ["image url 1"];

$resp = $client->updateImageUrls($serviceID, $urls);
var_dump($resp);
