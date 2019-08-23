<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Processing\PersistentFop;

// 触发持久化处理后返回的 Id
$persistentId = 'z0.564d5f977823de48a85ece59';

// 通过persistentId查询该 触发持久化处理的状态
$status = PersistentFop::status($persistentId);

var_dump($status);
