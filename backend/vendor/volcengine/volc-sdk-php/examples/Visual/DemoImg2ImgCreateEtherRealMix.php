<?php
require('../../vendor/autoload.php');

use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Img2ImgCreateEtherRealMix\n";

$body = [
    "req_key" => "img2img_real_mix_style",
    # "binary_data_base64"=>[],
    "image_urls" => [
        "https:"
    ],
    "prompt" => "",
    "strength" => 0.6,
    "seed" => -1,
    "scale" => 8,
    "ddim_steps" => 20,
    "lora_multipers" => [
        "Colorwater_v4" => 0.2,
        "Moxin_Shukezouma11" => 0.2,
        "shanzhagaoSomeSortOf_v1Epoch10" => 0.4
    ],
    "clip_skip" => 1,
    "canny_weight" => 0.6,
    "sampler_name" => "DPM++ 2M Karras",
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


$response = $client->Img2ImgCreateEtherRealMix(['json' => $body]);
$response = str_replace('\u0026', '&', $response);
echo $response;

