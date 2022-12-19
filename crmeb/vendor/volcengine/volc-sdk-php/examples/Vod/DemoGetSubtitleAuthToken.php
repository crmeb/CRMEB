<?php
require('../../vendor/autoload.php');

use Volc\Service\Vod\Models\Request\VodGetSubtitleInfoListRequest;
use Volc\Service\Vod\Vod;

$client = Vod::getInstance();
// call below method if you dont set ak and sk in ～/.vcloud/config
// $client->setAccessKey("");
// $client->setSecretKey("");

$vid = "vid";

echo "\n获取字幕token\n";

$req = new VodGetSubtitleInfoListRequest();

$req->setVid($vid);

try {
    $token = $client->getSubtitleAuthToken($req, 60);
} catch (Throwable $e) {
    print($e);
}

echo $token;