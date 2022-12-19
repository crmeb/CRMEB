<?php
use Volc\Service\SecretNumber;

require('../../vendor/autoload.php');
require('../../src/Service/SecretNumber.php');

$client = SecretNumber::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'NP162981168404095092',
    'SubId' => 'S16329006138991e7e1003',
    'UpdateType' => 'updatePhoneNoB',
    'PhoneNoB' => '13700000004',
];

$response = $client->UpdateAXN($body);
echo $response;