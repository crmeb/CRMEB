<?php

include 'DemoBase.php';
global $client;

$body = [
    'Domain' => 'domain',
    'SceneType' => 'push',
    "AuthDetailList" => [
        [
            "EncryptionAlgorithm" => "md5",
            "SecretKey" => "xx",
        ],
    ],
];

$response = $client->updateAuthKey(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Domain' => 'domain',
    'SceneType' => 'push'
];

$response = $client->describeAuth(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Domain' => 'domain',
    'App' => 'app',
    'RefererInfoList' => [
        [
            'Key' => 'asd',
            'Type' => 'xx',
            'Value' => 'sad',
            'Priority' => 0,
        ],
    ],
];

$response = $client->updateReferer(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Domain' => 'domain',
    'App' => 'app',
];

$response = $client->describeReferer(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Vhost' => 'vv',
    'App' => 'app',
];

$response = $client->deleteReferer(['json' => $body]);
echo $response;
echo '<br>';



