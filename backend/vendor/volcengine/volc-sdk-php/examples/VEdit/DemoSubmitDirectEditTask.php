<?php
require('../../vendor/autoload.php');

use Volc\Service\VEdit;

$client = VEdit::getInstance();
// $client->setAccessKey($ak);
// $client->setSecretKey($sk);


// async
// your custom editParam
// below just an example, not complete
$editParam = [
    'Upload' => [
        'Uploader' => 'your uploader',
        'VideoName' => 'your videoName',
    ],
    'Output' => [
        'Fps' => 25,
        'Height' => 720,
        'Width' => 1280,
        'Quality' => 'medium',
    ],
    'Segments' => [[
        'BackGround' => '0xFFFFFFFF',
        'Duration' => 3,
        'Elements' => [],
        'Volume' => 1,
    ]],
    'GlobalElements' => []
];

$body = [
    'EditParam' => $editParam,
    'CallbackArgs' => 'test args',
    'CallbackUri' => 'test callbackUri'
];

$response = $client->submitDirectEditTaskAsync(['json' => $body]);
echo $response;

// get results
$response = $client->getDirectEditResult(['json' => ['ReqIds' => ['123']]]);
echo $response;