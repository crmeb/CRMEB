# Qiniu Cloud SDK for PHP
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Build Status](https://travis-ci.org/qiniu/php-sdk.svg)](https://travis-ci.org/qiniu/php-sdk)
[![GitHub release](https://img.shields.io/github/v/tag/qiniu/php-sdk.svg?label=release)](https://github.com/qiniu/php-sdk/releases)
[![Latest Stable Version](https://img.shields.io/packagist/v/qiniu/php-sdk.svg)](https://packagist.org/packages/qiniu/php-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/qiniu/php-sdk.svg)](https://packagist.org/packages/qiniu/php-sdk)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/qiniu/php-sdk/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/qiniu/php-sdk/?branch=master)
[![Coverage Status](https://codecov.io/gh/qiniu/php-sdk/branch/master/graph/badge.svg)](https://codecov.io/gh/qiniu/php-sdk)
[![Join Chat](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/qiniu/php-sdk?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![@qiniu on weibo](http://img.shields.io/badge/weibo-%40qiniutek-blue.svg)](http://weibo.com/qiniutek)


## 安装

推荐使用 `composer` 进行安装。可以使用 composer.json 声明依赖，或者运行下面的命令。SDK 包已经放到这里 [`qiniu/php-sdk`][install-packagist] 。

```bash
$ composer require qiniu/php-sdk
```

## 运行环境

| Qiniu SDK版本 |                     PHP 版本                      |
|:--------------------:|:-----------------------------------------------:|
|          7.x         | cURL extension,   5.3 - 5.6, 7.0 - 7.4, 8.0-8.1 |
|          6.x         |           cURL extension,   5.2 - 5.6           |

## 使用方法

### 上传
```php
use Qiniu\Storage\UploadManager;
use Qiniu\Auth;
...
    $uploadMgr = new UploadManager();
    $auth = new Auth($accessKey, $secretKey);
    $token = $auth->uploadToken($bucket);
    list($ret, $error) = $uploadMgr->putFile($token, 'key', 'filePath');
...
```

## 测试

``` bash
$ ./vendor/bin/phpunit tests/Qiniu/Tests/
```

## 常见问题

- `$error` 保留了请求响应的信息，失败情况下 `ret` 为 `none`, 将 `$error` 可以打印出来，提交给我们。
- API 的使用 demo 可以参考 [examples](https://github.com/qiniu/php-sdk/tree/master/examples)。

## 代码贡献

详情参考[代码提交指南](https://github.com/qiniu/php-sdk/blob/master/CONTRIBUTING.md)。

## 贡献记录

- [所有贡献者](https://github.com/qiniu/php-sdk/contributors)

## 联系我们

- 如果需要帮助，请提交工单（在portal右侧点击咨询和建议提交工单，或者直接向 support@qiniu.com 发送邮件）
- 如果有什么问题，可以到问答社区提问，[问答社区](https://qiniu.segmentfault.com/)
- 更详细的文档，见[官方文档站](https://developer.qiniu.com/)
- 如果发现了 bug， 欢迎提交 [issue](https://github.com/qiniu/php-sdk/issues)
- 如果有功能需求，欢迎提交 [issue](https://github.com/qiniu/php-sdk/issues)
- 如果要提交代码，欢迎提交 pull request
- 欢迎关注我们的[微信](https://www.qiniu.com/#weixin) [微博](https://weibo.com/qiniutek)，及时获取动态信息。

## 代码许可

The MIT License (MIT).详情见 [License文件](https://github.com/qiniu/php-sdk/blob/master/LICENSE).

[packagist]: http://packagist.org
[install-packagist]: https://packagist.org/packages/qiniu/php-sdk
