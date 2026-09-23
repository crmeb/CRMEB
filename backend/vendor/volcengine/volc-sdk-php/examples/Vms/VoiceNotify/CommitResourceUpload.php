<?php


use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");


$body = [
    'FileName'=>'mytest.mp3',
];

$response = $client->CommitResourceUpload($body);
echo $response;