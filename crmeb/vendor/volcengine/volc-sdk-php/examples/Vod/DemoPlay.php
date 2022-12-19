<?php
require('../../vendor/autoload.php');

use Volc\Service\Vod\Models\Request\VodGetPlayInfoRequest;
use Volc\Service\Vod\Models\Response\VodGetPlayInfoResponse;
use Volc\Service\Vod\Vod;


$client = Vod::getInstance();
// $client->setAccessKey("");
// $client->setSecretKey("");

$vid = "your vid";
$expire = 6000000; // 请求的签名有效期


echo "\nstaging 获取播放地址\n";
$req = new VodGetPlayInfoRequest();
$req->setVid($vid);
$req->setSsl('1');
$req->setFormat('hls');
$req->setDefinition('480p');
$req->setFileType('evideo');
$response = new VodGetPlayInfoResponse();
try {
    $response = $client->getPlayInfo($req);
} catch (Exception $e) {
    echo $e, "\n";
} catch (Throwable $e) {
    echo $e, "\n";
}
if ($response->getResponseMetadata()->getError() != null) {
    print_r($response->getResponseMetadata()->getError());
}
echo $response->getResult()->getPlayInfoList()[0]->getMainPlayUrl();

echo "\n获取PlayAuthToken\n";
$req2 = new VodGetPlayInfoRequest();
$req2->setVid($vid);
try {
    $response2 = $client->getPlayAuthToken($req2, $expire);
    echo $response2;
} catch (Exception $e) {
    echo $e, "\n";
} catch (Throwable $e) {
    echo $e, "\n";
}