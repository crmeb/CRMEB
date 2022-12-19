<?php
/*
 * Copyright 2015 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Auth\Credentials;

/*
 * The AppIdentityService class is automatically defined on App Engine,
 * so including this dependency is not necessary, and will result in a
 * PHP fatal error in the App Engine environment.
 */
use google\appengine\api\app_identity\AppIdentityService;
use Google\Auth\CredentialsLoader;
use Google\Auth\ProjectIdProviderInterface;
use Google\Auth\SignBlobInterface;

/**
 * @deprecated
 *
 * AppIdentityCredentials supports authorization on Google App Engine.
 *
 * It can be used to authorize requests using the AuthTokenMiddleware or
 * AuthTokenSubscriber, but will only succeed if being run on App Engine:
 *
 * Example:
 * ```
 * use Google\Auth\Credentials\AppIdentityCredentials;
 * use Google\Auth\Middleware\AuthTokenMiddleware;
 * use GuzzleHttp\Client;
 * use GuzzleHttp\HandlerStack;
 *
 * $gae = new AppIdentityCredentials('https://www.googleapis.com/auth/books');
 * $middleware = new AuthTokenMiddleware($gae);
 * $stack = HandlerStack::create();
 * $stack->push($middleware);
 *
 * $client = new Client([
 *     'handler' => $stack,
 *     'base_uri' => 'https://www.googleapis.com/books/v1',
 *     'auth' => 'google_auth'
 * ]);
 *
 * $res = $client->get('volumes?q=Henry+David+Thoreau&country=US');
 * ```
 */
class AppIdentityCredentials extends CredentialsLoader implements
    SignBlobInterface,
    ProjectIdProviderInterface
{
    /**
     * Result of fetchAuthToken.
     *
     * @var array<mixed>
     */
    protected $lastReceivedToken;

    /**
     * Array of OAuth2 scopes to be requested.
     *
     * @var string[]
     */
    private $scope;

    /**
     * @var string
     */
    private $clientName;

    /**
     * @param string|string[] $scope One or more scopes.
     */
    public function __construct($scope = [])
    {
        $this->scope = is_array($scope) ? $scope : explode(' ', (string) $scope);
    }

    /**
     * Determines if this an App Engine instance, by accessing the
     * SERVER_SOFTWARE environment variable (prod) or the APPENGINE_RUNTIME
     * environment variable (dev).
     *
     * @return bool true if this an App Engine Instance, false otherwise
     */
    public static function onAppEngine()
    {
        $appEngineProduction = isset($_SERVER['SERVER_SOFTWARE']) &&
            0 === strpos($_SERVER['SERVER_SOFTWARE'], 'Google App Engine');
        if ($appEngineProduction) {
            return true;
        }
        $appEngineDevAppServer = isset($_SERVER['APPENGINE_RUNTIME']) &&
            $_SERVER['APPENGINE_RUNTIME'] == 'php';
        if ($appEngineDevAppServer) {
            return true;
        }
        return false;
    }

    /**
     * Implements FetchAuthTokenInterface#fetchAuthToken.
     *
     * Fetches the auth tokens using the AppIdentityService if available.
     * As the AppIdentityService uses protobufs to fetch the access token,
     * the GuzzleHttp\ClientInterface instance passed in will not be used.
     *
     * @param callable $httpHandler callback which delivers psr7 request
     * @return array<mixed> {
     *     A set of auth related metadata, containing the following
     *
     *     @type string $access_token
     *     @type string $expiration_time
     * }
     */
    public function fetchAuthToken(callable $httpHandler = null)
    {
        try {
            $this->checkAppEngineContext();
        } catch (\Exception $e) {
            return [];
        }

        /** @phpstan-ignore-next-line */
        $token = AppIdentityService::getAccessToken($this->scope);
        $this->lastReceivedToken = $token;

        return $token;
    }

    /**
     * Sign a string using AppIdentityService.
     *
     * @param string $stringToSign The string to sign.
     * @param bool $forceOpenSsl [optional] Does not apply to this credentials
     *        type.
     * @return string The signature, base64-encoded.
     * @throws \Exception If AppEngine SDK or mock is not available.
     */
    public function signBlob($stringToSign, $forceOpenSsl = false)
    {
        $this->checkAppEngineContext();

        /** @phpstan-ignore-next-line */
        return base64_encode(AppIdentityService::signForApp($stringToSign)['signature']);
    }

    /**
     * Get the project ID from AppIdentityService.
     *
     * Returns null if AppIdentityService is unavailable.
     *
     * @param callable $httpHandler Not used by this type.
     * @return string|null
     */
    public function getProjectId(callable $httpHandler = null)
    {
        try {
            $this->checkAppEngineContext();
        } catch (\Exception $e) {
            return null;
        }

        /** @phpstan-ignore-next-line */
        return AppIdentityService::getApplicationId();
    }

    /**
     * Get the client name from AppIdentityService.
     *
     * Subsequent calls to this method will return a cached value.
     *
     * @param callable $httpHandler Not used in this implementation.
     * @return string
     * @throws \Exception If AppEngine SDK or mock is not available.
     */
    public function getClientName(callable $httpHandler = null)
    {
        $this->checkAppEngineContext();

        if (!$this->clientName) {
            /** @phpstan-ignore-next-line */
            $this->clientName = AppIdentityService::getServiceAccountName();
        }

        return $this->clientName;
    }

    /**
     * @return array{access_token:string,expires_at:int}|null
     */
    public function getLastReceivedToken()
    {
        if ($this->lastReceivedToken) {
            return [
                'access_token' => $this->lastReceivedToken['access_token'],
                'expires_at' => $this->lastReceivedToken['expiration_time'],
            ];
        }

        return null;
    }

    /**
     * Caching is handled by the underlying AppIdentityService, return empty string
     * to prevent caching.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return '';
    }

    /**
     * @return void
     */
    private function checkAppEngineContext()
    {
        if (!self::onAppEngine() || !class_exists('google\appengine\api\app_identity\AppIdentityService')) {
            throw new \Exception(
                'This class must be run in App Engine, or you must include the AppIdentityService '
                . 'mock class defined in tests/mocks/AppIdentityService.php'
            );
        }
    }
}
