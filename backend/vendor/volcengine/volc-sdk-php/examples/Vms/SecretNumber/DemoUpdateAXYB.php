<?php
use Volc\Service\Vms\Vms;

require('../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'NP167091934402820309',
    'SubId' => 'S1672985491953460b641f',
    'UpdateType' => 'UpdatePhoneNoA',
    'PhoneNoA' => '18900000001',
];

$response = $client->UpdateAXYB($body);
echo $response;