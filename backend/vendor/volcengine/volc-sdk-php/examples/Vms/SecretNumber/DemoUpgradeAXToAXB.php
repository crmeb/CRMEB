<?php
use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'NP161156328504091435',
    'SubId' => 'S163290034831599ce523b',
    'PhoneNoB' => '13700000002',
    'UserData' => 'this is my user data',
];

$response = $client->UpgradeAXToAXB($body);
echo $response;