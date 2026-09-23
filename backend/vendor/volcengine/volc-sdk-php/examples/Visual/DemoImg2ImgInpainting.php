<?php
require('../../vendor/autoload.php');

use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Img2ImgInpainting\n";

$body = [
    "req_key" => "i2i_inpainting",
    "binary_data_base64" => [
        ""
    ]

];
$response = $client->Img2ImgInpainting(['json' => $body]);
echo $response;

