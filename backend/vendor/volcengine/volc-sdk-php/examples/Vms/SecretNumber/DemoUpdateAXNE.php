<?php
use Volc\Service\Vms\Vms;

require('../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'NP167091934402820309',
    'SubId' => 'S1672985491953460b645f',
    'UpdateType' => 'updatePhoneNoB',
    'PhoneNoB' => '189000000001',
];

$response = $client->UpdateAXNE($body);
echo $response;