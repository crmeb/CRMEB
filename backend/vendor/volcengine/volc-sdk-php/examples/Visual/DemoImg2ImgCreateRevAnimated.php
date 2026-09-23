<?php

require('../../vendor/autoload.php');


use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Img2ImgCreateRevAnimated\n";

$body = [
    "req_key" => "img2img_rev_animated_style",
    # "binary_data_base64"=>[],
    "image_urls" => [
        "https://"],
    "prompt" => "",
    "strength" => 0.8,
    "seed" => -1,
    "scale" => 8,
    "ddim_steps" => 20,
    "lora_multipers" => ['shanzhagaoSomeSortOf_v1Epoch10' => 0.6, 'Moxin_10' => 0.5, 'Colorwater_v4' => 0.3],
    "clip_skip" => 1,
    "canny_weight" => 1,
    "sampler_name" => "DPM++ 2M Karras",
    "id_scale" => 0,
    "return_url" => true,
    "logo_info" => [
        "add_logo" => false,
        "position" => 0,
        "language" => 0,
        "opacity" => 0.3
    ]
];

$response = $client->Img2ImgCreateRevAnimated(['json' => $body]);
$response = str_replace('\u0026', '&', $response);

echo $response;
