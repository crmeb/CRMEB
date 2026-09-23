<?php
require('../../../vendor/autoload.php');

use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey('ak');
$client->setSecretKey('sk');

echo "\nDemo CVSync2AsyncGetResult\n";

// 特别注意：body完整入参请参考不同接口的请求body部分
$body = [
    "req_key" => "xxx",
    "task_id" => "xxx",
    "req_json" => "{\"logo_info\":{\"add_logo\":true,\"position\":2,\"language\":0,\"opacity\":1}}"
];

$response = $client->CVSync2AsyncGetResult(['json' => $body]);
$response = str_replace('\u0026', '&', $response);
echo $response;

