<?php

require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\ArgusManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');


$auth = new Auth($accessKey, $secretKey);

$config = new Config();
$argusManager = new ArgusManager($auth, $config);

// 图片内容审核
// 参考文档：https://developer.qiniu.com/censor/api/5588/image-censor

$body = '{
    "data":{
        "uri":"https://www-static.qbox.me/_next/static/media/pic01.c8b5db2cc5fcb6fc4e6349c9e37c8e6b.png"
    },
    "params":{
        "scenes":[
            "pulp",
            "terror",
            "politician",
            "ads"
        ]
    }
}';

list($ret, $err) = $argusManager->censorImage($body);
echo "\n====> Result is: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
