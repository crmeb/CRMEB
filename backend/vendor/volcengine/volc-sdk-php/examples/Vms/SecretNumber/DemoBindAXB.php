<?php
use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'NP161156328504091435',
    'PhoneNoA' => '13700000001',
    'PhoneNoB' => '13700000002',
    'PhoneNoX' => '13700000003',
    'ExpireTime' => '1632920195',
    'UserData' => 'this is my user data',
];

$response = $client->BindAXB($body);
echo $response;