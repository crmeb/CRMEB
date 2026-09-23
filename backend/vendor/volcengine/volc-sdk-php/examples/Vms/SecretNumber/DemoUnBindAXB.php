<?php
use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("");
$client->setSecretKey("");

$body = [
    'NumberPoolNo' => 'NP161156328504091435',
    'SubId' => 'S16328999093159b70bc71',
];

$response = $client->UnbindAXB($body);
echo $response;