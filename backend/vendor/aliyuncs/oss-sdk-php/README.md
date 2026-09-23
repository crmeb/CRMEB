# Alibaba Cloud OSS SDK for PHP

[![Latest Stable Version](https://poser.pugx.org/aliyuncs/oss-sdk-php/v/stable)](https://packagist.org/packages/aliyuncs/oss-sdk-php)
[![Build Status](https://travis-ci.org/aliyun/aliyun-oss-php-sdk.svg?branch=master)](https://travis-ci.org/aliyun/aliyun-oss-php-sdk)
[![Coverage Status](https://coveralls.io/repos/github/aliyun/aliyun-oss-php-sdk/badge.svg?branch=master)](https://coveralls.io/github/aliyun/aliyun-oss-php-sdk?branch=master)

## [README of Chinese](https://github.com/aliyun/aliyun-oss-php-sdk/blob/master/README-CN.md)

## Overview

Alibaba Cloud Object Storage Service (OSS) is a cloud storage service provided by Alibaba Cloud, featuring a massive capacity, security, a low cost, and high reliability. You can upload and download data on any application anytime and anywhere by calling APIs, and perform simple management of data through the web console. The OSS can store any type of files and therefore applies to various websites, development enterprises and developers.


## Run environment
- PHP 5.3+.
- cURL extension.

Tips:

- In Ubuntu, you can use the ***apt-get*** package manager to install the *PHP cURL extension*: `sudo apt-get install php5-curl`.

## Install OSS PHP SDK

- If you use the ***composer*** to manage project dependencies, run the following command in your project's root directory:

        composer require aliyuncs/oss-sdk-php

   You can also declare the dependency on Alibaba Cloud OSS SDK for PHP in the `composer.json` file.

        "require": {
            "aliyuncs/oss-sdk-php": "~2.0"
        }

   Then run `composer install` to install the dependency. After the Composer Dependency Manager is installed, import the dependency in your PHP code: 

        require_once __DIR__ . '/vendor/autoload.php';

- You can also directly download the packaged [PHAR File][releases-page], and 
   introduce the file to your code: 

        require_once '/path/to/oss-sdk-php.phar';

- Download the SDK source code, and introduce the `autoload.php` file under the SDK directory to your code: 

        require_once '/path/to/oss-sdk/autoload.php';

## Quick use

### Common classes

| Class | Explanation |
|:------------------|:------------------------------------|
|OSS\OssClient | OSS client class. An OssClient instance can be used to call the interface.  |
|OSS\Core\OssException |OSS Exception class . You only need to pay attention to this exception when you use the OssClient. |

### Initialize an OssClient

The SDK's operations for the OSS are performed through the OssClient class. The code below creates an OssClient object:

```php
<?php
$accessKeyId = "<AccessKeyID that you obtain from OSS>";
$accessKeySecret = "<AccessKeySecret that you obtain from OSS>";
$endpoint = "<Domain that you select to access an OSS data center, such as "oss-cn-hangzhou.aliyuncs.com>";
try {
    $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
} catch (OssException $e) {
    print $e->getMessage();
}
```

### Operations on objects

Objects are the most basic data units on the OSS. You can simply consider objects as files. The following code uploads an object:

```php
<?php
$bucket= "<Name of the bucket in use. Pay attention to naming conventions>";
$object = "<Name of the object in use. Pay attention to naming conventions>";
$content = "Hello, OSS!"; // Content of the uploaded file
try {
    $ossClient->putObject($bucket, $object, $content);
} catch (OssException $e) {
    print $e->getMessage();
}
```

### Operations on buckets

Buckets are the space that you use to manage the stored objects. It is an object management unit for users. Each object must belong to a bucket. You can create a bucket with the following code:

```php
<?php
$bucket= "<Name of the bucket in use. Pay attention to naming conventions>";
try {
    $ossClient->createBucket($bucket);
} catch (OssException $e) {
    print $e->getMessage();
}
```

### Handle returned results

The OssClient provides the following two types of returned data from interfaces:

- Put and Delete interfaces: The *PUT* and *DELETE* operations are deemed successful if *null* is returned by the interfaces without *OSSException*.
- Get and List interfaces: The *GET* and *LIST* operations are deemed successful if the desired data is returned by the interfaces without *OSSException*. For example, 

    ```php
    <?php
    $bucketListInfo = $ossClient->listBuckets();
    $bucketList = $bucketListInfo->getBucketList();
    foreach($bucketList as $bucket) {
        print($bucket->getLocation() . "\t" . $bucket->getName() . "\t" . $bucket->getCreateDate() . "\n");
    }
    ```
In the above code, $bucketListInfo falls into the 'OSS\Model\BucketListInfo' data type.


### Run a sample project

- Modify `samples/Config.php` to complete the configuration information. 
- Run `cd samples/ && php RunAll.php`. 

### Run a unit test

- Run `composer install` to download the dependent libraries. 
- Set the environment variable. 

        export OSS_ACCESS_KEY_ID=access-key-id
        export OSS_ACCESS_KEY_SECRET=access-key-secret
        export OSS_ENDPOINT=endpoint
        export OSS_BUCKET=bucket-name

- Run `php vendor/bin/phpunit`

## License

- MIT

## Contact us

- [Alibaba Cloud OSS official website](http://oss.aliyun.com).
- [Alibaba Cloud OSS official forum](http://bbs.aliyun.com).
- [Alibaba Cloud OSS official documentation center](http://www.aliyun.com/product/oss#Docs).
- Alibaba Cloud official technical support: [Submit a ticket](https://workorder.console.aliyun.com/#/ticket/createIndex).

[releases-page]: https://github.com/aliyun/aliyun-oss-php-sdk/releases
[phar-composer]: https://github.com/clue/phar-composer

