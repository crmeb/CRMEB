<?php
require('../../vendor/autoload.php');

use Volc\Service\BusinessSecurity;
$client = BusinessSecurity::getInstance();

// call below method if you dont set ak and sk in ～/.volc/config
$client->setAccessKey("ak");
$client->setSecretKey("sk");

$response = $client->RiskDetect(1, "chat", "{\"uid\":123411, \"operate_time\":1609818934, \"chat_text\":\"a😊\"}");
echo $response;

$response = $client->ElementVerify(5461, "idcard_two_element_verify", "{\"operate_time\":1609818934, \"idcard_no\": \"\", \"idcard_name\":\"\"}");
echo $response;

$response = $client->MobileStatus(5461, "mobile_empty_status", "{\"operate_time\":1609818934, \"mobile\": \"\"}");
echo $response;

$response = $client->ImageContentRisk(5461, "image_content_risk", "{\"operate_time\": 1617960951, \"ip\": \"127.0.0.9\", \"did\":1357924680, \"data_id\": \"wangyifan\", \"account_id\": \"212910\", \"type\":1,\"url\":\"待检测图片\", \"label\": \"301,302,305,351,303,310\"}");
echo $response;

$response = $client->AsyncImageRisk(5461, "image_content_risk", "{\"operate_time\": 1617960951, \"ip\": \"127.0.0.9\", \"did\":1357924680, \"data_id\": \"w\", \"account_id\": \"212910\", \"type\":1,\"url\":\"待检测图片\", \"label\": \"301,302,305,351,303,310\"}");
echo $response;

$response = $client->GetImageResult(5461, "image_content_risk", "w");
echo $response;

$response = $client->TextRisk(5461, "text_risk",  "{\"operate_time\": 1652154752, \"text\": \"待检测文本\", \"did\":1357924680, \"account_id\": \"123\"}");
echo $response;

$response = $client->GetAudioResult(334361, "audio_risk",  "70");
echo $response;

$response = $client->TextSliceRisk(415493, "text_risk",  "{}");
echo $response;