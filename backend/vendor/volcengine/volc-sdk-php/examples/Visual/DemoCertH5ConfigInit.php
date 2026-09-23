<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo CertH5ConfigInit\n";

$body = [
    "req_key" => "cert_h5_config_init",
    "h5_config" => [
        "type" => "1",
        "redirect_url" => "https://xxx"
    ],
    "liveness_config" => [
        "ref_source" => "1",
        "liveness_type" => "motion"
    ]

];
$response = $client->CertH5ConfigInit(['json' => $body]);
echo $response;

