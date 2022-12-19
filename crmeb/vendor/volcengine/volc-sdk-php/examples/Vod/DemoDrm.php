<?php
require('../../vendor/autoload.php');

use Volc\Service\Vod\Models\Request\VodGetPrivateDrmPlayAuthRequest;
use Volc\Service\Vod\Models\Response\VodGetPrivateDrmPlayAuthResponse;
use Volc\Service\Vod\Vod;


$client = Vod::getInstance();
// $client->setAccessKey("");
// $client->setSecretKey("");

$vid = "your vid";
$playAuthId = "your play auth id";
$expire = 6000000; // 请求的签名有效期
$req = new VodGetPrivateDrmPlayAuthRequest();
$req->setVid($vid);
$req->setDrmType("your drm type");
$req->setPlayAuthIds($playAuthId);
$req->setUnionInfo("your unionInfo");

echo "\n获取HlsDrmAuthToken\n";
try {
    $response1 = $client->getSHA1HlsDrmAuthToken($expire);
    echo $response1;
} catch (Exception | Throwable $e) {
    echo $e, "\n";
}

echo "\n获取PrivateDrmAuthToken\n";
try {

    $response2 = $client->getPrivateDrmPlayAuthToken($req, $expire);
    echo $response2;
} catch (Exception $e) {
    echo $e, "\n";
} catch (Throwable $e) {
    echo $e, "\n";
}

echo "\n获取PrivateDrmAuth\n";
try {
    $response3 = $client->getPrivateDrmPlayAuth($req);
    echo $response3->serializeToJsonString();
} catch (Exception $e) {
    echo $e, "\n";
} catch (Throwable $e) {
    echo $e, "\n";
}

