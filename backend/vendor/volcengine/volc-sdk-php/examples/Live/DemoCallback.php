<?php

include 'DemoBase.php';
global $client;

$body = [
    'MessageType' => 'record',
    'Vhost' => 'vhost',
    'CallbackDetailList' => [],
];

$response = $client->updateCallback(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'MessageType' => 'record',
    'Domain' => 'domain',
    'App' => 'app',
];

$response = $client->describeCallback(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'MessageType' => 'record',
    'Vhost' => 'vhost1',
];

$response = $client->deleteCallback(['json' => $body]);
echo $response;
echo '<br>';
