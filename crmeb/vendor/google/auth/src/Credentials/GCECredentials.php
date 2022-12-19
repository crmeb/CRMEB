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

use Google\Auth\CredentialsLoader;
use Google\Auth\GetQuotaProjectInterface;
use Google\Auth\HttpHandler\HttpClientCache;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google\Auth\Iam;
use Google\Auth\ProjectIdProviderInterface;
use Google\Auth\SignBlobInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;

/**
 * GCECredentials supports authorization on Google Compute Engine.
 *
 * It can be used to authorize requests using the AuthTokenMiddleware, but will
 * only succeed if being run on GCE:
 *
 *   use Google\Auth\Credentials\GCECredentials;
 *   use Google\Auth\Middleware\AuthTokenMiddleware;
 *   use GuzzleHttp\Client;
 *   use GuzzleHttp\HandlerStack;
 *
 *   $gce = new GCECredentials();
 *   $middleware = new AuthTokenMiddleware($gce);
 *   $stack = HandlerStack::create();
 *   $stack->push($middleware);
 *
 *   $client = new Client([
 *      'handler' => $stack,
 *      'base_uri' => 'https://www.googleapis.com/taskqueue/v1beta2/projects/',
 *      'auth' => 'google_auth'
 *   ]);
 *
 *   $res = $client->get('myproject/taskqueues/myqueue');
 */
