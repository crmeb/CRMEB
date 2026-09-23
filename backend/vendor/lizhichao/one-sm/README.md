## php国密算法

- sm3
    - 字符串签名
    - 文件签名
- sm4
    - ecb
    - cbc
    - cfb
    - ofb
    - ctr

## 安装

```shell 
composer require lizhichao/one-sm
``` 

## SM3签名
```php
<?php
require __DIR__ . '/vendor/autoload.php';

$sm3 = new \OneSm\Sm3();

// 字符串签名
echo $sm3->sign('abc') . PHP_EOL;
echo $sm3->sign(str_repeat("adfas哈哈哈", 100)) . PHP_EOL;


// 文件签名
echo $sm3->signFile(__FILE__) . PHP_EOL;
```
### 性能测试
和 [openssl](https://github.com/openssl/openssl) , [SM3-PHP](https://github.com/DongyunLee/SM3-PHP) 性能测试

```shell
php bench.php
```
结果
```
openssl:4901d7181a1024b8c0f59b8d3c5c6d96b4b707ad10e8ebc8ece5dc49364a3067
one-sm3:4901d7181a1024b8c0f59b8d3c5c6d96b4b707ad10e8ebc8ece5dc49364a3067
SM3-PHP:4901d7181a1024b8c0f59b8d3c5c6d96b4b707ad10e8ebc8ece5dc49364a3067
openssl time:6.3741207122803ms
one-sm3 time:8.1770420074463ms
SM3-PHP time:1738.5928630829ms

```
[测试代码bench.php](https://github.com/lizhichao/sm/blob/master/bench.php)


## SM4加密

```php
<?php
use OneSm\Sm4;
require __DIR__ . '/vendor/autoload.php';

$data = str_repeat('阿斯顿发到付eeee', 160);
$str_len = strlen($data);

// md5 签名
$sign = md5($data);

// 加密key必须为16位
$key = hex2bin(md5(1));
$sm4 = new Sm4($key);

// ECB加密
$d = $sm4->enDataEcb($data);
// 加密后的长度和原数据长度一致
var_dump(strlen($d) === $str_len);

// ECB解密
$d = $sm4->deDataEcb($d);
// 解密后和原数据相等
var_dump(md5($d) === $sign);


// 初始化向量16位
$iv = hex2bin(md5(2));
// CBC加密
$d = $sm4->enDataCbc($data, $iv);
// 加密后的长度和原数据长度一致
var_dump(strlen($d)===$str_len);

// CBC解密
$d = $sm4->deDataCbc($d, $iv);
// 解密后和原数据相等
var_dump(md5($d)===$sign);

```

## 我的其他仓库

* [一个极简高性能php框架，支持[swoole | php-fpm ]环境](https://github.com/lizhichao/one)
* [clickhouse tcp 客户端](https://github.com/lizhichao/one-ck)
* [中文分词](https://github.com/lizhichao/VicWord)
* [nsq客户端](https://github.com/lizhichao/one-nsq)
