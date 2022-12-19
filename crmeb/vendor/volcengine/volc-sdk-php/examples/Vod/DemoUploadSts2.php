<?php
require('../../vendor/autoload.php');

use Volc\Service\Vod\Vod;

$client = Vod::getInstance();
// call below method if you dont set ak and sk in ～/.vcloud/config
// $client->setAccessKey($ak);
// $client->setSecretKey($sk);

$expire = 60; // 请求的签名有效期

echo "\nSTS2鉴权签名\n";
$space = "";
$response = $client->getUploadVideoAuthWithExpiredTime($expire);
echo json_encode($response);

echo "\nSTS2鉴权签名，过期时间默认1小时\n";
$vid = "";
$response = $client->getUploadVideoAuth();
echo json_encode($response);