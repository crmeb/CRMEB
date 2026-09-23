<?php
require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;

// 控制台获取密钥：https://portal.qiniu.com/user/key
$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$bucket = getenv('QINIU_TEST_BUCKET');

$auth = new Auth($accessKey, $secretKey);
$bucketManager = new BucketManager($auth);

// 资源列举
// 参考文档：https://developer.qiniu.com/kodo/api/1284/list

// 要列取文件的公共前缀
$prefix = '';

// 上次列举返回的位置标记，作为本次列举的起点信息。
$marker = '';

// 本次列举的条目数，范围为 1-1000
$limit = 200;

$delimiter = '/';

do {
    list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);
    if ($err !== null) {
        echo "\n====> list file err: \n";
        var_dump($err);
    } else {
        $marker = null;
        if (array_key_exists('marker', $ret)) {
            $marker = $ret['marker'];
        }
        echo "Marker: $marker\n";
        echo "\nList Items====>\n";
        //var_dump($ret['items']);
        print('items count:' . count($ret['items']) . "\n");
        if (array_key_exists('commonPrefixes', $ret)) {
            print_r($ret['commonPrefixes']);
        }
    }
} while (!empty($marker));
