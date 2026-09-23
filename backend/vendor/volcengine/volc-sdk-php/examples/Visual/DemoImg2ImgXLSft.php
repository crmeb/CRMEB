<?php

require('../../vendor/autoload.php');


use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Img2ImgXLSft\n";

$body = [
    "req_key" => "i2i_xl_sft",
//    "binary_data_base64":[],
    "image_urls" => [
        "https://xxx"
    ],
    "prompt" => "美女",
    "seed" => -1,
    "ddim_step" => 20,
    "scale" => 7.0,
    "controlnet_args" => [
        [
            "type" => "canny",
            "strength" => 0.4,
            "binary_data_index" => 0
        ]
    ],
    "style_reference_args" => [
        "id_weight" => 0.2,
        "style_weight" => 0.0,
        "binary_data_index" => 0
    ],
    "return_url" => True,
    "logo_info" => [
        "add_logo" => True,
        "position" => 2,
        "language" => 0,
        "opacity" => 1
    ]
];

$response = $client->Img2ImgXLSft(['json' => $body]);
// 将返回的链接中的 & 替换为 \u0026
$response = str_replace('\u0026', '&', $response);

echo $response;
