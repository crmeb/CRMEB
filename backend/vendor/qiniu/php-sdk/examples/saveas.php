<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

// 处理结果另存为
// 参考文档：https://developer.qiniu.com/dora/api/1305/processing-results-save-saveas

// 生成EncodedEntryURI的值，<Key>为生成缩略图的文件名
$entry = '<bucket>:<Key>';

// 生成的值
$encodedEntryURI = \Qiniu\base64_urlSafeEncode($entry);

// 使用 SecretKey 对新的下载 URL 进行 HMAC1-SHA1 签名
$newurl = "78re52.com1.z0.glb.clouddn.com/resource/Ship.jpg?imageView2/2/w/200/h/200|saveas/" . $encodedEntryURI;

$sign = hash_hmac("sha1", $newurl, $secretKey, true);

// 对签名进行 URL 安全的 Base64 编码
$encodedSign = \Qiniu\base64_urlSafeEncode($sign);

// 最终得到的完整下载 URL
$finalURL = "http://" . $newurl . "/sign/" . $accessKey . ":" . $encodedSign;

$callbackBody = file_get_contents("$finalURL");

echo $callbackBody;
