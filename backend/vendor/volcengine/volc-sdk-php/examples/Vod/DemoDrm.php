<?php
require('../../vendor/autoload.php');

use Volc\Service\Vod\Models\Request\VodGetPrivateDrmPlayAuthRequest;
use Volc\Service\Vod\Models\Response\VodGetPrivateDrmPlayAuthResponse;
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

