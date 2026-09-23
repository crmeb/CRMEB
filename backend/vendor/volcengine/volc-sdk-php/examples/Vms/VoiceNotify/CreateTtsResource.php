<?php



use Volc\Service\Vms\Vms;

require('../../../vendor/autoload.php');
require('../../../src/Service/Vms/Vms.php');

$client = Vms::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");


$body = [
    'Name'=>'测试',
    'TtsTemplateContent'=>'测试',
    'Remark'=>"测试",
    'TtsOption'=>"{\"loop\":0,\"loop_interval\":0,\"speed\":10,\"volume\":10,\"pitch\":10,\"voice_type\":\"BV001_streaming\",\"lang\":\"ch\",\"voice\":\"other\"}",
];

$response = $client->CreateTtsResource($body);
echo $response;
