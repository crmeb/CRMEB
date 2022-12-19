<?php
use Volc\Service\SecretNumber;

require('../../vendor/autoload.php');
require('../../src/Service/SecretNumber.php');

$client = SecretNumber::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'Caller' => "137XXXX8257",
    'Callee' => "158XXXX9130",
    'CallerNumberPoolNo' => 'NP163517154204092175',
    'CalleeNumberPoolNo' => 'NP163517154204092175',
];

$response = $client->Click2Call($body);
echo $response;