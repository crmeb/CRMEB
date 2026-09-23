<?php

use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'CallIdList' => 'CallId1,CallId2,CallId3',
];

$response = $client->QueryCallRecordMsg($body);
echo $response;