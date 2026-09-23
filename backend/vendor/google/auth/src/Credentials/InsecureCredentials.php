<?php
/*
 * Copyright 2018 Google Inc.
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

use Google\Auth\FetchAuthTokenInterface;

/**
 * Provides a set of credentials that will always return an empty access token.
 * This is useful for APIs which do not require authentication, for local
 * service emulators, and for testing.
 */
class InsecureCredentials implements FetchAuthTokenInterface
{
    /**
     * @var array{access_token:string}
     */
    private $token = [
        'access_token' => ''
    ];

    /**
     * Fetches the auth token. In this case it returns an empty string.
     *
     * @param callable $httpHandler
     * @return array{access_token:string} A set of auth related metadata
     */
    public function fetchAuthToken(callable $httpHandler = null)
    {
        return $this->token;
    }

    /**
     * Returns the cache key. In this case it returns a null value, disabling
     * caching.
     *
     * @return string|null
     */
    public function getCacheKey()
    {
        return null;
    }

    /**
     * Fetches the last received token. In this case, it returns the same empty string
     * auth token.
     *
     * @return array{access_token:string}
     */
    public function getLastReceivedToken()
    {
        return $this->token;
    }
}
