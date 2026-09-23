<?php
use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'Caller' => '137XXXX8257',
    'Callee' => '158XXXX9130',
    'NumberPoolNo' => 'NPXXXXX810901043',
];

$response = $client->Click2CallLite($body);
echo $response;