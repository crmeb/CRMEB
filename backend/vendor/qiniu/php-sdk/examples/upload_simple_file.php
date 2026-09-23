<?php
require_once __DIR__ . '/../autoload.php';

// 引入鉴权类
use Qiniu\Auth;

// 引入上传类
use Qiniu\Storage\UploadManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');

// 构建鉴权对象
$auth = new Auth($accessKey, $secretKey);

// 生成上传 Token
$token = $auth->uploadToken($bucket);

// 要上传文件的本地路径
$filePath = './php-logo.png';

// 上传到七牛存储后保存的文件名
$key = 'my-php-logo.png';

// 初始化 UploadManager 对象并进行文件的上传。
$uploadMgr = new UploadManager();

// 调用 UploadManager 的 putFile 方法进行文件的上传，该方法会判断文件大小，进而决定使用表单上传还是分片上传，无需手动配置。
list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
echo "\n====> putFile result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
