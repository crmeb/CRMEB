<?php

use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("");
$client->setSecretKey("");


$response = $client->QueryUsableResource(0);
echo $response;