<?php

use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'xxx',
    'NumberPoolTypeCode' => '101',
    'Limit' => '5',
    'Offset' => '0',
];

$response = $client->NumberList($body);
echo $response;