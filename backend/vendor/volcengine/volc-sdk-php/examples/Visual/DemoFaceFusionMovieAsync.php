<?php
require('../../../vendor/autoload.php');
use Volc\Service\Visual;

$client = Visual::getInstance();
// call below method if you dont set ak and sk in ～/.volc/config

$client->setAccessKey($ak);
$client->setSecretKey($sk);

echo "\nDemo FaceFusionMovieSubmitTask\n";

$body = [
    'req_key' => 'facefusionmovie_standard',
    'image_url' => 'https://xxxx',
    'video_url' => 'https://xxxx',
];
$response = $client->FaceFusionMovieSubmitTask(['json' => $body]);
echo $response;


// echo "\nDemo FaceFusionMovieGetResult\n";

// $body = [
//     'req_key' => 'facefusionmovie_standard',
//     'task_id' => 'xxxx',
// ];
// $response = $client->FaceFusionMovieGetResult(['json' => $body]);
// echo $response;