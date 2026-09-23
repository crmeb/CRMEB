<?php

use Volc\Service\Cdn;

require('../../vendor/autoload.php');

$client = Cdn::getInstance();
require_once "init.php";

$client = Cdn::getInstance();

$config = new Config();
$client->setAccessKey($config->ak);
$client->setSecretKey($config->sk);

$body = [
    'StartTime' => $config->startTime,
    'EndTime' => $config->endTime,
    'Domain' => $config->exampleDomain,
    'Metric' => "bandwidth",
    'Interval' => '5min',
    'Protocol' => 'http',
    'IpVersion' => 'ipv4'
];

$response = $client->describeCdnDataDetail($body);
var_dump($response);
