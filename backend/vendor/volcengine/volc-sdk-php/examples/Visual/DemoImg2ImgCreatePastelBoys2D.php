<?php
require('../../vendor/autoload.php');

use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Img2ImgCreatePastelBoys2D\n";

$body = [
    "req_key" => "img2img_pastel_boys_style",
    # "binary_data_base64"=> [],
    "image_urls" => [
        "https://"],
    "prompt" => "good looking,(((pure white statue))),(((only white color in picture))), (((white plaster figure))), (Renaissance), ancient Greek mythological statue, monochromatic realism style, rococo, (((plaster texture))), ((white hair)), 8k, best quality, masterpiece, depth, face light,",
    "strength" => 0.6,
    "seed" => -1,
    "scale" => 8,
    "ddim_steps" => 20,
    "lora_multipers" => [
        "shanzhagaoSomeSortOf_v1Epoch10" => 0.4,
        "Colorwater_v4" => 0.2,
        "Moxin_Shukezouma11" => 0.2
    ],
    "clip_skip" => 1,
    "canny_weight" => 0.8,
    "i2i_keep_texture" => 1,
    "long_resolution" => 704,
    "id_scale" => 0,
    "return_url" => true,
    "logo_info" => [
        "add_logo" => false,
        "position" => 0,
        "language" => 0,
        "opacity" => 0.3
    ]
];


$response = $client->Img2ImgCreatePastelBoys2D(['json' => $body]);
$response = str_replace('\u0026', '&', $response);
echo $response;

