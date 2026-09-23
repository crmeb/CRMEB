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
    'Metric' => 'pv',
    'Domain' => $config->exampleDomain,
    'Interval' => '5min',
    'Isp' => 'CT',
    'Region' => 'BJ',
    'Protocol' => 'http',
    'IpVersion' => 'ipv4'
];

$response = $client->describeCdnData($body);
var_dump($response);

$response = $client->describeCdnData($body, $client->useGet());
var_dump($response);
