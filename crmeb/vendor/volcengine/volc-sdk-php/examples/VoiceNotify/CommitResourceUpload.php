<?php


use Volc\Service\Notify;

require('../../../vendor/autoload.php');
require('../../src/Service/Notify.php');

$client = Notify::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");


$body = [
    'FileName'=>'mytest.mp3',
];

$response = $client->CommitResourceUpload($body);
echo $response;