class GCECredentials extends CredentialsLoader implements
    SignBlobInterface,
    ProjectIdProviderInterface,
    GetQuotaProjectInterface
{
    // phpcs:disable
    const cacheKey = 'GOOGLE_AUTH_PHP_GCE';
    // phpcs:enable

    /**
     * The metadata IP address on appengine instances.
     *
     * The IP is used instead of the domain 'metadata' to avoid slow responses
     * when not on Compute Engine.
     */
    const METADATA_IP = '169.254.169.254';

    /**
     * The metadata path of the default token.
     */
    const TOKEN_URI_PATH = 'v1/instance/service-accounts/default/token';

    /**
     * The metadata path of the default id token.
     */
    const ID_TOKEN_URI_PATH = 'v1/instance/service-accounts/default/identity';

    /**
     * The metadata path of the client ID.
     */
    const CLIENT_ID_URI_PATH = 'v1/instance/service-accounts/default/email';

    /**
     * The metadata path of the project ID.
     */
    const PROJECT_ID_URI_PATH = 'v1/project/project-id';

    /**
     * The header whose presence indicates GCE presence.
     */
    const FLAVOR_HEADER = 'Metadata-Flavor';

    /**
     * Note: the explicit `timeout` and `tries` below is a workaround. The underlying
     * issue is that resolving an unknown host on some networks will take
     * 20-30 seconds; making this timeout short fixes the issue, but
     * could lead to false negatives in the event that we are on GCE, but
     * the metadata resolution was particularly slow. The latter case is
     * "unlikely" since the expected 4-nines time is about 0.5 seconds.
     * This allows us to limit the total ping maximum timeout to 1.5 seconds
     * for developer desktop scenarios.
     */
    const MAX_COMPUTE_PING_TRIES = 3;
    const COMPUTE_PING_CONNECTION_TIMEOUT_S = 0.5;

    /**
     * Flag used to ensure that the onGCE test is only done once;.
     *
     * @var bool
     */
    private $hasCheckedOnGce = false;

    /**
     * Flag that stores the value of the onGCE check.
     *
     * @var bool
     */
    private $isOnGce = false;

    /**
     * Result of fetchAuthToken.
     *
     * @var array<mixed>
     */
    protected $lastReceivedToken;

    /**
     * @var string|null
     */
    private $clientName;

    /**
     * @var string|null
     */
    private $projectId;

    /**
     * @var Iam|null
     */
    private $iam;

    /**
     * @var string
     */
    private $tokenUri;

    /**
     * @var string
     */
    private $targetAudience;

    /**
     * @var string|null
     */
    private $quotaProject;

    /**
     * @var string|null
     */
    private $serviceAccountIdentity;

    /**
     * @param Iam $iam [optional] An IAM instance.
     * @param string|string[] $scope [optional] the scope of the access request,
     *        expressed either as an array or as a space-delimited string.
     * @param string $targetAudience [optional] The audience for the ID token.
     * @param string $quotaProject [optional] Specifies a project to bill for access
     *   charges associated with the request.
     * @param string $serviceAccountIdentity [optional] Specify a service
     *   account identity name to use instead of "default".
     */
    public function __construct(
        Iam $iam = null,
        $scope = null,
        $targetAudience = null,
        $quotaProject = null,
        $serviceAccountIdentity = null
    ) {
        $this->iam = $iam;

        if ($scope && $targetAudience) {
            throw new InvalidArgumentException(
                'Scope and targetAudience cannot both be supplied'
            );
        }

        $tokenUri = self::getTokenUri($serviceAccountIdentity);
        if ($scope) {
            if (is_string($scope)) {
                $scope = explode(' ', $scope);
            }

            $scope = implode(',', $scope);

            $tokenUri = $tokenUri . '?scopes=' . $scope;
        } elseif ($targetAudience) {
            $tokenUri = self::getIdTokenUri($serviceAccountIdentity);
            $tokenUri = $tokenUri . '?audience=' . $targetAudience;
            $this->targetAudience = $targetAudience;
        }

        $this->tokenUri = $tokenUri;
        $this->quotaProject = $quotaProject;
        $this->serviceAccountIdentity = $serviceAccountIdentity;
    }

    /**
     * The full uri for accessing the default token.
     *
     * @param string $serviceAccountIdentity [optional] Specify a service
     *   account identity name to use instead of "default".
     * @return string
     */
    public static function getTokenUri($serviceAccountIdentity = null)
    {
        $base = 'http://' . self::METADATA_IP . '/computeMetadata/';
        $base .= self::TOKEN_URI_PATH;

        if ($serviceAccountIdentity) {
            return str_replace(
                '/default/',
                '/' . $serviceAccountIdentity . '/',
                $base
            );
        }
        return $base;
    }

    /**
     * The full uri for accessing the default service account.
     *
     * @param string $serviceAccountIdentity [optional] Specify a service
     *   account identity name to use instead of "default".
     * @return string
     */
    public static function getClientNameUri($serviceAccountIdentity = null)
    {
        $base = 'http://' . self::METADATA_IP . '/computeMetadata/';
        $base .= self::CLIENT_ID_URI_PATH;

        if ($serviceAccountIdentity) {
            return str_replace(
                '/default/',
                '/' . $serviceAccountIdentity . '/',
                $base
            );
        }

        return $base;
    }

    /**
     * The full uri for accesesing the default identity token.
     *
     * @param string $serviceAccountIdentity [optional] Specify a service
     *   account identity name to use instead of "default".
     * @return string
     */
    private static function getIdTokenUri($serviceAccountIdentity = null)
    {
        $base = 'http://' . self::METADATA_IP . '/computeMetadata/';
        $base .= self::ID_TOKEN_URI_PATH;

        if ($serviceAccountIdentity) {
            return str_replace(
                '/default/',
                '/' . $serviceAccountIdentity . '/',
                $base
            );
        }

        return $base;
    }

    /**
     * The full uri for accessing the default project ID.
     *
     * @return string
     */
    private static function getProjectIdUri()
    {
        $base = 'http://' . self::METADATA_IP . '/computeMetadata/';

        return $base . self::PROJECT_ID_URI_PATH;
    }

    /**
     * Determines if this an App Engine Flexible instance, by accessing the
     * GAE_INSTANCE environment variable.
     *
     * @return bool true if this an App Engine Flexible Instance, false otherwise
     */
    public static function onAppEngineFlexible()
    {
        return substr((string) getenv('GAE_INSTANCE'), 0, 4) === 'aef-';
    }

    /**
     * Determines if this a GCE instance, by accessing the expected metadata
     * host.
     * If $httpHandler is not specified a the default HttpHandler is used.
     *
     * @param callable $httpHandler callback which delivers psr7 request
     * @return bool True if this a GCEInstance, false otherwise
     */
    public static function onGce(callable $httpHandler = null)
    {
        $httpHandler = $httpHandler
            ?: HttpHandlerFactory::build(HttpClientCache::getHttpClient());

        $checkUri = 'http://' . self::METADATA_IP;
        for ($i = 1; $i <= self::MAX_COMPUTE_PING_TRIES; $i++) {
            try {
                // Comment from: oauth2client/client.py
                //
                // Note: the explicit `timeout` below is a workaround. The underlying
                // issue is that resolving an unknown host on some networks will take
                // 20-30 seconds; making this timeout short fixes the issue, but
                // could lead to false negatives in the event that we are on GCE, but
                // the metadata resolution was particularly slow. The latter case is
                // "unlikely".
                $resp = $httpHandler(
                    new Request(
                        'GET',
                        $checkUri,
                        [self::FLAVOR_HEADER => 'Google']
                    ),
                    ['timeout' => self::COMPUTE_PING_CONNECTION_TIMEOUT_S]
                );

                return $resp->getHeaderLine(self::FLAVOR_HEADER) == 'Google';
            } catch (ClientException $e) {
            } catch (ServerException $e) {
            } catch (RequestException $e) {
            } catch (ConnectException $e) {
            }
        }
        return false;
    }

    /**
     * Implements FetchAuthTokenInterface#fetchAuthToken.
     *
     * Fetches the auth tokens from the GCE metadata host if it is available.
     * If $httpHandler is not specified a the default HttpHandler is used.
     *
     * @param callable $httpHandler callback which delivers psr7 request
     *
     * @return array<mixed> {
     *     A set of auth related metadata, based on the token type.
     *
     *     @type string $access_token for access tokens
     *     @type int    $expires_in   for access tokens
     *     @type string $token_type   for access tokens
     *     @type string $id_token     for ID tokens
     * }
     * @throws \Exception
     */
    public function fetchAuthToken(callable $httpHandler = null)
    {
        $httpHandler = $httpHandler
            ?: HttpHandlerFactory::build(HttpClientCache::getHttpClient());

        if (!$this->hasCheckedOnGce) {
            $this->isOnGce = self::onGce($httpHandler);
            $this->hasCheckedOnGce = true;
        }
        if (!$this->isOnGce) {
            return array();  // return an empty array with no access token
        }

        $response = $this->getFromMetadata($httpHandler, $this->tokenUri);

        if ($this->targetAudience) {
            return ['id_token' => $response];
        }

        if (null === $json = json_decode($response, true)) {
            throw new \Exception('Invalid JSON response');
        }

        $json['expires_at'] = time() + $json['expires_in'];

        // store this so we can retrieve it later
        $this->lastReceivedToken = $json;

        return $json;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return self::cacheKey;
    }

    /**
     * @return array{access_token:string,expires_at:int}|null
     */
    public function getLastReceivedToken()
    {
        if ($this->lastReceivedToken) {
            return [
                'access_token' => $this->lastReceivedToken['access_token'],
                'expires_at' => $this->lastReceivedToken['expires_at'],
            ];
        }

        return null;
    }

    /**
     * Get the client name from GCE metadata.
     *
     * Subsequent calls will return a cached value.
     *
     * @param callable $httpHandler callback which delivers psr7 request
     * @return string
     */
    public function getClientName(callable $httpHandler = null)
    {
        if ($this->clientName) {
            return $this->clientName;
        }

        $httpHandler = $httpHandler
            ?: HttpHandlerFactory::build(HttpClientCache::getHttpClient());

        if (!$this->hasCheckedOnGce) {
            $this->isOnGce = self::onGce($httpHandler);
            $this->hasCheckedOnGce = true;
        }

        if (!$this->isOnGce) {
            return '';
        }

        $this->clientName = $this->getFromMetadata(
            $httpHandler,
            self::getClientNameUri($this->serviceAccountIdentity)
        );

        return $this->clientName;
    }

    /**
     * Sign a string using the default service account private key.
     *
     * This implementation uses IAM's signBlob API.
     *
     * @see https://cloud.google.com/iam/credentials/reference/rest/v1/projects.serviceAccounts/signBlob SignBlob
     *
     * @param string $stringToSign The string to sign.
     * @param bool $forceOpenSsl [optional] Does not apply to this credentials
     *        type.
     * @param string $accessToken The access token to use to sign the blob. If
     *        provided, saves a call to the metadata server for a new access
     *        token. **Defaults to** `null`.
     * @return string
     */
    public function signBlob($stringToSign, $forceOpenSsl = false, $accessToken = null)
    {
        $httpHandler = HttpHandlerFactory::build(HttpClientCache::getHttpClient());

        // Providing a signer is useful for testing, but it's undocumented
        // because it's not something a user would generally need to do.
        $signer = $this->iam ?: new Iam($httpHandler);

        $email = $this->getClientName($httpHandler);

        if (is_null($accessToken)) {
            $previousToken = $this->getLastReceivedToken();
            $accessToken = $previousToken
                ? $previousToken['access_token']
                : $this->fetchAuthToken($httpHandler)['access_token'];
        }

        return $signer->signBlob($email, $accessToken, $stringToSign);
    }

    /**
     * Fetch the default Project ID from compute engine.
     *
     * Returns null if called outside GCE.
     *
     * @param callable $httpHandler Callback which delivers psr7 request
     * @return string|null
     */
    public function getProjectId(callable $httpHandler = null)
    {
        if ($this->projectId) {
            return $this->projectId;
        }

        $httpHandler = $httpHandler
            ?: HttpHandlerFactory::build(HttpClientCache::getHttpClient());

        if (!$this->hasCheckedOnGce) {
            $this->isOnGce = self::onGce($httpHandler);
            $this->hasCheckedOnGce = true;
        }

        if (!$this->isOnGce) {
            return null;
        }

        $this->projectId = $this->getFromMetadata($httpHandler, self::getProjectIdUri());
        return $this->projectId;
    }

    /**
     * Fetch the value of a GCE metadata server URI.
     *
     * @param callable $httpHandler An HTTP Handler to deliver PSR7 requests.
     * @param string $uri The metadata URI.
     * @return string
     */
    private function getFromMetadata(callable $httpHandler, $uri)
    {
        $resp = $httpHandler(
            new Request(
                'GET',
                $uri,
                [self::FLAVOR_HEADER => 'Google']
            )
        );

        return (string) $resp->getBody();
    }

    /**
     * Get the quota project used for this API request
     *
     * @return string|null
     */
    public function getQuotaProject()
    {
        return $this->quotaProject;
    }
}
