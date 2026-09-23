<?php
include_once(__DIR__ . '/../../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();
$client->config['cn-north-1']['config']['timeout'] = 30.0;

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("xx");
$client->setSecretKey("xx");

$contour = array(
    'Color' => 'color',
    'Size' => 0,
);
$params = array(
    "Contour" => $contour,
);
$params["ServiceId"] = "xx";
$params["Class"] = "class";
$params["Refine"] = true;
$params["StoreUri"] = "xx";
$params["OutFormat"] = "out format";
$params["TransBg"] = true;

$response = $client->getSegmentImage($params);
print_r($response);
