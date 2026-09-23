<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Processing\PersistentFop;

$pfop = new Qiniu\Processing\PersistentFop(null, null);

// 通过 persistentId 来主动查询持久化处理(prefop)的执行状态
// 参考文档：https://developer.qiniu.com/dora/api/1294/persistent-processing-status-query-prefop

// 触发持久化处理后返回的 persistentId
$persistentId = 'z2.01z201c4oyre6q1hgy00murnel0002nh';

list($ret, $err) = $pfop->status($persistentId);
if ($err != null) {
    var_dump($err);
} else {
    var_dump($ret);
}
