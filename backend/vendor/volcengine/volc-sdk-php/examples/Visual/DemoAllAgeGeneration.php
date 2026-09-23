<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo AllAgeGeneration\n";

$body = [
    'req_key' => 'all_age_generation',
    'binary_data_base64' => [''],
    'target_age' => 5,
];
$response = $client->AllAgeGeneration(['json' => $body]);
echo $response;