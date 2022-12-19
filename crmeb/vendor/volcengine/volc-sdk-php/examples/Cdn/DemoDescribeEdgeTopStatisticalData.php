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
    'Item' => 'url',
    'Domain' => $config->exampleDomain,
    'Area' => 'China',
];

$response = $client->describeEdgeTopStatisticalData($body);
var_dump($response);
