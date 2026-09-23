<?php
require('../../vendor/autoload.php');

use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');

echo "\nDemo CertSrcFaceComp\n";

$body = [
    'req_key' => 'cert_src_face_comp',
    'idcard_name' => '',
    'idcard_no' => '',
    'image' => ''
];

$response = $client->CertSrcFaceComp(['json' => $body]);
echo $response;
