<?php
require('../../vendor/autoload.php');

use Volc\Service\BusinessSecurity;

$client = BusinessSecurity::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$response = $client->RiskDetect(1, "", "{}");
echo $response;

$response = $client->GetVideoResult(222572, "video_risk",  "test");
echo $response;