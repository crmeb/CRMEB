<?php

include 'DemoBase.php';
global $client;

$body = [
    'Available' => true,
    'Expiring' => true,
    'Domain' => 'dd',
];

$response = $client->listCert(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'UseWay' => 'sign',
    'CertName' => 'asd',
];

$response = $client->createCert(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'UseWay' => 'sign',
    'ChainID' => 'xxx',
];

$response = $client->updateCert(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'ChainID' => 'xxx',
];

$response = $client->deleteCert(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Domain' => 'do',
    'CertDomain'=>'',
    'ChainID'=> 'x',
];
$response = $client->bindCert(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Domain' => 'do'
];
$response = $client->unbindCert(['json' => $body]);
echo $response;
echo '<br>';


