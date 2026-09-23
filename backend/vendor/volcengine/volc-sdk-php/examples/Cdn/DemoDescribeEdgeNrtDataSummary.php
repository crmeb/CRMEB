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
    'Area' => 'China',
    "Isp" => 'CT',
    'Region' => 'BJ',
    'Protocol' => 'http',
    'IpVersion' => 'ipv4',
    'Aggregate' => 'aggregate'
];

$response = $client->describeEdgeNrtDataSummary($body);
var_dump($response);
