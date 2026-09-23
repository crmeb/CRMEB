<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');

$auth = new Auth($accessKey, $secretKey);
$config = new Config();
$bucketManager = new BucketManager($auth, $config);

// 存储空间 - 新增生命周期规则
// 参考文档：https://developer.qiniu.com/kodo/manual/3699/life-cycle-management

$bucket = 'xxxx'; // 存储空间名称
$name = 'demo';   // 生命周期规则名称
$prefix = 'test'; // 规则策略中的前缀
$delete_after_days = 80; // 用户新创建的文件将在该设定时间之后自动删除
$to_line_after_days = 70; // 用户新创建的文件将在该设定的时间之后自动转为低频存储
$to_archive_ir_after_days = 71; // 用户新创建的文件将在该设定的时间之后自动转为归档直读存储
$to_archive_after_days = 72; // 用户新创建的文件将在该设定的时间之后自动转为归档存储
$to_deep_archive_after_days = 74; // 用户新创建的文件将在该设定的时间之后自动转为深度归档存储

list($ret, $err) = $bucketManager->bucketLifecycleRule(
    $bucket,
    $name,
    $prefix,
    $delete_after_days,
    $to_line_after_days,
    $to_archive_after_days,
    $to_deep_archive_after_days,
    $to_archive_ir_after_days
);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
