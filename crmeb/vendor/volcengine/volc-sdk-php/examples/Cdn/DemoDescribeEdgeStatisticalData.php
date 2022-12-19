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
    'Metric' => 'clientIp',
    'Domain' => $config->exampleDomain,
    'Interval' => 'hour',
    'Area' => 'China',
    'Region' => 'BJ',
    'IpVersion' => 'ipv4'
];

$response = $client->describeEdgeStatisticalData($body);
var_dump($response);
