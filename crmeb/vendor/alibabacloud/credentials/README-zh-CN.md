[English](/README.md) | 简体中文


# Alibaba Cloud Credentials for PHP
[![Latest Stable Version](https://poser.pugx.org/alibabacloud/credentials/v/stable)](https://packagist.org/packages/alibabacloud/credentials)
[![composer.lock](https://poser.pugx.org/alibabacloud/credentials/composerlock)](https://packagist.org/packages/alibabacloud/credentials)
[![Total Downloads](https://poser.pugx.org/alibabacloud/credentials/downloads)](https://packagist.org/packages/alibabacloud/credentials)
[![License](https://poser.pugx.org/alibabacloud/credentials/license)](https://packagist.org/packages/alibabacloud/credentials)
[![codecov](https://codecov.io/gh/aliyun/credentials-php/branch/master/graph/badge.svg)](https://codecov.io/gh/aliyun/credentials-php)
[![Travis Build Status](https://travis-ci.org/aliyun/credentials-php.svg?branch=master)](https://travis-ci.org/aliyun/credentials-php)
[![Appveyor Build Status](https://ci.appveyor.com/api/projects/status/6jxpwmhyfipagtge/branch/master?svg=true)](https://ci.appveyor.com/project/aliyun/credentials-php)


![](https://aliyunsdk-pages.alicdn.com/icons/AlibabaCloud.svg)


Alibaba Cloud Credentials for PHP 是帮助 PHP 开发者管理凭据的工具。


## 先决条件
您的系统需要满足[先决条件](/docs/zh-CN/0-Prerequisites.md)，包括 PHP> = 5.6。 我们强烈建议使用cURL扩展，并使用TLS后端编译cURL 7.16.2+。


## 安装依赖
如果已在系统上[全局安装 Composer](https://getcomposer.org/doc/00-intro.md#globally)，请直接在项目目录中运行以下内容来安装 Alibaba Cloud Credentials for PHP 作为依赖项：
```
composer require alibabacloud/credentials
```
> 一些用户可能由于网络问题无法安装，可以使用[阿里云 Composer 全量镜像](https://developer.aliyun.com/composer)。

请看[安装](/docs/zh-CN/1-Installation.md)有关通过 Composer 和其他方式安装的详细信息。


## 快速使用
在您开始之前，您需要注册阿里云帐户并获取您的[凭证](https://usercenter.console.aliyun.com/#/manage/ak)。

### 凭证类型

#### AccessKey

通过[用户信息管理][ak]设置 access_key，它们具有该账户完全的权限，请妥善保管。有时出于安全考虑，您不能把具有完全访问权限的主账户 AccessKey 交于一个项目的开发者使用，您可以[创建RAM子账户][ram]并为子账户[授权][permissions]，使用RAM子用户的 AccessKey 来进行API调用。

```php
<?php

use AlibabaCloud\Credentials\Credential;

// Chain Provider if no Parameter
$credential = new Credential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();

// Access Key
$ak = new Credential([
    'type'              => 'access_key',
    'access_key_id'     => '<access_key_id>',
    'access_key_secret' => '<access_key_secret>',
]);
$ak->getAccessKeyId();
$ak->getAccessKeySecret();
```

#### STS

通过安全令牌服务（Security Token Service，简称 STS），申请临时安全凭证（Temporary Security Credentials，简称 TSC），创建临时安全凭证。

```php
<?php

use AlibabaCloud\Credentials\Credential;

$sts = new Credential([
    'type'             => 'sts',
    'access_key_id'    => '<access_key_id>',
    'accessKey_secret' => '<accessKey_secret>',
    'security_token'   => '<security_token>',
]);
$sts->getAccessKeyId();
$sts->getAccessKeySecret();
$sts->getSecurityToken();
```

#### RamRoleArn

通过指定[RAM角色][RAM Role]，让凭证自动申请维护 STS Token。你可以通过为 `Policy` 赋值来限制获取到的 STS Token 的权限。

```php
<?php

use AlibabaCloud\Credentials\Credential;

$ramRoleArn = new Credential([
    'type'              => 'ram_role_arn',
    'access_key_id'     => '<access_key_id>',
    'access_key_secret' => '<access_key_secret>',
    'role_arn'          => '<role_arn>',
    'role_session_name' => '<role_session_name>',
    'policy'            => '',
]);
$ramRoleArn->getAccessKeyId();
$ramRoleArn->getAccessKeySecret();
$ramRoleArn->getRoleArn();
$ramRoleArn->getRoleSessionName();
$ramRoleArn->getPolicy();
```

#### EcsRamRole

通过指定角色名称，让凭证自动申请维护 STS Token

```php
<?php

use AlibabaCloud\Credentials\Credential;

$ecsRamRole = new Credential([
    'type'      => 'ecs_ram_role',
    'role_name' => '<role_name>',
]);
$ecsRamRole->getRoleName();
// Note: `role_name` is optional. It will be retrieved automatically if not set. It is highly recommended to set it up to reduce requests.
```

#### RsaKeyPair

通过指定公钥Id和私钥文件，让凭证自动申请维护 AccessKey。仅支持日本站。

```php
<?php

use AlibabaCloud\Credentials\Credential;

$rsaKeyPair = new Credential([
    'type'             => 'rsa_key_pair',
    'public_key_id'    => '<public_key_id>',
    'private_key_file' => '<private_key_file>',
]);
$rsaKeyPair->getPublicKeyId();
$rsaKeyPair->getPrivateKey();
```

#### Bearer Token

如呼叫中心(CCC)需用此凭证，请自行申请维护 Bearer Token。

```php
<?php

use AlibabaCloud\Credentials\Credential;

$bearerToken = new Credential([
    'type'         => 'bearer_token',
    'bearer_token' => '<bearer_token>',
]);
$bearerToken->getBearerToken();
$bearerToken->getSignature();
```

## 默认凭证提供程序链
默认凭证提供程序链查找可用的凭证，寻找顺序如下：

### 1. 环境凭证
程序首先会在环境变量里寻找环境凭证，如果定义了 `ALIBABA_CLOUD_ACCESS_KEY_ID`  和 `ALIBABA_CLOUD_ACCESS_KEY_SECRET` 环境变量且不为空，程序将使用他们创建默认凭证。

### 2. 配置文件
> 如果用户主目录存在默认文件 `~/.alibabacloud/credentials` （Windows 为 `C:\Users\USER_NAME\.alibabacloud\credentials`），程序会自动创建指定类型和名称的凭证。默认文件可以不存在，但解析错误会抛出异常。  凭证名称不分大小写，若凭证同名，后者会覆盖前者。不同的项目、工具之间可以共用这个配置文件，因为超出项目之外，也不会被意外提交到版本控制。Windows 上可以使用环境变量引用到主目录 %UserProfile%。类 Unix 的系统可以使用环境变量 $HOME 或 ~ (tilde)。 可以通过定义 `ALIBABA_CLOUD_CREDENTIALS_FILE` 环境变量修改默认文件的路径。

```ini
[default]
type = access_key                  # 认证方式为 access_key
access_key_id = foo                # Key
access_key_secret = bar            # Secret

[project1]
type = ecs_ram_role                # 认证方式为 ecs_ram_role
role_name = EcsRamRoleTest         # Role Name，非必填，不填则自动获取，建议设置，可以减少网络请求。

[project2]
type = ram_role_arn                # 认证方式为 ram_role_arn
access_key_id = foo
access_key_secret = bar
role_arn = role_arn
role_session_name = session_name

[project3]
type = rsa_key_pair                # 认证方式为 rsa_key_pair
public_key_id = publicKeyId        # Public Key ID
private_key_file = /your/pk.pem    # Private Key 文件
```

### 3. 实例 RAM 角色
如果定义了环境变量 `ALIBABA_CLOUD_ECS_METADATA` 且不为空，程序会将该环境变量的值作为角色名称，请求 `http://100.100.100.200/latest/meta-data/ram/security-credentials/` 获取临时安全凭证作为默认凭证。

### 自定义凭证提供程序链
可通过自定义程序链代替默认程序链的寻找顺序，也可以自行编写闭包传入提供者。
```php
<?php

use AlibabaCloud\Credentials\Providers\ChainProvider;

ChainProvider::set(
        ChainProvider::ini(),
        ChainProvider::env(),
        ChainProvider::instance()
);
```


## 文档
* [先决条件](/docs/zh-CN/0-Prerequisites.md)
* [安装](/docs/zh-CN/1-Installation.md)


## 问题
[提交 Issue](https://github.com/aliyun/credentials-php/issues/new/choose)，不符合指南的问题可能会立即关闭。


## 发行说明
每个版本的详细更改记录在[发行说明](/CHANGELOG.md)中。


## 贡献
提交 Pull Request 之前请阅读[贡献指南](/CONTRIBUTING.md)。


## 相关
* [OpenAPI 开发者门户][open-api]
* [Packagist][packagist]
* [Composer][composer]
* [Guzzle中文文档][guzzle-docs]
* [最新源码][latest-release]


## 许可证
[Apache-2.0](/LICENSE.md)

Copyright (c) 2009-present, Alibaba Cloud All rights reserved.


[open-api]: https://next.api.aliyun.com
[latest-release]: https://github.com/aliyun/credentials-php
[guzzle-docs]: https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html
[composer]: https://getcomposer.org
[packagist]: https://packagist.org/packages/alibabacloud/credentials
[home]: https://home.console.aliyun.com
[aliyun]: https://www.aliyun.com
[cURL]: http://php.net/manual/zh/book.curl.php
[OPCache]: http://php.net/manual/zh/book.opcache.php
[xdebug]: http://xdebug.org
[OpenSSL]: http://php.net/manual/zh/book.openssl.php
