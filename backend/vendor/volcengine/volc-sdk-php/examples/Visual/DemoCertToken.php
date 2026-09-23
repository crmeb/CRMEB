<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('sts-ak');
$client->setSecretKey('sts-sk');
// $client->setSessionToken('sts-token');

echo "\nDemo CertToken\n";

// 基础版入参示例
// $body = [
//     'req_key' => 'cert_token',  // 基础版：cert_token
//     'sts_token' => 'xxx',  // 使用临时密钥的情况下，需要传入sts-token，使用长期密钥可以随意传值

//     // 'tos_info' => [
//     //     "sts_ak" => "",
//     //     "sts_sk" => "",
//     //     "sts_token" => "",
//     //     "bucket" => "",
//     //     "endpoint" => "",
//     //     "region" => "",
//     // ],
//     'ref_source' => "1",  // 0：无源比对  1：有源比对
//     'idcard_name' => "xx", // 有源比对必填
//     'idcard_no' => "xx",  // 有源比对必填
//     // 'ref_image' => "",  // 无源比对必填
//     'liveness_timeout' => 10,
//     'max_liveness_trial' => 10,
//     'liveness_type' => "motion",
//     'motion_count' => 2,
//     'motion_list' => ["0", "1", "2", "3"],
//     'fixed_motion_list' => ["0", "1"],
//     // 'callback_info' => [
//     //     "switch" => false,
//     //     "block" => false,
//     //     "url" => "",
//     //     "client_name" => "",
//     // ],
//     // 'config_id' => "",
// ];

// 增强版入参示例
$body = [
    'req_key' => 'cert_pro_token',  // 增强版：cert_pro_token
    'sts_token' => 'xxx',  // 使用临时密钥的情况下，需要传入sts-token，使用长期密钥可以随意传值
    // 'tos_info' => [],
    'ref_source' => "1",  // 0：无源比对  1：有源比对
    'idcard_name' => "xx", // 有源比对必填
    'idcard_no' => "xx",  // 有源比对必填
    // 'ref_image' => "",  // 无源比对必填
    'liveness_timeout' => 10,
    'max_liveness_trial' => 10,
    'risk_liveness_type' => [
        "free" => "motion",
        "low" => "motion",
        "medium" => "motion",
        "high" => "motion",
    ],
    'risk_motion_count' => [
        "free" => 2,
        "low" => 2,
        "medium" => 2,
        "high" => 2,
    ],
    'risk_motion_list' => [
        "free" => ["0", "1", "2", "3"],
        "low" =>  ["0", "1", "2", "3"],
        "medium" => ["0", "1", "2", "3"],
        "high" =>  ["0", "1", "2", "3"],
    ],
    'risk_fixed_motion_list' => [
        "free" => ["0", "1"],
        "low" =>  ["2", "3"],
        "medium" => ["1", "2"],
        "high" =>  ["0", "3"],
    ],
    // 'callback_info' => [
    //     "switch" => false,
    //     "block" => false,
    //     "url" => "",
    //     "client_name" => "",
    // ],
    // 'config_id' => "",
];

$response = $client->CertToken(['json' => $body]);
echo $response;