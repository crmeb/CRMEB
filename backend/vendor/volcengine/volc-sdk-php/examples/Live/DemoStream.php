<?php

include 'DemoBase.php';
global $client;

$body = [
    'Domain' => 'domain1',
    'App' => 'app',
    'Stream' => 'stream',
    'EndTime' => '2022-02-12T08:12:48.000Z',
];

$response = $client->forbidStream(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Domain' => 'domain1',
    'App' => 'app',
    'Stream' => 'stream',
];

$response = $client->resumeStream(['json' => $body]);
echo $response;
echo '<br>';
