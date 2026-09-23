<?php
/*
 * Copyright 2020 Google Inc.
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

use Google\Auth\Credentials\GCECredentials;
use Psr\Cache\CacheItemPoolInterface;

/**
 * A class to implement caching for calls to GCECredentials::onGce. This class
 * is used automatically when you pass a `Psr\Cache\CacheItemPoolInterface`
 * cache object to `ApplicationDefaultCredentials::getCredentials`.
 *
 * ```
 * $sysvCache = new Google\Auth\SysvCacheItemPool();
 * $creds = Google\Auth\ApplicationDefaultCredentials::getCredentials(
 *     $scope,
 *     null,
 *     null,
 *     $sysvCache
 * );
 * ```
 */
class GCECache
{
    const GCE_CACHE_KEY = 'google_auth_on_gce_cache';

    use CacheTrait;

    /**
     * @param array<mixed> $cacheConfig Configuration for the cache
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(
        array $cacheConfig = null,
        CacheItemPoolInterface $cache = null
    ) {
        $this->cache = $cache;
        $this->cacheConfig = array_merge([
            'lifetime' => 1500,
            'prefix' => '',
        ], (array) $cacheConfig);
    }

    /**
     * Caches the result of onGce so the metadata server is not called multiple
     * times.
     *
     * @param callable $httpHandler callback which delivers psr7 request
     * @return bool True if this a GCEInstance, false otherwise
     */
    public function onGce(callable $httpHandler = null)
    {
        if (is_null($this->cache)) {
            return GCECredentials::onGce($httpHandler);
        }

        $cacheKey = self::GCE_CACHE_KEY;
        $onGce = $this->getCachedValue($cacheKey);

        if (is_null($onGce)) {
            $onGce = GCECredentials::onGce($httpHandler);
            $this->setCachedValue($cacheKey, $onGce);
        }

        return $onGce;
    }
}
