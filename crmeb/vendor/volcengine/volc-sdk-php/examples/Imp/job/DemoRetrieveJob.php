<?php

require('../../../vendor/autoload.php');

use Volc\Service\Imp\Models\Request\ImpRetrieveJobRequest;
use Volc\Service\Imp\Models\Response\ImpRetrieveJobResponse;
use Volc\Service\Imp\Imp;

// call below method if you don't set ak and sk in $HOME/.vcloud/config
$client = Imp::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$request = new ImpRetrieveJobRequest();

$jobIds = ["your job id 1", "your job id 2"];
$request->setJobIds($jobIds);

$response = new ImpRetrieveJobResponse();
try {
    $response = $client->RetrieveJob($request);
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
