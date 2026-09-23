<?php
use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'NP161156328504091435',
    'SubId' => 'S16329001153159fa121d9',
];

$response = $client->QuerySubscription($body);
echo $response;