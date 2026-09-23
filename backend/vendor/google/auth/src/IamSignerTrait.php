<?php

/*
 * Copyright 2022 Google Inc.
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

use Exception;
use Google\Auth\HttpHandler\HttpClientCache;
use Google\Auth\HttpHandler\HttpHandlerFactory;

trait IamSignerTrait
{
    /**
     * @var Iam|null
     */
    private $iam;

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
     * @throws Exception
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
}
