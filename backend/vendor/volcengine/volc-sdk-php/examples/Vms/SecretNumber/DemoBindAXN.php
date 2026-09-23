<?php
use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'NP162981168404095092',
    'PhoneNoA' => '13700000001',
    'PhoneNoB' => '13700000002',
    'PhoneNoX' => '13700000005',
    'ExpireTime' => '1632920195',
    'UserData' => 'this is my user data',
];

$response = $client->BindAXN($body);
echo $response;