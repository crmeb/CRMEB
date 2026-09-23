<?php

require('../../vendor/autoload.php');


use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Img2ImgComicsStyle\n";

$body = [
    "req_key" => "img2img_comic_style",
//    "binary_data_base64":[],
    "image_urls" => [
        "https://xxx"
    ],
    "sub_req_key" => "img2img_comic_style_monet",
    "return_url" => True,
    "logo_info" => [
        "add_logo" => True,
        "position" => 2,
        "language" => 0,
        "opacity" => 1.0
    ]
];

$response = $client->Img2ImgComicsStyle(['json' => $body]);
// 将返回的链接中的 & 替换为 \u0026
$response = str_replace('\u0026', '&', $response);

echo $response;
