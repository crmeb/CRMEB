<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo ConvertPhotoV2\n";

$body = [
    'req_key' => 'lens_opr',
    'image_urls' => [''],
//    'binary_data_base64' => ['']

];
$response = $client->ConvertPhotoV2(['json' => $body]);
echo $response;

