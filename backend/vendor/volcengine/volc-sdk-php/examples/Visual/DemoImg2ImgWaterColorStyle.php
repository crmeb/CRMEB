<?php

require('../../vendor/autoload.php');


use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Img2ImgWaterColorStyle\n";

$body = [
    "req_key" => "img2img_water_paint_style",
    # "binary_data_base64"=>[],
    "image_urls" => [
        "https://"
    ],
    "return_url" => true,
];

$response = $client->Img2ImgWaterColorStyle(['json' => $body]);
$response = str_replace('\u0026', '&', $response);
echo $response;
