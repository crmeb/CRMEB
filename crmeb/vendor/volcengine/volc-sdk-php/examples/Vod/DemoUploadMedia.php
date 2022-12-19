<?php
require('../../vendor/autoload.php');

use Volc\Service\Vod\Models\Request\VodUploadMediaRequest;
use Volc\Service\Vod\Models\Response\VodCommitUploadInfoResponse;
use Volc\Service\Vod\Upload\Functions;
use Volc\Service\Vod\Upload\VodUpload;

$client = VodUpload::getInstance();
$client->setAccessKey('your ak');
$client->setSecretKey('your sk');

$space = 'your space';
$filePath = "file path";

$functions = new Functions();
$functions->addGetMetaFunc();
$functions->addSnapshotTimeFunc(2.1);
$functions = $functions->getFunctionsString();

$request = new VodUploadMediaRequest();
$request->setSpaceName($space);
$request->setFilePath($filePath);
$request->setFunctions($functions);
$request->setCallbackArgs("my callback");
$request->setFileName("hello/vod");

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


