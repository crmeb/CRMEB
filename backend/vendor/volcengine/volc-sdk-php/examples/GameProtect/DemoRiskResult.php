<?php

require('../../vendor/autoload.php');

use Volc\Service\GameProduct;

$client = GameProduct::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$response = $client->riskResult(218745, 1618502400, 1618545491, 10, 1);
echo $response;