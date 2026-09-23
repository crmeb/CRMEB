# Socialite

Socialite 是一个 [OAuth2](https://oauth.net/2/) 认证工具。 它的灵感来源于 [laravel/socialite](https://github.com/laravel/socialite) ， 你可以很轻易的在任何 PHP 项目中使用它。[英文文档](/README_EN.md)

[![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/overtrue/socialite?style=flat-square)](https://github.com/overtrue/socialite/releases)
[![GitHub License](https://img.shields.io/github/license/overtrue/socialite?style=flat-square)](https://github.com/overtrue/socialite/blob/master/LICENSE)
[![Packagist Downloads](https://img.shields.io/packagist/dt/overtrue/socialite?style=flat-square)](https://packagist.org/packages/overtrue/socialite)

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me-button-s.svg?raw=true)](https://github.com/sponsors/overtrue)

该工具现已支持平台有：Apple，Facebook，Github，Google，Linkedin，Outlook，QQ，TAPD，支付宝，淘宝，百度，钉钉，微博，微信，抖音，飞书，Lark，豆瓣，企业微信，腾讯云，Line，Gitee，Coding，Twitter。

如果你喜欢我的项目并想支持我，[点击这里 :heart:](https://github.com/sponsors/overtrue)

## 版本要求

PHP >= 8.0.2

## 安装

```shell
composer require "overtrue/socialite" -vvv
```

## 使用指南

用户只需要创建相应配置变量，然后通过工具为各个平台创建认证应用，并轻松获取该平台的 access_token 和用户相关信息。工具实现逻辑详见参照各大平台 OAuth2 文档。

工具使用大致分为以下几步：

1. 配置平台设置
2. 创建对应平台应用
3. 让用户跳转至平台认证
4. 服务器收到平台回调 Code，使用 Code 换取平台处用户信息（包括 access_token）

为 Laravel 用户创建的更方便的整合的包： [overtrue/laravel-socialite](https://github.com/overtrue/laravel-socialite)

`authorize.php`: 让用户跳转至平台认证

```php
<?php

use Overtrue\Socialite\SocialiteManager;

$config = [
    'github' => [
        'client_id'     => 'your-app-id',
        'client_secret' => 'your-app-secret',
        'redirect_uri' => 'http://localhost/socialite/callback.php',
    ],
];

$socialite = new SocialiteManager($config);

$url = $socialite->create('github')->redirect();

return redirect($url);
```

`callback.php`:

```php
<?php

use Overtrue\Socialite\SocialiteManager;

$config = [
    'github' => [
        'client_id' => 'your-app-id',
        'client_secret' => 'your-app-secret',
        'redirect_uri' => 'http://localhost/socialite/callback.php',
    ],
];

$socialite = new SocialiteManager($config);

$code = request()->query('code');

$user = $socialite->create('github')->userFromCode($code);

$user->getId();        // 1472352
$user->getNickname();  // "overtrue"
$user->getUsername();  // "overtrue"
$user->getName();      // "安正超"
$user->getEmail();     // "anzhengchao@gmail.com"
...
```

## 配置

为每个平台设置相同的键值对后就能开箱即用：`client_id`, `client_secret`, `redirect_uri`。
同时兼容旧键名：`redirect`、`redirect_url`。

示例：

```php
$config = [
  'weibo' => [
    'client_id'     => 'your-app-id',
    'client_secret' => 'your-app-secret',
    'redirect_uri' => 'http://localhost/socialite/callback.php',
  ],
  'facebook' => [
    'client_id'     => 'your-app-id',
    'client_secret' => 'your-app-secret',
    'redirect_uri' => 'http://localhost/socialite/callback.php',
  ],
];
```

### 自定义应用名

你可以使用任意你喜欢的名字对每个平台进行命名，比如说 `foo`， 采用别名的方法后需要在配置中多设置一个 `provider` 键，这样才能告诉工具包如何正确找到你想要的程序：

```php
$config = [
  // 为 github 应用起别名为 foo
    'foo' => [
        'provider'    => 'github',  // <-- provider name
        'client_id'   => 'your-app-id',
        'client_secret' => 'your-app-secret',
        'redirect_uri' => 'http://localhost/socialite/callback.php',
    ],

    // 另外一个名字叫做 bar 的 github 应用
    'bar' => [
        'provider'    => 'github',  // <-- provider name
        'client_id'   => 'your-app-id',
        'client_secret' => 'your-app-secret',
        'redirect_uri' => 'http://localhost/socialite/callback.php',
    ],

    //...
];

$socialite = new SocialiteManager($config);

$appFoo = $socialite->create('foo');
$appBar = $socialite->create('bar');
```

### 扩展自定义服务提供程序

你可以很容易的从自定义的服务提供中创建应用，只需要遵循如下两点：

1. 使用自定义创建器

   如下代码所示，为 foo 应用定义了服务提供名，但是工具本身还未支持，所以使用创建器 `extend()`，以闭包函数的形式为该服务提供创建一个实例。

```php
$config = [
    'foo' => [
        'provider' => 'myprovider',  // <-- 一个工具还未支持的服务提供程序
        'client_id' => 'your-app-id',
        'client_secret' => 'your-app-secret',
        'redirect_uri' => 'http://localhost/socialite/callback.php',
    ],
];

$socialite = new SocialiteManager($config);

$socialite->extend('myprovider', function(array $config) {
    return new MyCustomProvider($config);
});

$app = $socialite->create('foo');
```

1. 使用服务提供类

> [!IMPORTANT]  
> 
> 👋🏻 你的自定义服务提供类必须实现 `Overtrue\Socialite\Contracts\ProviderInterface` 接口

```php
class MyCustomProvider implements \Overtrue\Socialite\Contracts\ProviderInterface
{
    //...
}
```

接下来为 `provider` 设置该类名让工具可以找到该类并实例化：

```php
$config = [
    'foo' => [
        'provider'    => MyCustomProvider::class,  // <-- 类名
        'client_id'   => 'your-app-id',
        'client_secret' => 'your-app-secret',
        'redirect_uri' => 'http://localhost/socialite/callback.php',
    ],
];

$socialite = new SocialiteManager($config);
$app = $socialite->create('foo');
```

## 平台

不同的平台有不同的配置方法，为了确保工具的正常运行，所以请确保你所使用的平台的配置都是如期设置的。

### [支付宝](https://opendocs.alipay.com/open/200/105310#s2)

请按如下方式配置

```php
$config = [
  'alipay' => [
    // 这个键名还能像官方文档那样叫做 'app_id'
    'client_id' => 'your-app-id',

    // 请根据官方文档，在官方管理后台配置 RSA2
    // 注意： 这是你自己的私钥
    // 注意： 不允许私钥内容有其他字符
    // 建议： 为了保证安全，你可以将文本信息从磁盘文件中读取，而不是在这里明文
    'rsa_private_key' => 'your-rsa-private-key',

    // 确保这里的值与你在服务后台绑定的地址值一致
    // 推荐使用 'redirect_uri'，同时兼容 'redirect' 与 'redirect_url'
    'redirect_uri' => 'http://localhost/socialite/callback.php',

    // 沙箱模式接入地址见 https://opendocs.alipay.com/open/220/105337#%E5%85%B3%E4%BA%8E%E6%B2%99%E7%AE%B1
    'sandbox' => false,
  ]
  ...
];

$socialite = new SocialiteManager($config);

$user = $socialite->create('alipay')->userFromCode('here is auth code');

// 详见文档后面 "User interface"
$user->getId();        // 1472352
$user->getNickname();  // "overtrue"
$user->getUsername();  // "overtrue"
$user->getName();      // "安正超"
...
```

本工具暂时只支持 RSA2 个人私钥认证方式。

### [钉钉](https://ding-doc.dingtalk.com/doc#/serverapi3/mrugr3)

如文档所示

> 注意：该工具仅支持 QR code 连接到第三方网站，用来获取用户信息（opeid， unionid 和 nickname）

```php
$config = [
  'dingtalk' => [
      // or 'app_id'
      'client_id' => 'your app id',

      // or 'app_secret'
      'client_secret' => 'your app secret',

      // 兼容旧键名：'redirect' 或 'redirect_url'
      'redirect_uri' => 'redirect URL'
  ]
];

$socialite = new SocialiteManager($config);

$user = $socialite->create('dingtalk')->userFromCode('here is auth code');

// 详见文档后面 "User interface"
$user->getId();        // 1472352
$user->getNickname();  // "overtrue"
$user->getUsername();  // "overtrue"
$user->getName();      // "安正超"
...
```

### [抖音](https://open.douyin.com/platform/doc/OpenAPI-oauth2)

> 注意： 使用抖音服务提供的时候，如果你想直接使用 access_token 获取用户信息时，请先设置 openid。 先调用 `withOpenId()` 再调用 `userFromToken()`

```php
$config = [
  'douyin' => [
      'client_id' => 'your app id',

      'client_secret' => 'your app secret',

      'redirect_uri' => 'redirect URL'
  ]
];

$socialite = new SocialiteManager($config);

$user = $socialite->create('douyin')->userFromCode('here is auth code');

$user = $socialite->create('douyin')->withOpenId('openId')->userFromToken('here is the access token');
```

### [头条](https://open.douyin.com/platform/resource/docs/develop/permission/toutiao-or-xigua/OAuth2.0/)

> 注意： 使用`头条`服务提供的时候，如果你想直接使用 access_token 获取用户信息时，请先设置 openid。 先调用 `withOpenId()` 再调用 `userFromToken()`

```php
$config = [
  'toutiao' => [
    'client_id' => 'your app id',
    'client_secret' => 'your app secret',
    'redirect_uri' => 'redirect URL'
  ]
];

$socialite = new SocialiteManager($config);

$user = $socialite->create('toutiao')->userFromCode('here is auth code');
$user = $socialite->create('toutiao')->withOpenId('openId')->userFromToken('here is the access token');
```

### [西瓜](https://open.douyin.com/platform/resource/docs/develop/permission/toutiao-or-xigua/OAuth2.0/)

> 注意： 使用`西瓜`服务提供的时候，如果你想直接使用 access_token 获取用户信息时，请先设置 openid。 先调用 `withOpenId()` 再调用 `userFromToken()`

```php
$config = [
  'xigua' => [
    'client_id' => 'your app id',
    'client_secret' => 'your app secret',
    'redirect_uri' => 'redirect URL'
  ]
];

$socialite = new SocialiteManager($config);

$user = $socialite->create('xigua')->userFromCode('here is auth code');
$user = $socialite->create('xigua')->withOpenId('openId')->userFromToken('here is the access token');
```

### [百度](https://developer.baidu.com/wiki/index.php?title=docs/oauth)

其他配置没啥区别，在用法上，可以很轻易的选择重定向登录页面的模式，通过 `withDisplay()`

- **page：**全屏形式的授权页面 (默认)，适用于 web 应用。
- **popup:** 弹框形式的授权页面，适用于桌面软件应用和 web 应用。
- **dialog:** 浮层形式的授权页面，只能用于站内 web 应用。
- **mobile:** Iphone/Android 等智能移动终端上用的授权页面，适用于 Iphone/Android 等智能移动终端上的应用。
- **tv:** 电视等超大显示屏使用的授权页面。
- **pad:** IPad/Android 等智能平板电脑使用的授权页面。

```php
$authUrl = $socialite->create('baidu')->withDisplay('mobile')->redirect();

```

`popup` 模式是工具内默认的使用模式。`basic` 是默认使用的 scopes 值。

### [飞书](https://open.feishu.cn/document/ukTMukTMukTM/uITNz4iM1MjLyUzM)

通过一些简单的方法配置  app_ticket 就能使用内部应用模式

```php
$config = [
    'feishu' => [
        // or 'app_id'
        'client_id' => 'your app id',

        // or 'app_secret'
        'client_secret' => 'your app secret',

        // 兼容旧键名：'redirect' 或 'redirect_url'
        'redirect_uri' => 'redirect URL',

        // 如果你想使用使用内部应用的方式获取 app_access_token
        // 对这个键设置了 'internal' 值那么你已经开启了内部应用模式
        'app_mode' => 'internal'
    ]
];

$socialite = new SocialiteManager($config);

$feishuDriver = $socialite->create('feishu');

$feishuDriver->withInternalAppMode()->userFromCode('here is code');
$feishuDriver->withDefaultMode()->withAppTicket('app_ticket')->userFromCode('here is code');
```

### [Lark](https://open.larksuite.com/document/ukTMukTMukTM/uITNz4iM1MjLyUzM)

通过一些简单的方法配置  app_ticket 就能使用内部应用模式

```php
$config = [
    'lark' => [
        // or 'app_id'
        'client_id' => 'your app id',

        // or 'app_secret'
        'client_secret' => 'your app secret',

        // 兼容旧键名：'redirect' 或 'redirect_url'
        'redirect_uri' => 'redirect URL',

        // 如果你想使用使用内部应用的方式获取 app_access_token
        // 对这个键设置了 'internal' 值那么你已经开启了内部应用模式
        'app_mode' => 'internal'
    ]
];

$socialite = new SocialiteManager($config);

$larkDriver = $socialite->create('lark');

$larkDriver->withInternalAppMode()->userFromCode('here is code');
$larkDriver->withDefaultMode()->withAppTicket('app_ticket')->userFromCode('here is code');
```

### [淘宝](https://open.taobao.com/doc.htm?docId=102635&docType=1&source=search)

其他配置与其他平台的一样，你能选择你想要展示的重定向页面类型通过使用 `withView()`

```php
$authUrl = $socialite->create('taobao')->withView('wap')->redirect();
```

`web` 模式是工具默认使用的展示方式， `user_info` 是默认使用的 scopes 范围值。

### [微信](https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/Official_Accounts/official_account_website_authorization.html)

我们支持开放平台代表公众号进行第三方平台网页授权。

你只需要像下面这样输入你的配置。官方账号不需要授权。

```php
...
[
    'wechat' =>
        [
            'client_id'   => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_uri' => 'redirect-url',

            // 开放平台 - 第三方平台所需
            'component' => [
                // or 'app_id', 'component_app_id' as key
                'id' => 'component-app-id',
                // or 'app_token', 'access_token', 'component_access_token' as key
                'token' => 'component-access-token',
            ]
        ]
],
...
```

### [Coding](https://coding.net/help/openapi#oauth)

您需要额外配置 `team_url` 为您的团队域名，例如：

```php
$config = [
    'coding' => [
        'team_url' => 'https://{your-team}.coding.net',
        'client_id' => 'your app id',
        'client_secret' => 'your app secret',
        'redirect_uri' => 'redirect URL',
    ]
];
```

### [PayPal](https://developer.paypal.com/docs/log-in-with-paypal/)

您可能需要设置responseType，可以使用`withResponseType`函数进行设置，默认是`code` 还可以设置为`id_token` 或`code` & `id_token`

> <https://developer.paypal.com/docs/log-in-with-paypal/integrate/generate-button/>

```php
$config = [
    'paypal' => [
        'client_id'     => 'AT******************',
        'client_secret' => 'EK**************',
        'sandbox'      => false,
        'redirect_uri' => "nativexo://paypalpay",
    ],
];
```

### [Apple](https://developer.apple.com/documentation/sign_in_with_apple/sign_in_with_apple_rest_api)

通过 Apple 登录需要在 Apple Developer 后台创建 Service ID，并配置好 Return URLs。client_secret 可以直接提供，也可以通过 `team_id`、`key_id` 和 `private_key` 自动生成（推荐）。

> <https://developer.apple.com/documentation/sign_in_with_apple/generate_and_validate_tokens>

```php
$config = [
    'apple' => [
        'client_id'   => 'com.example.app',   // Services ID
        'team_id'     => 'XXXXXXXXXX',         // Apple Developer Team ID
        'key_id'      => 'XXXXXXXXXX',         // Key ID from Apple Developer
        'private_key' => file_get_contents('/path/to/AuthKey_XXXXXXXXXX.p8'), // .p8 private key content
        'redirect_uri' => 'https://example.com/auth/apple/callback',
    ],
];
```

## 其他一些技巧

### Scopes

在重定向用户之前，您还可以使用 `scopes()` 方法在请求上设置 “范围”。此方法将覆盖所有现有的作用域：

```php
$response = $socialite->create('github')
                ->scopes(['scope1', 'scope2'])->redirect();
```

### Redirect URL

你也可以动态设置 `redirect_uri`，你可以使用以下方法来改变 `redirect_uri` URL:

```php
$url = 'your callback url.';

$socialite->redirect($url);
// or
$socialite->withRedirectUrl($url)->redirect();
```

### State

你的应用程序可以使用一个状态参数来确保响应属于同一个用户发起的请求，从而防止跨站请求伪造 (CSFR) 攻击。当恶意攻击者欺骗用户执行不需要的操作 (只有用户有权在受信任的 web 应用程序上执行) 时，就会发生 CSFR 攻击，所有操作都将在不涉及或警告用户的情况下完成。

这里有一个最简单的例子，说明了如何提供状态可以让你的应用程序更安全。在本例中，我们使用会话 ID 作为状态参数，但是您可以使用您想要为状态创建值的任何逻辑。

### 带着 `state` 参数的重定向

```php
<?php
session_start();

$config = [
    //...
];

// Assign to state the hashing of the session ID
$state = hash('sha256', session_id());

$socialite = new SocialiteManager($config);

$url = $socialite->create('github')->withState($state)->redirect();

return redirect($url);
```

### 检验回调的 `state`

一旦用户授权你的应用程序，用户将被重定向回你的应用程序的 redirect_uri。OAuth 服务器将不加修改地返回状态参数。检查 redirect_uri 中提供的状态是否与应用程序生成的状态相匹配：

```php
<?php
session_start();

$state = request()->query('state');
$code = request()->query('code');

// Check the state received with current session id
if ($state != hash('sha256', session_id())) {
    exit('State does not match!');
}
$user = $socialite->create('github')->userFromCode($code);

// authorized
```

[查看更多关于 `state` 参数的文档](https://auth0.com/docs/protocols/oauth2/oauth-state)

### 其他的一些参数

要在请求中包含任何可选参数，调用 `with()` 方法传入一个你想要设置的关联数组：

```php
$response = $socialite->create('google')
                    ->with(['hd' => 'example.com'])->redirect();
```

## User interface

### 标准的 user api

```php
$user = $socialite->create('github')->userFromCode($code);
```

```json
{
  "id": 1472352,
  "nickname": "overtrue",
  "name": "安正超",
  "email": "anzhengchao@gmail.com",
  "avatar": "https://avatars.githubusercontent.com/u/1472352?v=3",
  "raw": {
    "login": "overtrue",
    "id": 1472352,
    "avatar_url": "https://avatars.githubusercontent.com/u/1472352?v=3",
    "gravatar_id": "",
    "url": "https://api.github.com/users/overtrue",
    "html_url": "https://github.com/overtrue",
    ...
  },
  "token_response": {
    "access_token": "5b1dc56d64fffbd052359f032716cc4e0a1cb9a0",
    "token_type": "bearer",
    "scope": "user:email"
  }
}
```

你可以像这样以数组键的形式获取 user 属性：

```php
$user['id'];        // 1472352
$user['nickname'];  // "overtrue"
$user['name'];      // "安正超"
$user['email'];     // "anzhengchao@gmail.com"
...
```

或者使用该 `User` 对象的方法：

```php
mixed   $user->getId();
?string $user->getNickname();
?string $user->getName();
?string $user->getEmail();
?string $user->getAvatar();
?string $user->getRaw();
?string $user->getAccessToken();
?string $user->getRefreshToken();
?int    $user->getExpiresIn();
?array  $user->getTokenResponse();


```

### 从 OAuth API 响应中取得原始数据

`$user->getRaw()` 方法会返回一个 **array**。

### 当你使用 userFromCode() 想要获取 token 响应的原始数据

`$user->getTokenResponse()` 方法会返回一个 **array** 里面是响应从获取 token 时候 API 返回的响应。

> 注意：当你使用 `userFromCode()` 时，这个方法只返回一个 **有效的数组**，否则将返回 **null**，因为 `userFromToken()` 没有 token 的 HTTP 响应。

### 通过 access token 获取用户信息

```php
$accessToken = 'xxxxxxxxxxx';
$user = $socialite->userFromToken($accessToken);
```

## Enjoy it! :heart:

## 参照

- [Alipay - 用户信息授权](https://opendocs.alipay.com/open/289/105656)
- [DingTalk - 扫码登录第三方网站](https://ding-doc.dingtalk.com/doc#/serverapi3/mrugr3)
- [Google - OpenID Connect](https://developers.google.com/identity/protocols/OpenIDConnect)
- [Github - Authorizing OAuth Apps](https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps/)
- [Facebook - Graph API](https://developers.facebook.com/docs/graph-api)
- [Linkedin - Authenticating with OAuth 2.0](https://developer.linkedin.com/docs/oauth2)
- [微博 - OAuth 2.0 授权机制说明](http://open.weibo.com/wiki/%E6%8E%88%E6%9D%83%E6%9C%BA%E5%88%B6%E8%AF%B4%E6%98%8E)
- [QQ - OAuth 2.0 登录 QQ](http://wiki.connect.qq.com/oauth2-0%E7%AE%80%E4%BB%8B)
- [腾讯云 - OAuth2.0](https://cloud.tencent.com/document/product/306/37730#.E6.8E.A5.E5.85.A5.E8.85.BE.E8.AE.AF.E4.BA.91-oauth)
- [微信公众平台 - OAuth 文档](http://mp.weixin.qq.com/wiki/9/01f711493b5a02f24b04365ac5d8fd95.html)
- [微信开放平台 - 网站应用微信登录开发指南](https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=&lang=zh_CN)
- [微信开放平台 - 代公众号发起网页授权](https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419318590&token=&lang=zh_CN)
- [企业微信 - OAuth 文档](https://open.work.weixin.qq.com/api/doc/90000/90135/91020)
- [企业微信第三方应用 - OAuth 文档](https://open.work.weixin.qq.com/api/doc/90001/90143/91118)
- [豆瓣 - OAuth 2.0 授权机制说明](http://developers.douban.com/wiki/?title=oauth2)
- [抖音 - 网站应用开发指南](http://open.douyin.com/platform/doc)
- [飞书 - 授权说明](https://open.feishu.cn/document/ukTMukTMukTM/uMTNz4yM1MjLzUzM)
- [Lark - 授权说明](https://open.larksuite.com/document/ukTMukTMukTM/uMTNz4yM1MjLzUzM)
- [Tapd - 用户授权说明](https://www.tapd.cn/help/show#1120003271001000093)
- [Line - OAuth 2.0](https://developers.line.biz/en/docs/line-login/integrate-line-login/)
- [Gitee - OAuth文档](https://gitee.com/api/v5/oauth_doc#/)
- [PayPal - OAuth文档](https://developer.paypal.com/docs/log-in-with-paypal/)
- [Apple - Sign in with Apple REST API](https://developer.apple.com/documentation/sign_in_with_apple/sign_in_with_apple_rest_api)

## PHP 扩展包开发

> 想知道如何从零开始构建 PHP 扩展包？
>
> 请关注我的实战课程，我会在此课程中分享一些扩展开发经验 —— [《PHP 扩展包实战教程 - 从入门到发布》](https://learnku.com/courses/creating-package)

## License

MIT
