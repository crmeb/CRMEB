<?php


use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");



$response = $client->SingleCancel('9b39e17fb12444c78f20d6551469a6e3');
echo $response;