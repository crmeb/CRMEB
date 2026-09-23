<?php
require_once __DIR__ . '/../../autoload.php';

use Qiniu\Auth;
use Qiniu\Rtc\AppClient;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$client = new AppClient($auth);

// app 的唯一标识，创建的时候由系统生成
$appId = 'xxxx';

// 房间名称，需满足规格 ^[a-zA-Z0-9_-]{3,64}$
$roomName = 'room01';

// 请求加入房间的用户 ID，需满足规格 ^[a-zA-Z0-9_-]{3,50}$
$userId = '001';

// 鉴权的有效时间，传入以秒为单位的64位 Unix 绝对时间，token 将在该时间后失效
$expireAt = time()+3600;

// "admin"或"user"，默认为"user" 。当权限角色为"admin"时，拥有将其他用户移除出房间等特权
$permission = 'admin';

// 生成加入房间的令牌 RoomToken
// 参考文档：https://doc.qnsdk.com/rtn/docs/server_overview#1

$RoomToken  = $client->appToken($appId, $roomName, $userId, $expireAt, $permission);
echo "\n====> Create RoomToken Successfully： \n";
var_dump($RoomToken);
