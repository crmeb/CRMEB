<?php
/*
 * Copyright 2019 Google LLC
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

namespace Google\Auth\HttpHandler;

use GuzzleHttp\ClientInterface;

/**
 * Stores an HTTP Client in order to prevent multiple instantiations.
 */
class HttpClientCache
{
    /**
     * @var ClientInterface|null
     */
    private static $httpClient;

    /**
     * Cache an HTTP Client for later calls.
     *
     * Passing null will unset the cached client.
     *
     * @param ClientInterface|null $client
     * @return void
     */
    public static function setHttpClient(ClientInterface $client = null)
    {
        self::$httpClient = $client;
    }

    /**
     * Get the stored HTTP Client, or null.
     *
     * @return ClientInterface|null
     */
    public static function getHttpClient()
    {
        return self::$httpClient;
    }
}
