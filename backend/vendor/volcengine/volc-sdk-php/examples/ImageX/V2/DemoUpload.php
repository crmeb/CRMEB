<?php
include_once(__DIR__ . '/../../../vendor/autoload.php');

use Volc\Service\ImageX\V2\Imagex;

$client = Imagex::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
// $client->setAccessKey("ak");
// $client->setSecretKey("sk");

$params = array();
$params["ServiceId"] = "imagex service id";
//$params["UploadHost"] = "upload host";
$filePaths = array("image path 1");

$response = $client->uploadImages($params, $filePaths);
print_r($response);
