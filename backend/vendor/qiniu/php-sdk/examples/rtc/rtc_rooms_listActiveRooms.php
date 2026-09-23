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

// 所查询房间名的前缀索引，可以为空
$prefix = null;

// 分页查询的位移标记
$offset = null;

// 此次查询的最大长度
$limit = 100;

// 获取当前所有活跃的房间
// 参考文档：https://doc.qnsdk.com/rtn/docs/server_overview#2_2

list($ret, $err)  = $client->listActiveRooms($appId, $prefix, $offset, $limit);
if ($err !== null) {
    var_dump($err);
} else {
    echo "\n====> Active Rooms:\n";
    var_dump($ret);
}
