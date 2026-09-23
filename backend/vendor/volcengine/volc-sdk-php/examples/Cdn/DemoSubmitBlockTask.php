<?php

use Volc\Service\Cdn;

require('../../vendor/autoload.php');
require_once "init.php";

$client = Cdn::getInstance();

$config = new Config();
$client->setAccessKey($config->ak);
$client->setSecretKey($config->sk);


$body = [
    'Urls' => sprintf('http://%s/1.txt\nhttp://%s/2.jpg', $config->exampleDomain, $config->exampleDomain),
];

$response = $client->submitBlockTask($body);
var_dump($response);
