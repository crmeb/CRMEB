<?php

use Volc\Service\Notify;

require('../../../vendor/autoload.php');
require('../../src/Service/Notify.php');

$client = Notify::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$response = $client->ResumeTask("0742e8123ada40cb9efa6b55e8fced7a");
echo $response;