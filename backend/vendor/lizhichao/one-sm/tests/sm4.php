<?php
require __DIR__ . '/../vendor/autoload.php';
function eq($a, $b)
{
    if ($a !== $b) {
        print_r([$a, '!==', $b]);
        throw new \Exception('error');
    }
    print_r([$a, $b, 'true']);
}


$data = str_repeat('阿斯顿发到付eeee', 160);
$str_len = strlen($data);
// md5 签名
$sign = md5($data);
// 加密key必须为16位
$key = hex2bin(md5(1));
$iv = hex2bin(md5(2));




$sm4 = new \OneSm\Sm4($key);

echo "\n --- ecb --- \n";
// 加密ecb
$d = $sm4->enDataEcb($data);
// 加密后的长度和原数据长度一致
eq(strlen($d), $str_len);
// 解密ecb
$d = $sm4->deDataEcb($d);
// 解密后和原数据相等
eq(md5($d), $sign);

echo "\n --- cbc --- \n";
// 加密cbc
$d = $sm4->enDataCbc($data, $iv);
// 加密后的长度和原数据长度一致
eq(strlen($d), $str_len);
// 解密cbc
$d = $sm4->deDataCbc($d, $iv);
// 解密后和原数据相等
eq(md5($d), $sign);

echo "\n --- ofb --- \n";
// 加密ofb
$d = $sm4->enDataOfb($data, $iv);
// 加密后的长度和原数据长度一致
eq(strlen($d), $str_len);
// 解密ofb
$d = $sm4->deDataOfb($d, $iv);
// 解密后和原数据相等
eq(md5($d), $sign);

echo "\n --- cfb --- \n";

// 加密cfb
$d = $sm4->enDatacfb($data, $iv);
// 加密后的长度和原数据长度一致
eq(strlen($d), $str_len);
// 解密cfb
$d = $sm4->deDatacfb($d, $iv);
// 解密后和原数据相等
eq(md5($d), $sign);


echo "\n --- ctr --- \n";

// 加密ctr
$d = $sm4->enDataCtr($data, $iv);
// 加密后的长度和原数据长度一致
eq(strlen($d), $str_len);
// 解密ctr
$d = $sm4->deDataCtr($d, $iv);
// 解密后和原数据相等
eq(md5($d), $sign);

//ecb/cbc/cfb/ofb/ctr