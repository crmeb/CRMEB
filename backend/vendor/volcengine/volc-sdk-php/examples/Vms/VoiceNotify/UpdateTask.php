<?php
use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");


$body = [
    'TaskOpenId' => '0742e8123ada40cb9efa6b55e8fced7a',
    'Concurrency'=> 3,
    'StartTime' => '2022-05-17 00:00:00',
    'EndTime' => '2022-05-17 18:30:00',
    'Recall' => 1,
];

$response = $client->UpdateTask($body);
echo $response;