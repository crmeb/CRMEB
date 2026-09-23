<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('sts-ak');
$client->setSecretKey('sts-sk');
// $client->setSessionToken('sts-token');

echo "\nDemo CertConfigInit\n";

// 基础版入参示例
// $body = [
//     'req_key' => 'cert_config_init',
//     'config_name' => 'test-111',
//     'config_desc' => 'ceshi',
//     'token_api_config' => [
//         'ref_source' => '1',
//         'liveness_type' => 'motion',
//         'motion_list' => ['0', '1', '2', '3'],
//         'fixed_motion_list' => ['0', '1'],
//         'motion_count' => 3,
//         'max_liveness_trial' => 10,
//         // 'tos_info' => [],
//         // 'callback_info' => []
//     ],
//     'h5_config' => [
//         'redirectUrl' => 'xxx', 
//         'type' => 0, // 0正常流程，1跳过OCR，2直接进行活体认证
//         'source' => 1, // 0无源，1有源
//         // 'bucket' => '',
//         // 'endpoint' => '',
//         // 'region' => '',
//         'showResult' => 1,
//         // 'protocolName' => '',
//         // 'protocolLink' => '',
//         'demoteType' => 0,
//         // 'styleConf' => '',
//         'protocolNav' => 0,
//         // 'protocolNavTitle' => '',
//     ],
// ];

// 增强版入参示例
$body = [
    'req_key' => 'cert_config_init',
    'config_name' => 'xxx',
    'config_desc' => '',
    'token_api_config' => [
        'ref_source' => '',
        'risk_liveness_type' => '',
        'risk_motion_list' => '',
        'risk_fixed_motion_list' => '',
        'risk_motion_count' => '',
        'max_liveness_trial' => '',
        'tos_info' => '',
        'callback_info' => ''
    ]
];

$response = $client->CertConfigInit(['json' => $body]);
echo $response;