[English](/README.md) | 简体中文

![](https://aliyunsdk-pages.alicdn.com/icons/AlibabaCloud.svg)

# Alibaba Cloud Credentials for PHP

[![PHP CI](https://github.com/aliyun/credentials-php/actions/workflows/ci.yml/badge.svg)](https://github.com/aliyun/credentials-php/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/aliyun/credentials-php/graph/badge.svg?token=YIkSjtfKbB)](https://codecov.io/gh/aliyun/credentials-php)
[![Latest Stable Version](https://poser.pugx.org/alibabacloud/credentials/v/stable)](https://packagist.org/packages/alibabacloud/credentials)
[![composer.lock](https://poser.pugx.org/alibabacloud/credentials/composerlock)](https://packagist.org/packages/alibabacloud/credentials)
[![Total Downloads](https://poser.pugx.org/alibabacloud/credentials/downloads)](https://packagist.org/packages/alibabacloud/credentials)
[![License](https://poser.pugx.org/alibabacloud/credentials/license)](https://packagist.org/packages/alibabacloud/credentials)

Alibaba Cloud Credentials for PHP 是帮助 PHP 开发者管理凭据的工具。

## 先决条件

您的系统需要满足[先决条件](/docs/zh-CN/0-Prerequisites.md)，包括 PHP> = 5.6。 我们强烈建议使用cURL扩展，并使用TLS后端编译cURL 7.16.2+。

## 安装依赖

如果已在系统上[全局安装 Composer](https://getcomposer.org/doc/00-intro.md#globally)，请直接在项目目录中运行以下内容来安装 Alibaba Cloud Credentials for PHP 作为依赖项：

```sh
composer require alibabacloud/credentials
```

> 一些用户可能由于网络问题无法安装，可以使用[阿里云 Composer 全量镜像](https://developer.aliyun.com/composer)。

请看[安装](/docs/zh-CN/1-Installation.md)有关通过 Composer 和其他方式安装的详细信息。

## 快速使用

在您开始之前，您需要注册阿里云帐户并获取您的[凭证](https://usercenter.console.aliyun.com/#/manage/ak)。

### 凭证类型

#### 使用默认凭据链
当您在初始化凭据客户端不传入任何参数时，Credentials工具会使用默认凭据链方式初始化客户端。默认凭据的读取逻辑请参见[默认凭据链](#默认凭证提供程序链)。

```php
<?php

use AlibabaCloud\Credentials\Credential;

// Chain Provider if no Parameter
$client = new Credential();

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
$credential->getSecurityToken();
```

#### AccessKey

通过[用户信息管理][ak]设置 access_key，它们具有该账户完全的权限，请妥善保管。有时出于安全考虑，您不能把具有完全访问权限的主账户 AccessKey 交于一个项目的开发者使用，您可以[创建RAM子账户][ram]并为子账户[授权][permissions]，使用RAM子用户的 AccessKey 来进行API调用。

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

// Access Key
$config = new Config([
    'type'            => 'access_key',
    'accessKeyId'     => '<access_key_id>',
    'accessKeySecret' => '<access_key_secret>',
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
```

#### STS

通过安全令牌服务（Security Token Service，简称 STS），申请临时安全凭证（Temporary Security Credentials，简称 TSC），创建临时安全凭证。

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

$config = new Config([
    'type'            => 'sts',
    'accessKeyId'     => '<access_key_id>',
    'accessKeySecret' => '<access_key_secret>',
    'securityToken'   => '<security_token>',
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
$credential->getSecurityToken();
```

#### RamRoleArn

通过指定RAM角色的ARN（Alibabacloud Resource Name），Credentials工具可以帮助开发者前往STS换取STS Token。您也可以通过为 `Policy` 赋值来限制RAM角色到一个更小的权限集合。

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

$config = new Config([
    'type'                  => 'ram_role_arn',
    'accessKeyId'           => '<access_key_id>',
    'accessKeySecret'       => '<access_key_secret>',
    // 要扮演的RAM角色ARN，示例值：acs:ram::123456789012****:role/adminrole，可以通过环境变量ALIBABA_CLOUD_ROLE_ARN设置role_arn
    'roleArn'               => '<role_arn>',
    // 角色会话名称，可以通过环境变量ALIBABA_CLOUD_ROLE_SESSION_NAME设置role_session_name
    'roleSessionName'       => '<role_session_name>',
    // 设置更小的权限策略，非必填。示例值：{"Statement": [{"Action": ["*"],"Effect": "Allow","Resource": ["*"]}],"Version":"1"}
    'policy'                => '',
    // 设置session过期时间，非必填。
    'roleSessionExpiration' => 3600,
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
$credential->getSecurityToken();
```

#### EcsRamRole

ECS和ECI实例均支持绑定实例RAM角色，当在实例中使用Credentials工具时，将自动获取实例绑定的RAM角色，并通过访问元数据服务获取RAM角色的STS Token，以完成凭据客户端的初始化。

实例元数据服务器支持加固模式和普通模式两种访问方式，Credentials工具默认使用加固模式（IMDSv2）获取访问凭据。若使用加固模式时发生异常，您可以通过设置disableIMDSv1来执行不同的异常处理逻辑：

- 当值为false（默认值）时，会使用普通模式继续获取访问凭据。

- 当值为true时，表示只能使用加固模式获取访问凭据，会抛出异常。

服务端是否支持IMDSv2，取决于您在服务器的配置。

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

$config = new Config([
    'type'          => 'ecs_ram_role',
    // 选填，该ECS角色的角色名称，不填会自动获取，但是建议加上以减少请求次数，可以通过环境变量ALIBABA_CLOUD_ECS_METADATA设置role_name
    'roleName'      => '<role_name>',
    // 选填，是否强制关闭IMDSv1，即必须使用IMDSv2加固模式，可以通过环境变量ALIBABA_CLOUD_IMDSV1_DISABLED设置
    'disableIMDSv1' => true,
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
$credential->getSecurityToken();
```

#### OIDCRoleArn

在容器服务 Kubernetes 版中设置了Worker节点RAM角色后，对应节点内的Pod中的应用也就可以像ECS上部署的应用一样，通过元数据服务（Meta Data Server）获取关联角色的STS Token。但如果容器集群上部署的是不可信的应用（比如部署您的客户提交的应用，代码也没有对您开放），您可能并不希望它们能通过元数据服务获取Worker节点关联实例RAM角色的STS Token。为了避免影响云上资源的安全，同时又能让这些不可信的应用安全地获取所需的 STS Token，实现应用级别的权限最小化，您可以使用RRSA（RAM Roles for Service Account）功能。阿里云容器集群会为不同的应用Pod创建和挂载相应的服务账户OIDC Token文件，并将相关配置信息注入到环境变量中，Credentials工具通过获取环境变量的配置信息，调用STS服务的AssumeRoleWithOIDC - OIDC角色SSO时获取扮演角色的临时身份凭证接口换取绑定角色的STS Token。详情请参见[通过RRSA配置ServiceAccount的RAM权限实现Pod权限隔离](https://help.aliyun.com/zh/ack/ack-managed-and-ack-dedicated/user-guide/use-rrsa-to-authorize-pods-to-access-different-cloud-services#task-2142941)。

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

$config = new Config([
    'type'                  => 'oidc_role_arn',
    // OIDC提供商ARN，可以通过环境变量ALIBABA_CLOUD_OIDC_PROVIDER_ARN设置oidc_provider_arn
    'oidcProviderArn'       => '<oidc_provider_arn>',
    // OIDC Token文件路径，可以通过环境变量ALIBABA_CLOUD_OIDC_TOKEN_FILE设置oidc_token_file_path
    'oidcTokenFilePath'     => '<oidc_token_file_path>',
    // 要扮演的RAM角色ARN，示例值：acs:ram::123456789012****:role/adminrole，可以通过环境变量ALIBABA_CLOUD_ROLE_ARN设置role_arn
    'roleArn'               => '<role_arn>',
    // 角色会话名称，可以通过环境变量ALIBABA_CLOUD_ROLE_SESSION_NAME设置role_session_name
    'roleSessionName'       => '<role_session_name>',
    // 设置更小的权限策略，非必填。示例值：{"Statement": [{"Action": ["*"],"Effect": "Allow","Resource": ["*"]}],"Version":"1"}
    'policy'                => '',
    # 设置session过期时间
    'roleSessionExpiration' => 3600,
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
$credential->getSecurityToken();
```

#### Credentials URI

通过指定提供凭证的自定义网络服务地址，让凭证自动申请维护 STS Token。

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

$config = new Config([
    'type'               => 'credentials_uri',
    // 凭证的 URI，格式为http://local_or_remote_uri/，可以通过环境变量ALIBABA_CLOUD_CREDENTIALS_URI设置credentials_uri
    'credentialsURI'     => '<credentials_uri>',
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
$credential->getSecurityToken();
```

#### Bearer Token

目前只有云呼叫中心 CCC 这款产品支持 Bearer Token 的凭据初始化方式。

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

$config = new Config([
    'type'            => 'bearer',
    // 填入您的Bearer Token
    'bearerToken'     => '<bearer_token>',
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getBearerToken();
```

## 默认凭证提供程序链

当您的程序开发环境和生产环境采用不同的凭据类型，常见做法是在代码中获取当前环境信息，编写获取不同凭据的分支代码。借助Credentials工具的默认凭据链，您可以用同一套代码，通过程序之外的配置来控制不同环境下的凭据获取方式。当您在不传入参数的情况下，直接使用$credential = new Credential();初始化凭据客户端时，阿里云SDK将会尝试按照如下顺序查找相关凭据信息。

### 1. 使用环境变量

Credentials工具会优先在环境变量中获取凭据信息。

- 如果系统环境变量 `ALIBABA_CLOUD_ACCESS_KEY_ID`（密钥Key） 和 `ALIBABA_CLOUD_ACCESS_KEY_SECRET`（密钥Value） 不为空，Credentials工具会优先使用它们作为默认凭据。

- 如果系统环境变量 `ALIBABA_CLOUD_ACCESS_KEY_ID`（密钥Key）、`ALIBABA_CLOUD_ACCESS_KEY_SECRET`（密钥Value）、`ALIBABA_CLOUD_SECURITY_TOKEN`（Token）均不为空，Credentials工具会优先使用STS Token作为默认凭据。

### 2. 使用OIDC RAM角色
若不存在优先级更高的凭据信息，Credentials工具会在环境变量中获取如下内容：

`ALIBABA_CLOUD_ROLE_ARN`：RAM角色名称ARN；

`ALIBABA_CLOUD_OIDC_PROVIDER_ARN`：OIDC提供商ARN；

`ALIBABA_CLOUD_OIDC_TOKEN_FILE`：OIDC Token文件路径；

若以上三个环境变量都已设置内容，Credentials将会使用变量内容调用STS服务的[AssumeRoleWithOIDC - OIDC角色SSO时获取扮演角色的临时身份凭证](https://help.aliyun.com/zh/ram/developer-reference/api-sts-2015-04-01-assumerolewithoidc)接口换取STS Token作为默认凭据。

### 3. 使用 Aliyun CLI 工具的 config.json 配置文件

若不存在优先级更高的凭据信息，Credentials工具会优先在如下位置查找 `config.json` 文件是否存在：
Linux系统：`~/.aliyun/config.json`
Windows系统： `C:\Users\USER_NAME\.aliyun\config.json`
如果文件存在，程序将会使用配置文件中 `current` 指定的凭据信息初始化凭据客户端。当然，您也可以通过环境变量 `ALIBABA_CLOUD_PROFILE` 来指定凭据信息，例如设置 `ALIBABA_CLOUD_PROFILE` 的值为 `AK`。

在config.json配置文件中每个module的值代表了不同的凭据信息获取方式：

- AK：使用用户的Access Key作为凭据信息；
- RamRoleArn：使用RAM角色的ARN来获取凭据信息；
- EcsRamRole：利用ECS绑定的RAM角色来获取凭据信息；
- OIDC：通过OIDC ARN和OIDC Token来获取凭据信息；
- ChainableRamRoleArn：采用角色链的方式，通过指定JSON文件中的其他凭据，以重新获取新的凭据信息。

配置示例信息如下：

```json
{
    "current": "AK",
    "profiles": [
        {
            "name": "AK",
            "mode": "AK",
            "access_key_id": "access_key_id",
            "access_key_secret": "access_key_secret"
        },
        {
            "name": "RamRoleArn",
            "mode": "RamRoleArn",
            "access_key_id": "access_key_id",
            "access_key_secret": "access_key_secret",
            "ram_role_arn": "ram_role_arn",
            "ram_session_name": "ram_session_name",
            "expired_seconds": 3600,
            "sts_region": "cn-hangzhou"
        },
        {
            "name": "EcsRamRole",
            "mode": "EcsRamRole",
            "ram_role_name": "ram_role_name"
        },
        {
            "name": "OIDC",
            "mode": "OIDC",
            "ram_role_arn": "ram_role_arn",
            "oidc_token_file": "path/to/oidc/file",
            "oidc_provider_arn": "oidc_provider_arn",
            "ram_session_name": "ram_session_name",
            "expired_seconds": 3600,
            "sts_region": "cn-hangzhou"
        },
        {
            "name": "ChainableRamRoleArn",
            "mode": "ChainableRamRoleArn",
            "source_profile": "AK",
            "ram_role_arn": "ram_role_arn",
            "ram_session_name": "ram_session_name",
            "expired_seconds": 3600,
            "sts_region": "cn-hangzhou"
        }
    ]
}
```

### 4. 使用配置文件
>
> 如果用户主目录存在默认文件 `~/.alibabacloud/credentials` （Windows 为 `C:\Users\USER_NAME\.alibabacloud\credentials`），程序会自动创建指定类型和名称的凭证。您也可通过环境变量 `ALIBABA_CLOUD_CREDENTIALS_FILE` 指定配置文件路径。如果文件存在，程序将会使用配置文件中 default 指定的凭据信息初始化凭据客户端。当然，您也可以通过环境变量 `ALIBABA_CLOUD_PROFILE` 来指定凭据信息，例如设置 `ALIBABA_CLOUD_PROFILE` 的值为 `client1`。

配置示例信息如下：

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
type=oidc_role_arn                 # 认证方式为 oidc_role_arn
oidc_provider_arn=oidc_provider_arn
oidc_token_file_path=oidc_token_file_path
role_arn=role_arn
role_session_name=session_name
```

### 5. 使用 ECS 实例RAM角色

若不存在优先级更高的凭据信息，Credentials工具将通过环境变量获取ALIBABA_CLOUD_ECS_METADATA（ECS实例RAM角色名称）的值。若该变量的值存在，程序将采用加固模式（IMDSv2）访问ECS的元数据服务（Meta Data Server），以获取ECS实例RAM角色的STS Token作为默认凭据信息。在使用加固模式时若发生异常，将使用普通模式兜底来获取访问凭据。您也可以通过设置环境变量ALIBABA_CLOUD_IMDSV1_DISABLED，执行不同的异常处理逻辑：

- 当值为false时，会使用普通模式继续获取访问凭据。

- 当值为true时，表示只能使用加固模式获取访问凭据，会抛出异常。

服务端是否支持IMDSv2，取决于您在服务器的配置。

### 6. 使用外部服务 Credentials URI

若不存在优先级更高的凭据信息，Credentials工具会在环境变量中获取ALIBABA_CLOUD_CREDENTIALS_URI，若存在，程序将请求该URI地址，获取临时安全凭证作为默认凭据信息。

外部服务响应结构应如下：

```json
{
  "Code": "Success",
  "AccessKeyId": "AccessKeyId",
  "AccessKeySecret": "AccessKeySecret",
  "SecurityToken": "SecurityToken",
  "Expiration": "2024-10-26T03:46:38Z"
}
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

[open-api]: https://api.aliyun.com
[latest-release]: https://github.com/aliyun/credentials-php
[guzzle-docs]: https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html
[composer]: https://getcomposer.org
[packagist]: https://packagist.org/packages/alibabacloud/credentials
