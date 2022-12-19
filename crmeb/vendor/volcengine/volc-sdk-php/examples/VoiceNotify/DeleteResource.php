<?php
use Volc\Service\Notify;

require('../../../vendor/autoload.php');
require('../../src/Service/Notify.php');

$client = Notify::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$response = $client->DeleteResourceByKey("1e81711901d844cfa1fe89d341558a39");
echo $response;
