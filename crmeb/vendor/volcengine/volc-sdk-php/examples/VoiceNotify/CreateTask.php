<?php

use Volc\Service\Notify;

require('../../../vendor/autoload.php');
require('../../src/Service/Notify.php');

$client = Notify::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'Name' => '你好2222',
    'Resource' => '9b39e17fb12444c78f20d6551469a6f0',
    'Type' => 0,
    'NumberPoolNo' => 'NP162213338604093530',
    'Concurrency' => 2,
    'PhoneList' => [
        [
            'Phone' => 'your phone',
        ]
    ],
    'StartTime' => '2022-05-18 00:00:00',
    'EndTime' => '2022-05-15 18:00:00',
    'SelectNumberRule' => 5,
];

$response = $client->CreateTask($body);
echo $response;

