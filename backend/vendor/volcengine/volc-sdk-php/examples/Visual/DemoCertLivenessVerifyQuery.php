<?php
require('../../vendor/autoload.php');

use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey('');
$client->setSecretKey('');

echo "\nDemo CertLivenessVerifyQuery\n";

// 特别注意：body完整入参请参考不同接口的请求body部分
$body = [

        "req_key"=> "cert_pro_liveness_verify_query",
        "byted_token"=> "",
        "omit_data"=> False,
        "omit_image_data"=> False,
        "omit_video_data"=> False
    ];

$response = $client->CertLivenessVerifyQuery(['json' => $body]);
echo $response;

