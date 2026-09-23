<?php

use Volc\Service\Cdn;

require('../../vendor/autoload.php');
require_once "init.php";

$client = Cdn::getInstance();

$config = new Config();
$client->setAccessKey($config->ak);
$client->setSecretKey($config->sk);

$body = [
    'CertId' => 'cert-c195f679cecb4fc5yjt3dd8c54e6c0a2',
    'Domain' => 'www.example.com,img.example.com'
];

$response = $client->batchDeployCert($body);
var_dump($response);
