<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;

// 后台来获取AK, SK
$accessKey = 'Access_Key';
$secretKey = 'Secret_Key';

//生成EncodedEntryURI的值
$entry = '<bucket>:<Key>';//<Key>为生成缩略图的文件名
//生成的值
$encodedEntryURI = \Qiniu\base64_urlSafeEncode($entry);

//使用SecretKey对新的下载URL进行HMAC1-SHA1签名
$newurl = "78re52.com1.z0.glb.clouddn.com/resource/Ship.jpg?imageView2/2/w/200/h/200|saveas/" . $encodedEntryURI;

$sign = hash_hmac("sha1", $newurl, $secretKey, true);

//对签名进行URL安全的Base64编码
$encodedSign = \Qiniu\base64_urlSafeEncode($sign);
//最终得到的完整下载URL
$finalURL = "http://" . $newurl . "/sign/" . $accessKey . ":" . $encodedSign;

$callbackBody = file_get_contents("$finalURL");

echo $callbackBody;
