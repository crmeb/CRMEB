<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo FaceFusionMovieSync\n";

$body = [
    'req_key' => 'facefusionmovie_standard',
    // 'binary_data_base64' => ["xxxx"],
    'image_url' => 'http://xxxx',
    'video_url' => 'http://xxxx',
    'enable_face_beautify' => true,
];
$response = $client->FaceFusionMovieSync(['json' => $body]);
echo $response;