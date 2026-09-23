<?php

require('../../vendor/autoload.php');


use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Img2ImgAnimeAcceleratedMaintainID\n";

$body = [
    "req_key" => "img2img_anime_accelerated_maintain_id",
    "positive_prompt" => "在工厂打螺丝",
    "return_url" => true,
    "image_urls" => [

    ],
    # "binary_data_base64"=> [],
    "hyper_switch" => true,
    # "seed"=> -1,
    # "step"=> 18,
    # "cfg"=> 4.5,
    "face_image" => "uri://binary_data?index=0",
    # "style_image"=> "uri://binary_data?index=1",
    "face_switch" => true,
    "facestyle_switch" => true,
    # "style_switch"=> false,
    "width" => 1000,
    "height" => 1000,
    "logo_info" => [
        "add_logo" => true,
        "position" => 2,
        "language" => 0,
        "opacity" => 1
    ]
];

$response = $client->Img2ImgAnimeAcceleratedMaintainID(['json' => $body]);
$response = str_replace('\u0026', '&', $response);

echo $response;
