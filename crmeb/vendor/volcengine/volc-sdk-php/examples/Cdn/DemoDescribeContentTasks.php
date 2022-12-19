<?php

use Volc\Service\Cdn;

require('../../vendor/autoload.php');
require_once "init.php";

$client = Cdn::getInstance();

$config = new Config();
$client->setAccessKey($config->ak);
$client->setSecretKey($config->sk);

$body = [
    "PageNum" => 1,
    'PageSize' => 10,
    'TaskType' => 'refresh_file'
];
$response = $client->describeContentTasks($body);
var_dump($response);
