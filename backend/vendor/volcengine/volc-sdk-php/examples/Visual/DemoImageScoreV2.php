<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo ImageScoreV2\n";

$body = [
    'req_key' => 'lens_vida_single_pic',
    'image_urls' => [''],
//    'binary_data_base64' => [''],
//    'vida_mode' => 'vida_custom',
//    'vida_enable_module' => 'score_total_ds'
];
$response = $client->ImageScoreV2(['json' => $body]);
echo $response;

