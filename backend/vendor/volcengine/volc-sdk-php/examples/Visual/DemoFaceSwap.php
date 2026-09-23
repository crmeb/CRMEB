<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo FaceSwap\n";
//2.0
//$response = $client->FaceSwap(['form_params' => [
//    'action_id' => 'faceswap',
//    'image_url' => '',
//    'template_url' => '',
////    'image_base64' => '',
////    'template_base64' => '',
//    'version' => '2.0',
////    'do_risk' => false,
////    'source_similarity' => "0"
//]]);
//echo $response;


//2.1
$array = [["image_url" => "", "location" => 1, "template_location" => 1],
    ["image_url" => "", "location" => 1, "template_location" => 2]];
$json = json_encode($array, JSON_UNESCAPED_UNICODE);
$response = $client->FaceSwap(['form_params' => [
    'action_id' => 'faceswap',
    'template_url' => '',
//    'template_base64' => '',
    'version' => '2.1',
    'type' => 'l2r',
//    'do_risk' => false,
//    'source_similarity' => "1",
    'merge_infos' => $json
]]);
echo $response;

