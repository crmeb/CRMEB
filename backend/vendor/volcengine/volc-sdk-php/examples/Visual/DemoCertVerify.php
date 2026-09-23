<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('sts-ak');
$client->setSecretKey('sts-sk');
// $client->setSessionToken('sts-token');


echo "\nDemo CertVerify\n";

$body = [
    'req_key' => 'cert_pro_src_verify', // 有源比对填写cert_pro_src_verify，无源比对填写cert_pro_no_src_verify
    'byted_token' => 'xxx',
    'sdk_data' => '',
    // 'video_key' => '',
    // 'tos_bucket' => '',
    'risk_info' => 'https://xxxx',
    // 'extra' => []
];
$response = $client->CertVerify(['json' => $body]);
echo $response;