<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo Img2Video3D\n";

$body = [
    'req_key' => 'img2video3d',
    'image_urls' => [''],
//    'binary_data_base64' => [''],
    'render_spec' => [
        'mode' => 2,
        'long_side' => 960,
        'frame_num' => 90,
        'fps' => 30,
        'use_flow' => -1,
        'speed_shift' => [
            0,
            1,
            0.5,
            4,
            0.5,
            4,
            1,
            1
        ]
    ]

];
$response = $client->Img2Video3D(['json' => $body]);
echo $response;

