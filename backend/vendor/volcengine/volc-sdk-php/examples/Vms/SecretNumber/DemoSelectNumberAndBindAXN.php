<?php
use Volc\Service\Vms\Vms;

require('../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'NP167092127702825445',
    'PhoneNoA' => '13700000001',
    'PhoneNoB' => '13700000002',
    'UserData' => 'this is my user data',
    'CityCode' => '010',
    'ExpireTime' => '1675996797',
];

$response = $client->SelectNumberAndBindAXN($body);
echo $response;