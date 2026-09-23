<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Rtc\AppClient;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new AppClient($auth);

// app 的唯一标识，创建的时候由系统生成。
$appId = "xxxx";

// 绑定的直播hub，使用此hub的资源进行推流等业务功能，hub与app必须属于同一个七牛账户
$hub = 'rtchub';

// app 的名称，注意：Title 不是唯一标识，重复 create 动作将生成多个 app
$title = 'rtc';

// 连麦房间支持的最大在线人数
$maxUsers = 30;

// 连麦合流转推 RTMP 的配置
$mergePublishRtmp = array();
$mergePublishRtmp['enable'] = true;

// 更新一个应用的配置信息
// 注意：调用这个接口后仅对调用后新创建的房间有效，已经存在的房间需要等待被关闭重新创建后生效
// 参考文档：https://doc.qnsdk.com/rtn/docs/server_overview#2_1

list($ret, $err)  = $client->updateApp($appId, $hub, $title, $maxUsers, false, $mergePublishRtmp);
if ($err !== null) {
    var_dump($err);
} else {
    echo "\n====> Update $appId Conf Successfully： \n";
    var_dump($ret);
}
