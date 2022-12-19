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
    'ServiceType' => 'web',
    'PageNum' => 1,
    'PageSize' => 10
];

$response = $client->listCdnDomains($body);
var_dump($response);
