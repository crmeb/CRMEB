<?php

use Volc\Service\Cdn;

require('../../vendor/autoload.php');
require_once "init.php";

$client = Cdn::getInstance();

$config = new Config();
$client->setAccessKey($config->ak);
$client->setSecretKey($config->sk);

$body = [
    'CertId' => 'cert-2b12dd79c3ef441ea1e58a09248d0fd6'
];

$response = $client->describeCertConfig($body);
var_dump($response);
