<?php
include_once(__DIR__ . '/../../../vendor/autoload.php');

use Volc\Service\Live\V20230101\Live;

$client = Live::getInstance();

// call below method if you dont set ak and sk in ~/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$query = [];

$response = $client->listPullToPushTask($query);
print_r($response);
