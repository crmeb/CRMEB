<?php

require('../../../vendor/autoload.php');

use Volc\Service\Imp\Models\Request\ImpKillJobRequest;
use Volc\Service\Imp\Models\Response\ImpKillJobResponse;
use Volc\Service\Imp\Imp;

// call below method if you don't set ak and sk in $HOME/.vcloud/config
$client = Imp::getInstance();
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

$request = new ImpKillJobRequest();

$request->setJobId("your job id");

$response = new ImpKillJobResponse();
try {
    $response = $client->KillJob($request);
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
