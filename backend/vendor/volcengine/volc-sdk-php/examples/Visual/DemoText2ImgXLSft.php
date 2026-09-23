<?php

require('../../vendor/autoload.php');


use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Text2ImgXLSft\n";

$body = [
    "req_key" => "t2i_xl_sft",
    "prompt" => "少女，光影，瘦，白皙，干净，美丽",
    "width" => 1024,
    "height" => 1024,
    "seed" => -1,
    "ddim_steps" => 20,
    "scale" => 7.0,
    "return_url" => True,
    "logo_info" => [
        "add_logo" => True,
        "position" => 2,
        "language" => 0,
        "opacity" => 1
    ]

];

$response = $client->Text2ImgXLSft(['json' => $body]);
// 将返回的链接中的 & 替换为 \u0026
$response = str_replace('\u0026', '&', $response);

echo $response;
