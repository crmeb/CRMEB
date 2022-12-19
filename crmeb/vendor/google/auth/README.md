# Google Auth Library for PHP

<dl>
  <dt>Homepage</dt><dd><a href="http://www.github.com/google/google-auth-library-php">http://www.github.com/google/google-auth-library-php</a></dd>
  <dt>Reference Docs</dt><dd><a href="https://googleapis.github.io/google-auth-library-php/main/">https://googleapis.github.io/google-auth-library-php/main/</a></dd>
  <dt>Authors</dt>
    <dd><a href="mailto:temiola@google.com">Tim Emiola</a></dd>
    <dd><a href="mailto:stanleycheung@google.com">Stanley Cheung</a></dd>
    <dd><a href="mailto:betterbrent@google.com">Brent Shaffer</a></dd>
  <dt>Copyright</dt><dd>Copyright © 2015 Google, Inc.</dd>
  <dt>License</dt><dd>Apache 2.0</dd>
</dl>

## Description

This is Google's officially supported PHP client library for using OAuth 2.0
authorization and authentication with Google APIs.

### Installing via Composer

The recommended way to install the google auth library is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version:

```bash
composer.phar require google/auth
```

## Application Default Credentials

This library provides an implementation of
[application default credentials][application default credentials] for PHP.

The Application Default Credentials provide a simple way to get authorization
credentials for use in calling Google APIs.

They are best suited for cases when the call needs to have the same identity
and authorization level for the application independent of the user. This is
the recommended approach to authorize calls to Cloud APIs, particularly when
you're building an application that uses Google Compute Engine.

#### Download your Service Account Credentials JSON file

To use `Application Default Credentials`, You first need to download a set of
JSON credentials for your project. Go to **APIs & Services** > **Credentials** in
the [Google Developers Console][developer console] and select
**Service account** from the **Add credentials** dropdown.

> This file is your *only copy* of these credentials. It should never be
> committed with your source code, and should be stored securely.

Once downloaded, store the path to this file in the
`GOOGLE_APPLICATION_CREDENTIALS` environment variable.

```php
putenv('GOOGLE_APPLICATION_CREDENTIALS=/path/to/my/credentials.json');
```

> PHP's `putenv` function is just one way to set an environment variable.
> Consider using `.htaccess` or apache configuration files as well.

#### Enable the API you want to use

Before making your API call, you must be sure the API you're calling has been
enabled. Go to **APIs & Auth** > **APIs** in the
[Google Developers Console][developer console] and enable the APIs you'd like to
call. For the example below, you must enable the `Drive API`.

#### Call the APIs

As long as you update the environment variable below to point to *your* JSON
credentials file, the following code should output a list of your Drive files.

```php
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

// specify the path to your application credentials
putenv('GOOGLE_APPLICATION_CREDENTIALS=/path/to/my/credentials.json');

// define the scopes for your API call
$scopes = ['https://www.googleapis.com/auth/drive.readonly'];

// create middleware
$middleware = ApplicationDefaultCredentials::getMiddleware($scopes);
$stack = HandlerStack::create();
$stack->push($middleware);

// create the HTTP client
$client = new Client([
  'handler' => $stack,
  'base_uri' => 'https://www.googleapis.com',
  'auth' => 'google_auth'  // authorize all requests
]);

// make the request
$response = $client->get('drive/v2/files');

// show the result!
print_r((string) $response->getBody());
```

##### Guzzle 5 Compatibility

If you are using [Guzzle 5][Guzzle 5], replace the `create middleware` and
`create the HTTP Client` steps with the following:

```php
// create the HTTP client
$client = new Client([
  'base_url' => 'https://www.googleapis.com',
  'auth' => 'google_auth'  // authorize all requests
]);

// create subscriber
$subscriber = ApplicationDefaultCredentials::getSubscriber($scopes);
$client->getEmitter()->attach($subscriber);
```

#### Call using an ID Token
If your application is running behind Cloud Run, or using Cloud Identity-Aware
Proxy (IAP), you will need to fetch an ID token to access your application. For
this, use the static method `getIdTokenMiddleware` on
`ApplicationDefaultCredentials`.

```php
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

// specify the path to your application credentials
putenv('GOOGLE_APPLICATION_CREDENTIALS=/path/to/my/credentials.json');

// Provide the ID token audience. This can be a Client ID associated with an IAP application,
// Or the URL associated with a CloudRun App
//    $targetAudience = 'IAP_CLIENT_ID.apps.googleusercontent.com';
//    $targetAudience = 'https://service-1234-uc.a.run.app';
$targetAudience = 'YOUR_ID_TOKEN_AUDIENCE';

// create middleware
$middleware = ApplicationDefaultCredentials::getIdTokenMiddleware($targetAudience);
$stack = HandlerStack::create();
$stack->push($middleware);

// create the HTTP client
$client = new Client([
  'handler' => $stack,
  'auth' => 'google_auth',
  // Cloud Run, IAP, or custom resource URL
  'base_uri' => 'https://YOUR_PROTECTED_RESOURCE',
]);

// make the request
$response = $client->get('/');

// show the result!
print_r((string) $response->getBody());
```

