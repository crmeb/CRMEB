<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo EmotionPortrait\n";

$body = [
    'req_key' => 'emotion_portrait',
    'image_urls' => [''],
//    'binary_data_base64'=> [''],
    'target_emotion' => 'jiuwo',
];
$response = $client->EmotionPortrait(['json' => $body]);
echo $response;

