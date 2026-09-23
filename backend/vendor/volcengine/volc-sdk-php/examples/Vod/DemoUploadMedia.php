<?php
require('../../vendor/autoload.php');

use Volc\Service\Vod\Models\Request\VodUploadMediaRequest;
use Volc\Service\Vod\Models\Response\VodCommitUploadInfoResponse;
use Volc\Service\Vod\Upload\Functions;
use Volc\Service\Vod\Upload\VodUpload;
use Volc\Service\Vod\Upload\OptionInfo;
use Volc\Service\Vod\Upload\Template;

// Create a VOD instance in the specified region.
// $client = Volc\Service\Vod\Vod::getInstance('cn-north-1');
$client = VodUpload::getInstance();

// Configure your Access Key ID (AK) and Secret Access Key (SK) in the environment variables or in the local ~/.volc/config file. For detailed instructions, see https://www.volcengine.com/docs/4/4408.
// The SDK will automatically fetch the AK and SK from the environment variables or the ~/.volc/config file as needed.
// During testing, you may use the following code snippet. However, do not store the AK and SK directly in your project code to prevent potential leakage and safeguard the security of all resources associated with your account.
// $client->setAccessKey('your ak');
// $client->setSecretKey('your sk');

$space = 'your space';
$filePath = "file path";

$functions = new Functions();
$functions->addGetMetaFunc();
$functions->addSnapshotTimeFunc(2.1);
$impTemplate = new Template();
$impTemplate->TemplateIds = ["imp template id"];
$impTemplate->TemplateType = "imp";
$transcodeTemplate = new Template();
$transcodeTemplate->TemplateIds = ["transcode template id"];
$transcodeTemplate->TemplateType = "transcode";
$functions->addStartWorkflowTemplateFunc([$impTemplate, $transcodeTemplate]);
$optionInfo = new OptionInfo();
$optionInfo->IsHlsIndexOnly = true;
$functions->addOptionInfoFunc($optionInfo);
$functions = $functions->getFunctionsString();

$request = new VodUploadMediaRequest();
$request->setSpaceName($space);
$request->setFilePath($filePath);
$request->setFunctions($functions);
$request->setCallbackArgs("my callback");
$request->setFileName("hello/vod.mp4");
$request->setStorageClass(0);
$request->setUploadHostPrefer("");

$response = new VodCommitUploadInfoResponse();
try {
    $response = $client->uploadMedia($request);
} catch (Exception $e) {
    echo $e, "\n";
} catch (Throwable $e) {
    echo $e, "\n";
}
if ($response->getResponseMetadata() != null && $response->getResponseMetadata()->getError() != null) {
    echo $response->getResponseMetadata()->getError()->serializeToJsonString(), "\n";
}
echo $response->serializeToJsonString();
echo "\n";

if ($response->getResult() != null) {
    echo $response->getResult()->getData()->getVid(), "\n";
    echo $response->getResult()->getData()->getPosterUri(), "\n";
    echo $response->getResult()->getData()->getSourceInfo()->getWidth(), "\n";
    echo $response->getResult()->getData()->getSourceInfo()->getHeight(), "\n";
}


