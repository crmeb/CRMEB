<?php
require('../../vendor/autoload.php');

use Volc\Service\Sts;

$client = Sts::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$query = [
    "query" => [
        "DurationSeconds" => "900",
        "RoleSessionName" => "just_for_test",
        "RoleTrn" => "trn:iam::yourAccountID:role/yourRole",
    ]
];

$response = $client->assumeRole($query);
echo $response;