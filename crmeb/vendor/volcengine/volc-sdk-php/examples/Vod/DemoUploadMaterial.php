<?php
require('../../vendor/autoload.php');

use Volc\Service\Vod\Models\Request\VodUploadMaterialRequest;
use Volc\Service\Vod\Models\Response\VodCommitUploadInfoResponse;
use Volc\Service\Vod\Upload\Functions;
use Volc\Service\Vod\Upload\VodUpload;
use Volc\Service\Vod\Upload\OptionInfo;

$client = VodUpload::getInstance();
$client->setAccessKey('your ak');
$client->setSecretKey('your sk');

$space = 'your space';
$filePath = "file path";

//音视频素材上传
$functions = new Functions();
$functions->addGetMetaFunc();
$functions->addSnapshotTimeFunc(2.1);

$optionInfo = new OptionInfo();
$optionInfo->Title = '素材测试视频';
$optionInfo->Tags = 'test';
$optionInfo->Description = '素材测试，视频文件';
$optionInfo->Category = 'video';
$optionInfo->RecordType = 2;
$optionInfo->Format = 'mp4';
$functions->addOptionInfoFunc($optionInfo);

$functions = $functions->getFunctionsString();

$request = new VodUploadMaterialRequest();
$request->setSpaceName($space);
$request->setFilePath($filePath);
$request->setFunctions($functions);
$request->setFileType("media");

$request->setCallbackArgs("my callback");

$response = new VodCommitUploadInfoResponse();
try {
    $response = $client->uploadMaterial($request);
} catch (Exception $e) {
    echo $e, "\n";
} catch (Throwable $e) {
    echo $e, "\n";
}
if ($response->getResponseMetadata() != null && $response->getResponseMetadata()->getError() != null) {
    echo $response->getResponseMetadata()->getError(), "\n";
}
echo $response->serializeToJsonString();
echo "\n";

if ($response->getResult() != null) {
    echo $response->getResult()->getData()->getMid(), "\n";
    echo $response->getResult()->getData()->getSourceInfo()->getWidth(), "\n";
    echo $response->getResult()->getData()->getSourceInfo()->getHeight(), "\n";
}

////图片素材上传
//$functions = new Functions();
//$functions->addGetMetaFunc();
//
//$optionInfo = new OptionInfo();
//$optionInfo->Title = '素材测试图片';
//$optionInfo->Tags = 'test';
//$optionInfo->Description = '素材测试，图片文件';
//$optionInfo->Category = 'image';
//$optionInfo->RecordType = 2;
//$optionInfo->Format = 'jpg';
//$functions->addOptionInfoFunc($optionInfo);
//
//$functions = $functions->getFunctionsString();
//
//$request = new VodUploadMaterialRequest();
//$request->setSpaceName($space);
//$request->setFilePath($filePath);
//$request->setFunctions($functions);
//$request->setFileType("image");
//$request->setFileName("hello/vod/image");
//
//$request->setCallbackArgs("my callback");
//
//$response = new VodCommitUploadInfoResponse();
//try {
//    $response = $client->uploadMaterial($request);
//} catch (Exception $e) {
//    echo $e, "\n";
//} catch (Throwable $e) {
//    echo $e, "\n";
//}
//if ($response->getResponseMetadata() != null && $response->getResponseMetadata()->getError() != null) {
//    echo $response->getResponseMetadata()->getError(), "\n";
//}
//echo $response->serializeToJsonString();
//echo "\n";
//
//if ($response->getResult() != null) {
//    echo $response->getResult()->getData()->getMid(), "\n";
//    echo $response->getResult()->getData()->getSourceInfo()->getWidth(), "\n";
//    echo $response->getResult()->getData()->getSourceInfo()->getHeight(), "\n";
//}

////字幕素材上传
//$functions = new Functions();
//$functions->addGetMetaFunc();
//
//$optionInfo = new OptionInfo();
//$optionInfo->Title = '素材测试字幕';
//$optionInfo->Tags = 'test';
//$optionInfo->Description = '素材测试，字幕文件';
//$optionInfo->Category = 'subtitle';
//$optionInfo->RecordType = 2;
//$optionInfo->Format = 'vtt';
//$functions->addOptionInfoFunc($optionInfo);
//
//$functions = $functions->getFunctionsString();
//
//$request = new VodUploadMaterialRequest();
//$request->setSpaceName($space);
//$request->setFilePath($filePath);
//$request->setFunctions($functions);
//$request->setFileType("object");
//$request->setFileName("hello/vod/object");
//
//$request->setCallbackArgs("my callback");
//
//$response = new VodCommitUploadInfoResponse();
//try {
//    $response = $client->uploadMaterial($request);
//} catch (Exception $e) {
//    echo $e, "\n";
//} catch (Throwable $e) {
//    echo $e, "\n";
//}
//if ($response->getResponseMetadata() != null && $response->getResponseMetadata()->getError() != null) {
//    echo $response->getResponseMetadata()->getError(), "\n";
//}
//echo $response->serializeToJsonString();
//echo "\n";
//
//if ($response->getResult() != null) {
//    echo $response->getResult()->getData()->getMid(), "\n";
//    echo $response->getResult()->getData()->getSourceInfo()->getSize(), "\n";
//}


