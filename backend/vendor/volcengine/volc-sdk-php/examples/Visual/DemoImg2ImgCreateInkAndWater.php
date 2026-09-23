<?php

require('../../vendor/autoload.php');


use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Img2ImgCreateInkAndWater\n";

$body = [
    "req_key" => "img2img_water_ink_style",
    # "binary_data_base64"=>[],
    "image_urls" => [
        "https://"],
    "prompt" => "",
    "sub_prompts" => [""],
    "strength" => 0.6,
    "seed" => -1,
    "scale" => 8,
    "ddim_steps" => 20,
    "lora_map" => ['Cateye_AT45' => ['strength_model' => 0.2, 'strength_clip' => 0.2],
        'CATSEYEcp001' => ['strength_model' => 0.2, 'strength_clip' => 0.2]],
    "clip_skip" => 1,
    "controlnet_weight" => 1,
    "sampler_name" => "dpmpp_2m",
    "scheduler" => "karras",
    "long_resolution" => 832,
    "cn_mode" => 0,
    "id_weight" => 1.0,
    "apply_id_layer" => "2,3,4,5,6,7,8,9,10,11,12",
    "tagger_settings" => ["switch" => false],
    "vae_choice" => 1,
    "return_url" => true,
    "logo_info" => [
        "add_logo" => false,
        "position" => 0,
        "language" => 0,
        "opacity" => 0.3
    ]
];

$response = $client->Img2ImgCreateInkAndWater(['json' => $body]);
$response = str_replace('\u0026', '&', $response);

echo $response;
