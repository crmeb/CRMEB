<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

// 构建 Auth 对象
$auth = new Auth($accessKey, $secretKey);

// 私有空间中的外链 http(s)://<domain>/<file_key>，一定要带访问协议，也就是 http:// 或者 https://
$baseUrl = 'http://if-pri.qiniudn.com/qiniu.png?imageView2/1/h/500';

// 对链接进行签名，参考文档：https://developer.qiniu.com/kodo/manual/1656/download-private
$signedUrl = $auth->privateDownloadUrl($baseUrl);

echo $signedUrl;
