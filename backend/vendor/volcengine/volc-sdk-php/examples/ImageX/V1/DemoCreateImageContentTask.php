<?php
include_once(__DIR__ . '/../../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$args = ['ServiceId' => "",
    'TaskType' => 'block_url',
    'Urls' => ['1'],
];

$resp = $client->createImageContentTask($args);
print_r($resp);
