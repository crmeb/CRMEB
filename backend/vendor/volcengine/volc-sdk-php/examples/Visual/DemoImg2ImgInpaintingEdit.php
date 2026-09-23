<?php
require('../../vendor/autoload.php');

use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Img2ImgInpaintingEdit\n";

$body = [

    "custom_prompt" => "一只小狗", // 写入Prompt，AIGC生成取代的内容
    "req_key" => "i2i_inpainting_edit",
    "scale" => 5,
    "seed" => -1,
    "steps" => 25,
    "binary_data_base64" => [
        ""
    ]

];
$response = $client->Img2ImgInpaintingEdit(['json' => $body]);
echo $response;

