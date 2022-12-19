<?php

use Volc\Service\Cdn;

require('../../vendor/autoload.php');
require_once "init.php";

$client = Cdn::getInstance();

$config = new Config();
$client->setAccessKey($config->ak);
$client->setSecretKey($config->sk);

$body = [
    'Domain' => $config->exampleDomain,
    'Origin' => [
        [
            'OriginAction' => [
                'OriginLines' => [
                    [
                        'OriginType' => 'primary',
                        'InstanceType' => 'ip',
                        'Address' => '1.1.1.1',
                        'HttpPort' => '80',
                        'HttpsPort' => '443',
                        'Weight' => '100'
                    ]
                ]
            ]
        ]
    ],
    'OriginProtocol' => 'HTTP'
];

$response = $client->updateCdnConfig($body);
var_dump($response);
