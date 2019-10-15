<h1 align="center"> Socialite</h1>
<p align="center">
<a href="https://travis-ci.org/overtrue/socialite"><img src="https://travis-ci.org/overtrue/socialite.svg?branch=master" alt="Build Status"></a>
<a href="https://packagist.org/packages/overtrue/socialite"><img src="https://poser.pugx.org/overtrue/socialite/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/overtrue/socialite"><img src="https://poser.pugx.org/overtrue/socialite/v/unstable.svg" alt="Latest Unstable Version"></a>
<a href="https://scrutinizer-ci.com/g/overtrue/socialite/build-status/master"><img src="https://scrutinizer-ci.com/g/overtrue/socialite/badges/build.png?b=master" alt="Build Status"></a>
<a href="https://scrutinizer-ci.com/g/overtrue/socialite/?branch=master"><img src="https://scrutinizer-ci.com/g/overtrue/socialite/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality"></a>
<a href="https://scrutinizer-ci.com/g/overtrue/socialite/?branch=master"><img src="https://scrutinizer-ci.com/g/overtrue/socialite/badges/coverage.png?b=master" alt="Code Coverage"></a>
<a href="https://packagist.org/packages/overtrue/socialite"><img src="https://poser.pugx.org/overtrue/socialite/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/overtrue/socialite"><img src="https://poser.pugx.org/overtrue/socialite/license" alt="License"></a>
</p>


<p align="center">Socialite is an OAuth2 Authentication tool. It is inspired by <a href="https://github.com/laravel/socialite">laravel/socialite</a>, You can easily use it in any PHP project.</p>


<p align="center">
  <br>
  <b>创造不息，交付不止</b>
  <br>
  <a href="https://www.yousails.com">
    <img src="https://yousails.com/banners/brand.png" width=350>
  </a>
</p>

# Requirement

```
PHP >= 5.4
```
# Installation

```shell
$ composer require "overtrue/socialite:~1.1"
```

# Usage

