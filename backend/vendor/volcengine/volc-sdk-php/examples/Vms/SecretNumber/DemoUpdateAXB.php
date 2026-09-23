<?php
use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$body = [
    'NumberPoolNo' => 'NP161156328504091435',
    'SubId' => 'S1632900399315954ffbfd',
    'UpdateType' => 'updateExpireTime',
    'ExpireTime' => '1632924444',
];

$response = $client->UpdateAXB($body);
echo $response;