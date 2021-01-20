<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Processing\PersistentFop;

$pfop = new Qiniu\Processing\PersistentFop(null, null);

// 触发持久化处理后返回的 Id
$persistentId = 'z1.5b8a48e5856db843bc24cfc3';

// 通过persistentId查询该 触发持久化处理的状态
list($ret, $err) = $pfop->status($persistentId);

if ($err) {
    print_r($err);
} else {
    print_r($ret);
}
