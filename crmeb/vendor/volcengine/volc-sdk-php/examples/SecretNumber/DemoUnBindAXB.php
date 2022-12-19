<?php
use Volc\Service\SecretNumber;

require('../../vendor/autoload.php');
require('../../src/Service/SecretNumber.php');

$client = SecretNumber::getInstance();
$client->setAccessKey("");
$client->setSecretKey("");

$body = [
    'NumberPoolNo' => 'NP161156328504091435',
    'SubId' => 'S16328999093159b70bc71',
];

$response = $client->UnbindAXB($body);
echo $response;