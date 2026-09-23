<?php
require('../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey($ak);
$client->setSecretKey($sk);

$action = "MultiLanguageOCR";
$version = "2022-08-31";
$client->setAPI($action, $version);

echo "\nDemo OCR\n";
$response = $client->CallAPI($action, ['form_params' => ['image_base64' => $str]]);
echo $response;

