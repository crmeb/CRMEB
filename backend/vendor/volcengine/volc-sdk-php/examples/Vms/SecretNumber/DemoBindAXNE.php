<?php
use Volc\Service\Vms\Vms;

require('../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'NP167091934402820309',
    'PhoneNoA' => '13700000003',
    'PhoneNoB' => '13700000004',
    'UserData' => 'this is my user data',
    'CityCode' => '010',
    'ExpireTime' => '1675996797',
];

$response = $client->BindAXNE($body);
echo $response;