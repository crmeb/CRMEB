<?php

require('../../vendor/autoload.php');


use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo EntitySegment\n";

$body = [
    "image_urls" => [
        "https://"],
    "return_url" => True,
    "return_format" => 1,
    "refine_mask" => 0,
    "max_entity" => 20,
    "req_key" => "entity_seg"
];

$response = $client->EntitySegment(['json' => $body]);
$response = str_replace('\u0026', '&', $response);

echo $response;
