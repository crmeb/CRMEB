<?php
require('../../vendor/autoload.php');

use Volc\Service\Vod\Models\Request\VodUpdateMediaPublishStatusRequest;
use Volc\Service\Vod\Models\Response\VodUpdateMediaPublishStatusResponse;
use Volc\Service\Vod\Vod;

// Create a VOD instance in the specified region.
// $client = Volc\Service\Vod\Vod::getInstance('cn-north-1');
$client = Volc\Service\Vod\Vod::getInstance();

// Configure your Access Key ID (AK) and Secret Access Key (SK) in the environment variables or in the local ~/.volc/config file. For detailed instructions, see https://www.volcengine.com/docs/4/4408.
// The SDK will automatically fetch the AK and SK from the environment variables or the ~/.volc/config file as needed.
// During testing, you may use the following code snippet. However, do not store the AK and SK directly in your project code to prevent potential leakage and safeguard the security of all resources associated with your account.
// $client->setAccessKey('your ak');
// $client->setSecretKey('your sk');

$vid = "vid";
$status = "Published";

echo "\n修改发布状态\n";

$req = new VodUpdateMediaPublishStatusRequest();
$req->setVid($vid);
$req->setStatus($status);
$response = new VodUpdateMediaPublishStatusResponse();
try {
    $response = $client->updateMediaPublishStatus($req);
} catch (Throwable $e) {
    print($e);
}

if ($response->getResponseMetadata()->getError() != null) {
    print_r($response->getResponseMetadata()->getError());
}

echo $response->serializeToJsonString();