<?php

use Volc\Service\Cdn;

require('../../vendor/autoload.php');
require_once "init.php";

$client = Cdn::getInstance();

$config = new Config();
$client->setAccessKey($config->ak);
$client->setSecretKey($config->sk);

$body = [
    'IPList' => '1.1.1.1,2.2.2.2'
];

$response = $client->describeIPListInfo($body);
var_dump($response);
