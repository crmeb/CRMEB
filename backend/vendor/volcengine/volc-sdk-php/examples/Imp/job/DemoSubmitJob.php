<?php

require('../../../vendor/autoload.php');

use Volc\Service\Imp\Imp;
use Volc\Service\Imp\Models\Business\InputPath;
use Volc\Service\Imp\Models\Request\ImpSubmitJobRequest;
use Volc\Service\Imp\Models\Response\ImpSubmitJobResponse;

// call below method if you don't set ak and sk in $HOME/.vcloud/config
$client = Imp::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$request = new ImpSubmitJobRequest();

$inputPath = new InputPath();
$inputPath->setType("VOD");
$inputPath->setVodSpaceName("your space name");
$inputPath->setFileId("your file id");

$request->setTemplateId("your template id");
$request->setCallbackArgs("your callback args");
$request->setEnableLowPriority("false"); // true 开启 false 不开启 闲时转码
$request->setInputPath($inputPath);

$response = new ImpSubmitJobResponse();
try {
    $response = $client->SubmitJob($request);
} catch (Exception $e) {
    echo $e, "\n";
} catch (Throwable $e) {
    echo $e, "\n";
}
if ($response->getResponseMetadata()->getError() != null) {
    print_r($response->getResponseMetadata()->getError());
}

echo $response->serializeToJsonString();
echo "\n";
