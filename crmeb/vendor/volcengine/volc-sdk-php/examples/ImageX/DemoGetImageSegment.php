<?php
require('../../vendor/autoload.php');
use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("xx");
$client->setSecretKey("xx");

$Contour = array(
    'Color' => 'color',
    'Size' => 0,
);
$params = array(
    "Contour" => $Contour,
);
$params["ServiceId"] = "xx";
$params["Class"] = "class";
$params["Refine"] = true;
$params["StoreUri"] = "xx";
$params["OutFormat"] = "out format";
$params["TransBg"] = true;

$response = $client->getSegmentImage($params);
print_r($response);