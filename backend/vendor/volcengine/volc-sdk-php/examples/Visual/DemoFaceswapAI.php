<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo FaceswapAI\n";

$body = [
    "req_key" => "faceswap_ai",
    "image_urls"=> [
        "https://xxx",
        "https://xxx"
    ],
    "gpen"=>0.9,
    "skin"=>0.9,
    "do_risk"=>False,
    "tou_repair"=>1

];
$response = $client->FaceswapAI(['json' => $body]);
echo $response;

