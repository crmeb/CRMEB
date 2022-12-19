<?php
require('../../vendor/autoload.php');

use Volc\Service\ImageX;

$xclient = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$xclient->setAccessKey("ak");
$xclient->setSecretKey("sk");

$serviceIDList = [];
$response = $xclient->getUploadAuth($serviceIDList, 3600);
echo json_encode($response);

echo "\n=======================\n";

$query = ['query' => ['ServiceId' => "imagex service id"]];
$response = $xclient->getUploadAuthToken($query);
echo $response;
