<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('sts-ak');
$client->setSecretKey('sts-sk');
// $client->setSessionToken('sts-token');

echo "\nDemo CertVerifyQuery\n";

// 基础版入参示例
// $body = [
//     'req_key' => 'cert_verify_query', // 基础版：cert_verify_query
//     'byted_token' => 'xxx',
//     'omit_data' => false,
//     'omit_image_data' => false,
//     'omit_video_data' => false,
// ];

// 增强版入参示例
$body = [
    'req_key' => 'cert_pro_verify_query', // 增强版：cert_pro_verify_query
    'byted_token' => 'xxx',
    'omit_data' => false,
    'omit_image_data' => false,
    'omit_video_data' => false,
];

$response = $client->CertVerifyQuery(['json' => $body]);
echo $response;