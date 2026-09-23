<?php

use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberList' => 'xxx',
    'EnableCode' => '2',
];

$response = $client->EnableOrDisableNumber($body);
echo $response;