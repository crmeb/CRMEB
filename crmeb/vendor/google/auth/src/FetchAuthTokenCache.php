<?php
/*
 * Copyright 2010 Google Inc.
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

namespace Google\Auth;

use Psr\Cache\CacheItemPoolInterface;

/**
 * A class to implement caching for any object implementing
 * FetchAuthTokenInterface
 */
class FetchAuthTokenCache implements
    FetchAuthTokenInterface,
    GetQuotaProjectInterface,
    SignBlobInterface,
    ProjectIdProviderInterface,
    UpdateMetadataInterface
{
    use CacheTrait;

    /**
     * @var FetchAuthTokenInterface
     */
    private $fetcher;

    /**
     * @param FetchAuthTokenInterface $fetcher A credentials fetcher
     * @param array<mixed> $cacheConfig Configuration for the cache
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(
        FetchAuthTokenInterface $fetcher,
        array $cacheConfig = null,
        CacheItemPoolInterface $cache
    ) {
        $this->fetcher = $fetcher;
        $this->cache = $cache;
        $this->cacheConfig = array_merge([
            'lifetime' => 1500,
            'prefix' => '',
        ], (array) $cacheConfig);
    }

    /**
     * Implements FetchAuthTokenInterface#fetchAuthToken.
     *
     * Checks the cache for a valid auth token and fetches the auth tokens
     * from the supplied fetcher.
     *
     * @param callable $httpHandler callback which delivers psr7 request
     * @return array<mixed> the response
     * @throws \Exception
     */
    public function fetchAuthToken(callable $httpHandler = null)
    {
        if ($cached = $this->fetchAuthTokenFromCache()) {
            return $cached;
        }

        $auth_token = $this->fetcher->fetchAuthToken($httpHandler);

        $this->saveAuthTokenInCache($auth_token);

        return $auth_token;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->getFullCacheKey($this->fetcher->getCacheKey());
    }

    /**
     * @return array<mixed>|null
     */
    public function getLastReceivedToken()
    {
        return $this->fetcher->getLastReceivedToken();
    }

    /**
     * Get the client name from the fetcher.
     *
     * @param callable $httpHandler An HTTP handler to deliver PSR7 requests.
     * @return string
     */
    public function getClientName(callable $httpHandler = null)
    {
        if (!$this->fetcher instanceof SignBlobInterface) {
            throw new \RuntimeException(
                'Credentials fetcher does not implement ' .
                'Google\Auth\SignBlobInterface'
            );
        }

        return $this->fetcher->getClientName($httpHandler);
    }

    /**
     * Sign a blob using the fetcher.
     *
     * @param string $stringToSign The string to sign.
     * @param bool $forceOpenSsl Require use of OpenSSL for local signing. Does
     *        not apply to signing done using external services. **Defaults to**
     *        `false`.
     * @return string The resulting signature.
     * @throws \RuntimeException If the fetcher does not implement
     *     `Google\Auth\SignBlobInterface`.
     */
    public function signBlob($stringToSign, $forceOpenSsl = false)
    {
        if (!$this->fetcher instanceof SignBlobInterface) {
            throw new \RuntimeException(
                'Credentials fetcher does not implement ' .
                'Google\Auth\SignBlobInterface'
            );
        }

        // Pass the access token from cache to GCECredentials for signing a blob.
        // This saves a call to the metadata server when a cached token exists.
        if ($this->fetcher instanceof Credentials\GCECredentials) {
            $cached = $this->fetchAuthTokenFromCache();
            $accessToken = isset($cached['access_token']) ? $cached['access_token'] : null;
            return $this->fetcher->signBlob($stringToSign, $forceOpenSsl, $accessToken);
        }

        return $this->fetcher->signBlob($stringToSign, $forceOpenSsl);
    }

    /**
     * Get the quota project used for this API request from the credentials
     * fetcher.
     *
     * @return string|null
     */
    public function getQuotaProject()
    {
        if ($this->fetcher instanceof GetQuotaProjectInterface) {
            return $this->fetcher->getQuotaProject();
        }

        return null;
    }

    /*
     * Get the Project ID from the fetcher.
     *
     * @param callable $httpHandler Callback which delivers psr7 request
     * @return string|null
     * @throws \RuntimeException If the fetcher does not implement
     *     `Google\Auth\ProvidesProjectIdInterface`.
     */
    public function getProjectId(callable $httpHandler = null)
    {
        if (!$this->fetcher instanceof ProjectIdProviderInterface) {
            throw new \RuntimeException(
                'Credentials fetcher does not implement ' .
                'Google\Auth\ProvidesProjectIdInterface'
            );
        }

        return $this->fetcher->getProjectId($httpHandler);
    }

    /**
     * Updates metadata with the authorization token.
     *
     * @param array<mixed> $metadata metadata hashmap
     * @param string $authUri optional auth uri
     * @param callable $httpHandler callback which delivers psr7 request
     * @return array<mixed> updated metadata hashmap
     * @throws \RuntimeException If the fetcher does not implement
     *     `Google\Auth\UpdateMetadataInterface`.
     */
    public function updateMetadata(
        $metadata,
        $authUri = null,
        callable $httpHandler = null
    ) {
        if (!$this->fetcher instanceof UpdateMetadataInterface) {
            throw new \RuntimeException(
                'Credentials fetcher does not implement ' .
                'Google\Auth\UpdateMetadataInterface'
            );
        }

        $cached = $this->fetchAuthTokenFromCache($authUri);
        if ($cached) {
            // Set the access token in the `Authorization` metadata header so
            // the downstream call to updateMetadata know they don't need to
            // fetch another token.
            if (isset($cached['access_token'])) {
                $metadata[self::AUTH_METADATA_KEY] = [
                    'Bearer ' . $cached['access_token']
                ];
            }
        }

        $newMetadata = $this->fetcher->updateMetadata(
            $metadata,
            $authUri,
            $httpHandler
        );

        if (!$cached && $token = $this->fetcher->getLastReceivedToken()) {
            $this->saveAuthTokenInCache($token, $authUri);
        }

        return $newMetadata;
    }

    /**
     * @param string|null $authUri
     * @return array<mixed>|null
     */
    private function fetchAuthTokenFromCache($authUri = null)
    {
        // Use the cached value if its available.
        //
        // TODO: correct caching; update the call to setCachedValue to set the expiry
        // to the value returned with the auth token.
        //
        // TODO: correct caching; enable the cache to be cleared.

        // if $authUri is set, use it as the cache key
        $cacheKey = $authUri
            ? $this->getFullCacheKey($authUri)
            : $this->fetcher->getCacheKey();

        $cached = $this->getCachedValue($cacheKey);
        if (is_array($cached)) {
            if (empty($cached['expires_at'])) {
                // If there is no expiration data, assume token is not expired.
                // (for JwtAccess and ID tokens)
                return $cached;
            }
            if (time() < $cached['expires_at']) {
                // access token is not expired
                return $cached;
            }
        }

        return null;
    }

    /**
     * @param array<mixed> $authToken
     * @param string|null  $authUri
     * @return void
     */
    private function saveAuthTokenInCache($authToken, $authUri = null)
    {
        if (isset($authToken['access_token']) ||
            isset($authToken['id_token'])) {
            // if $authUri is set, use it as the cache key
            $cacheKey = $authUri
                ? $this->getFullCacheKey($authUri)
                : $this->fetcher->getCacheKey();

            $this->setCachedValue($cacheKey, $authToken);
        }
    }
}
