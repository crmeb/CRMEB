<?php

use Volc\Service\Notify;

require('../../../vendor/autoload.php');
require('../../src/Service/Notify.php');

$client = Notify::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");


$body = [
    'List' => [
        [
            'Phone'=>'your phone',
            'Resource' => '9b39e17fb12444c78f20d6551469a6f0',
            'NumberPoolNo'=>'NP162213338604093530',
            'NumberType'=>0,
            'TriggerTime'=>'2022-05-18 19:18:00',
            'Type'=>0,
            'SingleOpenId'=>'9b39e17fb12444c78f20d6551469a6e3',
        ],
    ],
];

$response = $client->SingleBatchAppend($body);
echo $response;