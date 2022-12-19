<?php
require('../../vendor/autoload.php');

use Volc\Service\Billing;

$client = Billing::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$ak = "<Your AK>";
$sk = "<Your SK>";
$client->setAccessKey($ak);
$client->setSecretKey($sk);

$response = $client->listBillOverviewByProd(['query' => [
    'BillPeriod' => '2022-01',
    'Limit' => 10,
    'Offset' => 0,
    'Product' => '',
    'BillingMode' => '',
    'BillCategoryParent' => '',
    'IgnoreZero' => '0',
    'NeedRecordNum' => '0']]);
echo $response;