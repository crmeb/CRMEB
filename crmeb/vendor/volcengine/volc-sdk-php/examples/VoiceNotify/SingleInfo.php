<?php


use Volc\Service\Notify;

require('../../../vendor/autoload.php');
require('../../src/Service/Notify.php');

$client = Notify::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");



$response = $client->SingleInfo('9b39e17fb12444c78f20d6551469a6e3');
echo $response;