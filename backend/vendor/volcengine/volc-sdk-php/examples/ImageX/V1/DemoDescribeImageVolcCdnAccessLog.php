<?php
include_once(__DIR__ . '/../../../vendor/autoload.php');

use Volc\Service\ImageX;

$client = ImageX::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$query = [
    'ServiceId' => "service id",
];

$body = [
    'StartTime' => 1680500000,
    'EndTime' => 1680515000,
    'Domain' => "domain",
    'Region' => "global",
    'PageNum' => 1,
    'PageSize' => 10,
];

$resp = $client->describeImageVolcCdnAccessLog($query, $body);
print_r($resp);
