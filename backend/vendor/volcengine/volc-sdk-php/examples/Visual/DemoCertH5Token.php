<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo CertH5Token\n";

$body = [
    "req_key" => "cert_h5_token",
    "h5_config_id"=> "",
    "sts_token"=>"",
    "idcard_name"=>"",
    "idcard_no"=>""
];
$response = $client->CertH5Token(['json' => $body]);
echo $response;

