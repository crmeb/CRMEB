<?php

include 'DemoBase.php';
global $client;

$body = [
    'Vhost' => 'vhost',
    'App' => 'app',
    'Bucket' => 'bb',
    'Interval' => 2.3,
    'StorageStrategy' => 1,
    'Label' => ['301'],
    'CallbackDetailList' => [
        [
            'URL' => 'http://xx',
            'CallbackType' => 'http'
        ]
    ],
    'StorageDir' => '/xx'
];

$response = $client->createSnapshotAuditPreset(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'PresetName' => 'presetName',
    'Vhost' => 'vhost',
    'App' => 'app',
    'Bucket' => 'bb2'
];
$response = $client->updateSnapshotAuditPreset(['json' => $body]);
echo $response;
echo '<br>';


$body = [
    'Vhost' => 'vhost',
    'App' => 'app',
    'PresetName' => 'presetName',
];
$response = $client->deleteSnapshotAuditPreset(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Vhost' => 'vhost',
];
$response = $client->listVhostSnapshotAuditPreset(['json' => $body]);
echo $response;
echo '<br>';
