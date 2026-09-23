<?php
require('../../vendor/autoload.php');

use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey('AK');
$client->setSecretKey('SK');
// $client->setSessionToken('sts-token');


echo "\nDemo StillLivenessImg\n";

$body = [
    "req_key" => "still_liveness",
    "binary_data_base64" => [
        ""
    ]

];
$response = $client->StillLivenessImg(['json' => $body]);
echo $response;