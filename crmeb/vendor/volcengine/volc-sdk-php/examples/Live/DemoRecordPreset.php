<?php

include 'DemoBase.php';
global $client;

$body = [
    'Vhost' => 'vhost',
    'App' => 'app',
    'Bucket' => 'bb',
    'RecordTob' => [
        [
            'Format' => 'hls',
            'Duration' => 100,
            'Splice' => -1,
            'RecordObject' => '/xx/xx',
        ],
    ]
];

$response = $client->createRecordPreset(['json' => $body]);
echo $response;
echo '<br>';


$body = [
    "Preset" => "preset",
    'Vhost' => 'vhost',
    'App' => 'app',
    'Bucket' => 'bb2',
    'RecordTob' => [
        [
            'Format' => 'hls',
            'Duration' => 100,
            'Splice' => -1,
            'RecordObject' => '/xx/xx',
        ],
    ]
];
$response = $client->updateRecordPreset(['json' => $body]);
echo $response;
echo '<br>';


$body = [
    'Vhost' => 'vhost',
    'App' => 'app',
    'Preset' => 'xx',
];
$response = $client->deleteRecordPreset(['json' => $body]);
echo $response;
echo '<br>';


$body = [
    'Vhost' => 'vhost',
];
$response = $client->listVhostRecordPreset(['json' => $body]);
echo $response;
echo '<br>';
