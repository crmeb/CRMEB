<?php

include 'DemoBase.php';
global $client;

$body = [
    'Vhost' => 'vhost',
    'App' => 'app',
    'Bucket' => 'bb',
    'SnapshotFormat' => 'jpeg',
    'SnapshotObject' => 'xx/xx',
    'StorageDir' => '/xx'
];

$response = $client->createSnapshotPreset(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Preset' => 'preset',
    'Vhost' => 'vhost',
    'App' => 'app',
    'Bucket' => 'bb2',
    'SnapshotFormat' => 'jpeg',
    'SnapshotObject' => 'xx/xx',
    'StorageDir' => '/xx'
];
$response = $client->updateSnapshotPreset(['json' => $body]);
echo $response;
echo '<br>';


$body = [
    'Vhost' => 'vhost',
    'App' => 'app',
    'Preset' => 'xx',
];
$response = $client->deleteSnapshotPreset(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Vhost' => 'vhost',
];
$response = $client->listVhostSnapshotPreset(['json' => $body]);
echo $response;
echo '<br>';
