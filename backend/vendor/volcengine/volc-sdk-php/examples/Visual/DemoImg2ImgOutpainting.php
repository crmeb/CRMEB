<?php
require('../../vendor/autoload.php');

use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo Img2ImgOutpainting\n";

$body = [

    "req_key" => "i2i_outpainting",
    "prompt" => "蓝色的海洋",
    "binary_data_base64" => [
        ""
    ],
    "scale" => 7,
    "seed" => -1,
    "steps" => 30,
    "strength" => 0.8,
    "top" => 0.1,
    "bottom" => 0.1,
    "left" => 1,
    "right" => 1,
    "max_height" => 1920,
    "max_width" => 1920

];
$response = $client->Img2ImgOutpainting(['json' => $body]);
echo $response;

