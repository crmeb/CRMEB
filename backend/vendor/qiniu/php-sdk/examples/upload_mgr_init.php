<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$auth = new Auth($accessKey, $secretKey);

// 存储空间名称
$bucket = 'Bucket_Name';

// 生成上传 Token
$token = $auth->uploadToken($bucket);

// 构建 UploadManager 对象
$uploadMgr = new UploadManager();
