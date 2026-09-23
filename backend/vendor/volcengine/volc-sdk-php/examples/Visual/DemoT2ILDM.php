<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo T2ILDM\n";

$body = [
    'req_key' => 't2i_ldm',
    'text' => 'xxxx',
    'style_term' => 'cartoon style',
];
$response = $client->T2ILDM(['json' => $body]);
echo $response;