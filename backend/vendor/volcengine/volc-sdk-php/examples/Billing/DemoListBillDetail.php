<?php
require('../../vendor/autoload.php');

use Volc\Service\Billing;

$client = Billing::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$ak = "<Your AK>";
$sk = "<Your SK>";
$client->setAccessKey($ak);
$client->setSecretKey($sk);

$response = $client->listBillDetail(['query' => [
    'BillPeriod' => '2022-01',
    'Limit' => 10,
    'Offset' => 0,
    'GroupTerm' => 0,
    'GroupPeriod' => 2,
    'Product' => '',
    'BillingMode' => '',
    'BillCategory' => '',
    'InstanceNo' => '',
    'IgnoreZero' => '0',
    'NeedRecordNum' => '0']]);
echo $response;