For invoking Cloud Run services, your service account will need the
[`Cloud Run Invoker`](https://cloud.google.com/run/docs/authenticating/service-to-service)
IAM permission.

For invoking Cloud Identity-Aware Proxy, you will need to pass the Client ID
used when you set up your protected resource as the target audience. See how to
[secure your IAP app with signed headers](https://cloud.google.com/iap/docs/signed-headers-howto).

#### Call using a specific JSON key
If you want to use a specific JSON key instead of using `GOOGLE_APPLICATION_CREDENTIALS` environment variable, you can
 do this:

```php
use Google\Auth\CredentialsLoader;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

// Define the Google Application Credentials array
$jsonKey = ['key' => 'value'];

// define the scopes for your API call
$scopes = ['https://www.googleapis.com/auth/drive.readonly'];

// Load credentials
$creds = CredentialsLoader::makeCredentials($scopes, $jsonKey);

// optional caching
// $creds = new FetchAuthTokenCache($creds, $cacheConfig, $cache);

// create middleware
$middleware = new AuthTokenMiddleware($creds);
$stack = HandlerStack::create();
$stack->push($middleware);

// create the HTTP client
$client = new Client([
  'handler' => $stack,
  'base_uri' => 'https://www.googleapis.com',
  'auth' => 'google_auth'  // authorize all requests
]);

// make the request
$response = $client->get('drive/v2/files');

// show the result!
print_r((string) $response->getBody());

```

#### Call using Proxy-Authorization Header
If your application is behind a proxy such as [Google Cloud IAP][iap-proxy-header],
and your application occupies the `Authorization` request header,
you can include the ID token in a `Proxy-Authorization: Bearer`
header instead. If a valid ID token is found in a `Proxy-Authorization` header,
IAP authorizes the request with it. After authorizing the request, IAP passes
the Authorization header to your application without processing the content.
For this, use the static method `getProxyIdTokenMiddleware` on
`ApplicationDefaultCredentials`.

```php
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

// specify the path to your application credentials
putenv('GOOGLE_APPLICATION_CREDENTIALS=/path/to/my/credentials.json');

// Provide the ID token audience. This can be a Client ID associated with an IAP application
//    $targetAudience = 'IAP_CLIENT_ID.apps.googleusercontent.com';
$targetAudience = 'YOUR_ID_TOKEN_AUDIENCE';

// create middleware
$middleware = ApplicationDefaultCredentials::getProxyIdTokenMiddleware($targetAudience);
$stack = HandlerStack::create();
$stack->push($middleware);

// create the HTTP client
$client = new Client([
  'handler' => $stack,
  'auth' => ['username', 'pass'], // auth option handled by your application
  'proxy_auth' => 'google_auth',
]);

// make the request
$response = $client->get('/');

// show the result!
print_r((string) $response->getBody());
```

[iap-proxy-header]: https://cloud.google.com/iap/docs/authentication-howto#authenticating_from_proxy-authorization_header

#### Verifying JWTs

If you are [using Google ID tokens to authenticate users][google-id-tokens], use
the `Google\Auth\AccessToken` class to verify the ID token:

```php
use Google\Auth\AccessToken;

$auth = new AccessToken();
$auth->verify($idToken);
```

If your app is running behind [Google Identity-Aware Proxy][iap-id-tokens]
(IAP), you can verify the ID token coming from the IAP server by pointing to the
appropriate certificate URL for IAP. This is because IAP signs the ID
tokens with a different key than the Google Identity service:

```php
use Google\Auth\AccessToken;

$auth = new AccessToken();
$auth->verify($idToken, [
  'certsLocation' => AccessToken::IAP_CERT_URL
]);
```

[google-id-tokens]: https://developers.google.com/identity/sign-in/web/backend-auth
[iap-id-tokens]: https://cloud.google.com/iap/docs/signed-headers-howto

## License

This library is licensed under Apache 2.0. Full license text is
available in [COPYING][copying].

## Contributing

See [CONTRIBUTING][contributing].

## Support

Please
[report bugs at the project on Github](https://github.com/google/google-auth-library-php/issues). Don't
hesitate to
[ask questions](http://stackoverflow.com/questions/tagged/google-auth-library-php)
about the client or APIs on [StackOverflow](http://stackoverflow.com).

[google-apis-php-client]: https://github.com/google/google-api-php-client
[application default credentials]: https://developers.google.com/accounts/docs/application-default-credentials
[contributing]: https://github.com/google/google-auth-library-php/tree/main/.github/CONTRIBUTING.md
[copying]: https://github.com/google/google-auth-library-php/tree/main/COPYING
[Guzzle]: https://github.com/guzzle/guzzle
[Guzzle 5]: http://docs.guzzlephp.org/en/5.3
[developer console]: https://console.developers.google.com
