<?php

include 'DemoBase.php';
global $client;

$body = [
    'PresetList' => []
];

$response = $client->listCommonTransPresetDetail(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Vhost' => 'vhost',
    'App' => 'app',
    'Status' => 0,
    'SuffixName' => 'xx',
    'Preset' => 'test',
    'FPS' => 30,
];

$response = $client->createTranscodePreset(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Vhost' => 'vhost',
    'App' => 'app',
    'Status' => 0,
    'SuffixName' => 'xx2',
    'Preset' => 'test',
    'FPS' => 60,
];
$response = $client->updateTranscodePreset(['json' => $body]);
echo $response;
echo '<br>';


$body = [
    'Vhost' => 'vhost',
    'App' => 'app',
    'Preset' => 'test',
];
$response = $client->deleteTranscodePreset(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Vhost' => 'vhost',
];
$response = $client->listVhostTransCodePreset(['json' => $body]);
echo $response;
echo '<br>';