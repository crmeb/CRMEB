English | [简体中文](/README-zh-CN.md)


# Alibaba Cloud Credentials for PHP
[![Latest Stable Version](https://poser.pugx.org/alibabacloud/credentials/v/stable)](https://packagist.org/packages/alibabacloud/credentials)
[![composer.lock](https://poser.pugx.org/alibabacloud/credentials/composerlock)](https://packagist.org/packages/alibabacloud/credentials)
[![Total Downloads](https://poser.pugx.org/alibabacloud/credentials/downloads)](https://packagist.org/packages/alibabacloud/credentials)
[![License](https://poser.pugx.org/alibabacloud/credentials/license)](https://packagist.org/packages/alibabacloud/credentials)
[![codecov](https://codecov.io/gh/aliyun/credentials-php/branch/master/graph/badge.svg)](https://codecov.io/gh/aliyun/credentials-php)
[![Travis Build Status](https://travis-ci.org/aliyun/credentials-php.svg?branch=master)](https://travis-ci.org/aliyun/credentials-php)
[![Appveyor Build Status](https://ci.appveyor.com/api/projects/status/6jxpwmhyfipagtge/branch/master?svg=true)](https://ci.appveyor.com/project/aliyun/credentials-php)


![](https://aliyunsdk-pages.alicdn.com/icons/AlibabaCloud.svg)


Alibaba Cloud Credentials for PHP is a tool that helps PHP developers manage their credentials.


## Prerequisites
Your system needs to meet [Prerequisites](/docs/zh-CN/0-Prerequisites.md), including PHP> = 5.6. We strongly recommend using the cURL extension and compiling cURL 7.16.2+ using the TLS backend.


## Installation
If you have [Globally Install Composer](https://getcomposer.org/doc/00-intro.md#globally) on your system, install Alibaba Cloud Credentials for PHP as a dependency by running the following directly in the project directory:
```
composer require alibabacloud/credentials
```
> Some users may not be able to install due to network problems, you can switch to the [Alibaba Cloud Composer Mirror](https://developer.aliyun.com/composer).

See [Installation](/docs/zh-CN/1-Installation.md) for details on installing through Composer and other means.


## Quick Examples
Before you begin, you need to sign up for an Alibaba Cloud account and retrieve your [Credentials](https://usercenter.console.aliyun.com/#/manage/ak).

### Credential Type

#### AccessKey

Setup access_key credential through [User Information Management][ak], it have full authority over the account, please keep it safe. Sometimes for security reasons, you cannot hand over a primary account AccessKey with full access to the developer of a project. You may create a sub-account [RAM Sub-account][ram] , grant its [authorization][permissions]，and use the AccessKey of RAM Sub-account.

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

Create a temporary security credential by applying Temporary Security Credentials (TSC) through the Security Token Service (STS).

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

By specifying [RAM Role][RAM Role], the credential will be able to automatically request maintenance of STS Token. If you want to limit the permissions([How to make a policy][policy]) of STS Token, you can assign value for `Policy`.

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

By specifying the role name, the credential will be able to automatically request maintenance of STS Token.

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

By specifying the public key Id and the private key file, the credential will be able to automatically request maintenance of the AccessKey before sending the request. Only Japan station is supported. 


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

If credential is required by the Cloud Call Centre (CCC), please apply for Bearer Token maintenance by yourself.

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

## Default credential provider chain
The default credential provider chain looks for available credentials, looking in the following order:

### 1. Environmental certificate
The program first looks for environment credentials in the environment variable. If the `ALIBABA_CLOUD_ACCESS_KEY_ID` and `ALIBABA_CLOUD_ACCESS_KEY_SECRET` environment variables are defined and not empty, the program will use them to create default credentials.

### 2. Configuration file
> If the user's home directory has the default file `~/.alibabacloud/credentials` (Windows is `C:\Users\USER_NAME\.alibabacloud\credentials`), the program will automatically create credentials with the specified type and name. The default file may not exist, but parsing errors will throw an exception. The voucher name is not case sensitive. If the voucher has the same name, the latter will overwrite the former. This configuration file can be shared between different projects and tools, and it will not be accidentally submitted to version control because it is outside the project. Environment variables can be referenced to the home directory %UserProfile% on Windows. Unix-like systems can use the environment variable $HOME or ~ (tilde). The path to the default file can be modified by defining the `ALIBABA_CLOUD_CREDENTIALS_FILE` environment variable.

```ini
[default]
type = access_key                  # Authentication method is access_key
access_key_id = foo                # Key
access_key_secret = bar            # Secret

[project1]
type = ecs_ram_role                # Authentication method is ecs_ram_role
role_name = EcsRamRoleTest         # Role name, optional. It will be retrieved automatically if not set. It is highly recommended to set it up to reduce requests.

[project2]
type = ram_role_arn                # Authentication method is ram_role_arn
access_key_id = foo
access_key_secret = bar
role_arn = role_arn
role_session_name = session_name

[project3]
type = rsa_key_pair                # Authentication method is rsa_key_pair
public_key_id = publicKeyId        # Public Key ID
private_key_file = /your/pk.pem    # Private Key File
```

### 3. Instance RAM role
If the environment variable `ALIBABA_CLOUD_ECS_METADATA` is defined and not empty, the program will take the value of the environment variable as the role name and request `http://100.100.100.200/latest/meta-data/ram/security-credentials/` to get the temporary Security credentials are used as default credentials.

### Custom credential provider chain
You can replace the default order of the program chain by customizing the program chain, or you can write the closure to the provider.
```php
<?php

use AlibabaCloud\Credentials\Providers\ChainProvider;

ChainProvider::set(
        ChainProvider::ini(),
        ChainProvider::env(),
        ChainProvider::instance()
);
```


## Documentation
* [Prerequisites](/docs/zh-CN/0-Prerequisites.md)
* [Installation](/docs/zh-CN/1-Installation.md)


## Issue
[Submit Issue](https://github.com/aliyun/credentials-php/issues/new/choose), Problems that do not meet the guidelines may close immediately.


## Release notes
Detailed changes for each version are recorded in the [Release Notes](/CHANGELOG.md).


## Contribution
Please read the [Contribution Guide](/CONTRIBUTING.md) before submitting a Pull Request.


## Related
* [OpenAPI Developer Portal][open-api]
* [Packagist][packagist]
* [Composer][composer]
* [Guzzle Doc][guzzle-docs]
* [Latest Release][latest-release]


## License
[Apache-2.0](/LICENSE.md)

Copyright (c) 2009-present, Alibaba Cloud All rights reserved.


[open-api]: https://next.api.aliyun.com
[latest-release]: https://github.com/aliyun/credentials-php
[guzzle-docs]: http://docs.guzzlephp.org/en/stable/request-options.html
[composer]: https://getcomposer.org
[packagist]: https://packagist.org/packages/alibabacloud/credentials
[home]: https://home.console.aliyun.com
[aliyun]: https://www.aliyun.com
[cURL]: https://www.php.net/manual/en/book.curl.php
[OPCache]: http://php.net/manual/en/book.opcache.php
[xdebug]: http://xdebug.org
[OpenSSL]: http://php.net/manual/en/book.openssl.php
