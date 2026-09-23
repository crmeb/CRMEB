<?php

use Volc\Service\Cdn;

require('../../vendor/autoload.php');
require_once "init.php";

$client = Cdn::getInstance();

$config = new Config();
$client->setAccessKey($config->ak);
$client->setSecretKey($config->sk);

$body = [
    'TaskType' => 'block_url',
    'StartTime' => $config->startTime,
    'EndTime' => $config->endTime,
];

$response = $client->describeContentBlockTasks($body);
var_dump($response);
