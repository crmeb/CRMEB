# Rtc Streaming Cloud Server-Side Library For PHP

## Features

- Appclient
    - [x] 创建房间: client->createApp()
    - [x] 查看房间: client->getApp()
    - [x] 删除房间: client->deleteApp()
    - [x] 生成房间token: client->appToken()



## Contents

- [Installation](#installation)
- [Usage](#usage)
    - [Configuration](#configuration)
    - [App](#app)
        - [Create a app](#create-a-app)
        - [Get a app](#get-a-app)
        - [Delete a app](#delete-a-app)
        - [Generate a app token](#generate-a-app-token)


## Usage

### App

#### Create a app

```php
$ak = "gwd_gV4gPKZZsmEOvAuNU1AcumicmuHooTfu64q5";
$sk = "xxxx";
$auth = new Auth($ak, $sk);
$client = new Qiniu\Rtc\AppClient($auth);
$resp=$client->createApp("901","testApp");
print_r($resp);
```

#### Get an app

```php
$ak = "gwd_gV4gPKZZsmEOvAuNU1AcumicmuHooTfu64q5";
$sk = "xxxx";
$auth = new Auth($ak, $sk);
$client = new Qiniu\Rtc\AppClient($auth);
$resp=$client->getApp("deq02uhb6");
print_r($resp);
```

#### Delete an app

```php
$ak = "gwd_gV4gPKZZsmEOvAuNU1AcumicmuHooTfu64q5";
$sk = "xxxx";
$auth = new Auth($ak, $sk);
$client = new Qiniu\Rtc\AppClient($auth);
$resp=$client->deleteApp("deq02uhb6");
print_r($resp);
```

#### Generate an app token

```php
$ak = "gwd_gV4gPKZZsmEOvAuNU1AcumicmuHooTfu64q5";
$sk = "xxxx";
$auth = new Auth($ak, $sk);
$client = new Qiniu\Rtc\AppClient($auth);
$resp=$client->appToken("deq02uhb6", "lfx", '1111', (time()+3600), 'user');
print_r($resp);
```