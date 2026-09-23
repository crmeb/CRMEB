English | [简体中文](/README-zh-CN.md)

![](https://aliyunsdk-pages.alicdn.com/icons/AlibabaCloud.svg)

# Alibaba Cloud Credentials for PHP

[![PHP CI](https://github.com/aliyun/credentials-php/actions/workflows/ci.yml/badge.svg)](https://github.com/aliyun/credentials-php/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/aliyun/credentials-php/graph/badge.svg?token=YIkSjtfKbB)](https://codecov.io/gh/aliyun/credentials-php)
[![Latest Stable Version](https://poser.pugx.org/alibabacloud/credentials/v/stable)](https://packagist.org/packages/alibabacloud/credentials)
[![composer.lock](https://poser.pugx.org/alibabacloud/credentials/composerlock)](https://packagist.org/packages/alibabacloud/credentials)
[![Total Downloads](https://poser.pugx.org/alibabacloud/credentials/downloads)](https://packagist.org/packages/alibabacloud/credentials)
[![License](https://poser.pugx.org/alibabacloud/credentials/license)](https://packagist.org/packages/alibabacloud/credentials)

Alibaba Cloud Credentials for PHP is a tool that helps PHP developers manage their credentials.

## Prerequisites

Your system needs to meet [Prerequisites](/docs/zh-CN/0-Prerequisites.md), including PHP> = 5.6. We strongly recommend using the cURL extension and compiling cURL 7.16.2+ using the TLS backend.

## Installation

If you have [Globally Install Composer](https://getcomposer.org/doc/00-intro.md#globally) on your system, install Alibaba Cloud Credentials for PHP as a dependency by running the following directly in the project directory:

```sh
composer require alibabacloud/credentials
```

> Some users may not be able to install due to network problems, you can switch to the [Alibaba Cloud Composer Mirror](https://developer.aliyun.com/composer).

See [Installation](/docs/zh-CN/1-Installation.md) for details on installing through Composer and other means.

## Quick Examples

Before you begin, you need to sign up for an Alibaba Cloud account and retrieve your [Credentials](https://usercenter.console.aliyun.com/#/manage/ak).

### Credential Type

#### Default credential provider chain

If you do not specify a method to initialize a Credentials client, the default credential provider chain is used. For more information, see the Default credential provider chain section of this topic.

```php
<?php

use AlibabaCloud\Credentials\Credential;

// Chain Provider if no Parameter
// Do not specify a method to initialize a Credentials client.
$client = new Credential();

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
$credential->getSecurityToken();
```

#### AccessKey

Setup access_key credential through [User Information Management][ak], it have full authority over the account, please keep it safe. Sometimes for security reasons, you cannot hand over a primary account AccessKey with full access to the developer of a project. You may create a sub-account [RAM Sub-account][ram] , grant its [authorization][permissions]，and use the AccessKey of RAM Sub-account.

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

Create a temporary security credential by applying Temporary Security Credentials (TSC) through the Security Token Service (STS).

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

By specifying [RAM Role][RAM Role], the credential will be able to automatically request maintenance of STS Token. If you want to limit the permissions([How to make a policy][policy]) of STS Token, you can assign value for `Policy`.

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

$config = new Config([
    'type'                  => 'ram_role_arn',
    'accessKeyId'           => '<access_key_id>',
    'accessKeySecret'       => '<access_key_secret>',
    // Specify the ARN of the RAM role to be assumed. Example: acs:ram::123456789012****:role/adminrole.
    'roleArn'               => '<role_arn>',
    // Specify the name of the role session.
    'roleSessionName'       => '<role_session_name>',
    // Optional. Specify limited permissions for the RAM role. Example: {"Statement": [{"Action": ["*"],"Effect": "Allow","Resource": ["*"]}],"Version":"1"}.
    'policy'                => '',
    // Optional. Specify the expiration of the session
    'roleSessionExpiration' => 3600,
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
$credential->getSecurityToken();
```

#### EcsRamRole

Both ECS and ECI instances support binding instance RAM roles. When the Credentials tool is used in an instance, the RAM role bound to the instance will be automatically obtained, and the STS Token of the RAM role will be obtained by accessing the metadata service to complete the initialization of the credential client.

The instance metadata server supports two access modes: hardened mode and normal mode. The Credentials tool uses hardened mode (IMDSv2) by default to obtain access credentials. If an exception occurs when using hardened mode, you can set disableIMDSv1 to perform different exception handling logic:

- When the value is false (default value), the normal mode will continue to be used to obtain access credentials.

- When the value is true, it means that only hardened mode can be used to obtain access credentials, and an exception will be thrown.

Whether the server supports IMDSv2 depends on your configuration on the server.

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

$config = new Config([
    'type'          => 'ecs_ram_role',
    // Optional. Specify the name of the RAM role of the ECS instance. If you do not specify this parameter, its value is automatically obtained. To reduce the number of requests, we recommend that you specify this parameter.
    'roleName'      => '<role_name>',
    //Optional, whether to forcibly disable IMDSv1, that is, to use IMDSv2 hardening mode, which can be set by the environment variable ALIBABA_CLOUD_IMDSV1_DISABLED
    'disableIMDSv1' => true,
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
$credential->getSecurityToken();
```

#### OIDCRoleArn

After you attach a RAM role to a worker node in an Container Service for Kubernetes, applications in the pods on the worker node can use the metadata server to obtain an STS token the same way in which applications on ECS instances do. However, if an untrusted application is deployed on the worker node, such as an application that is submitted by your customer and whose code is unavailable to you, you may not want the application to use the metadata server to obtain an STS token of the RAM role attached to the worker node. To ensure the security of cloud resources and enable untrusted applications to securely obtain required STS tokens, you can use the RAM Roles for Service Accounts (RRSA) feature to grant minimum necessary permissions to an application. In this case, the ACK cluster creates a service account OpenID Connect (OIDC) token file, associates the token file with a pod, and then injects relevant environment variables into the pod. Then, the Credentials tool uses the environment variables to call the AssumeRoleWithOIDC operation of STS and obtains an STS token of the RAM role. For more information about the RRSA feature, see [Use RRSA to authorize different pods to access different cloud services](https://www.alibabacloud.com/help/en/ack/ack-managed-and-ack-dedicated/user-guide/use-rrsa-to-authorize-pods-to-access-different-cloud-services#task-2142941).

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

$config = new Config([
    'type'                  => 'oidc_role_arn',
    // Specify the ARN of the OIDC IdP by specifying the ALIBABA_CLOUD_OIDC_PROVIDER_ARN environment variable.
    'oidcProviderArn'       => '<oidc_provider_arn>',
    // Specify the path of the OIDC token file by specifying the ALIBABA_CLOUD_OIDC_TOKEN_FILE environment variable.
    'oidcTokenFilePath'     => '<oidc_token_file_path>',
    // Specify the ARN of the RAM role by specifying the ALIBABA_CLOUD_ROLE_ARN environment variable.
    'roleArn'               => '<role_arn>',
    // Specify the role session name by specifying the ALIBABA_CLOUD_ROLE_SESSION_NAME environment variable.
    'roleSessionName'       => '<role_session_name>',
    // Optional. Specify limited permissions for the RAM role. Example: {"Statement": [{"Action": ["*"],"Effect": "Allow","Resource": ["*"]}],"Version":"1"}.
    'policy'                => '',
    // Optional. Specify the validity period of the session.
    'roleSessionExpiration' => 3600,
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
$credential->getSecurityToken();
```

#### Credentials URI

By specifying the url, the credential will be able to automatically request maintenance of STS Token.

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

$config = new Config([
    'type'               => 'credentials_uri',
    // Format: http url. `credentialsURI` can be replaced by setting environment variable: ALIBABA_CLOUD_CREDENTIALS_URI
    'credentialsURI'     => '<credentials_uri>',
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getAccessKeyId();
$credential->getAccessKeySecret();
$credential->getSecurityToken();
```

#### Bearer Token

If credential is required by the Cloud Call Centre (CCC), please apply for Bearer Token maintenance by yourself.

```php
<?php

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;

$config = new Config([
    'type'            => 'bearer',
    'bearerToken'     => '<bearer_token>',
]);
$client = new Credential($config);

$credential = $client->getCredential();
$credential->getBearerToken();
```

## Default credential provider chain

If you want to use different types of credentials in the development and production environments of your application, you generally need to obtain the environment information from the code and write code branches to obtain different credentials for the development and production environments. The default credential provider chain of the Credentials tool allows you to use the same code to obtain credentials for different environments based on configurations independent of the application. If you use $credential = new Credential(); to initialize a Credentials client without specifying an initialization method, the Credentials tool obtains the credential information in the following order:

### 1. Environmental certificate

Look for environment credentials in environment variable.
- If the `ALIBABA_CLOUD_ACCESS_KEY_ID` and `ALIBABA_CLOUD_ACCESS_KEY_SECRET` environment variables are defined and are not empty, the program will use them to create default credentials.
- If the `ALIBABA_CLOUD_ACCESS_KEY_ID`, `ALIBABA_CLOUD_ACCESS_KEY_SECRET` and `ALIBABA_CLOUD_SECURITY_TOKEN` environment variables are defined and are not empty, the program will use them to create temporary security credentials(STS). Note: This token has an expiration time, it is recommended to use it in a temporary environment.

### 2. The RAM role of an OIDC IdP

If no credentials are found in the previous step, the Credentials tool obtains the values of the following environment variables:

`ALIBABA_CLOUD_ROLE_ARN`: the ARN of the RAM role.

`ALIBABA_CLOUD_OIDC_PROVIDER_ARN`: the ARN of the OIDC IdP.

`ALIBABA_CLOUD_OIDC_TOKEN_FILE`: the path of the OIDC token file.

If the preceding three environment variables are specified, the Credentials tool uses the environment variables to call the [AssumeRoleWithOIDC](https://www.alibabacloud.com/help/en/ram/developer-reference/api-sts-2015-04-01-assumerolewithoidc) operation of STS to obtain an STS token as the default credential.

### 3. Using the config.json Configuration File of Aliyun CLI Tool
If there is no higher-priority credential information, the Credentials tool will first check the following locations to see if the config.json file exists:

Linux system: `~/.aliyun/config.json`
Windows system: `C:\Users\USER_NAME\.aliyun\config.json`
If the file exists, the program will use the credential information specified by `current` in the configuration file to initialize the credentials client. Of course, you can also use the environment variable `ALIBABA_CLOUD_PROFILE` to specify the credential information, for example by setting the value of `ALIBABA_CLOUD_PROFILE` to `AK`.

In the config.json configuration file, the value of each module represents different ways to obtain credential information:

- AK: Use the Access Key of the user as credential information;
- RamRoleArn: Use the ARN of the RAM role to obtain credential information;
- EcsRamRole: Use the RAM role bound to the ECS to obtain credential information;
- OIDC: Obtain credential information through OIDC ARN and OIDC Token;
- ChainableRamRoleArn: Use the role chaining method to obtain new credential information by specifying other credentials in the JSON file.

The configuration example information is as follows:

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

### 4. Configuration file
>
> If the user's home directory has the default file `~/.alibabacloud/credentials` (Windows is `C:\Users\USER_NAME\.alibabacloud\credentials`), the program will automatically create credentials with the specified type and name. You can also specify the configuration file path by configuring the `ALIBABA_CLOUD_CREDENTIALS_FILE` environment variable. If the configuration file exists, the application initializes a Credentials client by using the credential information that is specified by default in the configuration file. You can also configure the `ALIBABA_CLOUD_PROFILE` environment variable to modify the default credential information that is read.

Configuration example:
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
type=oidc_role_arn                 # Authentication method is oidc_role_arn
oidc_provider_arn=oidc_provider_arn
oidc_token_file_path=oidc_token_file_path
role_arn=role_arn
role_session_name=session_name
```

### 5. Instance RAM role

If there is no credential information with a higher priority, the Credentials tool will obtain the value of ALIBABA_CLOUD_ECS_METADATA (ECS instance RAM role name) through the environment variable. If the value of this variable exists, the program will use the hardened mode (IMDSv2) to access the metadata service (Meta Data Server) of ECS to obtain the STS Token of the ECS instance RAM role as the default credential information. If an exception occurs when using the hardened mode, the normal mode will be used as a fallback to obtain access credentials. You can also set the environment variable ALIBABA_CLOUD_IMDSV1_DISABLED to perform different exception handling logic:

- When the value is false, the normal mode will continue to obtain access credentials.

- When the value is true, it means that only the hardened mode can be used to obtain access credentials, and an exception will be thrown.

Whether the server supports IMDSv2 depends on your configuration on the server.

### 6. Using External Service Credentials URI

If there are no higher-priority credential information, the Credentials tool will obtain the `ALIBABA_CLOUD_CREDENTIALS_URI` from the environment variables. If it exists, the program will request the URI address to obtain temporary security credentials as the default credential information.

The external service response structure should be as follows:

```json
{
  "Code": "Success",
  "AccessKeyId": "AccessKeyId",
  "AccessKeySecret": "AccessKeySecret",
  "SecurityToken": "SecurityToken",
  "Expiration": "2024-10-26T03:46:38Z"
}
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

[open-api]: https://api.alibabacloud.com
[latest-release]: https://github.com/aliyun/credentials-php
[guzzle-docs]: http://docs.guzzlephp.org/en/stable/request-options.html
[composer]: https://getcomposer.org
[packagist]: https://packagist.org/packages/alibabacloud/credentials
