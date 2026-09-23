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

// 视频内容审核
// 参考文档：https://developer.qiniu.com/censor/api/5620/video-censor

$body = '{
    "data":{
        "uri":"https://dn-mars-assets.qbox.me/Fi1UC6waXtXYCpnTGHa8XxIziGNk"
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

list($jobid, $err) = $argusManager->censorVideo($body);
echo "\n====> Result is: \n";
if ($err !== null) {
    var_dump($err);
} else {
    echo "job_id is: $jobid\n";
}

// 查询视频审核结果
list($ret, $err) = $argusManager->censorStatus($jobid);
echo "\n====> job status: \n";

if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
