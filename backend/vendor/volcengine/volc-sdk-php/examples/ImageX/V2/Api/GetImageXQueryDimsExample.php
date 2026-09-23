<?php
include_once(__DIR__ . '/../../../../vendor/autoload.php');

use Volc\Service\ImageX\V2\Imagex;

$client = Imagex::getInstance();

// call below method if you dont set ak and sk in ~/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$query = [];

$response = $client->getImageXQueryDims($query);
print_r($response);
