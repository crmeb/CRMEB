<?php
require('../../vendor/autoload.php');

use Volc\Service\Vod\Models\Request\VodGetPlayInfoRequest;
use Volc\Service\Vod\Models\Response\VodGetPlayInfoResponse;
use Volc\Service\Vod\Vod;

// Create a VOD instance in the specified region.
// $client = Volc\Service\Vod\Vod::getInstance('cn-north-1');
$client = Volc\Service\Vod\Vod::getInstance();

// Configure your Access Key ID (AK) and Secret Access Key (SK) in the environment variables or in the local ~/.volc/config file. For detailed instructions, see https://www.volcengine.com/docs/4/4408.
// The SDK will automatically fetch the AK and SK from the environment variables or the ~/.volc/config file as needed.
// During testing, you may use the following code snippet. However, do not store the AK and SK directly in your project code to prevent potential leakage and safeguard the security of all resources associated with your account.
// $client->setAccessKey('your ak');
// $client->setSecretKey('your sk');

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