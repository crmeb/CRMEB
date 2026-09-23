<?php


use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");


$body = [
    'Url'=>'公网可以访问的url，需要申请正向代理',
    'Name'=>'测试',
];

$response = $client->FetchResource($body);
echo $response;