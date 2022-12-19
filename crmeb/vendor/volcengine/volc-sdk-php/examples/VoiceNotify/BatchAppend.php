<?php

use Volc\Service\Notify;

require('../../../vendor/autoload.php');
require('../../src/Service/Notify.php');


$client = Notify::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'TaskOpenId' => '106d2984fbf0480480cbc8b98d609592',
    'PhoneList' => [
        [
            'Phone' => 'your phone',
        ]
    ],
];

$response = $client->BatchAppend($body);
echo $response;