For Laravel 5: [overtrue/laravel-socialite](https://github.com/overtrue/laravel-socialite)

`authorize.php`:

```php
<?php
use Overtrue\Socialite\SocialiteManager;

$config = [
    'github' => [
        'client_id'     => 'your-app-id',
        'client_secret' => 'your-app-secret',
        'redirect'      => 'http://localhost/socialite/callback.php',
    ],
];

$socialite = new SocialiteManager($config);

$response = $socialite->driver('github')->redirect();

echo $response;// or $response->send();
```

`callback.php`:

```php
<?php

// ...
$user = $socialite->driver('github')->user();

$user->getId();        // 1472352
$user->getNickname();  // "overtrue"
$user->getName();      // "安正超"
$user->getEmail();     // "anzhengchao@gmail.com"
$user->getProviderName(); // GitHub
...
```

### Configuration

Now we support the following sites:

`facebook`, `github`, `google`, `linkedin`, `weibo`, `qq`, `wechat`, `wechat_open`, and `douban`.

Each drive uses the same configuration keys: `client_id`, `client_secret`, `redirect`.

Example:
```
...
  'weibo' => [
    'client_id'     => 'your-app-id',
    'client_secret' => 'your-app-secret',
    'redirect'      => 'http://localhost/socialite/callback.php',
  ],
...
```

Special configuration options for [WeChat Open Platform](https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419318590&token=&lang=zh_CN)
```
'wechat_open' => [
    'client_id'     => 'your-app-id',
    'client_secret' => ['your-component-appid', 'your-component-access-token'],
    'redirect'      => 'http://localhost/socialite/callback.php',
]
```

### Scope

Before redirecting the user, you may also set "scopes" on the request using the scope method. This method will overwrite all existing scopes:

```php
$response = $socialite->driver('github')
                ->scopes(['scope1', 'scope2'])->redirect();

```

### Redirect URL

You may also want to dynamic set `redirect`，you can use the following methods to change the `redirect` URL:

```php
$socialite->redirect($url);
// or
$socialite->withRedirectUrl($url)->redirect();
// or
$socialite->setRedirectUrl($url)->redirect();
```

> WeChat scopes:
- `snsapi_base`, `snsapi_userinfo` - Used to Media Platform Authentication.
- `snsapi_login` - Used to web Authentication.

### Additional parameters

To include any optional parameters in the request, call the with method with an associative array:

```php
$response = $socialite->driver('google')
                    ->with(['hd' => 'example.com'])->redirect();
```

### User interface

#### Standard user api:

```php

$user = $socialite->driver('weibo')->user();
```

```json
{
  "id": 1472352,
  "nickname": "overtrue",
  "name": "安正超",
  "email": "anzhengchao@gmail.com",
  "avatar": "https://avatars.githubusercontent.com/u/1472352?v=3",
  "original": {
    "login": "overtrue",
    "id": 1472352,
    "avatar_url": "https://avatars.githubusercontent.com/u/1472352?v=3",
    "gravatar_id": "",
    "url": "https://api.github.com/users/overtrue",
    "html_url": "https://github.com/overtrue",
    ...
  },
  "token": {
    "access_token": "5b1dc56d64fffbd052359f032716cc4e0a1cb9a0",
    "token_type": "bearer",
    "scope": "user:email"
  }
}
```

You can fetch the user attribute as a array key like this:

```php
$user['id'];        // 1472352
$user['nickname'];  // "overtrue"
$user['name'];      // "安正超"
$user['email'];     // "anzhengchao@gmail.com"
...
```

Or using method:

```php
$user->getId();
$user->getNickname();
$user->getName();
$user->getEmail();
$user->getAvatar();
$user->getOriginal();
$user->getToken();// or $user->getAccessToken()
$user->getProviderName(); // GitHub/Google/Facebook...
```

#### Get original response from OAuth API

The `$user->getOriginal()` method will return an array of the API raw response.

#### Get access token Object

You can get the access token instance of current session by call `$user->getToken()` or `$user->getAccessToken()` or `$user['token']` .


### Get user with access token

```php
$accessToken = new AccessToken(['access_token' => $accessToken]);
$user = $socialite->user($accessToken);
```


### Custom Session or Request instance.

You can set the request with your custom `Request` instance which instanceof `Symfony\Component\HttpFoundation\Request` before you call `driver` method.


```php

$request = new Request(); // or use AnotherCustomRequest.

$socialite = new SocialiteManager($config, $request);
```

Or set request to `SocialiteManager` instance:

```php
$socialite->setRequest($request);
```

You can get the request from `SocialiteManager` instance by `getRequest()`:

```php
$request = $socialite->getRequest();
```

#### Set custom session manager.

By default, the `SocialiteManager` use `Symfony\Component\HttpFoundation\Session\Session` instance as session manager, you can change it as following lines:

```php
$session = new YourCustomSessionManager();
$socialite->getRequest()->setSession($session);
```

> Your custom session manager must be implement the [`Symfony\Component\HttpFoundation\Session\SessionInterface`](http://api.symfony.com/3.0/Symfony/Component/HttpFoundation/Session/SessionInterface.html).

Enjoy it! :heart:

# Reference

- [Google - OpenID Connect](https://developers.google.com/identity/protocols/OpenIDConnect)
- [Facebook - Graph API](https://developers.facebook.com/docs/graph-api)
- [Linkedin - Authenticating with OAuth 2.0](https://developer.linkedin.com/docs/oauth2)
- [微博 - OAuth 2.0 授权机制说明](http://open.weibo.com/wiki/%E6%8E%88%E6%9D%83%E6%9C%BA%E5%88%B6%E8%AF%B4%E6%98%8E)
- [QQ - OAuth 2.0 登录QQ](http://wiki.connect.qq.com/oauth2-0%E7%AE%80%E4%BB%8B)
- [微信公众平台 - OAuth文档](http://mp.weixin.qq.com/wiki/9/01f711493b5a02f24b04365ac5d8fd95.html)
- [微信开放平台 - 网站应用微信登录开发指南](https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=&lang=zh_CN)
- [微信开放平台 - 代公众号发起网页授权](https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419318590&token=&lang=zh_CN)
- [豆瓣 - OAuth 2.0 授权机制说明](http://developers.douban.com/wiki/?title=oauth2)

# License

MIT
