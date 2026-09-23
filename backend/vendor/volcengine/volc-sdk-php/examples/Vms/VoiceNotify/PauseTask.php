<?php


use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$response = $client->PauseTask("0742e8123ada40cb9efa6b55e8fced7a");
echo $response;