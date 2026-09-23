<?php

use Volc\Service\Cdn;

require('../../vendor/autoload.php');
require_once "init.php";

$client = Cdn::getInstance();

$config = new Config();
$client->setAccessKey($config->ak);
$client->setSecretKey($config->sk);

$body = [
    'StartTime' => $config->startTime,
    'EndTime' => $config->endTime,
    'Domain' => $config->exampleDomain,
    'PageSize' => 100,
    'PageNum' => 1
];

$response = $client->describeCdnAccessLog($body);
var_dump($response);
