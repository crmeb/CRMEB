<?php
use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'NP161156328504091435',
    'PhoneNoX' => '13700000003',
    'Status' => 1,
    'Offset' => 0,
    'Limit' => 20,
];

$response = $client->QuerySubscriptionForList($body);
echo $response;