<?php
require('../../vendor/autoload.php');
use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("xx");
$client->setSecretKey("xx");

$params = array();
$params["ServiceId"] = "xx";
$params["StoreUri"] = "xx";
$params["Scene"] = "license";

$response = $client->getImageOCR($params);
print_r($response);