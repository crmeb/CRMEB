<?php

//调用示例

namespace YourProject\Rtc;

require('../vendor/autoload.php');

$client = Rtc::getInstance();

// ak/sk 获取方式参考:https://www.volcengine.com/docs/6348/69828
$ak = '';
$sk = '';
$appId = '';

$client->setAccessKey($ak);
$client->setSecretKey($sk);

// 参数规范参考: https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html#query
// startRecord - POST 请求
$roomId = 'Your_RoomId';
$body = [
    'AppId' => $appId,
    'RoomId' => '1234',
    'TaskId' => $appId.'_'.$roomId,
    'BusinessId' => 'Your_BusinessId',
    'Encode' => [
        "VideoWidth" => 1920,
        "VideoHeight" => 1080,
        "VideoFps" => 15,
        "VideoBitrate"=> 4000,
    ],
    'FileFormatConfig' => [
        'FileFormat' => ['MP4'],
    ],
    'StorageConfig' => [
        'TosConfig'=> [
            'AccountId' => 'Your_Volc_AccountId',
            'Bucket' => 'Your_Bucket',
        ],
    ],
];

$response = $client->startRecord(['json' => $body]);
echo "startRecord result:\n";
echo $response;
echo "\n";

// getRecordTask - GET 请求
$body = [
    'AppId' => $appId,
    'RoomId' => $roomId,
    'TaskId' => $appId.'_'.$roomId,
];

$response = $client->getRecordTask(['query' => $body]);
echo "getRecordTask result:\n";
echo $response;
echo "\n";