<?php

use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'Name' => 'siptest-ch',
    'ServiceType' => '100',
    'SubServiceType' => '101',
];

$response = $client->CreateNumberPool($body);
echo $response;