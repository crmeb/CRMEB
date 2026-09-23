<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo OverResolutionV2\n";

$body = [
    'req_key' => 'lens_vida_nnsr',
    'image_urls' => [''],
//    'binary_data_base64' => ['']

];
$response = $client->OverResolutionV2(['json' => $body]);
echo $response;

